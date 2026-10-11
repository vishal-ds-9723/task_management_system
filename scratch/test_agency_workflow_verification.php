<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Media;
use App\Services\Strategist\TaskService as StrategistTaskService;
use App\Services\Designer\TaskService as DesignerTaskService;
use App\Services\Admin\ApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "=== STARTING AGENCY DOUBLE-CHECK WORKFLOW VERIFICATION ===\n\n";

// 1. Verify Users
$admin = User::where('role', 'admin')->first();
$strategist = User::where('role', 'strategist')->first() ?? $admin;
$designer = User::where('role', 'designer')->first();
$clientUser = User::where('role', 'client')->first();
$client = ($clientUser && $clientUser->client_id) ? Client::find($clientUser->client_id) : Client::first();

if (!$admin || !$designer || !$client) {
    echo "ERROR: Missing seed data (admin, designer, or client not found)\n";
    exit(1);
}

echo "1. Users confirmed:\n";
echo "   - Admin: {$admin->name} (ID: {$admin->id})\n";
echo "   - Strategist: {$strategist->name} (ID: {$strategist->id})\n";
echo "   - Designer: {$designer->name} (ID: {$designer->id})\n";
echo "   - Client: {$client->name} (ID: {$client->id})\n\n";

// 2. Test Task Creation with Upfront Copy (Strategist)
Auth::login($strategist);
$strategistService = app(StrategistTaskService::class);

$taskData = [
    'title' => 'Workflow Verification Test Post ' . time(),
    'client_id' => $client->id,
    'assigned_to' => $designer->id,
    'type' => 'post',
    'priority' => 'high',
    'status' => 'todo',
    'brief' => 'Detailed creative brief for summer campaign',
    'headline_copy' => '5 Rules to Master Agency Growth',
    'caption' => 'Scaling an agency requires tight systems between copy and visuals. Read more below! 👇',
    'hashtags' => '#agency #growth #marketing #design',
    'requires_client_approval' => true,
    'post_date' => now()->addDays(5)->toDateString(),
    'platform' => ['instagram', 'linkedin'],
];

$task = $strategistService->createTask($taskData);

assert(!empty($task->id), "Task must have an ID");
assert($task->headline_copy === '5 Rules to Master Agency Growth', "Headline copy must match");
assert($task->caption === 'Scaling an agency requires tight systems between copy and visuals. Read more below! 👇', "Caption must match");
assert($task->requires_client_approval === true, "requires_client_approval must be true");
assert($task->status === 'todo', "Task status must be todo");

echo "2. Task created with upfront copy & client sign-off flag: PASSED (Task ID: {$task->id})\n\n";

// 3. Test Designer Access & Parallel Queue (No 403 Lock)
Auth::login($designer);
$designerService = app(DesignerTaskService::class);

// Access show() - should NOT throw 403
$showData = $designerService->show($task);
$task->refresh();
assert(in_array($task->status, ['todo', 'inprogress']), "Status must be auto-started or todo");
echo "3. Designer unblocked & able to open task: PASSED (Status: {$task->status})\n\n";

// Attach dummy deliverable media to allow review submission
$media = Media::create([
    'model_type' => Task::class,
    'model_id' => $task->id,
    'collection_name' => 'task-media',
    'name' => 'test-creative.png',
    'file_name' => 'test-creative.png',
    'mime_type' => 'image/png',
    'disk' => 'public',
    'path' => 'task-media/test-creative.png',
    'size' => 1024,
    'type' => 'image',
    'metadata' => ['is_deliverable' => true],
]);

// Designer submits for review
$request = new \Illuminate\Http\Request();
$designerService->submitForReview($request, $task);
$task->refresh();
assert($task->status === 'review', "Task status must now be 'review'");
echo "4. Designer submitted deliverable -> Status: 'review': PASSED\n\n";

// 5. Strategist Tier 1 Review: Approves task -> transitions to 'pending_approval'
Auth::login($strategist);
$strategistController = app(\App\Http\Controllers\Strategist\TaskController::class);
$req = new \Illuminate\Http\Request();
$strategistController->approve($req, $task);
$task->refresh();
assert($task->status === 'pending_approval', "Task status must now be 'pending_approval'");
echo "5. Strategist Tier 1 Review Approved -> Status: 'pending_approval': PASSED\n\n";

// 6. Admin Tier 2 Double-Check: Request Targeted Copy Revision to Strategist
Auth::login($admin);
$approvalService = app(ApprovalService::class);

$approvalService->rejectTask([
    'revision_target' => 'strategist',
    'rejection_reason' => 'Please punch up the CTA in the caption.',
], $task);
$task->refresh();

assert($task->status === 'review', "Status for copy revision must stay 'review'");
assert($task->revision_target === 'strategist', "revision_target must be 'strategist'");
assert($task->isCopyRevision() === true, "isCopyRevision() helper must return true");
assert($task->revision_count === 0, "Visual revision_count must not increment for copy revision");
echo "6. Admin targeted copy revision to Strategist: PASSED\n";
echo "   - Status remains 'review', revision_target='strategist', Designer NOT disturbed.\n\n";

// 7. Strategist adjusts copy and re-approves for Admin
Auth::login($strategist);
$task->update([
    'caption' => 'Scaling an agency requires tight systems. Click the link in bio to learn how! 🚀',
]);
$strategistController->approve($req, $task);
$task->refresh();
assert($task->status === 'pending_approval', "Task status must be 'pending_approval'");
assert($task->revision_target === null, "revision_target must be cleared after strategist approval");
echo "7. Strategist updated copy and re-approved for Admin: PASSED\n\n";

// 8. Admin Tier 2 Final Approval with Client Sign-Off Gate
Auth::login($admin);
$approvalService->approveTask($task);
$task->refresh();
assert($task->status === 'client_review', "Task with requires_client_approval must transition to 'client_review'");
assert($task->admin_approval_status === 'approved', "Admin approval status must be approved");
echo "8. Admin approved -> Task gated in 'client_review': PASSED\n\n";

// 9. Client Sign-Off in Client Portal
if ($clientUser) {
    Auth::login($clientUser);
}
$clientController = app(\App\Http\Controllers\Client\DashboardController::class);
$clientController->approveTask($task);
$task->refresh();
assert($task->status === 'completed', "Task after client approval must be 'completed'");
assert(!empty($task->client_approved_at), "client_approved_at must be populated");
echo "9. Client signed off -> Task completed & ready for publishing: PASSED\n\n";

// 10. Test Blade View Compilation for all touched views
echo "10. Testing Blade view compilation:\n";

$viewsToTest = [
    'admin.approvals.show' => ['task' => $task],
    'admin.pending-approvals' => ['tasks' => collect([$task])],
    'designer.task-detail' => ['task' => $task, 'workflowSteps' => [], 'selectedSocialMediaLinks' => collect(), 'isReadOnly' => false],
    'designer.tasks' => ['tasks' => collect([$task]), 'clients' => collect([$client]), 'strategists' => collect([$strategist]), 'designers' => collect([$designer])],
    'client.tasks.show' => ['task' => $task, 'client' => $client],
    'strategist.create-task' => ['clients' => collect([$client]), 'users' => collect([$designer])],
    'strategist.task-detail' => ['task' => $task],
];

foreach ($viewsToTest as $viewName => $viewData) {
    try {
        $rendered = View::make($viewName, $viewData)->render();
        echo "   - View '{$viewName}': COMPILED & RENDERED OK (" . strlen($rendered) . " bytes)\n";
    } catch (\Throwable $e) {
        echo "   - ERROR rendering '{$viewName}': " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
}

// Cleanup test task & media
$media->delete();
$task->comments()->delete();
$task->delete();

echo "\n=== ALL 10 VERIFICATION STEPS PASSED PERFECTLY! ===\n";

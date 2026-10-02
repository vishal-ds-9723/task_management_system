{{-- 
    Mia — AI Assistant Floating Widget for Strategists
    Include this in any strategist page to show the floating assistant.
    Required variables: passed via @include or computed inline.
--}}
@php
    $user = auth()->user();
    $userId = $user->id;

    // ── Fetch data for assistant reminders ──
    $base = \App\Models\Task::query()->whereHas('creator', fn($q) => $q->where('role', 'strategist'));

    $avaOverdueCount = (clone $base)->where('status', '!=', 'completed')
        ->whereNotNull('deadline')
        ->whereDate('deadline', '<', today())->count();

    $avaPendingApprovals = (clone $base)->where('status', 'review')->count();

    $avaPendingAssign = (clone $base)->whereNull('assigned_to')
        ->where('status', '!=', 'completed')->count();

    $avaDueSoonCount = (clone $base)->where('status', '!=', 'completed')
        ->where(function ($q) {
            $q->whereBetween('deadline', [now(), now()->addHours(48)])
              ->orWhereBetween('post_date', [now(), now()->addHours(48)]);
        })->count();

    $avaStuckCount = (clone $base)->whereIn('status', ['inprogress', 'review', 'todo'])
        ->where('updated_at', '<=', now()->subDays(3))->count();

    $avaCompletedToday = (clone $base)->where('status', 'completed')
        ->whereDate('updated_at', today())->count();

    $avaTotalTasks = (clone $base)->count();
    $avaCompletedTasks = (clone $base)->where('status', 'completed')->count();
    $avaCompletionRate = $avaTotalTasks > 0 ? round(($avaCompletedTasks / $avaTotalTasks) * 100) : 0;

    $avaCreatedToday = (clone $base)->whereDate('created_at', today())->count();

    $avaMissingCaption = (clone $base)->where('status', '!=', 'completed')
        ->where(function ($q) { $q->whereNull('caption')->orWhere('caption', ''); })->count();

    $avaMissingAssign = (clone $base)->where('status', '!=', 'completed')
        ->whereNull('assigned_to')->count();

    $avaPublishingAwaiting = (clone $base)->where('status', 'completed')
        ->whereDoesntHave('socialMediaPosts')->count();

    // ── Build Reminders ──
    $avaReminders = collect();

    if($avaOverdueCount > 0) {
        $avaReminders->push([
            'icon' => '🚨', 'iconBg' => 'rgba(239,68,68,0.1)', 'iconColor' => 'var(--red)',
            'text' => "<strong>{$avaOverdueCount} task" . ($avaOverdueCount > 1 ? 's are' : ' is') . " overdue!</strong> Reassign or follow up.",
            'time' => 'Urgent', 'priority' => 0,
        ]);
    }

    if($avaPendingApprovals > 0) {
        $avaReminders->push([
            'icon' => '📋', 'iconBg' => 'rgba(139,92,246,0.1)', 'iconColor' => '#8B5CF6',
            'text' => "<strong>{$avaPendingApprovals} task" . ($avaPendingApprovals > 1 ? 's' : '') . "</strong> waiting for your review.",
            'time' => 'Action needed', 'priority' => 1,
        ]);
    }

    if($avaPendingAssign > 0) {
        $avaReminders->push([
            'icon' => '👤', 'iconBg' => 'rgba(245,158,11,0.1)', 'iconColor' => '#F59E0B',
            'text' => "<strong>{$avaPendingAssign} task" . ($avaPendingAssign > 1 ? 's' : '') . "</strong> not assigned to anyone yet.",
            'time' => 'Assign designers', 'priority' => 2,
        ]);
    }

    if($avaDueSoonCount > 0) {
        $avaReminders->push([
            'icon' => '⏰', 'iconBg' => 'rgba(59,130,246,0.1)', 'iconColor' => '#3B82F6',
            'text' => "<strong>{$avaDueSoonCount} task" . ($avaDueSoonCount > 1 ? 's' : '') . "</strong> due in the next 48 hours.",
            'time' => 'Due soon', 'priority' => 3,
        ]);
    }

    if($avaStuckCount > 0) {
        $avaReminders->push([
            'icon' => '🧊', 'iconBg' => 'rgba(107,114,128,0.1)', 'iconColor' => '#6B7280',
            'text' => "<strong>{$avaStuckCount} task" . ($avaStuckCount > 1 ? 's' : '') . "</strong> stuck with no updates for 3+ days.",
            'time' => 'Follow up', 'priority' => 4,
        ]);
    }

    if($avaMissingCaption > 3) {
        $avaReminders->push([
            'icon' => '✏️', 'iconBg' => 'rgba(234,179,8,0.1)', 'iconColor' => '#EAB308',
            'text' => "<strong>{$avaMissingCaption} tasks</strong> are missing captions. Add them for completeness.",
            'time' => 'Content gap', 'priority' => 5,
        ]);
    }

    if($avaPublishingAwaiting > 0) {
        $avaReminders->push([
            'icon' => '📢', 'iconBg' => 'rgba(16,185,129,0.1)', 'iconColor' => '#10B981',
            'text' => "<strong>{$avaPublishingAwaiting} completed task" . ($avaPublishingAwaiting > 1 ? 's' : '') . "</strong> awaiting publishing proof.",
            'time' => 'Post & verify', 'priority' => 6,
        ]);
    }

    $avaReminders = $avaReminders->sortBy('priority');
    $avaBadgeCount = $avaOverdueCount + $avaPendingApprovals + $avaPendingAssign;

    // ── Speech Bubbles ──
    $avaSpeeches = collect();

    if($avaOverdueCount > 0) {
        $avaSpeeches->push("🚨 <b>{$avaOverdueCount} overdue</b> task" . ($avaOverdueCount > 1 ? 's' : '') . "! Let's prioritize these first.");
    }
    if($avaPendingApprovals > 0) {
        $avaSpeeches->push("📋 <b>{$avaPendingApprovals}</b> task" . ($avaPendingApprovals > 1 ? 's' : '') . " waiting for your review. Don't keep designers waiting! 😊");
    }
    if($avaPendingAssign > 0) {
        $avaSpeeches->push("👤 <b>{$avaPendingAssign} unassigned</b> task" . ($avaPendingAssign > 1 ? 's' : '') . " — assign them to get work moving.");
    }
    if($avaDueSoonCount > 0 && $avaOverdueCount === 0) {
        $avaSpeeches->push("⏰ <b>{$avaDueSoonCount}</b> task" . ($avaDueSoonCount > 1 ? 's' : '') . " due in 48 hours. Stay ahead! 💪");
    }
    if($avaStuckCount > 0) {
        $avaSpeeches->push("🧊 <b>{$avaStuckCount}</b> task" . ($avaStuckCount > 1 ? 's' : '') . " haven't been updated in 3+ days. Time to follow up!");
    }
    if($avaCompletedToday > 0) {
        $avaSpeeches->push("✅ <b>{$avaCompletedToday}</b> task" . ($avaCompletedToday > 1 ? 's' : '') . " completed today. Great progress! 🎉");
    }
    if($avaCompletionRate > 70 && $avaOverdueCount === 0) {
        $avaSpeeches->push("🌟 <b>{$avaCompletionRate}% completion rate</b> — your team is crushing it! 🔥");
    }
    if($avaCreatedToday > 0) {
        $avaSpeeches->push("📝 You created <b>{$avaCreatedToday}</b> new task" . ($avaCreatedToday > 1 ? 's' : '') . " today. Keep planning ahead! 📅");
    }
    if($avaSpeeches->isEmpty()) {
        $avaSpeeches->push("✨ Everything looks good! No urgent items right now. You're on top of things! 😎");
    }
@endphp

<div class="ava-container" id="avaContainer">
    {{-- Speech Bubble --}}
    <div class="ava-bubble" id="avaBubble" onclick="toggleAvaPanel()">
        <button class="ava-bubble-dismiss" onclick="event.stopPropagation();dismissBubble()" title="Dismiss">&times;</button>
        <span id="avaBubbleText">{!! $avaSpeeches->first() !!}</span>
    </div>

    {{-- Reminder Panel --}}
    <div class="ava-panel" id="avaPanel">
        <div class="ava-panel-header">
            <div class="ava-panel-avatar"><img src="/images/assistant-girl.png" alt="Mia"></div>
            <div>
                <div class="ava-panel-name">Mia — Strategy Assistant</div>
                <div class="ava-panel-sub">Here's your action summary</div>
            </div>
            <button class="ava-panel-close" onclick="toggleAvaPanel()">&times;</button>
        </div>
        <div class="ava-panel-body">
            @if($avaReminders->count() > 0)
                @foreach($avaReminders as $r)
                <div class="ava-reminder">
                    <div class="ava-reminder-icon" style="background:{{ $r['iconBg'] }};color:{{ $r['iconColor'] }}">{{ $r['icon'] }}</div>
                    <div>
                        <div class="ava-reminder-text">{!! $r['text'] !!}</div>
                        @if($r['time'])<div class="ava-reminder-time">{{ $r['time'] }}</div>@endif
                    </div>
                </div>
                @endforeach
            @else
                <div class="ava-panel-empty">
                    ✨ All caught up! No pending items.<br>
                    <span style="font-size:11px;margin-top:6px;display:block">Great job, {{ $user->name }}!</span>
                </div>
            @endif
        </div>
        <div class="ava-panel-footer">
            <a href="{{ route('strategist.tracking') }}">📊 Tracking</a>
            <a href="{{ route('strategist.approvals') }}">📋 Approvals</a>
            <a href="{{ route('strategist.create-task') }}">➕ Create Task</a>
        </div>
    </div>

    {{-- Girl Avatar Trigger --}}
    <div class="ava-trigger" onclick="toggleAvaPanel()" title="Click for reminders">
        @if($avaBadgeCount > 0)
            <div class="ava-badge">{{ min($avaBadgeCount, 99) }}</div>
        @endif
        <div class="ava-img-wrap">
            <img src="/images/assistant-girl.png" alt="Mia — Assistant">
        </div>
    </div>
</div>

<style>
/* ── Floating Assistant (shared with designer) ── */
.ava-container {
    position:fixed;bottom:24px;right:24px;z-index:999;
    display:flex;flex-direction:column;align-items:flex-end;gap:10px;
}
.ava-trigger {
    position:relative;cursor:pointer;
    transition:transform 0.3s cubic-bezier(0.25,0.8,0.25,1);
}
.ava-trigger:hover { transform:scale(1.08); }
.ava-trigger:active { transform:scale(0.95); }
.ava-img-wrap {
    width:72px;height:72px;border-radius:50%;overflow:hidden;
    border:3px solid var(--primary);
    box-shadow:0 6px 28px rgba(0,0,0,0.15), 0 0 0 4px color-mix(in srgb, var(--primary) 15%, transparent);
    animation:avaFloat 4s ease-in-out infinite;
    background:var(--card);
}
.ava-img-wrap img { width:100%;height:100%;object-fit:cover;object-position:top center; }
@keyframes avaFloat {
    0%,100% { transform:translateY(0); }
    50% { transform:translateY(-6px); }
}
.ava-badge {
    position:absolute;top:-2px;right:-2px;
    min-width:20px;height:20px;border-radius:50%;
    background:var(--red);color:#fff;
    font-size:10px;font-weight:800;
    display:flex;align-items:center;justify-content:center;
    border:2px solid var(--card);
    animation:avaBadgePulse 2s ease-in-out infinite;
    padding:0 4px;
}
@keyframes avaBadgePulse {
    0%,100% { transform:scale(1); }
    50% { transform:scale(1.15); }
}

.ava-bubble {
    max-width:280px;padding:12px 16px;
    background:var(--card);border:1px solid var(--border);
    border-radius:16px 16px 4px 16px;
    box-shadow:0 6px 24px rgba(0,0,0,0.08);
    font-size:13px;line-height:1.5;color:var(--text);
    position:relative;cursor:pointer;
    animation:avaBubbleIn 0.4s cubic-bezier(0.25,0.8,0.25,1);
}
.ava-bubble::after {
    content:'';position:absolute;bottom:-6px;right:20px;
    width:12px;height:12px;background:var(--card);
    border-right:1px solid var(--border);
    border-bottom:1px solid var(--border);
    transform:rotate(45deg);
}
@keyframes avaBubbleIn {
    from { opacity:0;transform:translateY(8px) scale(0.95); }
    to { opacity:1;transform:translateY(0) scale(1); }
}
.ava-bubble-dismiss {
    position:absolute;top:4px;right:8px;
    font-size:14px;color:var(--text3);cursor:pointer;
    background:none;border:none;padding:2px;line-height:1;
}
.ava-bubble-dismiss:hover { color:var(--text); }

.ava-panel {
    position:absolute;bottom:90px;right:0;
    width:340px;background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:0 12px 40px rgba(0,0,0,0.12);
    overflow:hidden;display:none;
    animation:avaPanelIn 0.35s cubic-bezier(0.25,0.8,0.25,1);
}
.ava-panel.active { display:block; }
@keyframes avaPanelIn {
    from { opacity:0;transform:translateY(12px) scale(0.97); }
    to { opacity:1;transform:translateY(0) scale(1); }
}
.ava-panel-header {
    display:flex;align-items:center;gap:10px;
    padding:14px 16px;border-bottom:1px solid var(--border);
    background:var(--card2);
}
.ava-panel-avatar {
    width:32px;height:32px;border-radius:50%;overflow:hidden;
    border:2px solid var(--primary);flex-shrink:0;
}
.ava-panel-avatar img { width:100%;height:100%;object-fit:cover;object-position:top center; }
.ava-panel-name { font-size:13px;font-weight:700;color:var(--text); }
.ava-panel-sub { font-size:10.5px;color:var(--text3); }
.ava-panel-close {
    margin-left:auto;background:none;border:none;
    color:var(--text3);font-size:16px;cursor:pointer;padding:4px;
}
.ava-panel-close:hover { color:var(--text); }
.ava-panel-body { padding:12px 16px;max-height:300px;overflow-y:auto; }
.ava-reminder {
    display:flex;align-items:flex-start;gap:10px;
    padding:10px 0;border-bottom:1px solid var(--border);
}
.ava-reminder:last-child { border-bottom:none; }
.ava-reminder-icon {
    width:28px;height:28px;border-radius:8px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:12px;
}
.ava-reminder-text { font-size:12.5px;color:var(--text);line-height:1.5; }
.ava-reminder-text strong { font-weight:700; }
.ava-reminder-time { font-size:10px;color:var(--text3);margin-top:2px; }
.ava-panel-empty { padding:24px 16px;text-align:center;color:var(--text3);font-size:13px; }

.ava-panel-footer {
    display:flex;gap:0;border-top:1px solid var(--border);
}
.ava-panel-footer a {
    flex:1;padding:10px 8px;text-align:center;
    font-size:11px;font-weight:600;color:var(--text2);
    text-decoration:none;transition:all 0.15s;
    border-right:1px solid var(--border);
}
.ava-panel-footer a:last-child { border-right:none; }
.ava-panel-footer a:hover { background:var(--primary-dim);color:var(--primary); }

@media (max-width:768px) {
    .ava-container { bottom:12px;right:12px; }
    .ava-bubble { max-width:220px;font-size:12px; }
    .ava-panel { width:290px;bottom:90px; }
}
</style>

<script>
(function(){
    const speeches = @json($avaSpeeches->values());
    let speechIndex = 0;
    let bubbleDismissed = false;
    const bubble = document.getElementById('avaBubble');
    const bubbleText = document.getElementById('avaBubbleText');
    const panel = document.getElementById('avaPanel');

    if(speeches.length > 1) {
        setInterval(() => {
            if(bubbleDismissed || panel.classList.contains('active')) return;
            speechIndex = (speechIndex + 1) % speeches.length;
            bubble.style.animation = 'none';
            bubble.offsetHeight;
            bubble.style.animation = 'avaBubbleIn 0.4s cubic-bezier(0.25,0.8,0.25,1)';
            bubbleText.innerHTML = speeches[speechIndex];
        }, 6000);
    }

    window.toggleAvaPanel = function() {
        panel.classList.toggle('active');
        bubble.style.display = panel.classList.contains('active') ? 'none' : (bubbleDismissed ? 'none' : '');
    };

    window.dismissBubble = function() {
        bubbleDismissed = true;
        bubble.style.display = 'none';
    };

    document.addEventListener('click', function(e) {
        const container = document.getElementById('avaContainer');
        if(panel.classList.contains('active') && !container.contains(e.target)) {
            panel.classList.remove('active');
            if(!bubbleDismissed) bubble.style.display = '';
        }
    });

    setTimeout(() => { if(bubble) bubble.style.display = ''; }, 1500);
})();
</script>

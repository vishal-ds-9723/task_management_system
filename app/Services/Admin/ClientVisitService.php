<?php

namespace App\Services\Admin;

use App\Models\Client;
use App\Models\ClientVisit;
use App\Models\ClientVisitUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientVisitService
{
    public function getFilteredVisits(Request $request): array
    {
        $tab = $request->query('tab', 'all'); // all, lead, client, upcoming, follow_ups
        $status = $request->query('status', '');
        $search = $request->query('search', '');
        $visitorId = $request->query('visitor_id', '');
        $clientId = $request->query('client_id', '');
        $mode = $request->query('mode', '');
        $month = $request->query('month', '');
        $year = $request->query('year', '');
        $sort = $request->query('sort', 'newest'); // newest, oldest, visit_date_asc, visit_date_desc, follow_up

        $clients = Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'color', 'emoji', 'logo', 'contact_person', 'contact_email', 'contact_phone']);
        $teamMembers = User::whereIn('role', ['admin', 'strategist', 'manager', 'editor', 'developer', 'designer'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'avatar_color']);

        if (!\Illuminate\Support\Facades\Schema::hasTable('client_visits')) {
            $visits = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $totalVisits = 0;
            $leadVisitsCount = 0;
            $clientVisitsCount = 0;
            $upcomingCount = 0;
            $followUpsCount = 0;
            $convertedCount = 0;
            $completedCount = 0;
            $conversionRate = 0;

            return compact(
                'visits', 'tab', 'status', 'search', 'visitorId', 'clientId', 'mode', 'month', 'year', 'sort',
                'totalVisits', 'leadVisitsCount', 'clientVisitsCount', 'upcomingCount',
                'followUpsCount', 'convertedCount', 'completedCount', 'conversionRate',
                'clients', 'teamMembers'
            );
        }

        $query = ClientVisit::with(['client', 'visitor', 'creator', 'updates.user']);

        // Tab filtering
        if ($tab === 'lead') {
            $query->where('visit_type', 'lead');
        } elseif ($tab === 'client') {
            $query->where('visit_type', 'client');
        } elseif ($tab === 'upcoming') {
            $query->where('visit_date', '>=', now()->startOfDay())
                  ->whereIn('status', ['scheduled', 'follow_up_needed']);
        } elseif ($tab === 'follow_ups') {
            $query->where(function ($q) {
                $q->where('status', 'follow_up_needed')
                  ->orWhereNotNull('next_follow_up');
            });
        }

        // Direct filters
        if ($status) {
            $query->where('status', $status);
        }

        if ($visitorId) {
            $query->where('visited_by', $visitorId);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($mode) {
            $query->where('meeting_mode', $mode);
        }

        if ($month) {
            $query->whereMonth('visit_date', $month);
        }

        if ($year) {
            $query->whereYear('visit_date', $year);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('lead_name', 'like', "%{$search}%")
                  ->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        // Sorting
        $query = match ($sort) {
            'oldest'           => $query->oldest('id'),
            'visit_date_asc'   => $query->orderBy('visit_date', 'asc'),
            'visit_date_desc'  => $query->orderBy('visit_date', 'desc'),
            'follow_up'        => $query->orderByRaw('CASE WHEN next_follow_up IS NULL THEN 1 ELSE 0 END, next_follow_up ASC'),
            default            => $query->orderBy('visit_date', 'desc')->latest('id'),
        };

        $visits = $query->paginate(15)->withQueryString();

        // Overall statistics (for metric cards)
        $totalVisits = ClientVisit::count();
        $leadVisitsCount = ClientVisit::where('visit_type', 'lead')->count();
        $clientVisitsCount = ClientVisit::where('visit_type', 'client')->count();
        $upcomingCount = ClientVisit::where('visit_date', '>=', now()->startOfDay())
            ->whereIn('status', ['scheduled', 'follow_up_needed'])->count();
        $followUpsCount = ClientVisit::where('status', 'follow_up_needed')
            ->orWhere(function ($q) {
                $q->whereNotNull('next_follow_up')->where('next_follow_up', '>=', now()->startOfDay());
            })->count();
        $convertedCount = ClientVisit::where('status', 'converted')->count();
        $completedCount = ClientVisit::where('status', 'completed')->count();

        $conversionRate = $leadVisitsCount > 0
            ? round(($convertedCount / $leadVisitsCount) * 100)
            : 0;

        return compact(
            'visits', 'tab', 'status', 'search', 'visitorId', 'clientId', 'mode', 'month', 'year', 'sort',
            'totalVisits', 'leadVisitsCount', 'clientVisitsCount', 'upcomingCount',
            'followUpsCount', 'convertedCount', 'completedCount', 'conversionRate',
            'clients', 'teamMembers'
        );
    }

    public function createVisit(array $data, int $userId): ClientVisit
    {
        return DB::transaction(function () use ($data, $userId) {
            if (isset($data['attachment']) && $data['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                $data['attachment'] = $this->uploadAttachment($data['attachment']);
            }

            // Auto sync contact details from selected client if client visit
            if (($data['visit_type'] ?? 'client') === 'client' && !empty($data['client_id'])) {
                $client = Client::find($data['client_id']);
                if ($client) {
                    $data['company_name'] = $data['company_name'] ?? $client->name;
                    $data['contact_person'] = $data['contact_person'] ?? $client->contact_person;
                    $data['contact_email'] = $data['contact_email'] ?? $client->contact_email;
                    $data['contact_phone'] = $data['contact_phone'] ?? $client->contact_phone;
                }
            }

            $data['created_by'] = $userId;
            $data['visited_by'] = $data['visited_by'] ?? $userId;

            $visit = ClientVisit::create($data);

            // Log initial creation note
            ClientVisitUpdate::create([
                'client_visit_id' => $visit->id,
                'user_id'         => $userId,
                'update_type'     => 'note',
                'note'            => 'Visit created with status: ' . ucfirst(str_replace('_', ' ', $visit->status)) . ($visit->summary ? ' — ' . $visit->summary : ''),
                'status_to'       => $visit->status,
                'next_follow_up'  => $visit->next_follow_up,
                'attachment'      => $visit->attachment,
            ]);

            return $visit;
        });
    }

    public function updateVisit(ClientVisit $visit, array $data, int $userId): ClientVisit
    {
        return DB::transaction(function () use ($visit, $data, $userId) {
            $oldStatus = $visit->status;
            $oldFollowUp = $visit->next_follow_up;

            if (isset($data['attachment']) && $data['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                if ($visit->attachment) {
                    $this->deleteStoredFile($visit->attachment);
                }
                $data['attachment'] = $this->uploadAttachment($data['attachment']);
            } elseif (!empty($data['delete_attachment'])) {
                $this->deleteStoredFile($visit->attachment);
                $data['attachment'] = null;
            }

            if (($data['visit_type'] ?? $visit->visit_type) === 'client' && !empty($data['client_id'])) {
                $client = Client::find($data['client_id']);
                if ($client) {
                    $data['company_name'] = $data['company_name'] ?? $client->name;
                }
            }

            $visit->update($data);

            // If status or follow up changed, log an update entry
            if ($oldStatus !== $visit->status || ($oldFollowUp && $visit->next_follow_up && $oldFollowUp->format('Y-m-d') !== $visit->next_follow_up->format('Y-m-d'))) {
                ClientVisitUpdate::create([
                    'client_visit_id' => $visit->id,
                    'user_id'         => $userId,
                    'update_type'     => 'status_change',
                    'note'            => 'Visit details updated. Status: ' . ucfirst(str_replace('_', ' ', $visit->status)),
                    'status_from'     => $oldStatus,
                    'status_to'       => $visit->status,
                    'next_follow_up'  => $visit->next_follow_up,
                ]);
            }

            return $visit;
        });
    }

    public function deleteVisit(ClientVisit $visit): bool
    {
        if ($visit->attachment) {
            $this->deleteStoredFile($visit->attachment);
        }

        foreach ($visit->updates as $update) {
            if ($update->attachment) {
                $this->deleteStoredFile($update->attachment);
            }
        }

        $visit->delete();
        return true;
    }

    public function addVisitUpdate(ClientVisit $visit, array $data, int $userId): ClientVisitUpdate
    {
        return DB::transaction(function () use ($visit, $data, $userId) {
            $attachmentPath = null;
            if (isset($data['attachment']) && $data['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                $attachmentPath = $this->uploadAttachment($data['attachment']);
            }

            $oldStatus = $visit->status;
            $newStatus = !empty($data['status']) ? $data['status'] : $oldStatus;
            $nextFollowUp = !empty($data['next_follow_up']) ? $data['next_follow_up'] : $visit->next_follow_up;

            $update = ClientVisitUpdate::create([
                'client_visit_id' => $visit->id,
                'user_id'         => $userId,
                'update_type'     => $data['update_type'] ?? 'note',
                'note'            => $data['note'],
                'status_from'     => $oldStatus !== $newStatus ? $oldStatus : null,
                'status_to'       => $newStatus,
                'next_follow_up'  => $nextFollowUp,
                'attachment'      => $attachmentPath,
            ]);

            // Synchronize parent visit status and follow-up
            $visit->update([
                'status'         => $newStatus,
                'next_follow_up' => $nextFollowUp,
            ]);

            return $update;
        });
    }

    public function convertLeadToClient(ClientVisit $visit, array $clientData, int $userId): Client
    {
        return DB::transaction(function () use ($visit, $clientData, $userId) {
            $clientName = $clientData['name'] ?? ($visit->company_name ?: ($visit->lead_name ?: 'New Client'));
            
            $client = Client::create([
                'name'           => $clientName,
                'category'       => $clientData['category'] ?? 'General',
                'color'          => $clientData['color'] ?? '#4F6DF0',
                'emoji'          => $clientData['emoji'] ?? '🚀',
                'is_active'      => true,
                'contact_person' => $clientData['contact_person'] ?? ($visit->contact_person ?: $visit->lead_name),
                'contact_email'  => $clientData['contact_email'] ?? $visit->contact_email,
                'contact_phone'  => $clientData['contact_phone'] ?? $visit->contact_phone,
                'notes'          => 'Converted from Lead Visit on ' . now()->format('M d, Y') . '. Purpose: ' . $visit->purpose . ($visit->summary ? ' | Summary: ' . $visit->summary : ''),
            ]);

            // Update visit to point to this client and mark as converted
            $visit->update([
                'visit_type' => 'client',
                'client_id'  => $client->id,
                'status'     => 'converted',
            ]);

            // Log update
            ClientVisitUpdate::create([
                'client_visit_id' => $visit->id,
                'user_id'         => $userId,
                'update_type'     => 'status_change',
                'note'            => "🎉 Successfully converted lead into active client: \"{$client->name}\" (ID: {$client->id}).",
                'status_from'     => 'scheduled',
                'status_to'       => 'converted',
            ]);

            return $client;
        });
    }

    private function uploadAttachment(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $folder = 'client-visit-attachments';

        $publicDir = public_path('storage/' . $folder);
        $storageDir = storage_path($folder);

        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $file->move($publicDir, $filename);
        copy($publicDir . '/' . $filename, $storageDir . '/' . $filename);

        return $folder . '/' . $filename;
    }

    private function deleteStoredFile(?string $path): void
    {
        if (!$path) return;

        $oldPublic = public_path('storage/' . $path);
        $oldStorage = storage_path($path);

        if (file_exists($oldPublic)) @unlink($oldPublic);
        if (file_exists($oldStorage)) @unlink($oldStorage);
        Storage::disk('public')->delete($path);
    }
}

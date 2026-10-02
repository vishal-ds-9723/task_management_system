@extends('layouts.app')

@section('content')
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
        <a href="{{ route('admin.tasks.edit', $task) }}" style="font-size: 20px; color: var(--primary); text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 style="margin: 0; font-size: 28px; color: var(--text);">
                <i class="fas fa-history" style="margin-right: 10px;"></i>Audit History
            </h1>
            <p style="margin: 5px 0 0 0; color: var(--text2); font-size: 13px;">
                All changes made to "<strong>{{ $task->title ?? 'Untitled' }}</strong>"
            </p>
        </div>
    </div>

    <!-- Task Info Card -->
    <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; margin-bottom: 25px;">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <div>
                <p style="margin: 0 0 5px 0; color: var(--text3); font-size: 12px; font-weight: 500; text-transform: uppercase;">Client</p>
                <p style="margin: 0; color: var(--text); font-weight: 500;">{{ $task->client?->name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p style="margin: 0 0 5px 0; color: var(--text3); font-size: 12px; font-weight: 500; text-transform: uppercase;">Status</p>
                <p style="margin: 0; color: var(--text); font-weight: 500;">{{ ucfirst($task->status) }}</p>
            </div>
            <div>
                <p style="margin: 0 0 5px 0; color: var(--text3); font-size: 12px; font-weight: 500; text-transform: uppercase;">Assigned To</p>
                <p style="margin: 0; color: var(--text); font-weight: 500;">{{ $task->assignee?->name ?? 'Unassigned' }}</p>
            </div>
            <div>
                <p style="margin: 0 0 5px 0; color: var(--text3); font-size: 12px; font-weight: 500; text-transform: uppercase;">Created By</p>
                <p style="margin: 0; color: var(--text); font-weight: 500;">{{ $task->creator?->name ?? 'Unknown' }}</p>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div style="display: flex; flex-direction: column; gap: 0;">
        @forelse($logs as $index => $log)
            <div style="display: flex; gap: 20px; padding: 20px; border: 1px solid var(--border); border-top: none; background: {{ $index % 2 === 0 ? 'white' : 'var(--card2)' }};">
                <!-- Timeline dot and line -->
                <div style="display: flex; flex-direction: column; align-items: center; width: 40px;">
                    @php
                        $actionIcon = 'fa-edit';
                        $actionColor = '#3B82F6';
                        if (str_contains($log->action, 'Created')) {
                            $actionIcon = 'fa-plus-circle';
                            $actionColor = '#10B981';
                        } elseif (str_contains($log->action, 'Deleted')) {
                            $actionIcon = 'fa-trash';
                            $actionColor = '#EF4444';
                        } elseif (str_contains($log->action, 'Cloned')) {
                            $actionIcon = 'fa-clone';
                            $actionColor = '#8B5CF6';
                        } elseif (str_contains($log->action, 'status')) {
                            $actionIcon = 'fa-exchange-alt';
                            $actionColor = '#F59E0B';
                        }
                    @endphp
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $actionColor }}20; display: flex; align-items: center; justify-content: center; color: {{ $actionColor }};">
                        <i class="fas {{ $actionIcon }}"></i>
                    </div>
                    @if(!$loop->last)
                        <div style="width: 2px; height: 60px; background: var(--border); margin-top: 10px;"></div>
                    @endif
                </div>

                <!-- Content -->
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div>
                            <h3 style="margin: 0 0 5px 0; font-size: 16px; color: var(--text); font-weight: 600;">
                                {{ $log->action }}
                            </h3>
                            <p style="margin: 0; font-size: 13px; color: var(--text2);">
                                <strong>{{ $log->user?->name ?? 'System' }}</strong>
                                <span style="color: var(--text3);">•</span>
                                <span style="color: var(--text3);">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                            </p>
                        </div>
                        <button onclick="toggleDetails(this, {{ $log->id }})" 
                            style="padding: 6px 12px; background: var(--card2); border: 1px solid var(--border); border-radius: 4px; color: var(--primary); cursor: pointer; font-weight: 500; font-size: 12px;">
                            <i class="fas fa-chevron-down"></i> Details
                        </button>
                    </div>

                    <!-- Details Section (Hidden by default) -->
                    <div id="details-{{ $log->id }}" style="display: none; margin-top: 15px; padding: 15px; background: var(--card2); border-radius: 8px; border-left: 4px solid {{ $actionColor }};">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <p style="margin: 0 0 5px 0; color: var(--text3); font-size: 11px; font-weight: 500; text-transform: uppercase;">User Email</p>
                                <p style="margin: 0; color: var(--text); font-size: 13px;">{{ $log->user?->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p style="margin: 0 0 5px 0; color: var(--text3); font-size: 11px; font-weight: 500; text-transform: uppercase;">IP Address</p>
                                <p style="margin: 0; color: var(--text); font-size: 13px; font-family: monospace;">{{ $log->ip_address ?? 'Unknown' }}</p>
                            </div>
                        </div>

                        @if($log->old_values && is_array($log->old_values) && count($log->old_values) > 0)
                            <div style="margin-bottom: 15px;">
                                <p style="margin: 0 0 8px 0; color: var(--text3); font-size: 11px; font-weight: 500; text-transform: uppercase;">Previous Values</p>
                                <div style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid var(--red); font-size: 12px; color: var(--text);">
                                    @foreach($log->old_values as $key => $value)
                                        <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid var(--border);">
                                            <strong style="color: var(--text3);">{{ $key }}:</strong>
                                            <span style="font-family: monospace; color: var(--red);">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($log->new_values && is_array($log->new_values) && count($log->new_values) > 0)
                            <div>
                                <p style="margin: 0 0 8px 0; color: var(--text3); font-size: 11px; font-weight: 500; text-transform: uppercase;">New Values</p>
                                <div style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid var(--teal); font-size: 12px; color: var(--text);">
                                    @foreach($log->new_values as $key => $value)
                                        <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid var(--border);">
                                            <strong style="color: var(--text3);">{{ $key }}:</strong>
                                            <span style="font-family: monospace; color: var(--teal);">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div style="padding: 40px; text-align: center; background: white; border: 1px solid var(--border); border-radius: var(--radius);">
                <i class="fas fa-inbox" style="font-size: 32px; color: var(--text3); margin-bottom: 10px; display: block;"></i>
                <p style="margin: 0; color: var(--text3); font-size: 14px;">No audit history for this task</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($logs->lastPage() > 1)
        <div style="margin-top: 20px; display: flex; align-items: center; justify-content: center;">
            {{ $logs->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>

<script>
    function toggleDetails(button, logId) {
        const details = document.getElementById(`details-${logId}`);
        const icon = button.querySelector('i');
        
        if (details.style.display === 'none') {
            details.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            button.style.background = 'var(--primary)';
            button.style.color = 'white';
        } else {
            details.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            button.style.background = 'var(--card2)';
            button.style.color = 'var(--primary)';
        }
    }
</script>

@endsection

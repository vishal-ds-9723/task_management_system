@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')

@php
    $onlineUsers = $users->filter(fn($u) => $u->isOnline());
    $offlineUsers = $users->reject(fn($u) => $u->isOnline());
@endphp

<div class="au-header">
    <div>
        <div class="au-title"><i class="fa-solid fa-satellite-dish" style="color:var(--teal);margin-right:6px"></i> Active Users</div>
        <div class="au-subtitle">{{ $onlineUsers->count() }} online now · {{ $users->count() }} total users</div>
    </div>
    <button onclick="location.reload()" class="au-refresh-btn"><i class="fa-solid fa-arrows-rotate"></i> Refresh</button>
</div>

{{-- Online Users --}}
<div class="au-section-label"><span class="au-dot au-dot-online"></span> Online Now ({{ $onlineUsers->count() }})</div>
<div class="au-table-card">
    <table class="au-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Current Page</th>
                <th>Last Activity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($onlineUsers as $user)
                <tr>
                    <td>
                        <div class="au-user-cell">
                            <div class="av-sm" style="background:{{ $user->avatar_color ?? '#555' }}">{{ $user->initial }}</div>
                            <div>
                                <div class="au-user-name">{{ $user->name }}</div>
                                <div class="au-user-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->role === 'admin')
                            <span class="au-role au-role-admin">Admin</span>
                        @elseif($user->role === 'strategist')
                            <span class="au-role au-role-strategist">Strategist</span>
                        @else
                            <span class="au-role au-role-designer">Designer</span>
                        @endif
                    </td>
                    <td>
                        <div class="au-page">
                            <i class="fa-solid fa-globe" style="color:var(--text3);font-size:10px"></i>
                            <span>/{{ $user->current_url }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="au-time">{{ $user->last_active_at->diffForHumans() }}</span>
                    </td>
                    <td><span class="au-status au-status-online"><i class="fa-solid fa-circle"></i> Online</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:24px;color:var(--text3);font-size:12.5px">No users online right now</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Offline Users --}}
<div class="au-section-label" style="margin-top:20px"><span class="au-dot au-dot-offline"></span> Offline ({{ $offlineUsers->count() }})</div>
<div class="au-table-card">
    <table class="au-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Last Page</th>
                <th>Last Seen</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($offlineUsers as $user)
                <tr class="au-row-offline">
                    <td>
                        <div class="au-user-cell">
                            <div class="av-sm" style="background:{{ $user->avatar_color ?? '#555' }};opacity:0.5">{{ $user->initial }}</div>
                            <div>
                                <div class="au-user-name" style="color:var(--text3)">{{ $user->name }}</div>
                                <div class="au-user-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->role === 'admin')
                            <span class="au-role au-role-admin" style="opacity:0.5">Admin</span>
                        @elseif($user->role === 'strategist')
                            <span class="au-role au-role-strategist" style="opacity:0.5">Strategist</span>
                        @else
                            <span class="au-role au-role-designer" style="opacity:0.5">Designer</span>
                        @endif
                    </td>
                    <td>
                        @if($user->current_url)
                            <div class="au-page" style="color:var(--text3)">
                                <i class="fa-solid fa-globe" style="font-size:10px"></i>
                                <span>/{{ $user->current_url }}</span>
                            </div>
                        @else
                            <span style="color:var(--text3);font-size:11px">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="au-time">{{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}</span>
                    </td>
                    <td><span class="au-status au-status-offline"><i class="fa-solid fa-circle"></i> Offline</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:24px;color:var(--text3);font-size:12.5px">All users are online!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
.au-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:16px; flex-wrap:wrap; }
.au-title { font-size:22px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-0.3px; }
.au-subtitle { font-size:12.5px; color:var(--text3); margin-top:3px; font-weight:500; }
.au-refresh-btn { padding:9px 18px; border-radius:10px; font-size:12.5px; font-weight:600; display:inline-flex; align-items:center; gap:6px; background:var(--card); color:var(--text2); border:1px solid var(--border); cursor:pointer; transition:all 0.2s ease; font-family:inherit; }
.au-refresh-btn:hover { background:var(--card2); color:var(--text); transform:translateY(-1px); }

.au-section-label { font-size:11px; font-weight:700; color:var(--text2); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
.au-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
.au-dot-online { background:#10B981; box-shadow:0 0 6px rgba(16,185,129,0.5); }
.au-dot-offline { background:var(--text3); }

.au-table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); overflow:hidden; margin-bottom:8px; }
.au-table { width:100%; border-collapse:collapse; }
.au-table thead th { text-align:left; padding:11px 16px; font-size:10.5px; color:var(--text3); letter-spacing:0.5px; text-transform:uppercase; font-weight:700; background:var(--card2); border-bottom:1px solid var(--border); white-space:nowrap; }
.au-table tbody tr { border-bottom:1px solid var(--border); transition:all 0.15s ease; }
.au-table tbody tr:last-child { border-bottom:none; }
.au-table tbody tr:hover { background:var(--primary-dim); }
.au-table tbody td { padding:12px 16px; font-size:12.5px; color:var(--text2); vertical-align:middle; }
.au-row-offline { opacity:0.7; }
.au-row-offline:hover { opacity:1; }

.au-user-cell { display:flex; align-items:center; gap:10px; }
.au-user-name { font-size:13px; font-weight:600; color:var(--text); line-height:1.2; }
.au-user-email { font-size:10.5px; color:var(--text3); margin-top:1px; }

.au-role { font-size:10px; font-weight:700; padding:3px 10px; border-radius:6px; text-transform:uppercase; letter-spacing:0.3px; }
.au-role-admin { background:var(--primary-dim); color:var(--primary); }
.au-role-strategist { background:var(--blue-dim); color:var(--blue); }
.au-role-designer { background:var(--teal-dim); color:var(--teal); }

.au-page { display:flex; align-items:center; gap:5px; font-size:11.5px; color:var(--text2); font-family:'Courier New',monospace; background:var(--card2); padding:4px 10px; border-radius:6px; border:1px solid var(--border); max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.au-time { font-size:11.5px; color:var(--text3); font-weight:500; }

.au-status { font-size:10.5px; font-weight:600; display:inline-flex; align-items:center; gap:5px; }
.au-status i { font-size:7px; }
.au-status-online { color:#10B981; }
.au-status-online i { animation:pulse-dot 2s ease-in-out infinite; }
.au-status-offline { color:var(--text3); }

@keyframes pulse-dot {
    0%, 100% { opacity:1; }
    50% { opacity:0.3; }
}

@media (max-width:768px) {
    .au-header { flex-direction:column; align-items:flex-start; }
    .au-table thead th { font-size:9.5px; padding:10px 10px; }
    .au-table tbody td { padding:10px 10px; font-size:11.5px; }
    .au-page { max-width:150px; }
}
</style>

<script>
// Auto-refresh every 30 seconds
setTimeout(function() { location.reload(); }, 30000);
</script>
@endsection

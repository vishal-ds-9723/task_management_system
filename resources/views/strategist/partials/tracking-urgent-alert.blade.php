@if(($scope ?? 'all') === 'my' && ($systemUrgentCount ?? 0) > 0)
    <div class="trk-urgent-alert-banner" style="display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(249,115,22,0.08));border:1.5px solid rgba(239,68,68,0.35);border-radius:12px;padding:12px 18px;margin-bottom:16px;box-shadow:0 4px 15px rgba(239,68,68,0.08);flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:36px;height:36px;border-radius:10px;background:#EF4444;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 4px 10px rgba(239,68,68,0.35);flex-shrink:0">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <div style="font-size:13.5px;font-weight:800;color:var(--text)">
                    ⚡ {{ $systemUrgentCount }} Urgent Designer-Initiated Task{{ $systemUrgentCount > 1 ? 's' : '' }} Active
                </div>
                <div style="font-size:11.5px;color:var(--text2);margin-top:2px">
                    A designer started an emergency task that is not in your personal "My Created" view.
                </div>
            </div>
        </div>
        <button type="button" onclick="setTrackingScope('all'); filterByStatus('urgent');" class="trk-header-btn trk-btn-primary" style="background:#EF4444;font-size:11.5px;padding:7px 14px;border:none;box-shadow:0 2px 8px rgba(239,68,68,0.3);cursor:pointer">
            <i class="fa-solid fa-bolt"></i> View Urgent Tasks
        </button>
    </div>
@endif

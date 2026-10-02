{{-- Assign Modal for Festival Selection --}}
<div id="assignModal" class="assign-modal">
    <div class="assign-box">

        <h3>Assign Task to Strategist</h3>

        <form id="assignForm" method="POST" action="">
            @csrf

            <input type="hidden" id="selection_id" name="selection_id">

            <label>Select Strategist <span style="color:#EF4444">*</span></label>
            <select name="assigned_to" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:13px;margin-bottom:14px">
                <option value="">-- Choose a Strategist --</option>
                @foreach($strategists ?? [] as $strategist)
                    <option value="{{ $strategist->id }}">
                        {{ $strategist->name }}
                    </option>
                @endforeach
            </select>

            <label>Deadline <span style="color:#EF4444">*</span></label>
            <input type="date" name="deadline" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:13px;margin-bottom:18px">

            <div class="assign-actions" style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn-sec" onclick="closeAssignModal()">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Assign Task
                </button>
            </div>

        </form>

    </div>
</div>

<style>
    /* Assign Modal Styles */
    .assign-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .assign-box {
        background: white;
        border-radius: 14px;
        padding: 28px;
        width: 90%;
        max-width: 450px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .assign-box h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 20px;
    }

    .assign-box label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text2);
        margin-bottom: 8px;
    }

    .assign-box select,
    .assign-box input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 14px;
        font-family: inherit;
    }

    .assign-box select:focus,
    .assign-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-dim);
    }

    .assign-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }
</style>

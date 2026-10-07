{{-- Reusable Self-Task Creation Modal for Team Members (Designers, Developers, etc.) --}}
@php
    $modalClients = \App\Models\Client::where('is_active', true)->orderBy('name')->get();
@endphp

<div id="selfTaskModal" class="self-task-modal-backdrop" style="display:none;" aria-hidden="true">
    <div class="self-task-modal-dialog">
        <div class="self-task-modal-header">
            <div class="self-task-modal-title">
                <i class="fas fa-plus-circle" style="color:var(--primary, #4F6DF0)"></i>
                <span>Create New Task</span>
            </div>
            <button type="button" class="self-task-modal-close" onclick="closeSelfTaskModal()" title="Close">&times;</button>
        </div>

        <form id="selfTaskModalForm" onsubmit="handleSelfTaskSubmit(event)">
            @csrf
            <div class="self-task-modal-body">
                <div class="stm-alert-error" id="stmErrorAlert" style="display:none;"></div>

                {{-- Task Title --}}
                <div class="stm-form-group">
                    <label for="stmTitle" class="stm-label">Task Title <span class="stm-required">*</span></label>
                    <input type="text" id="stmTitle" name="title" class="stm-input" placeholder="e.g. Logo Design, Cloud Server Maintenance, Header UI redesign..." required maxlength="255">
                </div>

                {{-- Client & Category Grid --}}
                <div class="stm-form-row">
                    <div class="stm-form-group">
                        <label for="stmClientId" class="stm-label">Client / Entity</label>
                        <select id="stmClientId" name="client_id" class="stm-input">
                            <option value="">🏢 Internal / Agency Office</option>
                            @foreach($modalClients as $c)
                                <option value="{{ $c->id }}">{{ $c->emoji ?? '💼' }} {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="stm-form-group">
                        <label for="stmType" class="stm-label">Task Category <span class="stm-required">*</span></label>
                        <select id="stmType" name="type" class="stm-input" required>
                            <optgroup label="🎨 Creative & Graphics Design">
                                <option value="logo">🎨 Logo Design</option>
                                <option value="product_design">📱 Product Design / UI-UX</option>
                                <option value="branding">🏷️ Branding & Identity</option>
                                <option value="banner">🖼️ Banner / Display Ad</option>
                                <option value="flyer">📄 Flyer / Poster</option>
                                <option value="brochure">📖 Brochure / Print</option>
                                <option value="reel">🎬 Reel / Short Video</option>
                                <option value="post" selected>📌 Social Media Post</option>
                                <option value="video">🎥 Video Production</option>
                            </optgroup>
                            <optgroup label="💻 Web & Technical">
                                <option value="website">🌐 Website Development</option>
                                <option value="software">💻 Software / App Development</option>
                                <option value="server_maintenance">🖥️ Server Maintenance / DevOps</option>
                                <option value="maintenance">⚙️ Website Maintenance</option>
                            </optgroup>
                            <optgroup label="📋 Other">
                                <option value="others">📝 General / Other</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                {{-- Priority, Status & Deadline Grid --}}
                <div class="stm-form-row-3">
                    <div class="stm-form-group">
                        <label for="stmPriority" class="stm-label">Priority</label>
                        <select id="stmPriority" name="priority" class="stm-input">
                            <option value="normal" selected>Normal</option>
                            <option value="high">🟠 High</option>
                            <option value="urgent">🔴 Urgent</option>
                        </select>
                    </div>

                    <div class="stm-form-group">
                        <label for="stmStatus" class="stm-label">Initial Status</label>
                        <select id="stmStatus" name="status" class="stm-input">
                            <option value="inprogress" selected>⚡ In Progress (Working now)</option>
                            <option value="todo">📋 To Do (Queue)</option>
                            <option value="review">🔍 In Review</option>
                            <option value="completed">✅ Completed</option>
                        </select>
                    </div>

                    <div class="stm-form-group">
                        <label for="stmDeadline" class="stm-label">Deadline</label>
                        <input type="date" id="stmDeadline" name="deadline" class="stm-input" value="{{ today()->toDateString() }}">
                    </div>
                </div>

                {{-- Brief / Notes --}}
                <div class="stm-form-group">
                    <label for="stmBrief" class="stm-label">Task Details / Brief <span class="stm-subtext">(Optional)</span></label>
                    <textarea id="stmBrief" name="brief" class="stm-input" rows="2" placeholder="Key objectives, specs, server credentials note, or deliverables..."></textarea>
                </div>
            </div>

            <div class="self-task-modal-footer">
                <button type="button" class="stm-btn stm-btn-secondary" onclick="closeSelfTaskModal()">Cancel</button>
                <button type="submit" class="stm-btn stm-btn-primary" id="stmSubmitBtn">
                    <i class="fas fa-check"></i>
                    <span>Create &amp; Assign to Me</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.self-task-modal-backdrop {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease-in-out;
}
.self-task-modal-backdrop.is-open {
    opacity: 1;
    pointer-events: auto;
}
.self-task-modal-dialog {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transform: scale(0.96);
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    font-family: inherit;
}
.self-task-modal-backdrop.is-open .self-task-modal-dialog {
    transform: scale(1);
}
.self-task-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafafa;
}
.self-task-modal-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.self-task-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 8px;
    transition: color 0.15s, background 0.15s;
}
.self-task-modal-close:hover {
    color: #0f172a;
    background: #e2e8f0;
}
.self-task-modal-body {
    padding: 20px 24px;
    max-height: calc(85vh - 140px);
    overflow-y: auto;
}
.stm-form-group {
    margin-bottom: 16px;
}
.stm-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.stm-form-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
}
@media (max-width: 600px) {
    .stm-form-row, .stm-form-row-3 {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}
.stm-label {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}
.stm-required { color: #ef4444; }
.stm-subtext { font-size: 11px; color: #94a3b8; font-weight: 500; }
.stm-input {
    width: 100%;
    height: 38px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 0 12px;
    font: inherit;
    font-size: 13.5px;
    color: #0f172a;
    background: #ffffff;
    box-sizing: border-box;
    transition: border-color 0.15s, box-shadow 0.15s;
}
textarea.stm-input {
    height: auto;
    padding: 10px 12px;
    resize: vertical;
}
.stm-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}
.stm-alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 14px;
}
.self-task-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 24px;
    border-top: 1px solid #f1f5f9;
    background: #fafafa;
}
.stm-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 38px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    transition: background 0.15s, transform 0.1s;
}
.stm-btn-secondary {
    background: #f1f5f9;
    color: #475569;
}
.stm-btn-secondary:hover { background: #e2e8f0; }
.stm-btn-primary {
    background: #4f46e5;
    color: #ffffff;
}
.stm-btn-primary:hover { background: #4338ca; }
.stm-btn:active { transform: scale(0.98); }
</style>

<script>
window._selfTaskCallback = null;

function openSelfTaskModal(options = {}) {
    const sidebar = document.getElementById('appSidebar');
    if (sidebar && sidebar.classList.contains('mobile-open')) {
        sidebar.classList.remove('mobile-open');
    }

    const modal = document.getElementById('selfTaskModal');
    if (!modal) return;
    const form = document.getElementById('selfTaskModalForm');
    if (form) form.reset();

    const err = document.getElementById('stmErrorAlert');
    if (err) { err.style.display = 'none'; err.textContent = ''; }

    const deadlineInput = document.getElementById('stmDeadline');
    if (deadlineInput && !deadlineInput.value) {
        deadlineInput.value = new Date().toISOString().split('T')[0];
    }

    if (options.defaultType) {
        const typeSelect = document.getElementById('stmType');
        if (typeSelect) typeSelect.value = options.defaultType;
    }
    if (options.defaultStatus) {
        const statusSelect = document.getElementById('stmStatus');
        if (statusSelect) statusSelect.value = options.defaultStatus;
    }
    if (options.callback) {
        window._selfTaskCallback = options.callback;
    } else {
        window._selfTaskCallback = null;
    }

    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.classList.add('is-open');
        const titleInput = document.getElementById('stmTitle');
        if (titleInput) titleInput.focus();
    });
}

function closeSelfTaskModal() {
    const modal = document.getElementById('selfTaskModal');
    if (!modal) return;
    modal.classList.remove('is-open');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 200);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('selfTaskModal');
        if (modal && modal.classList.contains('is-open')) closeSelfTaskModal();
    }
});

async function handleSelfTaskSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('stmSubmitBtn');
    const err = document.getElementById('stmErrorAlert');
    if (err) { err.style.display = 'none'; err.textContent = ''; }

    const originalBtnHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("employee.tasks.self-create") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Failed to create task.');
            throw new Error(msg);
        }

        const task = data.task;

        // Dispatch custom global event
        document.dispatchEvent(new CustomEvent('self-task:created', { detail: task }));

        if (window._selfTaskCallback && typeof window._selfTaskCallback === 'function') {
            window._selfTaskCallback(task);
        }

        closeSelfTaskModal();

        // If on daily log page, announce and add to select dropdowns
        if (typeof window.onSelfTaskCreatedInDailyLog === 'function') {
            window.onSelfTaskCreatedInDailyLog(task);
        } else if (window.location.pathname.includes('/tasks')) {
            // If on tasks board, reload page to reflect new task on board
            window.location.reload();
        } else {
            // General notification
            if (window.showToast) {
                window.showToast(`Task "${task.title}" created successfully!`, 'success');
            } else {
                alert(`Task "${task.title}" created and assigned to you.`);
            }
        }
    } catch (error) {
        if (err) {
            err.textContent = error.message;
            err.style.display = 'block';
        } else {
            alert(error.message);
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
    }
}
</script>

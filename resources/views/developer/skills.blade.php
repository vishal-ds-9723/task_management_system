@extends('layouts.app')

@section('content')

<style>
.skills-wrap {
    --skills-theme: #f13535;
    --skills-theme-dark: #c92323;
    --skills-theme-soft: #fee2e2;
    --skills-theme-soft-2: #ffe6e6;
    --skills-border: #fecaca;
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px;
    color: #1f2937;
}

.skills-head {
    margin-bottom: 22px;
}

.skills-head h1 {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 800;
    color: #111111;
    display: flex;
    align-items: center;
    gap: 10px;
}

.skills-head p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.skills-alert {
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 13px;
    font-weight: 600;
}

.skills-alert.success {
    background: #ecfdf3;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.skills-alert.error {
    background: #fff1f1;
    color: #b91c1c;
    border: 1px solid var(--skills-border);
}

.skills-alert ul {
    margin: 0;
    padding-left: 18px;
}

.skills-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.skills-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 1px solid #f1f5f9;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 10px 26px rgba(0, 0, 0, 0.04);
}

.skills-card h2 {
    margin: 0 0 14px;
    font-size: 17px;
    font-weight: 800;
    color: #111827;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 13px;
    background: #fff;
    color: #111827;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--skills-theme);
    box-shadow: 0 0 0 3px rgba(241, 53, 53, 0.12);
}

.btn-save {
    margin-top: 14px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--skills-theme), var(--skills-theme-dark));
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 11px 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 8px 18px rgba(241, 53, 53, 0.24);
}

.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(241, 53, 53, 0.3);
}

.skill-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 8px 8px 8px 12px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid transparent;
}

.skill-chip.primary {
    background: var(--skills-theme-soft);
    color: var(--skills-theme-dark);
    border-color: var(--skills-border);
}

.skill-chip.secondary {
    background: var(--skills-theme-soft-2);
    color: #b91c1c;
    border-color: #fca5a5;
}

.chip-type {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 2px 6px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.65);
}

.chip-delete-form {
    display: inline-flex;
}

.chip-delete {
    width: 24px;
    height: 24px;
    border: 0;
    border-radius: 50%;
    background: rgba(185, 28, 28, 0.12);
    color: #991b1b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.chip-delete:hover {
    background: rgba(185, 28, 28, 0.2);
}

.empty-state {
    background: #fff7f7;
    border: 1px dashed var(--skills-border);
    border-radius: 12px;
    padding: 20px;
    color: #991b1b;
    font-size: 13px;
    font-weight: 600;
}

@media (max-width: 900px) {
    .skills-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .skills-wrap {
        padding: 16px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}

</style>

<div class="skills-wrap">

    <div class="skills-head">
        <h1><i class="fa-solid fa-bolt"></i> My Skills</h1>
        <p>Add your strongest skills and keep your developer profile up to date.</p>
    </div>

    @if(session('success'))
        <div class="skills-alert success">{{ session('success') }}</div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="skills-alert error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="skills-grid">
        <div class="skills-card">
            <h2>Add New Skill</h2>

            <form method="POST" action="{{ route('developer.skills.store') }}">
                @csrf
                <div class="form-grid">

                    <div class="form-group">
                        <label for="skillName">Skill Name</label>
                        <input id="skillName" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Laravel" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="skillType">Skill Type</label>
                        <select id="skillType" name="type" required>
                            <option value="primary" {{ old('type') === 'primary' ? 'selected' : '' }}>Primary</option>
                            <option value="secondary" {{ old('type', 'secondary') === 'secondary' ? 'selected' : '' }}>Secondary</option>
                        </select>
                    </div>

                </div>

                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-plus"></i> Add Skill
                </button>
            </form>

        </div>

        <div class="skills-card">
            <h2>Your Skills</h2>

            @if(($skills ?? collect())->isEmpty())
                <div class="empty-state">
                    No skills added yet. Add your first skill from the form.
                </div>
            @else
                <div class="skill-list">
                    @foreach($skills as $skill)
                        <div class="skill-chip {{ $skill->type }}">
                            <span class="chip-name">{{ $skill->name }}</span>
                            <span class="chip-type">{{ ucfirst($skill->type) }}</span>
                            <form class="chip-delete-form" method="POST" action="{{ route('developer.skills.delete', $skill) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="chip-delete" aria-label="Remove {{ $skill->name }}" title="Remove skill">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

@endsection

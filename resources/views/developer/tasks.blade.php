@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
@endpush

@section('content')
<style>

.dev-wrap {
    --dev-theme: #f13535;
    --dev-theme-dark: #c92323;
    --dev-theme-soft: #fee2e2;
    max-width: 1500px;
    margin: 0 auto;
}

    .dev-wrap {
        padding: 24px;
        font-family: 'Inter', sans-serif;
        color: #1f2937;
    }

    .dev-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .dev-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #111111;
        color: #111111;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Filters */
    .filter-bar {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 12px 16px;
        margin-bottom: 24px;
        display: flex;
        gap: 10px;
        align-items: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
        flex-wrap: wrap;
    }

    .filter-label {
        font-size: 13px;
        font-weight: 700;
        color: #6b7280;
        margin-right: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        background: rgba(255, 255, 255, 0.6);
        color: #4b5563;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .filter-btn:hover {
        background: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .filter-btn.active {
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-1px);
    }

    .filter-btn[data-status="all"].active { background: var(--dev-theme); }
    .filter-btn[data-status="todo"].active { background: var(--dev-theme); }
    .filter-btn[data-status="inprogress"].active { background: var(--dev-theme); }
    .filter-btn[data-status="review"].active { background: var(--dev-theme); }
    .filter-btn[data-status="completed"].active { background: var(--dev-theme); }
    .filter-btn[data-status="all"].active { background: var(--dev-theme); }
    .filter-btn[data-status="todo"].active { background: var(--dev-theme); }
    .filter-btn[data-status="inprogress"].active { background: var(--dev-theme); }
    .filter-btn[data-status="review"].active { background: var(--dev-theme); }
    .filter-btn[data-status="completed"].active { background: var(--dev-theme); }

    /* Task Grid */
    .tasks-container {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
        max-width: 100%;
        max-width: 100%;
        margin: 0 auto;
    }




    .task-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 20px;
        padding: 28px 30px 24px;
        padding: 28px 30px 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        width: 100%;
    }

    .task-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 6px;
        height: 100%;
        background: var(--dev-theme);
        background: var(--dev-theme);
    }
    
    .task-card.priority-high::before { background: #dc2626; }
    .task-card.priority-urgent::before { background: #991b1b; }
    .task-card.priority-high::before { background: #dc2626; }
    .task-card.priority-urgent::before { background: #991b1b; }

    .task-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.08);
    }

    .task-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
        padding-right: 132px;
        padding-right: 132px;
    }

    .task-client {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #4b5563;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .task-client img {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        object-fit: cover;
    }

    .task-title {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 8px 0;
        line-height: 1.3;
    }

    .task-type-badge {
        display: inline-block;
        padding: 4px 10px;
        background: var(--dev-theme-soft);
        color: var(--dev-theme);
        background: var(--dev-theme-soft);
        color: var(--dev-theme);
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: absolute;
        top: 18px;
        right: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        position: absolute;
        top: 18px;
        right: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
    }

    .status-todo { background: #ffe6e6; color: #f13535; }
    .status-inprogress { background: #ffd9d9; color: #dd2f2f; }
    .status-review { background: #ffcdcd; color: #c92323; }
    .status-completed { background: #ffeff0; color: #a81d1d; }
    .status-todo { background: #ffe6e6; color: #f13535; }
    .status-inprogress { background: #ffd9d9; color: #dd2f2f; }
    .status-review { background: #ffcdcd; color: #c92323; }
    .status-completed { background: #ffeff0; color: #a81d1d; }

    .deadline-warning {
        background: linear-gradient(135deg, #fff1f1, #ffe1e1);
        border: 1px solid #fecaca;
        background: linear-gradient(135deg, #fff1f1, #ffe1e1);
        border: 1px solid #fecaca;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #b91c1c;
        color: #b91c1c;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .deadline-warning i { color: #f13535; }
    .deadline-warning i { color: #f13535; }

    .task-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .meta-label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .meta-value {
        font-size: 13px;
        font-weight: 700;
        color: #1f2937;
    }

    .dev-section {
        margin-bottom: 20px;
    }

    .dev-section-title {
        font-size: 12px;
        font-weight: 800;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e5e7eb;
    }

    .dev-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .dev-field {
        background: #fff;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .dev-field-label {
        font-size: 10px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .dev-field-value {
        font-size: 12px;
        font-weight: 600;
        color: #1f2937;
    }

    .text-content {
        font-size: 12px;
        color: #374151;
        line-height: 1.6;
        background: #fff;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .asset-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .asset-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .asset-status {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 10px;
    }

    .asset-yes { background: #10b981; }
    .asset-no { background: #ef4444; }

    /* Action Forms */
    .action-container {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px dashed #d1d5db;
    }

    .btn-action {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 10px;
        font-weight: 800;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-start {
        background: linear-gradient(135deg, var(--dev-theme), var(--dev-theme-dark));
        background: linear-gradient(135deg, var(--dev-theme), var(--dev-theme-dark));
        color: #fff;
        box-shadow: 0 4px 12px rgba(241, 53, 53, 0.3);
        box-shadow: 0 4px 12px rgba(241, 53, 53, 0.3);
    }

    .btn-start:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(241, 53, 53, 0.4);
        box-shadow: 0 6px 16px rgba(241, 53, 53, 0.4);
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--dev-theme-dark), #991b1b);
        background: linear-gradient(135deg, var(--dev-theme-dark), #991b1b);
        color: #fff;
        box-shadow: 0 4px 12px rgba(201, 35, 35, 0.3);
        box-shadow: 0 4px 12px rgba(201, 35, 35, 0.3);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(201, 35, 35, 0.4);
        box-shadow: 0 6px 16px rgba(201, 35, 35, 0.4);
    }

    .submit-form {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        padding: 16px;
        border-radius: 12px;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        transition: border-color 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--dev-theme);
        box-shadow: 0 0 0 3px rgba(241, 53, 53, 0.12);
        border-color: var(--dev-theme);
        box-shadow: 0 0 0 3px rgba(241, 53, 53, 0.12);
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 20px;
        background: rgba(255,255,255,0.6);
        backdrop-filter: blur(8px);
        border: 1px dashed #cbd5e1;
        border-radius: 20px;
    }

    .empty-state i {
        font-size: 48px;
        color: #9ca3af;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        margin: 0 0 8px;
        font-size: 20px;
        color: #1f2937;
    }

    .empty-state p {
        margin: 0;
        color: #6b7280;
    }

    @media (max-width: 1024px) {
        .task-card {
            padding: 24px 22px 20px;
        }

        .task-header {
            padding-right: 118px;
        }
    }

    @media (max-width: 768px) {
        .tasks-container {
            gap: 18px;
        }

        .task-meta,
        .dev-grid,
        .asset-list {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .dev-wrap {
            padding: 16px;
        }

        .task-card {
            padding: 20px 16px 18px;
            border-radius: 16px;
        }

        .task-header {
            padding-right: 0;
        }

        .status-badge {
            position: static;
            margin-top: 8px;
        }
    }





    

.project-list-card{
    background: linear-gradient(135deg,#ffffff,#fafafa);
    border-radius:24px;
    padding:25px;
    margin-bottom:30px;
    box-shadow:0 15px 40px rgba(0,0,0,.06);
    border:1px solid #f1f5f9;
}


.project-list-card h3{
    margin-bottom:20px;
    font-size:24px;
    font-weight:800;
    color:#111827;
}

.project-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:22px;
    border-radius:18px;
    background:#fff;
    margin-bottom:12px;
    border:1px solid #eef2f7;
    transition:all .3s ease;
    cursor:pointer;
}


.project-row:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 30px rgba(241,53,53,.12);
    border-color:#f13535;
}


.project-row strong{
    display:block;
    font-size:22px;
    font-weight:800;
    color:#111827;
    margin-bottom:5px;
}



.project-row small{
    font-size:14px;
    color:#6b7280;
}






.project-row:last-child{
    border-bottom:none;
}

.btn-view{
    background:linear-gradient(
        135deg,
        #f13535,
        #c92323
    );

    color:white;
    border:none;
    border-radius:12px;

    padding:12px 24px;

    font-size:14px;
    font-weight:700;

    cursor:pointer;

    transition:all .3s ease;

    box-shadow:
    0 8px 20px rgba(241,53,53,.25);
}


.btn-view:hover{
    transform:translateY(-2px);
    box-shadow:
    0 12px 25px rgba(241,53,53,.35);
}

.btn-view::after{
    content:' →';
    transition:.3s;
}

.btn-view:hover::after{
    margin-left:6px;
}




.task-popup-content .task-card{
    display:block;
}







.task-popup{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.6);
    z-index:9999;
}

.task-popup-content{
    background:#fff;
    width:90%;
    max-width:1200px;
    margin:40px auto;
    max-height:90vh;
    overflow-y:auto;
    border-radius:12px;
    padding:20px;
    position:relative;
}

.close-popup{
    position: fixed;
    top: 25px;
    right: 25px;

    width: 50px;
    height: 50px;

    border: none;
    border-radius: 50%;

    background: #f13535;
    color: #fff;

    font-size: 28px;
    font-weight: bold;

    cursor: pointer;

    z-index: 10001;

    display: flex;
    align-items: center;
    justify-content: center;

    box-shadow: 0 5px 20px rgba(0,0,0,.25);
}


.close-popup:hover{
    background:#c92323;
    transform:scale(1.05);
}





.tasks-container{
    display:none;
}



.project-meta{
    color:#6b7280;
    font-size:14px;
    font-weight:500;
}
    @media (max-width: 1024px) {
        .task-card {
            padding: 24px 22px 20px;
        }

        .task-header {
            padding-right: 118px;
        }
    }

    @media (max-width: 768px) {
        .tasks-container {
            gap: 18px;
        }

        .task-meta,
        .dev-grid,
        .asset-list {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .dev-wrap {
            padding: 16px;
        }

        .task-card {
            padding: 20px 16px 18px;
            border-radius: 16px;
        }

        .task-header {
            padding-right: 0;
        }

        .status-badge {
            position: static;
            margin-top: 8px;
        }
    }





    

.project-list-card{
    background: linear-gradient(135deg,#ffffff,#fafafa);
    border-radius:24px;
    padding:25px;
    margin-bottom:30px;
    box-shadow:0 15px 40px rgba(0,0,0,.06);
    border:1px solid #f1f5f9;
}


.project-list-card h3{
    margin-bottom:20px;
    font-size:24px;
    font-weight:800;
    color:#111827;
}

.project-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:22px;
    border-radius:18px;
    background:#fff;
    margin-bottom:12px;
    border:1px solid #eef2f7;
    transition:all .3s ease;
    cursor:pointer;
}


.project-row:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 30px rgba(241,53,53,.12);
    border-color:#f13535;
}


.project-row strong{
    display:block;
    font-size:22px;
    font-weight:800;
    color:#111827;
    margin-bottom:5px;
}



.project-row small{
    font-size:14px;
    color:#6b7280;
}






.project-row:last-child{
    border-bottom:none;
}

.btn-view{
    background:linear-gradient(
        135deg,
        #f13535,
        #c92323
    );

    color:white;
    border:none;
    border-radius:12px;

    padding:12px 24px;

    font-size:14px;
    font-weight:700;

    cursor:pointer;

    transition:all .3s ease;

    box-shadow:
    0 8px 20px rgba(241,53,53,.25);
}


.btn-view:hover{
    transform:translateY(-2px);
    box-shadow:
    0 12px 25px rgba(241,53,53,.35);
}

.btn-view::after{
    content:' →';
    transition:.3s;
}

.btn-view:hover::after{
    margin-left:6px;
}




.task-popup-content .task-card{
    display:block;
}







.task-popup{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.6);
    z-index:9999;
}

.task-popup-content{
    background:#fff;
    width:90%;
    max-width:1200px;
    margin:40px auto;
    max-height:90vh;
    overflow-y:auto;
    border-radius:12px;
    padding:20px;
    position:relative;
}

.close-popup{
    position: fixed;
    top: 25px;
    right: 25px;

    width: 50px;
    height: 50px;

    border: none;
    border-radius: 50%;

    background: #f13535;
    color: #fff;

    font-size: 28px;
    font-weight: bold;

    cursor: pointer;

    z-index: 10001;

    display: flex;
    align-items: center;
    justify-content: center;

    box-shadow: 0 5px 20px rgba(0,0,0,.25);
}


.close-popup:hover{
    background:#c92323;
    transform:scale(1.05);
}





.tasks-container{
    display:none;
}



.project-meta{
    color:#6b7280;
    font-size:14px;
    font-weight:500;
}

</style>

<div class="dev-wrap">
    
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:20px;">
        <div class="dev-header" style="margin-bottom:0">
            <h1><i class="fa-solid fa-laptop-code"></i> {{ ($scope ?? 'my') === 'all' ? 'All Team Projects & Tasks' : 'My Projects & Tasks' }}</h1>
        </div>
        <div class="dev-scope-toggle" style="display:inline-flex;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:4px;gap:4px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
            <a href="{{ route('developer.tasks', array_merge(request()->except('scope'), ['scope' => 'my'])) }}" 
               style="padding:8px 16px;border-radius:8px;font-size:12.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s; {{ ($scope ?? 'my') === 'my' ? 'background:#f13535;color:#fff;box-shadow:0 2px 8px rgba(241,53,53,0.35);' : 'color:#4b5563;background:transparent;' }}">
                <i class="fa-solid fa-user-check"></i> My Tasks
            </a>
            <a href="{{ route('developer.tasks', array_merge(request()->except('scope'), ['scope' => 'all'])) }}" 
               style="padding:8px 16px;border-radius:8px;font-size:12.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s; {{ ($scope ?? 'my') === 'all' ? 'background:#f13535;color:#fff;box-shadow:0 2px 8px rgba(241,53,53,0.35);' : 'color:#4b5563;background:transparent;' }}">
                <i class="fa-solid fa-users"></i> All Team Tasks
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <span class="filter-label"><i class="fa-solid fa-filter"></i> Filter:</span>
        <button class="filter-btn active" data-status="all" onclick="filterTasks('all', event)">
            <i class="fa-solid fa-layer-group"></i> All Projects
        </button>
        <button class="filter-btn" data-status="todo" onclick="filterTasks('todo', event)">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> To Do
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> To Do
        </button>
        <button class="filter-btn" data-status="inprogress" onclick="filterTasks('inprogress', event)">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> In Progress
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> In Progress
        </button>
        <button class="filter-btn" data-status="review" onclick="filterTasks('review', event)">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> Review
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> Review
        </button>
        <button class="filter-btn" data-status="completed" onclick="filterTasks('completed', event)">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> Completed
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> Completed
        </button>
    </div>

    <div class="project-list-card">

    <h3>{{ ($scope ?? 'my') === 'all' ? 'All Team Projects' : 'All Assigned Projects' }} ({{ count($tasks ?? []) }})</h3>

    @foreach($tasks ?? [] as $task)

        <div class="project-row" style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #f3f4f6">

            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <strong>{{ $task->title }}</strong>
                    <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:#f3f4f6;font-weight:700">{{ ucfirst($task->type ?? 'project') }}</span>
                    @if($task->creator)
                        <span style="font-size:11.5px;color:#4b5563;background:rgba(99,102,241,0.08);padding:2px 8px;border-radius:6px;border:1px solid rgba(99,102,241,0.2);font-weight:600">
                            <i class="fa-solid fa-user-pen" style="color:#6366f1"></i> By: {{ $task->creator->name }}
                        </span>
                    @endif
                    @if(($scope ?? 'my') === 'all' && $task->assignee)
                        <span style="font-size:11.5px;color:#4b5563;background:rgba(16,185,129,0.08);padding:2px 8px;border-radius:6px;border:1px solid rgba(16,185,129,0.2);font-weight:600">
                            <i class="fa-solid fa-user" style="color:#10b981"></i> To: {{ $task->assignee->name }}
                        </span>
                    @endif
                </div>
                <div class="project-meta" style="margin-top:4px">
                    Client: <strong>{{ $task->client->name ?? 'N/A' }}</strong> · 
                    Deadline: 📅 {{ $task->dev_deadline ? $task->dev_deadline->format('M d, Y') : 'N/A' }}
                </div>
            </div>

            <button
                type="button"
                class="btn-view open-task"
                data-task="task-{{ $task->id }}">
                View
            </button>

        </div>

    @endforeach

</div>


<div class="task-popup">

    <div class="task-popup-content">

        <button class="close-popup">
            ×
        </button>

        <div id="popup-body"></div>

    </div>

</div>



    <div class="tasks-container" id="tasksContainer">
        @if (count($tasks ?? []) > 0)
            @foreach ($tasks ?? [] as $task)
                <div id="task-{{ $task->id }}"
     class="task-card priority-{{ strtolower($task->priority ?? 'normal') }}"
     data-status="{{ $task->status ?? 'todo' }}">
                <div id="task-{{ $task->id }}"
     class="task-card priority-{{ strtolower($task->priority ?? 'normal') }}"
     data-status="{{ $task->status ?? 'todo' }}">
                    
                    <div class="task-header">
                        <div>
                            <div class="task-client">
                                @if($task->client && $task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="">
                                @else
                                    <span>{{ $task->client->emoji ?? '📁' }}</span>
                                @endif
                                <span>{{ $task->client->name ?? 'Unknown Client' }}</span>
                            </div>
                            <h3 class="task-title">{{ $task->title ?? 'Untitled Project' }}</h3>
                            <span class="task-type-badge">{{ ucfirst($task->type ?? 'project') }}</span>
                        </div>
                        @php
                            $status = $task->status ?? 'todo';
                            $statusLabel = [
                                'todo' => 'To Do',
                                'inprogress' => 'In Progress',
                                'review' => 'Review',
                                'completed' => 'Completed',
                            ][$status] ?? ucfirst($status);
                        @endphp
                        <span class="status-badge status-{{ $status }}">{{ $statusLabel }}</span>
                        @php
                            $status = $task->status ?? 'todo';
                            $statusLabel = [
                                'todo' => 'To Do',
                                'inprogress' => 'In Progress',
                                'review' => 'Review',
                                'completed' => 'Completed',
                            ][$status] ?? ucfirst($status);
                        @endphp
                        <span class="status-badge status-{{ $status }}">{{ $statusLabel }}</span>
                    </div>

                    @if ($task->dev_deadline && $task->dev_deadline->diffInDays(now()) <= 3 && $task->dev_deadline->isFuture() && !in_array($task->status, ['review', 'completed']))
                        <div class="deadline-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Deadline approaching: {{ $task->dev_deadline->format('M d, Y') }}
                        </div>
                    @endif

                    <div class="task-meta">
                        <div class="meta-item">
                            <span class="meta-label"><i class="fa-solid fa-calendar-xmark"></i> Dev Deadline</span>
                            <span class="meta-value">
                                {{ $task->dev_deadline ? $task->dev_deadline->format('M d, Y') : 'Not set' }}
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label"><i class="fa-solid fa-rocket"></i> Launch Date</span>
                            <span class="meta-value">
                                {{ $task->launch_date ? $task->launch_date->format('M d, Y') : 'Not set' }}
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label"><i class="fa-solid fa-bolt"></i> Priority</span>
                            <span class="meta-value">{{ ucfirst($task->priority ?? 'Normal') }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label"><i class="fa-solid fa-user-tie"></i> Strategist</span>
                            <span class="meta-value">{{ $task->creator->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    @if ($task->project_start_date || $task->preferred_tech || $task->modules || $task->features)
                        <div class="dev-section">
                            <div class="dev-section-title"><i class="fa-solid fa-code"></i> Project Requirements</div>
                            
                            <div class="dev-grid">
                                @if ($task->project_start_date)
                                    <div class="dev-field">
                                        <div class="dev-field-label">Start Date</div>
                                        <div class="dev-field-value">{{ $task->project_start_date->format('M d, Y') }}</div>
                                    </div>
                                @endif
                                @if ($task->preferred_tech)
                                    <div class="dev-field">
                                        <div class="dev-field-label">Tech Stack</div>
                                        <div class="dev-field-value">{{ $task->preferred_tech }}</div>
                                    </div>
                                @endif
                            </div>

                            @if ($task->modules)
                                <div style="margin-bottom: 12px;">
                                    <div class="dev-field-label">Pages / Modules</div>
                                    <div class="text-content">
                                        {{ str_replace([',', '•'], [', ', ' • '], $task->modules) }}
                                    </div>
                                </div>
                            @endif

                            @if ($task->features)
                                <div>
                                    <div class="dev-field-label">Special Features</div>
                                    <div class="text-content">
                                        {{ str_replace([',', '•'], [', ', ' • '], $task->features) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($task->logo_received !== null || $task->images_received !== null || $task->content_received !== null || $task->domain_purchased !== null || $task->hosting_access !== null)
                        <div class="dev-section">
                            <div class="dev-section-title"><i class="fa-solid fa-box-open"></i> Assets & Access Status</div>
                            <div class="asset-list">
                                @if ($task->logo_received !== null)
                                    <div class="asset-item">
                                        <span>Logo</span>
                                        <span class="asset-status {{ $task->logo_received ? 'asset-yes' : 'asset-no' }}">
                                            <i class="fa-solid {{ $task->logo_received ? 'fa-check' : 'fa-xmark' }}"></i>
                                        </span>
                                    </div>
                                @endif
                                @if ($task->images_received !== null)
                                    <div class="asset-item">
                                        <span>Images</span>
                                        <span class="asset-status {{ $task->images_received ? 'asset-yes' : 'asset-no' }}">
                                            <i class="fa-solid {{ $task->images_received ? 'fa-check' : 'fa-xmark' }}"></i>
                                        </span>
                                    </div>
                                @endif
                                @if ($task->content_received !== null)
                                    <div class="asset-item">
                                        <span>Content</span>
                                        <span class="asset-status {{ $task->content_received ? 'asset-yes' : 'asset-no' }}">
                                            <i class="fa-solid {{ $task->content_received ? 'fa-check' : 'fa-xmark' }}"></i>
                                        </span>
                                    </div>
                                @endif
                                @if ($task->domain_purchased !== null)
                                    <div class="asset-item">
                                        <span>Domain</span>
                                        <span class="asset-status {{ $task->domain_purchased ? 'asset-yes' : 'asset-no' }}">
                                            <i class="fa-solid {{ $task->domain_purchased ? 'fa-check' : 'fa-xmark' }}"></i>
                                        </span>
                                    </div>
                                @endif
                                @if ($task->hosting_access !== null)
                                    <div class="asset-item">
                                        <span>Hosting</span>
                                        <span class="asset-status {{ $task->hosting_access ? 'asset-yes' : 'asset-no' }}">
                                            <i class="fa-solid {{ $task->hosting_access ? 'fa-check' : 'fa-xmark' }}"></i>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    @if (in_array($task->status, ['todo', 'inprogress']))
                        <div class="action-container">
                            @if ($task->status === 'todo')
                                <form action="{{ route('developer.tasks.start', $task) }}" method="POST" data-no-loader="true">
                                    @csrf
                                    <button type="submit" class="btn-action btn-start">
                                        <i class="fa-solid fa-play"></i> Start Working
                                    </button>
                                </form>
                            @elseif ($task->status === 'inprogress')
                                <div class="submit-form">
                                    <form action="{{ route('developer.tasks.submit', $task) }}" method="POST" class="developer-submit-form" data-no-loader="true">
                                        @csrf
                                        <div class="form-group">
                                            <label><i class="fa-solid fa-link"></i> Live Server / Submission Link:</label>
                                            <input type="url" name="link" class="form-input" required placeholder="https://...">
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fa-solid fa-comment-dots"></i> Dev Notes (Optional):</label>
                                            <textarea name="notes" class="form-input" style="height: 60px" placeholder="Any notes for the strategist..."></textarea>
                                        </div>
                                        <button type="submit" class="btn-action btn-submit">
                                            <i class="fa-solid fa-paper-plane"></i> Submit for Review
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($task->status === 'review')
                        <div class="action-container" style="text-align: center; color: #6b7280; font-size: 13px; font-weight: 600;">
                            <i class="fa-solid fa-hourglass-half" style="color: #f13535;"></i> Currently under review by Strategist.
                            <i class="fa-solid fa-hourglass-half" style="color: #f13535;"></i> Currently under review by Strategist.
                            @if($task->dev_submission_link)
                                <div style="margin-top: 8px;">
                                    <a href="{{ $task->dev_submission_link }}" target="_blank" style="color: #f13535; text-decoration: none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Submission</a>
                                    <a href="{{ $task->dev_submission_link }}" target="_blank" style="color: #f13535; text-decoration: none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Submission</a>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="fa-solid fa-mug-hot"></i>
                <h3>No Projects Assigned</h3>
                <p>You don't have any assigned tasks right now. Check back later!</p>
            </div>
        @endif
    </div>
</div>

<script>
    function filterTasks(status, event) {
        // Update active button state
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        if (event) {
            event.currentTarget.classList.add('active');
        } else {
            // Fallback for direct calls
            const btn = document.querySelector(`.filter-btn[data-status="${status}"]`);
            if (btn) btn.classList.add('active');
        }

        // Filter task cards
        const tasks = document.querySelectorAll('.task-card');
        tasks.forEach(task => {
            const taskStatus = task.getAttribute('data-status');
            if (status === 'all' || taskStatus === status) {
                task.style.display = 'block';
                task.style.animation = 'fadeIn 0.3s ease forwards';
            } else {
                task.style.display = 'none';
            }
        });
    }

    // Add keyframes for fadeIn
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);

    // Handle AJAX submission for developer forms
    document.querySelectorAll('.developer-submit-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-submit');
            const originalHtml = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (response.ok) {
                    if (typeof ajax !== 'undefined') {
                        ajax.showSuccess('Task submitted for review successfully!');
                    } else {
                        alert('Task submitted for review successfully!');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const data = await response.json();
                    throw new Error(data.message || 'Submission failed');
                }
            } catch (error) {
                console.error('Submission error:', error);
                if (typeof ajax !== 'undefined') {
                    ajax.showError(error.message);
                } else {
                    alert('Error: ' + error.message);
                }
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    });
</script>


<script>
    $(document).ready(function(){
        $('.open-task').click(function(){
            let taskId = $(this).data('task');
            let taskHtml = $('#' + taskId).prop('outerHTML');
            $('#popup-body').html(taskHtml);
            $('#popup-body .task-card').show();
            $('.task-popup').fadeIn(200);
        });

        $('.close-popup').click(function(){
            $('.task-popup').fadeOut(200);
        });

        $('.task-popup').click(function(e){
            if($(e.target).hasClass('task-popup')){
                $('.task-popup').fadeOut(200);
            }
        });
    });
</script>

@endsection

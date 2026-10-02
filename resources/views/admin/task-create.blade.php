@extends('layouts.app')

@section('content')
<div style="width:100%;max-width:1320px;margin:0 auto;padding:8px 0 32px">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
        <a href="{{ route('admin.tasks') }}" title="Back to tasks" aria-label="Back to tasks" style="width:40px;height:40px;border-radius:11px;border:1px solid var(--border);background:var(--card);display:flex;align-items:center;justify-content:center;color:var(--text2);text-decoration:none;box-shadow:0 3px 12px rgba(15,23,42,.06)">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="page-title" style="margin:0">Create Task</h1>
            <p class="page-subtitle" style="margin:4px 0 0">Add and assign a new client task.</p>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div style="background:var(--red-dim);color:var(--red);padding:14px 18px;border-radius:12px;margin-bottom:16px;font-size:13px;font-weight:600;border:1px solid rgba(239,68,68,.15)">
            <div style="margin-bottom:6px"><i class="fas fa-exclamation-triangle"></i> Please fix the following:</div>
            <ul style="margin:0;padding-left:18px;font-weight:500">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-create-task-modal :clients="$clients" :members="$members" :page="true" />
</div>
@endsection

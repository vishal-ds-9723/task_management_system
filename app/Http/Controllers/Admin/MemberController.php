<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMemberRequest;
use App\Http\Requests\Admin\UpdateMemberRequest;
use App\Models\Employee;
use App\Services\Admin\MemberService;

class MemberController extends Controller
{
    protected $service;

    public function __construct(MemberService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->getFilteredMembers(request());

        return view('admin.members', $data);
    }

    public function store(StoreMemberRequest $request)
    {
        $result = $this->service->createMember($request->validated());

        $message = "Member '{$result['employee']->name}' added successfully!\n\nEmail: {$result['employee']->email}\nPassword: {$result['passwordDisplay']}";

        return back()->with('success', $message);
    }

    public function update(UpdateMemberRequest $request, Employee $employee)
    {
        $this->service->updateMember($employee, $request->validated());

        return back()->with('success', "'{$employee->name}' updated successfully!");
    }

    public function destroy(Employee $employee)
    {
        $this->service->deleteMember($employee);

        return back()->with('success', "'{$employee->name}' has been removed.");
    }
}


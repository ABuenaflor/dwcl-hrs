<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Level;
use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function index(): View
    {
        return view('admin.setup', [
            'campuses' => Campus::withCount('departments')->orderBy('name')->get(),
            'departments' => Department::with('campus')->withCount(['users', 'jobPostings'])->orderBy('name')->get(),
        ]);
    }

    public function storeCampus(Request $request): RedirectResponse
    {
        Campus::create($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:campuses'],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('toast', 'Campus added.');
    }

    public function updateCampus(Request $request, Campus $campus): RedirectResponse
    {
        $campus->update($request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('campuses')->ignore($campus)],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('toast', 'Campus updated.');
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        Department::create($this->validatedDepartment($request));

        return back()->with('toast', 'Department added.');
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validatedDepartment($request, $department));

        return back()->with('toast', 'Department updated.');
    }

    public function destroyDepartment(Department $department): RedirectResponse
    {
        if ($department->users()->exists() || $department->jobPostings()->exists()) {
            return back()->withErrors(['department' => "{$department->name} is in use and cannot be deleted."]);
        }

        $department->delete();

        return back()->with('toast', 'Department deleted.');
    }

    private function validatedDepartment(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:150',
                Rule::unique('departments')->where('campus_id', $request->input('campus_id'))->ignore($department)],
            'code' => ['nullable', 'string', 'max:20'],
            'level' => ['required', Rule::enum(Level::class)],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\EmploymentType;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AcademicRank;
use App\Models\Campus;
use App\Models\Department;
use App\Models\User;
use App\Notifications\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = Role::tryFrom($request->string('role'));
        $status = AccountStatus::tryFrom($request->string('status'));

        return view('admin.users.index', [
            'users' => User::with(['department', 'campus'])
                ->when($role, fn ($q) => $q->where('role', $role))
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                    ->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")))
                ->orderByRaw("case status when 'pending' then 0 else 1 end")
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'role' => $role,
            'status' => $status,
            'pendingCount' => User::where('status', AccountStatus::Pending)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', $this->formData(new User(['role' => Role::Faculty, 'status' => AccountStatus::Active])));
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return redirect()->route('admin.users.index')->with('toast', 'Account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', $this->formData($user));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && ($data['role'] !== Role::Admin->value || $data['status'] !== AccountStatus::Active->value)) {
            return back()->withErrors(['role' => 'You cannot demote or disable your own account.']);
        }

        $user->update(array_filter($data, fn ($v, $k) => $k !== 'password' || filled($v), ARRAY_FILTER_USE_BOTH));

        return redirect()->route('admin.users.index')->with('toast', 'Account updated.');
    }

    /** Approve a pending request, or enable / disable an account. */
    public function status(Request $request, User $user): RedirectResponse
    {
        $status = AccountStatus::from($request->validate(['status' => ['required', Rule::enum(AccountStatus::class)]])['status']);
        abort_if($user->is($request->user()), 422, 'You cannot change your own status.');

        $wasPending = $user->status === AccountStatus::Pending;
        $user->update(['status' => $status]);

        if ($wasPending && $status === AccountStatus::Active) {
            $user->notify(new Alert('Account approved', 'Your employee account is active. You can now file your self-rating.', $user->homeRoute(), 'success'));
        }

        return back()->with('toast', "{$user->name} is now {$status->label()}.");
    }

    private function formData(User $user): array
    {
        return [
            'user' => $user,
            'campuses' => Campus::orderBy('name')->get(),
            'departments' => Department::with('campus')->orderBy('name')->get(),
            'ranks' => AcademicRank::orderBy('level')->orderBy('sort_order')->get(),
        ];
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'middle_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::enum(Role::class)],
            'status' => ['required', Rule::enum(AccountStatus::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'employee_no' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user)],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'department_id' => ['nullable', 'required_if:role,faculty', 'exists:departments,id'],
            'academic_rank_id' => ['nullable', 'exists:academic_ranks,id'],
            'employment_type' => ['nullable', Rule::enum(EmploymentType::class)],
            'designation' => ['nullable', 'string', 'max:100'],
            'date_hired' => ['nullable', 'date'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ]);
    }
}

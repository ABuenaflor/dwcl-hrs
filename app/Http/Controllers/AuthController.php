<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Enums\EmploymentType;
use App\Enums\Role;
use App\Models\Campus;
use App\Models\Department;
use App\Models\User;
use App\Notifications\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = $request->user();

        if (! $user->isActive()) {
            Auth::logout();
            $message = $user->status === AccountStatus::Pending
                ? 'Your account is awaiting approval by the HRDO.'
                : 'Your account is disabled. Please contact the HRDO.';

            return back()->withErrors(['email' => $message])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended($user->homeRoute())->with('toast', "Welcome back, {$user->first_name}!");
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    /** Applicants sign up and can apply immediately. */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($data + ['role' => Role::Applicant, 'status' => AccountStatus::Active]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('applicant.dashboard'))->with('toast', 'Account created. You can now apply for vacancies.');
    }

    public function showEmployeeRequest(): View
    {
        return view('auth.employee-request', [
            'campuses' => Campus::orderBy('name')->get(),
            'departments' => Department::with('campus')->orderBy('name')->get(),
        ]);
    }

    /** Employees request an account; the HRDO verifies and approves it. */
    public function requestEmployeeAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'middle_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'employee_no' => ['nullable', 'string', 'max:30', 'unique:users'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'designation' => ['nullable', 'string', 'max:100'],
            'date_hired' => ['nullable', 'date', 'before_or_equal:today'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($data + ['role' => Role::Faculty, 'status' => AccountStatus::Pending]);

        Notification::send(
            User::role(Role::Admin)->where('status', AccountStatus::Active)->get(),
            new Alert('Account request', "{$user->name} requested an employee account.", route('admin.users.index', ['status' => 'pending']), 'warning'),
        );

        return redirect()->route('login')->with('toast', 'Request sent. You can sign in once the HRDO approves your account.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

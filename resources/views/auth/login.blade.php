<x-layouts.auth title="Sign in" heading="Welcome back" subheading="Sign in with the email you registered — applicants, faculty and HRDO staff all use this page.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <x-field name="email" label="Email address" type="email" autocomplete="email" required autofocus />
        <x-field name="password" label="Password" type="password" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-700 focus:ring-brand-500"> Keep me signed in
        </label>

        <button class="btn btn-primary w-full py-3"><span class="btn-label">Sign in</span></button>
    </form>

    <div class="mt-8 space-y-3 border-t border-slate-100 pt-6 text-sm">
        <p class="text-slate-500">Applying for a position? <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:underline">Create an applicant account</a></p>
        <p class="text-slate-500">DWCL employee without an account? <a href="{{ route('register.employee') }}" class="font-semibold text-brand-700 hover:underline">Request access</a></p>
    </div>
</x-layouts.auth>

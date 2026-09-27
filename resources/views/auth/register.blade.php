<x-layouts.auth title="Create account" heading="Create your applicant account" subheading="One account lets you apply to any DWCL vacancy and track every application.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <x-field name="first_name" label="First name" autocomplete="given-name" required autofocus />
            <x-field name="last_name" label="Last name" autocomplete="family-name" required />
        </div>
        <x-field name="email" label="Email address" type="email" autocomplete="email" required />
        <x-field name="password" label="Password" type="password" autocomplete="new-password" hint="At least 8 characters." required />
        <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />

        <button class="btn btn-primary w-full py-3"><span class="btn-label">Create account</span></button>
    </form>
    <p class="mt-6 text-sm text-slate-500">Already registered? <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Sign in</a></p>
</x-layouts.auth>

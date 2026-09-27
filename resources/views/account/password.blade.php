<x-layouts.app title="Change password">
    <x-page-header title="Change password" subtitle="Use a password you don't use anywhere else." />
    <form method="POST" action="{{ route('password.update') }}" class="card max-w-xl space-y-5 p-6">
        @csrf @method('PUT')
        <x-field name="current_password" label="Current password" type="password" autocomplete="current-password" required />
        <x-field name="password" label="New password" type="password" autocomplete="new-password" hint="At least 8 characters." required />
        <x-field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required />
        <div class="flex justify-end"><button class="btn btn-primary"><span class="btn-label">Update password</span></button></div>
    </form>
</x-layouts.app>

{{-- Session flash messages and validation summaries, rendered as toasts. --}}
<div x-data x-init="
        @if (session('toast')) $store.toasts.push(@js(session('toast')), 'success'); @endif
        @if ($errors->any()) $store.toasts.push(@js($errors->count() === 1 ? $errors->first() : 'Please review the highlighted fields.'), 'danger'); @endif
     "
     class="pointer-events-none fixed inset-x-0 bottom-0 z-[60] flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6" aria-live="polite">
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div x-transition:enter="transition duration-300 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:leave="transition duration-200" x-transition:leave-end="opacity-0 translate-x-6"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl bg-brand-950 p-4 text-sm text-white shadow-(--shadow-lift)">
            <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full" :class="toast.tone === 'danger' ? 'bg-rose-500' : 'bg-emerald-500'">
                <svg x-show="toast.tone !== 'danger'" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                <svg x-show="toast.tone === 'danger'" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 7v6M12 17h.01"/></svg>
            </span>
            <p class="flex-1 leading-5" x-text="toast.message"></p>
            <button type="button" class="text-white/60 transition hover:text-white" x-on:click="$store.toasts.dismiss(toast.id)" aria-label="Dismiss">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>

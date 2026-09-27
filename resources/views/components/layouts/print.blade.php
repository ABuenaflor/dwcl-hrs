{{-- Printable documents (certificate of rank, SAW report). --}}
@props(['title' => null, 'back' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="bg-slate-100 print:bg-white">
    <div class="no-print sticky top-0 z-10 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <a href="{{ $back ?? url()->previous() }}" class="btn btn-ghost btn-sm"><x-icon name="arrow-left" class="size-4" /> Back</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm"><x-icon name="printer" class="size-4" /> Print / Save as PDF</button>
        </div>
    </div>
    <main class="mx-auto max-w-5xl p-4 sm:p-8 print:max-w-none print:p-0">
        {{ $slot }}
    </main>
</body>
</html>

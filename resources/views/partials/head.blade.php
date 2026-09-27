<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#142243">
<title>{{ $title ? $title.' · ' : '' }}DWCL HRDO</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%23c4973e'/%3E%3Cpath d='M20 10.5 12 6.5l-8 4 8 4 8-4Z M7 12v3.5c2.5 2.3 7.5 2.3 10 0V12' fill='none' stroke='%23142243' stroke-width='1.8' stroke-linejoin='round'/%3E%3C/svg%3E">
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
{{-- Prefetch same-site pages on hover so navigation feels instant. POST actions and downloads are excluded. --}}
<script type="speculationrules">
{"prefetch": [{"where": {"and": [{"href_matches": "/*"}, {"not": {"href_matches": ["/logout", "/documents/*", "/evidence/*", "/*.csv", "/*\\?*"]}}]}, "eagerness": "moderate"}]}
</script>

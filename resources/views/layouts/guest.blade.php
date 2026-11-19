<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Authentication' }} - CargoTrace Visibility Platform</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-950 text-slate-100">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-blue-500/10 border border-blue-500/30 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
            <span class="text-2xl font-extrabold tracking-tight text-white">Cargo<span class="text-blue-500">Trace</span></span>
        </div>
        <h2 class="text-xl font-bold tracking-tight text-slate-200">Global Shipment Visibility Platform</h2>
        <p class="mt-1 text-sm text-slate-400">Deterministic Logistics Control, Tracking & Exception Engine</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-slate-900 py-8 px-6 shadow-2xl rounded-2xl sm:px-10 border border-slate-800">
            @if(session('info'))
                <div class="mb-4 p-3.5 rounded-xl bg-blue-900/30 border border-blue-700/50 text-blue-300 text-sm">
                    {{ session('info') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3.5 rounded-xl bg-red-900/30 border border-red-700/50 text-red-300 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</body>
</html>

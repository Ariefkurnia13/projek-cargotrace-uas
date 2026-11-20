<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CargoTrace' }} - Global Shipment Visibility Platform</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Leaflet.js + OpenStreetMap (UAS Required) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Chart.js (UAS Required) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .leaflet-popup-content-wrapper { border-radius: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col antialiased text-slate-800">
    <div class="min-h-full flex flex-col">
        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-8">
                        <!-- Brand -->
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xl font-extrabold tracking-tight text-slate-900">Cargo<span class="text-blue-600">Trace</span></span>
                                <span class="hidden md:inline-block ml-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase bg-slate-100 px-2 py-0.5 rounded">v2.0 • UAS Edition</span>
                            </div>
                        </a>

                        <!-- Navigation Links -->
                        <nav class="hidden md:flex items-center space-x-1">
                            <a href="{{ route('dashboard') }}"
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('shipments.index') }}"
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('shipments.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Shipments
                            </a>

                            @if(auth()->user()->canManageShipments())
                                <a href="{{ route('exceptions.index') }}"
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('exceptions.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Exceptions
                                </a>
                            @endif

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('carriers.index') }}"
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('carriers.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Carriers
                                </a>
                                <a href="{{ route('locations.index') }}"
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('locations.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Locations
                                </a>
                                <a href="{{ route('audit-logs.index') }}"
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('audit-logs.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Audit Logs
                                </a>
                            @endif
                        </nav>
                    </div>

                    <!-- User and Notifications Profile -->
                    <div class="flex items-center gap-3">
                        <!-- Notifications -->
                        <a href="{{ route('notifications.index') }}"
                           class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                           title="In-app Notifications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @php($unreadCount = auth()->user()->unreadNotifications()->count())
                            @if($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>

                        <!-- User Role Badge -->
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</span>
                            <span class="text-xs text-slate-500">{{ auth()->user()->company_name ?? auth()->user()->role->label() }}</span>
                        </div>

                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ auth()->user()->role->badgeClass() }}">
                            {{ auth()->user()->role->label() }}
                        </span>

                        <!-- Logout -->
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Sign Out">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Notifications -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
        </div>

                    @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 shadow-sm mb-4">
                    <p class="font-bold text-xs uppercase tracking-wider mb-1">⚠️ Form Validation Notice:</p>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs font-medium">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <div>
                    <strong>CargoTrace Platform</strong> • Web Programming 2 Final UAS Project Brief Implementation
                </div>
                <div>
                    Configured Scenario: <span class="font-semibold text-slate-700">{{ config('cargotrace.scenario_group') }}</span> (Delay Threshold: <span class="font-semibold text-blue-600">{{ config('cargotrace.delay_threshold_hours') }}h</span>)
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>

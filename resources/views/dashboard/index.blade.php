<x-app-layout title="Operations Dashboard">
    <div class="space-y-6">
        <!-- Header & Group Scenario Info Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 p-6 rounded-2xl text-white shadow-lg border border-slate-800">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-ping"></span>
                    Operational Group Scenario: {{ config('cargotrace.scenario_group') }}
                </span>
                <h1 class="text-2xl font-black tracking-tight">Global Logistics Command Tower</h1>
                <p class="text-sm text-slate-300 mt-1">
                    Real-time container tracking, status lifecycle adherence, and deterministic delay resolution.
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->canManageShipments())
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold text-sm transition shadow-md shadow-blue-600/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Shipment
                    </a>
                @endif
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 font-semibold text-sm text-slate-200 border border-slate-700 transition">
                    View Registry
                </a>
            </div>
        </div>

        <!-- 5 Core Operational Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Shipments -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Total Consignments</span>
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold text-slate-900">{{ number_format($totalShipments) }}</div>
                    <div class="text-xs text-slate-500 mt-1">Database actual metrics</div>
                </div>
            </div>

            <!-- In Transit -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Active In-Transit</span>
                    <div class="p-2 bg-sky-50 text-sky-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold text-blue-600 flex items-center gap-2">
                        {{ number_format($inTransitCount) }}
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">Underway via sea/air corridors</div>
                </div>
            </div>

            <!-- Delivered (Terminal) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Delivered (Terminal)</span>
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold text-emerald-600">{{ number_format($deliveredCount) }}</div>
                    <div class="text-xs text-slate-500 mt-1">Completed lifecycle</div>
                </div>
            </div>

            <!-- Delayed Shipments -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Delayed Shipments</span>
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold text-amber-600">{{ number_format($delayedCount) }}</div>
                    <div class="text-xs text-slate-500 mt-1">Delay Rate: <span class="font-bold text-slate-700">{{ $delayRate }}%</span></div>
                </div>
            </div>

            <!-- Open Exceptions -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Open Exceptions</span>
                    <div class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold text-rose-600">{{ number_format($openExceptionsCount) }}</div>
                    <div class="text-xs text-slate-500 mt-1">Requires Operator Action</div>
                </div>
            </div>
        </div>

        <!-- Charts Section (UAS Slide 10: Status breakdown & Carrier Performance) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart 1: Status Distribution -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900">Shipment Lifecycle Distribution</h2>
                    <span class="text-xs font-medium text-slate-400">Deterministic status pipeline</span>
                </div>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Top Carriers Volume -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900">Carrier Volume Allocation</h2>
                    <span class="text-xs font-medium text-slate-400">Consignments by logistics partner</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="carrierChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Priority Watchlist & Real-Time Historical Tracking Stream -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Critical Attention Table (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Priority Operational Watchlist</h2>
                        <p class="text-xs text-slate-500">Delayed consignments or shipments with open exceptions</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200">
                        {{ $criticalShipments->count() }} Urgent
                    </span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($criticalShipments as $shipment)
                        <div class="p-4 hover:bg-slate-50 transition flex items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="text-sm font-bold text-blue-600 hover:underline font-mono">
                                        {{ $shipment->shipment_number }}
                                    </a>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $shipment->current_status->badgeClass() }}">
                                        {{ $shipment->current_status->label() }}
                                    </span>
                                    @if($shipment->is_delayed)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold border {{ $shipment->delay_severity->badgeClass() }}">
                                            +{{ round($shipment->delay_duration_minutes / 60, 1) }}h Delay
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 flex items-center gap-2">
                                    <span>{{ $shipment->origin->city }} → {{ $shipment->destination->city }}</span>
                                    <span>•</span>
                                    <span>{{ $shipment->carrier->name }}</span>
                                    @if($shipment->openExceptions->count() > 0)
                                        <span>•</span>
                                        <span class="text-rose-600 font-semibold">{{ $shipment->openExceptions->count() }} Open Exception(s)</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('shipments.show', $shipment) }}"
                               class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                Inspect
                            </a>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-sm">
                            🎉 No delayed shipments or active exceptions requiring intervention.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Historical Tracking Feed (1 Col) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900">Live Telemetry Feed</h2>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Latest Chronological</span>
                </div>
                <div class="space-y-4">
                    @forelse($recentEvents as $event)
                        <div class="relative pl-6 pb-2 border-l-2 {{ $event->isException() ? 'border-rose-400' : 'border-blue-400' }}">
                            <div class="absolute -left-[7px] top-0 w-3 h-3 rounded-full {{ $event->isException() ? 'bg-rose-500 ring-4 ring-rose-100' : 'bg-blue-500 ring-4 ring-blue-100' }}"></div>
                            <div class="text-xs text-slate-400 font-mono">{{ $event->event_time->format('M d, H:i') }} UTC</div>
                            <a href="{{ route('shipments.show', $event->shipment_id) }}" class="text-xs font-bold text-slate-800 hover:text-blue-600">
                                {{ $event->shipment->shipment_number }}
                            </a>
                            <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">{{ $event->description }}</p>
                            <span class="text-[10px] text-slate-400 block mt-0.5">📍 {{ $event->location_name }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">No recent tracking updates recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Chart 1: Lifecycle Status Distribution
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Created', 'Booked', 'Picked Up', 'In Transit', 'Delivered'],
                    datasets: [{
                        data: [
                            {{ $statuses['CREATED'] ?? 0 }},
                            {{ $statuses['BOOKED'] ?? 0 }},
                            {{ $statuses['PICKED_UP'] ?? 0 }},
                            {{ $statuses['IN_TRANSIT'] ?? 0 }},
                            {{ $statuses['DELIVERED'] ?? 0 }}
                        ],
                        backgroundColor: [
                            '#94a3b8',
                            '#38bdf8',
                            '#fbbf24',
                            '#2563eb',
                            '#10b981'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Chart 2: Top Carriers Performance
            const carrierCtx = document.getElementById('carrierChart').getContext('2d');
            new Chart(carrierCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($carrierLabels) !!},
                    datasets: [{
                        label: 'Consignments',
                        data: {!! json_encode($carrierCounts) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

<x-app-layout title="Shipment #{{ $shipment->shipment_number }}">
    <div class="space-y-6">
        <!-- Top Breadcrumb & Status Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <a href="{{ route('shipments.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                        ← Back to Registry
                    </a>
                    <span class="text-slate-300">•</span>
                    <span class="font-mono text-xs font-bold text-slate-400">ID: #{{ $shipment->id }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-900 font-mono tracking-tight">{{ $shipment->shipment_number }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $shipment->current_status->badgeClass() }}">
                        {{ $shipment->current_status->label() }}
                    </span>
                    @if($shipment->is_delayed)
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $shipment->delay_severity->badgeClass() }}">
                            ⚠️ Delayed (+{{ round($shipment->delay_duration_minutes / 60, 1) }}h)
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✅ On Schedule
                        </span>
                    @endif
                </div>
            </div>

            <!-- Operator Action Buttons -->
            @if(auth()->user()->canManageShipments())
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$shipment->current_status->isTerminal())
                        <!-- Transition Status Modal Trigger -->
                        <button type="button" onclick="openModal('statusModal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Advance Status
                        </button>

                        <!-- Add Tracking Event Trigger -->
                        <button type="button" onclick="openModal('trackingModal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Log Checkpoint
                        </button>

                        <!-- Report Exception Trigger -->
                        <button type="button" onclick="openModal('exceptionModal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Report Exception
                        </button>
                    @else
                        <span class="text-xs font-semibold px-3 py-1.5 bg-slate-100 text-slate-500 rounded-lg">
                            🔒 Terminal State (DELIVERED) - Read Only
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Shipment Lifecycle Step Progress Bar -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Lifecycle Progression Pipeline</h2>
            @php($currentIndex = $shipment->current_status->stepIndex())
            <div class="grid grid-cols-5 gap-2 relative">
                @foreach(\App\Enums\ShipmentStatus::cases() as $st)
                    @php($stIndex = $st->stepIndex())
                    <div class="flex flex-col items-center text-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold mb-2 transition
                            {{ $stIndex < $currentIndex ? 'bg-emerald-500 text-white' : ($stIndex === $currentIndex ? 'bg-blue-600 text-white ring-4 ring-blue-100 animate-pulse' : 'bg-slate-200 text-slate-500') }}">
                            @if($stIndex < $currentIndex)
                                ✓
                            @else
                                {{ $stIndex }}
                            @endif
                        </div>
                        <span class="text-xs font-bold {{ $stIndex <= $currentIndex ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ $st->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2-Column Main Layout: Left Map & Route, Right Telemetry & Weather -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Col (2/3): Leaflet Map & Interactive Visualizer -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Leaflet.js OpenStreetMap Container -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Geospatial Transit Route & Checkpoints</h3>
                                <p class="text-xs text-slate-500">Live Leaflet.js + OpenStreetMap dynamic route polyline</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md">
                            EPSG:4326 WGS-84
                        </span>
                    </div>

                    <!-- Map Canvas -->
                    <div id="shipmentMap" class="w-full h-96 rounded-xl border border-slate-200 z-10"></div>

                    <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Origin Port</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Active Checkpoint</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Destination Port</span>
                        </div>
                        <span>Tiles: © OpenStreetMap contributors</span>
                    </div>
                </div>

                <!-- Cargo Items Manifest Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h3 class="text-base font-bold text-slate-900 mb-3">Cargo Items Manifest</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                                    <th class="py-2.5 px-3">Item Description</th>
                                    <th class="py-2.5 px-3">SKU</th>
                                    <th class="py-2.5 px-3">Package</th>
                                    <th class="py-2.5 px-3">Qty</th>
                                    <th class="py-2.5 px-3">Weight</th>
                                    <th class="py-2.5 px-3">Declared Value</th>
                                    <th class="py-2.5 px-3">Safety Classification</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($shipment->items as $item)
                                    <tr>
                                        <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $item->item_name }}</td>
                                        <td class="py-2.5 px-3 font-mono text-slate-500">{{ $item->sku ?? '—' }}</td>
                                        <td class="py-2.5 px-3 text-slate-600">{{ $item->package_type }}</td>
                                        <td class="py-2.5 px-3 font-bold text-slate-800">{{ $item->quantity }}</td>
                                        <td class="py-2.5 px-3 text-slate-600">{{ number_format($item->weight_kg) }} kg</td>
                                        <td class="py-2.5 px-3 font-mono text-slate-700">${{ number_format($item->declared_value, 2) }}</td>
                                        <td class="py-2.5 px-3">
                                            @if($item->is_hazardous)
                                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[10px]">⚠️ HAZARDOUS</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px]">Standard Cargo</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-slate-400">Standard general cargo consignment manifest.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historical Chronological Tracking Timeline (Slide 7) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Historical Chronological Timeline</h3>
                            <p class="text-xs text-slate-500">Immutable sequential event log (No records overwritten)</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                            {{ $timeline->count() }} Recorded Event(s)
                        </span>
                    </div>

                    <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        @foreach($timeline as $event)
                            <div class="relative group">
                                <!-- Marker Icon -->
                                <div class="absolute -left-[27px] top-1 w-4 h-4 rounded-full border-2 border-white
                                    {{ $event->isException() ? 'bg-rose-500 ring-4 ring-rose-100' : 'bg-blue-600 ring-4 ring-blue-100' }}"></div>

                                <div class="bg-slate-50 p-4 rounded-xl border {{ $event->isException() ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200/80' }}">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-xs uppercase px-2 py-0.5 rounded font-mono
                                                {{ $event->isException() ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $event->event_type }}
                                            </span>
                                            <span class="text-xs font-semibold text-slate-700">📍 {{ $event->location_name }}</span>
                                        </div>
                                        <time class="text-xs text-slate-500 font-mono">
                                            {{ $event->event_time->format('M d, Y • H:i') }} UTC
                                        </time>
                                    </div>
                                    <p class="text-xs text-slate-700 mt-1 leading-relaxed">{{ $event->description }}</p>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                                        <span>Logged by: <strong>{{ $event->creator->name }}</strong> ({{ $event->creator->role->label() }})</span>
                                        @if($event->latitude && $event->longitude)
                                            <span class="font-mono">Lat: {{ round($event->latitude, 4) }}, Lon: {{ round($event->longitude, 4) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Col (1/3): ETA Engine, Situational Weather, and Exceptions -->
            <div class="space-y-6">
                <!-- ETA, Delay & Group Benchmark Engine (Slide 8) -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            ETA & SLA Delay Calculation
                        </h3>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                            Threshold: {{ config('cargotrace.delay_threshold_hours') }}h
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Expected SLA Arrival:</span>
                            <span class="font-bold text-slate-800 font-mono">{{ $shipment->expected_arrival->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Dynamic Estimated Arrival:</span>
                            <span class="font-bold {{ $shipment->is_delayed ? 'text-amber-600' : 'text-emerald-600' }} font-mono">
                                {{ $shipment->estimated_arrival->format('M d, Y H:i') }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Delay Duration:</span>
                            <span class="font-bold {{ $shipment->is_delayed ? 'text-rose-600' : 'text-slate-700' }} font-mono">
                                {{ round($shipment->delay_duration_minutes / 60, 2) }} Hours ({{ $shipment->delay_duration_minutes }} mins)
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Severity Classification:</span>
                            <span class="font-bold px-2 py-0.5 rounded border {{ $shipment->delay_severity->badgeClass() }}">
                                {{ $shipment->delay_severity->label() }}
                            </span>
                        </div>
                    </div>

                    <!-- Calculation Reason Box -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600">
                        <span class="font-bold text-slate-800 block mb-1">Engine Valuation Logic:</span>
                        {{ $shipment->delay_reason ?? 'Calculated deterministically based on estimated arrival differential.' }}
                    </div>
                </div>

                <!-- Situational External Weather Widget (Slide 9) -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5 rounded-2xl shadow-sm border border-slate-700 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-700">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                            <h3 class="text-sm font-bold">Situational Maritime Weather</h3>
                        </div>
                        <span class="text-[10px] bg-slate-700 text-slate-300 px-2 py-0.5 rounded font-mono">
                            {{ $weatherData['source'] ?? 'Telemetry API' }}
                        </span>
                    </div>

                    @if($weatherData)
                        <div class="grid grid-cols-3 gap-2 text-center py-2">
                            <div class="bg-slate-800/80 p-2 rounded-xl">
                                <span class="block text-[10px] text-slate-400">Temperature</span>
                                <span class="text-base font-extrabold text-white">{{ $weatherData['temperature_celsius'] }}°C</span>
                            </div>
                            <div class="bg-slate-800/80 p-2 rounded-xl">
                                <span class="block text-[10px] text-slate-400">Humidity</span>
                                <span class="text-base font-extrabold text-sky-400">{{ $weatherData['humidity_percent'] }}%</span>
                            </div>
                            <div class="bg-slate-800/80 p-2 rounded-xl">
                                <span class="block text-[10px] text-slate-400">Wind Velocity</span>
                                <span class="text-base font-extrabold text-emerald-400">{{ $weatherData['wind_speed_kmh'] }} km/h</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-300">
                            Condition: <strong class="text-white">{{ $weatherData['condition'] }}</strong> at {{ $shipment->currentLocation?->name ?? 'Corridor Checkpoint' }}
                        </div>
                        <!-- Explicit Mandatory Academic Disclaimer (Slide 9) -->
                        <div class="p-2.5 rounded-lg bg-slate-800/90 border border-slate-700/80 text-[10px] text-slate-400 italic">
                            ⚠️ <strong>System Note:</strong> {{ $weatherData['disclaimer'] }}
                        </div>
                    @else
                        <p class="text-xs text-slate-400">Real-time weather data currently initializing for coordinate sector.</p>
                    @endif
                </div>

                <!-- Active Exceptions for this Shipment (Slide 8) -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900">Shipment Exceptions</h3>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $shipment->exceptions->where('status.value', '!=', 'RESOLVED')->count() > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $shipment->exceptions->count() }} Total
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse($shipment->exceptions as $ex)
                            <div class="p-3 rounded-xl border text-xs {{ $ex->isOpen() ? 'bg-rose-50/60 border-rose-200' : ($ex->isInvestigating() ? 'bg-amber-50/60 border-amber-200' : 'bg-emerald-50/40 border-emerald-200') }}">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-bold text-slate-800">{{ $ex->exception_type->label() }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $ex->status->badgeClass() }}">
                                        {{ $ex->status->label() }}
                                    </span>
                                </div>
                                <p class="text-slate-600 line-clamp-2 mt-1">{{ $ex->description }}</p>
                                <div class="mt-2 text-[10px] text-slate-400 flex items-center justify-between">
                                    <span>Detected: {{ $ex->detected_at->format('M d, H:i') }}</span>
                                    @if($ex->isResolved())
                                        <span class="text-emerald-700 font-semibold">Resolved in {{ $ex->resolutionDurationHours() }}h</span>
                                    @elseif(auth()->user()->canManageShipments())
                                        <button type="button" onclick="openResolveModal({{ $ex->id }}, '{{ $ex->exception_type->label() }}')" class="text-blue-600 font-bold hover:underline">
                                            Update Status →
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">No exceptions recorded for this consignment.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Status Transition (Slide 6 Validations) -->
    <div id="statusModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Advance Shipment Status</h3>
            <p class="text-xs text-slate-500 mb-4">
                Current status: <strong class="text-blue-600">{{ $shipment->current_status->label() }}</strong>.
                Transitions must follow deterministic sequential lifecycle rules.
            </p>

            <form action="{{ route('shipments.status', $shipment) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Next Status</label>
                    <select name="target_status" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($shipment->current_status->allowedNextStatuses() as $allowed)
                            <option value="{{ $allowed->value }}">{{ $allowed->label() }} (Next Valid Step)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Current Checkpoint Location</label>
                    <select name="location_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Retain Current Location ({{ $shipment->currentLocation?->name ?? 'Default' }})</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Transition Notes / Reason</label>
                    <textarea name="description" rows="2" placeholder="e.g. Vessel berthed on schedule..."
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('statusModal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-sm">Execute Transition</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Add Tracking Checkpoint Event (Slide 7) -->
    <div id="trackingModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Log Historical Checkpoint</h3>
            <p class="text-xs text-slate-500 mb-4">Append tracking update without overwriting historical timeline entries.</p>

            <form action="{{ route('shipments.tracking.store', $shipment) }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Location Hub</label>
                    <select name="location_id" id="tracking_location_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Custom Coordinate / Open Sea Checkpoint</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Event Type</label>
                        <select name="event_type" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="CHECKPOINT">CHECKPOINT</option>
                            <option value="NORMAL">NORMAL</option>
                            <option value="DEPARTURE">DEPARTURE</option>
                            <option value="ARRIVAL">ARRIVAL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Timestamp</label>
                        <input type="datetime-local" name="event_time" required value="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Location Name / Sector</label>
                    <input type="text" name="location_name" placeholder="e.g. Malacca Strait Sector 4"
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Event Description</label>
                    <textarea name="description" rows="2" required placeholder="Details on vessel progress or container handling..."
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('trackingModal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-sm">Save Checkpoint</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Report Exception (Slide 8) -->
    <div id="exceptionModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Report Shipment Exception</h3>
            <p class="text-xs text-slate-500 mb-4">Flags consignment issue, recalculates ETA delay, and alerts customer.</p>

            <form action="{{ route('shipments.exceptions.store', $shipment) }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Exception Category</label>
                    <select name="exception_type" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(\App\Enums\ExceptionType::cases() as $ext)
                            <option value="{{ $ext->value }}">{{ $ext->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Severity</label>
                        <select name="severity" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="LOW">LOW (≤ 6h)</option>
                            <option value="MEDIUM" selected>MEDIUM (6h - 24h)</option>
                            <option value="HIGH">HIGH (> 24h)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Impact Delay (Hours)</label>
                        <input type="number" step="0.5" name="impact_delay_hours" value="12.0" required
                               class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Issue Description</label>
                    <textarea name="description" rows="2" required placeholder="Specific disruption explanation..."
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Root Cause Analysis</label>
                    <textarea name="root_cause" rows="2" placeholder="Underlying causal factor (customs audit, cyclone, documentation error)..."
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('exceptionModal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-sm">Log & Notify</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: Update Exception Status (OPEN -> INVESTIGATING -> RESOLVED) -->
    <div id="resolveModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-1" id="resolveModalTitle">Update Exception Status</h3>
            <p class="text-xs text-slate-500 mb-4">Investigate or mark this exception as resolved.</p>

            <form id="resolveForm" method="POST" class="space-y-3.5">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">New Exception Status</label>
                    <select name="status" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="INVESTIGATING">INVESTIGATING</option>
                        <option value="RESOLVED" selected>RESOLVED</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Resolution Notes</label>
                    <textarea name="resolution_notes" rows="3" required placeholder="How the exception was handled and resolved..."
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('resolveModal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-sm">Save Resolution</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Modal toggling helpers
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        function openResolveModal(exceptionId, title) {
            document.getElementById('resolveModalTitle').innerText = 'Resolve: ' + title;
            document.getElementById('resolveForm').action = '/exceptions/' + exceptionId + '/status';
            openModal('resolveModal');
        }

        // Initialize Leaflet Map (Slide 9 Requirement)
        document.addEventListener('DOMContentLoaded', function () {
            // Collect route coordinates
            const origin = [{{ $shipment->origin->latitude }}, {{ $shipment->origin->longitude }}];
            const destination = [{{ $shipment->destination->latitude }}, {{ $shipment->destination->longitude }}];

            // Build route waypoints array from chronological tracking events
            const waypoints = [origin];
            @foreach($timeline as $event)
                @if($event->latitude && $event->longitude)
                    waypoints.push([{{ $event->latitude }}, {{ $event->longitude }}]);
                @endif
            @endforeach
            waypoints.push(destination);

            // Initialize Map
            const map = L.map('shipmentMap').setView(origin, 3);

            // Add OpenStreetMap Tile Layer with Attribution
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Origin Marker (Green)
            const originMarker = L.circleMarker(origin, {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.9,
                radius: 8
            }).addTo(map);
            originMarker.bindPopup(`
                <div class="text-xs">
                    <strong class="text-emerald-700 block text-sm">Origin: {{ $shipment->origin->name }}</strong>
                    <span>{{ $shipment->origin->city }}, {{ $shipment->origin->country }}</span><br>
                    <span class="text-slate-400">Departure SLA: {{ $shipment->expected_departure->format('M d, H:i') }}</span>
                </div>
            `);

            // Destination Marker (Red)
            const destMarker = L.circleMarker(destination, {
                color: '#ef4444',
                fillColor: '#ef4444',
                fillOpacity: 0.9,
                radius: 8
            }).addTo(map);
            destMarker.bindPopup(`
                <div class="text-xs">
                    <strong class="text-rose-700 block text-sm">Destination: {{ $shipment->destination->name }}</strong>
                    <span>{{ $shipment->destination->city }}, {{ $shipment->destination->country }}</span><br>
                    <span class="text-slate-400">Arrival SLA: {{ $shipment->expected_arrival->format('M d, H:i') }}</span>
                </div>
            `);

            // Checkpoints Markers along the path
            @foreach($timeline as $event)
                @if($event->latitude && $event->longitude)
                    const marker{{ $event->id }} = L.circleMarker([{{ $event->latitude }}, {{ $event->longitude }}], {
                        color: '{{ $event->isException() ? "#e11d48" : "#2563eb" }}',
                        fillColor: '{{ $event->isException() ? "#f43f5e" : "#3b82f6" }}',
                        fillOpacity: 0.85,
                        radius: {{ $event->isException() ? 7 : 5 }}
                    }).addTo(map);
                    marker{{ $event->id }}.bindPopup(`
                        <div class="text-xs">
                            <strong class="{{ $event->isException() ? 'text-rose-700' : 'text-blue-700' }} block text-sm">
                                [{{ $event->event_type }}] {{ $event->location_name }}
                            </strong>
                            <p class="mt-1 text-slate-700">{{ $event->description }}</p>
                            <span class="text-slate-400 block mt-1 font-mono">{{ $event->event_time->format('M d, H:i') }} UTC</span>
                        </div>
                    `);
                @endif
            @endforeach

            // Draw Route Polyline
            const routeLine = L.polyline(waypoints, {
                color: '#2563eb',
                weight: 3.5,
                opacity: 0.75,
                dashArray: '6, 8',
                lineJoin: 'round'
            }).addTo(map);

            // Fit map bounds to encompass all waypoints
            map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
        });
    </script>
    @endpush
</x-app-layout>

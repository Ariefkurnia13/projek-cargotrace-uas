<x-app-layout title="Shipment Registry">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Shipment Registry & Tracking</h1>
                <p class="text-sm text-slate-500">
                    Comprehensive overview of all active, in-transit, and delivered cargo consignments.
                </p>
            </div>
            @if(auth()->user()->canManageShipments())
                <a href="{{ route('shipments.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold text-sm text-white shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Shipment Manifest
                </a>
            @endif
        </div>

        <!-- Advanced Filter & Search Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('shipments.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Query -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Search Identifier</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Shipment #, cargo, client..."
                               class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Lifecycle Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">All Statuses</option>
                            @foreach(\App\Enums\ShipmentStatus::cases() as $st)
                                <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                    {{ $st->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Carrier Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Carrier Partner</label>
                        <select name="carrier_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">All Carriers</option>
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }} ({{ $carrier->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Delay Status Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">SLA Punctuality</label>
                        <select name="is_delayed" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">All Consignments</option>
                            <option value="true" {{ request('is_delayed') === 'true' ? 'selected' : '' }}>⚠️ Delayed Only</option>
                            <option value="false" {{ request('is_delayed') === 'false' ? 'selected' : '' }}>✅ On Schedule Only</option>
                        </select>
                    </div>
                </div>

                <!-- Secondary Filter Row: Date Range & Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-2 border-t border-slate-100 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Origin Port</label>
                        <select name="origin_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Any Origin</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('origin_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} ({{ $loc->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Destination Port</label>
                        <select name="destination_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Any Destination</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('destination_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} ({{ $loc->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Departure From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Arrival Until</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div class="flex items-center gap-2 sm:col-span-2">
                        <button type="submit" class="w-full py-2 px-3 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl transition shadow-sm">
                            Apply Filters
                        </button>
                        <a href="{{ route('shipments.index') }}" class="py-2 px-3 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition text-center">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Shipments Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4">Shipment #</th>
                            <th class="py-3 px-4">Client</th>
                            <th class="py-3 px-4">Carrier & Cargo</th>
                            <th class="py-3 px-4">Transit Corridor</th>
                            <th class="py-3 px-4">Lifecycle Status</th>
                            <th class="py-3 px-4">ETA & Delay</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($shipments as $shipment)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                        {{ $shipment->shipment_number }}
                                    </a>
                                    <span class="block text-[11px] text-slate-400 font-mono">{{ $shipment->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $shipment->customer->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $shipment->customer->company_name ?? 'Individual' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">{{ $shipment->carrier->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $shipment->cargo_type }} • {{ number_format($shipment->total_weight_kg) }} kg</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-900 flex items-center gap-1.5">
                                        <span>{{ $shipment->origin->city }}</span>
                                        <span class="text-slate-400">→</span>
                                        <span>{{ $shipment->destination->city }}</span>
                                    </div>
                                    <div class="text-xs text-slate-400 font-mono">
                                        📍 Current: {{ $shipment->currentLocation?->name ?? 'En Route' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $shipment->current_status->badgeClass() }}">
                                        {{ $shipment->current_status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($shipment->is_delayed)
                                        <div class="flex items-center gap-1">
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold border {{ $shipment->delay_severity->badgeClass() }}">
                                                +{{ round($shipment->delay_duration_minutes / 60, 1) }}h Delayed
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">ETA: {{ $shipment->estimated_arrival->format('M d, H:i') }}</div>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            On Schedule
                                        </span>
                                        <div class="text-[11px] text-slate-400 mt-0.5">ETA: {{ $shipment->estimated_arrival->format('M d, H:i') }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('shipments.show', $shipment) }}"
                                           class="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition" title="Inspect Timeline & Map">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if(auth()->user()->canManageShipments() && !$shipment->current_status->isTerminal())
                                            <a href="{{ route('shipments.edit', $shipment) }}"
                                               class="p-1.5 rounded-lg text-slate-600 hover:text-amber-600 hover:bg-slate-100 transition" title="Edit Manifest">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                    No shipments match your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination Links -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

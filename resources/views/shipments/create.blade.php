<x-app-layout title="Create New Shipment">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('shipments.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    ← Back to Registry
                </a>
                <h1 class="text-2xl font-black text-slate-900 mt-1">Generate Shipment Manifest</h1>
                <p class="text-sm text-slate-500">Initiate a new consignment within CargoTrace platform lifecycle.</p>
            </div>
        </div>

        <form action="{{ route('shipments.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Customer & Logistics Operator Details -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400">1. Commercial Entities</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Customer / Consignee *</label>
                        <select name="customer_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Customer Account</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                    {{ $cust->name }} ({{ $cust->company_name ?? 'Individual' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier Partner *</label>
                        <select name="carrier_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Carrier Line</option>
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }} ({{ $carrier->code }} - {{ $carrier->service_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('carrier_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Route & Corridors -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400">2. Route & Transit Corridors</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Origin Port / Terminal *</label>
                        <select name="origin_id" id="origin_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Origin Hub</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('origin_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                </option>
                            @endforeach
                        </select>
                        @error('origin_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Destination Port / Terminal *</label>
                        <select name="destination_id" id="destination_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Destination Hub</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('destination_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                </option>
                            @endforeach
                        </select>
                        @error('destination_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Schedules & Physical Cargo Specifications -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400">3. Schedules & Specifications</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Expected Departure SLA *</label>
                        <input type="datetime-local" name="expected_departure" required value="{{ old('expected_departure', now()->addDay()->format('Y-m-d\TH:i')) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('expected_departure') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Expected Arrival SLA *</label>
                        <input type="datetime-local" name="expected_arrival" required value="{{ old('expected_arrival', now()->addDays(10)->format('Y-m-d\TH:i')) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('expected_arrival') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Dynamic Estimated Arrival</label>
                        <input type="datetime-local" name="estimated_arrival" value="{{ old('estimated_arrival', now()->addDays(10)->format('Y-m-d\TH:i')) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cargo Type / Commodity *</label>
                        <input type="text" name="cargo_type" required value="{{ old('cargo_type', 'GENERAL ELECTRONICS') }}"
                               placeholder="e.g. TEXTILE, PERISHABLE"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Weight (Kg) *</label>
                        <input type="number" step="0.01" name="total_weight_kg" required value="{{ old('total_weight_kg', 12500) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Volume (CBM) *</label>
                        <input type="number" step="0.01" name="total_volume_cbm" required value="{{ old('total_volume_cbm', 38.5) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Manifest Notes / Handling Instructions</label>
                    <textarea name="notes" rows="2" placeholder="Special handling requirements, seal numbers..."
                              class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('shipments.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-md transition">
                    Register Shipment
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

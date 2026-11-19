<x-app-layout title="Edit Shipment #{{ $shipment->shipment_number }}">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('shipments.show', $shipment) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    ← Back to Shipment Details
                </a>
                <h1 class="text-2xl font-black text-slate-900 mt-1">Edit Consignment Parameters</h1>
                <p class="text-sm text-slate-500">Update operational cargo metrics or recalculate dynamic ETA arrival.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $shipment->current_status->badgeClass() }}">
                Status: {{ $shipment->current_status->label() }}
            </span>
        </div>

        <form action="{{ route('shipments.update', $shipment) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier Partner</label>
                        <select name="carrier_id" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @foreach($carriers as $c)
                                <option value="{{ $c->id }}" {{ $shipment->carrier_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cargo Type / Commodity</label>
                        <input type="text" name="cargo_type" value="{{ old('cargo_type', $shipment->cargo_type) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Weight (Kg)</label>
                        <input type="number" step="0.01" name="total_weight_kg" value="{{ old('total_weight_kg', $shipment->total_weight_kg) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Volume (CBM)</label>
                        <input type="number" step="0.01" name="total_volume_cbm" value="{{ old('total_volume_cbm', $shipment->total_volume_cbm) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Dynamic Estimated Arrival (ETA)</label>
                        <input type="datetime-local" name="estimated_arrival" value="{{ old('estimated_arrival', $shipment->estimated_arrival->format('Y-m-d\TH:i')) }}"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400">Triggers automatic delay & threshold recalculation</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Operational Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes', $shipment->notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('shipments.show', $shipment) }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-md transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

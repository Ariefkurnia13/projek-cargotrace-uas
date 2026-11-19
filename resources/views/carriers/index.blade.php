<x-app-layout title="Carriers Management">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Logistics Carrier Registry</h1>
                <p class="text-sm text-slate-500">Configure global ocean shipping lines, air cargo, and land freight operators.</p>
            </div>
            @if(auth()->user()->isAdmin())
                <button type="button" onclick="document.getElementById('carrierModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold text-sm text-white shadow-sm transition">
                    + Register Carrier
                </button>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4">Carrier Code</th>
                            <th class="py-3 px-4">Carrier Name</th>
                            <th class="py-3 px-4">Service Mode</th>
                            <th class="py-3 px-4">Operations Contact</th>
                            <th class="py-3 px-4">Active Shipments</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($carriers as $carrier)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-blue-600">{{ $carrier->code }}</td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $carrier->name }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $carrier->service_type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500">
                                    <div>{{ $carrier->contact_email ?? '—' }}</div>
                                    <div class="font-mono text-slate-400">{{ $carrier->phone ?? '—' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ $carrier->shipments_count }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $carrier->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $carriers->links() }}
            </div>
        </div>
    </div>

    <!-- Modal to Add Carrier -->
    <div id="carrierModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Add Logistics Carrier</h3>
            <form action="{{ route('carriers.store') }}" method="POST" class="space-y-3.5 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier Code (e.g. MSK, MSC, DHL)</label>
                    <input type="text" name="code" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Service Type</label>
                    <select name="service_type" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="OCEAN">OCEAN</option>
                        <option value="AIR">AIR</option>
                        <option value="ROAD">ROAD</option>
                        <option value="RAIL">RAIL</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Email</label>
                    <input type="email" name="contact_email" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('carrierModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-sm">Save Carrier</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

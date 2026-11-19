<x-app-layout title="Port & Hub Locations">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Port & Terminal Geographic Registry</h1>
                <p class="text-sm text-slate-500">Manage UN/LOCODE international maritime ports and transit checkpoints.</p>
            </div>
            @if(auth()->user()->isAdmin())
                <button type="button" onclick="document.getElementById('locationModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold text-sm text-white shadow-sm transition">
                    + Add New Port / Hub
                </button>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4">UN/LOCODE</th>
                            <th class="py-3 px-4">Facility Name</th>
                            <th class="py-3 px-4">City & Country</th>
                            <th class="py-3 px-4">WGS-84 Coordinates</th>
                            <th class="py-3 px-4">Timezone</th>
                            <th class="py-3 px-4">Hub Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($locations as $loc)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-blue-600">{{ $loc->code }}</td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $loc->name }}</td>
                                <td class="py-3.5 px-4 text-slate-600">{{ $loc->city }}, {{ $loc->country }}</td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">
                                    Lat: {{ round($loc->latitude, 4) }}, Lon: {{ round($loc->longitude, 4) }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ $loc->timezone }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $loc->is_hub ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $loc->is_hub ? 'International Hub' : 'Transit Point' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $locations->links() }}
            </div>
        </div>
    </div>

    <!-- Modal to Add Location with Live Geocoding Assist -->
    <div id="locationModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Add Location / Maritime Hub</h3>
            <p class="text-xs text-slate-500 mb-3">Integrates external OpenStreetMap Geocoding API for coordinate auto-lookup.</p>

            <!-- Geocoding Lookup Box (Vanilla JS Fetch API) -->
            <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl mb-4">
                <label class="block text-[11px] font-bold text-blue-900 mb-1">Lookup Coordinates via External API:</label>
                <div class="flex gap-2">
                    <input type="text" id="geoQuery" placeholder="e.g. Port of Rotterdam" class="w-full px-2.5 py-1.5 text-xs border border-blue-300 rounded-lg focus:outline-none">
                    <button type="button" onclick="lookupGeo()" id="geoBtn" class="px-3 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-lg shrink-0">
                        Search
                    </button>
                </div>
                <div id="geoResults" class="mt-2 space-y-1 text-xs hidden"></div>
            </div>

            <form action="{{ route('locations.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">UN/LOCODE</label>
                        <input type="text" name="code" id="loc_code" required placeholder="e.g. NLRTM" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Facility Name</label>
                        <input type="text" name="name" id="loc_name" required placeholder="Port of Rotterdam" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">City</label>
                        <input type="text" name="city" id="loc_city" required class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Country</label>
                        <input type="text" name="country" id="loc_country" required class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="number" step="0.0001" name="latitude" id="loc_lat" required class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="number" step="0.0001" name="longitude" id="loc_lon" required class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="flex items-center text-xs text-slate-700">
                        <input type="checkbox" name="is_hub" value="1" class="h-4 w-4 text-blue-600 rounded border-slate-300 mr-2">
                        Mark as Primary International Hub
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('locationModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-sm">Save Location</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        async function lookupGeo() {
            const query = document.getElementById('geoQuery').value.trim();
            if (!query) return;

            const btn = document.getElementById('geoBtn');
            const resultsBox = document.getElementById('geoResults');
            btn.innerText = '...';

            try {
                const res = await fetch('/locations/geocode?q=' + encodeURIComponent(query));
                const data = await res.json();
                resultsBox.innerHTML = '';
                resultsBox.classList.remove('hidden');

                if (data.results && data.results.length > 0) {
                    data.results.forEach(item => {
                        const opt = document.createElement('div');
                        opt.className = 'p-1.5 bg-white rounded border border-blue-200 hover:bg-blue-50 cursor-pointer text-[11px]';
                        opt.innerText = item.display_name;
                        opt.onclick = () => {
                            document.getElementById('loc_lat').value = item.lat;
                            document.getElementById('loc_lon').value = item.lon;
                            resultsBox.classList.add('hidden');
                        };
                        resultsBox.appendChild(opt);
                    });
                } else {
                    resultsBox.innerHTML = '<div class="text-[11px] text-slate-500">No external geocoding match found.</div>';
                }
            } catch (err) {
                resultsBox.innerHTML = '<div class="text-[11px] text-rose-500">Geocoding service unavailable.</div>';
                resultsBox.classList.remove('hidden');
            } finally {
                btn.innerText = 'Search';
            }
        }
    </script>
    @endpush
</x-app-layout>

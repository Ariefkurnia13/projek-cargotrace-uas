<x-app-layout title="Exception Management">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Logistics Disruption & Exception Control</h1>
                <p class="text-sm text-slate-500">Monitor and remediate operational exceptions affecting transit timelines.</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('exceptions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Exception Category</label>
                    <select name="type" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">All Categories</option>
                        @foreach(\App\Enums\ExceptionType::cases() as $ext)
                            <option value="{{ $ext->value }}" {{ request('type') === $ext->value ? 'selected' : '' }}>
                                {{ $ext->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">All Statuses</option>
                        @foreach(\App\Enums\ExceptionStatus::cases() as $st)
                            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Severity</label>
                    <select name="severity" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">All Severities</option>
                        <option value="LOW" {{ request('severity') === 'LOW' ? 'selected' : '' }}>LOW (≤ 6h)</option>
                        <option value="MEDIUM" {{ request('severity') === 'MEDIUM' ? 'selected' : '' }}>MEDIUM (6h - 24h)</option>
                        <option value="HIGH" {{ request('severity') === 'HIGH' ? 'selected' : '' }}>HIGH (> 24h)</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2.5 px-4 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-sm transition">
                        Filter
                    </button>
                    <a href="{{ route('exceptions.index') }}" class="py-2.5 px-3 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Exceptions Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4">Consignment #</th>
                            <th class="py-3 px-4">Exception Category</th>
                            <th class="py-3 px-4">Status & Severity</th>
                            <th class="py-3 px-4">Impact Delay</th>
                            <th class="py-3 px-4">Detected At</th>
                            <th class="py-3 px-4">Details & Resolution</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($exceptions as $ex)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('shipments.show', $ex->shipment) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                        {{ $ex->shipment->shipment_number }}
                                    </a>
                                    <div class="text-xs text-slate-400">{{ $ex->shipment->customer->name }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $ex->exception_type->label() }}</div>
                                    <div class="text-xs text-slate-500 line-clamp-1">{{ $ex->description }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold border {{ $ex->status->badgeClass() }}">
                                            {{ $ex->status->label() }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $ex->severity->badgeClass() }}">
                                            {{ $ex->severity->value }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                                    +{{ $ex->impact_delay_hours }}h
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">
                                    {{ $ex->detected_at->format('M d, Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600 max-w-xs">
                                    @if($ex->isResolved())
                                        <div class="text-emerald-700 font-semibold">Resolved in {{ $ex->resolutionDurationHours() }}h</div>
                                        <div class="text-slate-400 line-clamp-1">Notes: {{ $ex->resolution_notes }}</div>
                                    @else
                                        <span class="text-amber-700">Active investigation required</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if(!$ex->isResolved() && auth()->user()->canManageShipments())
                                        <button type="button" onclick="openResolveModal({{ $ex->id }}, '{{ $ex->exception_type->label() }}')"
                                                class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition shadow-sm">
                                            Update →
                                        </button>
                                    @else
                                        <a href="{{ route('shipments.show', $ex->shipment) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                                            Inspect
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    No exceptions match your query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $exceptions->links() }}
            </div>
        </div>
    </div>

    <!-- Reusable Resolve Modal -->
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
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openResolveModal(exceptionId, title) {
            document.getElementById('resolveModalTitle').innerText = 'Update: ' + title;
            document.getElementById('resolveForm').action = '/exceptions/' + exceptionId + '/status';
            openModal('resolveModal');
        }
    </script>
    @endpush
</x-app-layout>

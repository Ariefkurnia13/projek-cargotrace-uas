<x-app-layout title="System Audit Trail">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Security & Governance Audit Trail</h1>
                <p class="text-sm text-slate-500">Immutable ledger recording user actions, entity state mutations, and telemetry inputs.</p>
            </div>
            <span class="text-xs font-mono font-bold bg-slate-200 text-slate-700 px-3 py-1 rounded-full">
                Compliance Log
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4">Timestamp (UTC)</th>
                            <th class="py-3 px-4">Action</th>
                            <th class="py-3 px-4">Entity</th>
                            <th class="py-3 px-4">Actor</th>
                            <th class="py-3 px-4">Network IP / Client</th>
                            <th class="py-3 px-4">State Modifications</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-slate-100 text-slate-800">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-bold text-blue-600">{{ $log->entity_type }} #{{ $log->entity_id }}</span>
                                </td>
                                <td class="py-3 px-4 font-sans whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">{{ $log->user->name ?? 'System Service' }}</span>
                                </td>
                                <td class="py-3 px-4 text-slate-400 text-[11px]">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-mono text-[10px] max-w-md">
                                    @if($log->new_values)
                                        <details class="cursor-pointer">
                                            <summary class="text-blue-600 hover:underline">View JSON Payload</summary>
                                            <pre class="mt-1 p-2 bg-slate-900 text-slate-200 rounded text-[10px] overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 font-sans">
                                    No audit entries recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

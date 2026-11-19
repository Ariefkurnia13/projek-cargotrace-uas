<x-app-layout title="In-App Notifications">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Notification Center</h1>
                <p class="text-sm text-slate-500">Real-time alerts regarding status transitions, delays, and flagged exceptions.</p>
            </div>
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Mark All as Read
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
            @forelse($notifications as $notif)
                <div class="p-4 flex items-start justify-between gap-4 transition {{ $notif->is_read ? 'bg-white' : 'bg-blue-50/40' }}">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-xl mt-0.5 {{ $notif->type === 'EXCEPTION' ? 'bg-rose-100 text-rose-600' : 'bg-blue-100 text-blue-600' }}">
                            @if($notif->type === 'EXCEPTION')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-slate-900">{{ $notif->title }}</h4>
                                @if(!$notif->is_read)
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $notif->message }}</p>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($notif->link)
                            <a href="{{ $notif->link }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                View →
                            </a>
                        @endif
                        @if(!$notif->is_read)
                            <form action="{{ route('notifications.read', $notif) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-slate-400 hover:text-slate-600 p-1">
                                    ✓
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400 text-sm">
                    No notifications in your inbox.
                </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>

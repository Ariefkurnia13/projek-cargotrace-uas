@php($title = 'Sign In')
<x-guest-layout :title="$title">
    <form class="space-y-5" action="{{ route('login.post') }}" method="POST">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
            <div class="mt-1">
                <input id="email" name="email" type="email" autocomplete="email" required
                       value="{{ old('email') }}"
                       class="appearance-none block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800/90 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </div>
            @error('email')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
            </div>
            <div class="mt-1">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="appearance-none block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800/90 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </div>
            @error('password')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-700 rounded bg-slate-800">
                <label for="remember" class="ml-2 block text-xs text-slate-400">Remember me</label>
            </div>
            <a href="{{ route('register') }}" class="text-xs font-medium text-blue-400 hover:text-blue-300">New customer? Sign up</a>
        </div>

        <div>
            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Sign In to Platform
            </button>
        </div>
    </form>

    <!-- Quick Role Fill Pills for Live Demo & Code Interview -->
    <div class="mt-6 pt-6 border-t border-slate-800">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5 text-center">
            🚀 1-Click Demo Accounts (UAS Evaluation)
        </p>
        <div class="grid grid-cols-1 gap-2 text-xs">
            <button type="button" onclick="fillCredentials('admin@cargotrace.test', 'Password123!')"
                    class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-750 border border-purple-500/30 text-purple-300 transition text-left">
                <span><strong>Administrator</strong> (Full Platform Control)</span>
                <span class="text-[10px] bg-purple-900/50 text-purple-200 px-1.5 py-0.5 rounded">Fill</span>
            </button>
            <button type="button" onclick="fillCredentials('operator@cargotrace.test', 'Password123!')"
                    class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-750 border border-blue-500/30 text-blue-300 transition text-left">
                <span><strong>Logistics Operator</strong> (Operations & Exceptions)</span>
                <span class="text-[10px] bg-blue-900/50 text-blue-200 px-1.5 py-0.5 rounded">Fill</span>
            </button>
            <button type="button" onclick="fillCredentials('customer1@cargotrace.test', 'Password123!')"
                    class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-750 border border-emerald-500/30 text-emerald-300 transition text-left">
                <span><strong>Customer</strong> (Scoped Visibility - Group A)</span>
                <span class="text-[10px] bg-emerald-900/50 text-emerald-200 px-1.5 py-0.5 rounded">Fill</span>
            </button>
        </div>
    </div>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</x-guest-layout>

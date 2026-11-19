@php($title = 'Create Customer Account')
<x-guest-layout :title="$title">
    <form class="space-y-4" action="{{ route('register.post') }}" method="POST">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                   class="mt-1 block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300">Work Email</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}"
                   class="mt-1 block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="company_name" class="block text-sm font-medium text-slate-300">Company Name</label>
            <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}"
                   class="mt-1 block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-300">Phone / WhatsApp</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                   class="mt-1 block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-300">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="mt-1 block w-full px-3.5 py-2.5 border border-slate-700 rounded-xl bg-slate-800 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 transition shadow">
                Register Customer Account
            </button>
        </div>

        <p class="text-center text-xs text-slate-400 mt-3">
            Already have an account? <a href="{{ route('login') }}" class="text-blue-400 hover:underline">Sign In</a>
        </p>
    </form>
</x-guest-layout>

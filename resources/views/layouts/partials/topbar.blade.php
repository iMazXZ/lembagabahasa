<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200 h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-all duration-300">
    <div class="flex items-center gap-4">
        {{-- Mobile Toggle Button --}}
        <button
            @click="sidebarOpen = true"
            class="lg:hidden -ml-2 p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-um-blue"
        >
            <span class="sr-only">Open sidebar</span>
            <i class="fa-solid fa-bars text-xl"></i>
        </button>

        <h1 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">
            @yield('page-title', 'Dashboard')
        </h1>
    </div>

    <div class="flex items-center gap-4">
        {{-- Tanggal (Hidden di HP kecil) --}}
        <div class="hidden md:flex flex-col items-end mr-2">
            <span class="text-xs font-semibold text-slate-700">{{ now()->translatedFormat('l') }}</span>
            <span class="text-[10px] text-slate-500">{{ now()->translatedFormat('d F Y') }}</span>
        </div>

        {{-- Divider --}}
        <div class="h-8 w-px bg-slate-200 hidden md:block"></div>

        {{-- Logout Button --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="group flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all duration-200 border border-red-100 hover:shadow-md">
                <span>Logout</span>
                <i class="fa-solid fa-arrow-right-from-bracket transition-transform group-hover:translate-x-1"></i>
            </button>
        </form>
    </div>
</header>
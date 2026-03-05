<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    <x-nav-link :href="route('sales-stock-sessions.index')" :active="request()->routeIs('sales-stock-sessions.*')">
                        Session Stok Sales
                    </x-nav-link>

                    @if(auth()->user()->role !== 'admin_gudang')

                    <x-nav-link :href="route('areas.index')" :active="request()->routeIs('areas.*')">
                        Areas
                    </x-nav-link>

                    <x-nav-link :href="route('stores.index')" :active="request()->routeIs('stores.*')">
                        Stores
                    </x-nav-link>

                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                        Master Produk
                    </x-nav-link>

                    <x-nav-link :href="route('cash-sales.create')" :active="request()->routeIs('cash-sales.*')">
                        Penjualan Tunai
                    </x-nav-link>

                    <x-nav-link :href="route('receivables.index')" :active="request()->routeIs('receivables.*')">
                        Piutang Toko
                    </x-nav-link>

                    <x-nav-link :href="route('sales-fees.index')" :active="request()->routeIs('sales-fees.*')">
                        Fee Sales
                    </x-nav-link>

                    <x-nav-link :href="route('reports.margin.index')" :active="request()->routeIs('reports.*')">
                        Reports
                    </x-nav-link>

                    <x-nav-link :href="route('productions.create')" :active="request()->routeIs('productions.*')">
                        Produksi
                    </x-nav-link>

                    @endif

                    {{-- SYSTEM BACKUP - ADMIN ONLY --}}
                    @if(auth()->user()->role === 'admin')
                        <x-nav-link :href="route('system.backups.index')" :active="request()->routeIs('system.backups.*')">
                            System Backup
                        </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- Settings Desktop -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white hover:text-gray-700 transition">
                            <div>{{ Auth::user()->name }}</div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Toggle -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }"
                              class="inline-flex"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }"
                              class="hidden"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">

            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            @if(auth()->user()->role !== 'admin_gudang')

            <x-responsive-nav-link :href="route('areas.index')" :active="request()->routeIs('areas.*')">
                Areas
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('stores.index')" :active="request()->routeIs('stores.*')">
                Stores
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                Master Produk
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('cash-sales.create')" :active="request()->routeIs('cash-sales.*')">
                Penjualan Tunai
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('receivables.index')" :active="request()->routeIs('receivables.*')">
                Piutang Toko
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('sales-fees.index')" :active="request()->routeIs('sales-fees.*')">
                Fee Sales
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('reports.margin.index')" :active="request()->routeIs('reports.*')">
                Reports
            </x-responsive-nav-link>

            @endif

            {{-- SYSTEM BACKUP - ADMIN ONLY (MOBILE) --}}
            @if(auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('system.backups.index')" :active="request()->routeIs('system.backups.*')">
                    System Backup
                </x-responsive-nav-link>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200 mt-2 pt-2">
                @csrf
                <x-responsive-nav-link :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    Log Out
                </x-responsive-nav-link>
            </form>

        </div>
    </div>
</nav>
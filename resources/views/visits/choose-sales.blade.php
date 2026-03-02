<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pilih Sales Untuk Visit
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">

                @if($salesUsers->isEmpty())
                    <div class="text-gray-500 text-sm">
                        Tidak ada user sales tersedia.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($salesUsers as $user)
                            <a href="{{ route('stores.index', ['sales_id' => $user->id]) }}"
                               class="block p-4 border rounded hover:bg-gray-50 transition">
                                <div class="font-semibold">
                                    {{ $user->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Role: {{ $user->role }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>

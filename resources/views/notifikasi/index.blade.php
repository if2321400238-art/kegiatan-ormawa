<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                Notifikasi
            </h2>
            @if($unreadCount > 0)
                <form action="{{ route('notifikasi.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto text-sm text-center px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Filter --}}
            <div class="mb-4 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('notifikasi.index') }}" class="w-full sm:w-auto text-center px-4 py-2 rounded text-sm {{ !request('filter') ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                    Semua
                </a>
                <a href="{{ route('notifikasi.index', ['filter' => 'unread']) }}" class="w-full sm:w-auto text-center px-4 py-2 rounded text-sm {{ request('filter') == 'unread' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                    Belum Dibaca ({{ $unreadCount }})
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    @if($notifikasi->count() > 0)
                        <div class="space-y-3">
                            @foreach($notifikasi as $item)
                                <div class="border rounded-lg p-3 sm:p-4 hover:shadow-md transition {{ !$item->dibaca ? 'bg-blue-50 border-blue-200' : 'bg-white' }}">
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-3 sm:gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <span class="px-2 py-1 text-xs rounded font-medium bg-{{ $item->tipe_badge }}-100 text-{{ $item->tipe_badge }}-800">
                                                    {{ ucfirst($item->tipe) }}
                                                </span>
                                                @if(!$item->dibaca)
                                                    <span class="px-2 py-1 text-xs rounded bg-blue-600 text-white font-semibold">🆕 BARU</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">✓ DIBACA</span>
                                                @endif

                                                {{-- Delivery Status Badge --}}
                                                <span class="px-2 py-1 text-xs rounded font-medium bg-{{ $item->delivery_status_badge }}-100 text-{{ $item->delivery_status_badge }}-800">
                                                    {{ $item->delivery_status_label }}
                                                </span>

                                                {{-- Channels Info --}}
                                                @if($item->channel_summary)
                                                    <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">
                                                        📤 {{ $item->channel_summary }}
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="font-semibold text-gray-900 text-sm sm:text-base break-all">{{ $item->judul }}</h3>
                                            <p class="text-xs sm:text-sm text-gray-700 mt-1 break-words">{{ $item->pesan }}</p>

                                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4 mt-2 text-xs text-gray-500">
                                                <span>{{ $item->waktu }}</span>
                                                @if($item->read_at)
                                                    <span class="text-green-600">✓ Dibaca {{ $item->read_at->diffForHumans() }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex gap-2 mt-3 sm:mt-0">
                                            @if($item->link)
                                                <form action="{{ route('notifikasi.read', $item) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="w-full sm:w-auto px-3 py-1 text-xs sm:text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                                                        Lihat →
                                                    </button>
                                                </form>
                                            @elseif(!$item->dibaca)
                                                <form action="{{ route('notifikasi.read', $item) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 text-xs sm:text-sm bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                                        ✓
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('notifikasi.destroy', $item) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 text-xs sm:text-sm text-red-600 hover:text-red-800">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notifikasi->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p class="mt-4 text-gray-500 text-sm">Tidak ada notifikasi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<div class="w-full bg-white rounded-xl border border-gray-200 shadow-xl overflow-hidden">
    <div class="sticky top-0 z-10 bg-white border-b border-gray-100 p-4 flex items-center justify-between gap-3">
        <h3 class="text-base font-bold text-gray-900">Notifikasi</h3>

        @if($unreadCount > 0)
            <form action="{{ route('notifikasi.read-all') }}" method="POST"
                  @submit.prevent="fetch($el.action, { method: 'POST', body: new FormData($el) }).then(() => { openNotif = false; location.reload(); })"
                  class="shrink-0">
                @csrf
                <button type="submit"
                        class="text-xs font-semibold text-brand-accent hover:text-brand-active transition">
                    Tandai Semua
                </button>
            </form>
        @endif
    </div>

    <div class="max-h-96 overflow-y-auto">
        @forelse($notifikasi as $item)
            <div class="w-full p-4 border-b border-gray-100 hover:bg-gray-50 {{ !$item->dibaca ? 'bg-blue-50' : 'bg-white' }}">
                <div class="space-y-2">
                    <h4 class="w-full text-sm font-semibold text-gray-900 break-words whitespace-normal">
                        {{ $item->judul }}
                    </h4>
                    <p class="w-full text-sm text-gray-600 leading-relaxed break-words whitespace-normal">
                        {{ $item->pesan }}
                    </p>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs text-gray-400">
                    <span class="break-words whitespace-normal">
                        {{ $item->waktu }}
                    </span>

                    @if($item->link)
                        <form action="{{ route('notifikasi.read', $item) }}" method="POST"
                              @submit.prevent="fetch($el.action, { method: 'POST', body: new FormData($el) }).then(() => { window.location.href = '{{ $item->link }}'; })"
                              class="inline-flex items-center shrink-0">
                            @csrf
                            <button type="submit"
                                    class="text-brand-accent text-sm font-semibold hover:text-brand-active transition">
                                Lihat
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-gray-500">
                Tidak ada notifikasi
            </div>
        @endforelse
    </div>
</div>

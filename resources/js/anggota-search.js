export default function anggotaSearch(endpoint, initialSelected = null) {
    return {
        endpoint,
        query: initialSelected?.nama || '',
        selectedUser: initialSelected,
        results: [],
        showResults: false,
        loading: false,
        searched: false,
        errorMessage: '',
        requestId: 0,
        debounceTimer: null,

        onInput() {
            this.selectedUser = null;
            this.search();
        },

        onFocus() {
            if (this.query.trim().length >= 2 && !this.selectedUser) {
                this.search();
            }
        },

        search() {
            clearTimeout(this.debounceTimer);
            const query = this.query.trim();

            if (query.length < 2) {
                this.results = [];
                this.showResults = false;
                this.loading = false;
                this.searched = false;
                this.errorMessage = '';
                return;
            }

            this.debounceTimer = setTimeout(() => this.fetchResults(query), 350);
        },

        async fetchResults(query) {
            const currentRequest = ++this.requestId;
            this.loading = true;
            this.searched = false;
            this.errorMessage = '';
            this.showResults = true;

            try {
                const url = new URL(this.endpoint, window.location.origin);
                url.searchParams.set('search', query);

                const response = await fetch(url.toString(), {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'API mahasiswa sedang tidak dapat diakses.');
                }

                if (currentRequest === this.requestId) {
                    this.results = payload.data || [];
                    this.searched = true;
                }
            } catch (error) {
                if (currentRequest === this.requestId) {
                    this.results = [];
                    this.searched = true;
                    this.errorMessage = error.message || 'Gagal mengambil data mahasiswa.';
                }
            } finally {
                if (currentRequest === this.requestId) {
                    this.loading = false;
                }
            }
        },

        select(user) {
            if (user.already_member) return;

            this.selectedUser = user;
            this.query = user.nama;
            this.results = [];
            this.showResults = false;
            this.errorMessage = '';
        },

        clearSelection() {
            this.selectedUser = null;
            this.query = '';
            this.results = [];
            this.showResults = false;
            this.searched = false;
            this.errorMessage = '';
        },
    };
}

export default function mahasiswaSearch(initialId = '', initialName = '', initialNim = '', initialProgram = '', endpoint = '') {
    return {
        searchInput: initialName,
        selectedId: initialId,
        selectedMahasiswa: null,
        results: [],
        showResults: false,
        loading: false,
        searched: false,
        debounceTimer: null,
        requestId: 0,
        errorMessage: '',
        endpoint: endpoint,

        init() {
            if (this.selectedId && this.searchInput) {
                this.selectedMahasiswa = {
                    id: this.selectedId,
                    nama: this.searchInput,
                    nim: initialNim,
                    program_studi: initialProgram
                };
            }
        },

        onFocus() {
            if (this.searchInput.trim().length >= 2 && !this.selectedMahasiswa) {
                this.search();
            }
        },

        onInput() {
            this.selectedMahasiswa = null;
            this.selectedId = '';
            this.search();
        },

        search() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(async () => {
                const query = this.searchInput.toLowerCase().trim();
                
                if (query.length < 2) {
                    this.results = [];
                    this.showResults = false;
                    this.loading = false;
                    this.searched = false;
                    this.errorMessage = '';
                    return;
                }

                const currentRequest = ++this.requestId;
                this.loading = true;
                this.searched = false;
                this.showResults = true;
                try {
                    const response = await fetch(`${this.endpoint}?q=${encodeURIComponent(query)}`);
                    if (!response.ok) {
                        const payload = await response.json().catch(() => ({}));
                        throw new Error(payload.message || `HTTP ${response.status}`);
                    }
                    
                    const data = await response.json();
                    if (currentRequest === this.requestId) {
                        this.results = Array.isArray(data) ? data : [];
                        this.searched = true;
                        this.errorMessage = '';
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
            }, 300);
        },

        selectMahasiswa(mahasiswa) {
            this.selectedMahasiswa = mahasiswa;
            this.selectedId = mahasiswa.id || '';
            this.searchInput = mahasiswa.nama;
            this.showResults = false;
            this.results = [];
        },

        clearSelection() {
            this.selectedMahasiswa = null;
            this.selectedId = '';
            this.searchInput = '';
            this.results = [];
            this.showResults = false;
            this.searched = false;
            this.errorMessage = '';
        }
    }
}

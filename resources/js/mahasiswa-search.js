export default function mahasiswaSearch(initialId = '', initialName = '', initialNim = '', initialEmail = '', endpoint = '') {
    return {
        searchInput: initialName,
        selectedId: initialId,
        selectedMahasiswa: null,
        results: [],
        showResults: false,
        debounceTimer: null,
        errorMessage: '',
        endpoint: endpoint,

        init() {
            if (this.selectedId && this.searchInput) {
                this.selectedMahasiswa = {
                    id: this.selectedId,
                    nama: this.searchInput,
                    nim: initialNim,
                    email: initialEmail
                };
            }
        },

        onFocus() {
            if (this.searchInput.length > 0) {
                this.search();
            }
        },

        search() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(async () => {
                const query = this.searchInput.toLowerCase().trim();
                
                if (query.length < 1) {
                    this.results = [];
                    this.showResults = false;
                    return;
                }

                try {
                    const response = await fetch(`${this.endpoint}?q=${encodeURIComponent(query)}`);
                    if (!response.ok) {
                        throw new Error(`Error: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    this.results = Array.isArray(data) ? data : [];
                    this.showResults = this.results.length > 0;
                    this.errorMessage = '';
                } catch (error) {
                    console.error('Error fetching mahasiswa:', error);
                    this.errorMessage = 'Gagal mengambil data mahasiswa.';
                }
            }, 300);
        },

        selectMahasiswa(mahasiswa) {
            this.selectedMahasiswa = mahasiswa;
            this.selectedId = mahasiswa.id;
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
        }
    }
}

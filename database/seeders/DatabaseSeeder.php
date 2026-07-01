<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ormawa;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==========================================
        // 1. CREATE ADMIN
        // ==========================================
        User::updateOrCreate(['email' => 'admin@gmail.com'], [
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'nama' => 'Administrator Sistem',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        // ==========================================
        // 2. CREATE BAUAK USER
        // ==========================================
        User::updateOrCreate(['email' => 'bauak@gmail.com'], [
            'username' => 'bauak',
            'password' => Hash::make('password'),
            'role' => 'bauak',
            'nama' => 'Staff BAUAK',
            'no_hp' => '081234567891',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'bauak2@gmail.com'], [
            'username' => 'bauak2',
            'password' => Hash::make('password'),
            'role' => 'bauak',
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567892',
            'is_active' => true,
        ]);

        // ==========================================
        // 3. CREATE WAREK3 USER
        // ==========================================
        User::updateOrCreate(['email' => 'warek3@gmail.com'], [
            'username' => 'warek3',
            'password' => Hash::make('password'),
            'role' => 'warek3',
            'nama' => 'Dr. H. Ahmad Dahlan, M.Pd',
            'no_hp' => '081234567893',
            'is_active' => true,
        ]);

        // ==========================================
        // 4. CREATE ADDITIONAL ACADEMIC & PP ROLES
        // ==========================================
        User::updateOrCreate(['email' => 'kaprodi@gmail.com'], [
            'username' => 'kaprodi',
            'password' => Hash::make('password'),
            'role' => 'kaprodi',
            'nama' => 'Dr. Siti Nurjanah, M.Si',
            'no_hp' => '081234567894',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'rektor@gmail.com'], [
            'username' => 'rektor',
            'password' => Hash::make('password'),
            'role' => 'rektor',
            'nama' => 'Rektor Universitas',
            'no_hp' => '081234567896',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'pp@gmail.com'], [
            'username' => 'pp',
            'password' => Hash::make('password'),
            'role' => 'pp',
            'nama' => 'Kepala/Wakil PP',
            'no_hp' => '081234567897',
            'is_active' => true,
        ]);


        // ==========================================
        // 5. SEED ACADEMIC USERS AND ROLES
        // ==========================================
        $this->call(AcademicSeeder::class);
        $this->call(ProgramStudiSeeder::class);

        // ==========================================
        // 6. CREATE ORMAWA USERS & DATA
        // ==========================================

        $ormawaData = [
            [
                'username' => 'bem',
                'email' => 'bem@gmail.com',
                'nama' => 'Ketua BEM',
                'nama_ormawa' => 'Badan Eksekutif Mahasiswa',
                'ketua' => 'Muhammad Rizki Firmansyah',
                'no_hp' => '082111111111',
            ],
            [
                'username' => 'hmi',
                'email' => 'hmi@gmail.com',
                'nama' => 'Ketua HMI',
                'nama_ormawa' => 'Himpunan Mahasiswa Islam',
                'ketua' => 'Fatimah Azzahra',
                'no_hp' => '082222222222',
            ],
            [
                'username' => 'pmii',
                'email' => 'pmii@gmail.com',
                'nama' => 'Ketua PMII',
                'nama_ormawa' => 'Pergerakan Mahasiswa Islam Indonesia',
                'ketua' => 'Ahmad Fauzi Rahman',
                'no_hp' => '082333333333',
            ],
            [
                'username' => 'himti',
                'email' => 'himti@gmail.com',
                'nama' => 'Ketua HIMTI',
                'nama_ormawa' => 'Himpunan Mahasiswa Teknik Informatika',
                'ketua' => 'Andi Pratama Putra',
                'no_hp' => '082444444444',
                'prodi_kode' => 'S1 IF',
            ],
            [
                'username' => 'himsi',
                'email' => 'himsi@gmail.com',
                'nama' => 'Ketua HIMSI',
                'nama_ormawa' => 'Himpunan Mahasiswa Sistem Informasi',
                'ketua' => 'Dewi Lestari',
                'no_hp' => '082555555555',
                'kategori_organisasi' => 'eksternal',
            ],
        ];

        foreach ($ormawaData as $data) {
            $user = User::updateOrCreate(['email' => $data['email']], [
                'username' => $data['username'],
                'password' => Hash::make('password'),
                'role' => 'ormawa',
                'nama' => $data['nama'],
                'no_hp' => $data['no_hp'],
                'is_active' => true,
            ]);

            $kategori = $data['kategori_organisasi'] ?? 'internal';
            $tingkat = $kategori === 'internal'
                ? (isset($data['prodi_kode']) ? 'prodi' : (in_array($data['username'], ['bem', 'hmi', 'pmii']) ? 'universitas' : 'fakultas'))
                : null;

            $prodi = isset($data['prodi_kode'])
                ? \App\Models\ProgramStudi::where('kode', $data['prodi_kode'])->first()
                : null;

            Ormawa::updateOrCreate(['user_id' => $user->id], [
                'nama_ormawa' => $data['nama_ormawa'],
                'ketua' => $data['ketua'],
                'kategori_organisasi' => $kategori,
                'tingkat_organisasi' => $tingkat,
                'fakultas_id' => $prodi?->fakultas_id,
                'prodi_id' => $prodi?->id,
                'program_studi' => $prodi?->nama,
                'kontak' => $data['no_hp'],
            ]);

        }

        // ==========================================
        // CALL ADDITIONAL SEEDERS
        // ==========================================
        $this->call(OrmawaUserSeeder::class);

        // Assign fakultas_id to Ormawa yang bertingkat fakultas berdasarkan keyword sederhana

        // ==========================================
        // INFO
        // ==========================================

        echo "\n";
        echo "✅ Seeder berhasil dijalankan!\n";
        echo "\n";
        echo "📋 Data yang dibuat:\n";
        echo "   - 1 Admin\n";
        echo "   - 2 Staff BAUAK\n";
        echo "   - 1 Warek III\n";
        echo "   - 5 Ormawa\n";
        echo "\n";
        echo "🔑 Login credentials (semua password: password):\n";
        echo "   Admin:   admin@kampus.ac.id\n";
        echo "   BAUAK:   bauak@kampus.ac.id\n";
        echo "   Warek3:  warek3@kampus.ac.id\n";
        echo "   BEM:     bem@kampus.ac.id\n";
        echo "   HMI:     hmi@kampus.ac.id\n";
        echo "\n";
    }
}

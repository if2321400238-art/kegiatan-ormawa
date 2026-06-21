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
        User::create([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'nama' => 'Administrator Sistem',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        // ==========================================
        // 2. CREATE BAUAK USER
        // ==========================================
        User::create([
            'username' => 'bauak',
            'email' => 'bauak@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'bauak',
            'nama' => 'Staff BAUAK',
            'no_hp' => '081234567891',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'bauak2',
            'email' => 'bauak2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'bauak',
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567892',
            'is_active' => true,
        ]);

        // ==========================================
        // 3. CREATE WAREK3 USER
        // ==========================================
        User::create([
            'username' => 'warek3',
            'email' => 'warek3@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'warek3',
            'nama' => 'Dr. H. Ahmad Dahlan, M.Pd',
            'no_hp' => '081234567893',
            'is_active' => true,
        ]);



        // ==========================================
        // 5. CREATE ORMAWA USERS & DATA
        // ==========================================

        $ormawaData = [
            [
                'username' => 'bem',
                'email' => 'bem@gmail.com',
                'nama' => 'Ketua BEM',
                'nama_ormawa' => 'Badan Eksekutif Mahasiswa',
                'ketua' => 'Muhammad Rizki Firmansyah',
                'pembina' => 'Dr. Ahmad Dahlan, M.Pd',
                'no_hp' => '082111111111',
            ],
            [
                'username' => 'hmi',
                'email' => 'hmi@gmail.com',
                'nama' => 'Ketua HMI',
                'nama_ormawa' => 'Himpunan Mahasiswa Islam',
                'ketua' => 'Fatimah Azzahra',
                'pembina' => 'Prof. Dr. Siti Nurjanah, M.Si',
                'no_hp' => '082222222222',
            ],
            [
                'username' => 'pmii',
                'email' => 'pmii@gmail.com',
                'nama' => 'Ketua PMII',
                'nama_ormawa' => 'Pergerakan Mahasiswa Islam Indonesia',
                'ketua' => 'Ahmad Fauzi Rahman',
                'pembina' => 'Dr. Abdullah Hasan, M.Ag',
                'no_hp' => '082333333333',
            ],
            [
                'username' => 'himti',
                'email' => 'himti@gmail.com',
                'nama' => 'Ketua HIMTI',
                'nama_ormawa' => 'Himpunan Mahasiswa Teknik Informatika',
                'ketua' => 'Andi Pratama Putra',
                'pembina' => 'Ir. Bambang Suryanto, M.Kom',
                'no_hp' => '082444444444',
            ],
            [
                'username' => 'himsi',
                'email' => 'himsi@gmail.com',
                'nama' => 'Ketua HIMSI',
                'nama_ormawa' => 'Himpunan Mahasiswa Sistem Informasi',
                'ketua' => 'Dewi Lestari',
                'pembina' => 'Dr. Ir. Slamet Riyadi, M.T',
                'no_hp' => '082555555555',
            ],
        ];

        foreach ($ormawaData as $data) {
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'ormawa',
                'nama' => $data['nama'],
                'no_hp' => $data['no_hp'],
                'is_active' => true,
            ]);

            Ormawa::create([
                'user_id' => $user->id,
                'nama_ormawa' => $data['nama_ormawa'],
                'ketua' => $data['ketua'],
                'pembina' => $data['pembina'],
                'kontak' => $data['no_hp'],
            ]);
        }



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

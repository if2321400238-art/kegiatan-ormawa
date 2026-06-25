<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DosenPembinaSeeder extends Seeder
{
    /**
     * Seed the dosen pembina and additional roles.
     */
    public function run(): void
    {
        // ==========================================
        // DOSEN PEMBINA (Advisor for ORMAWA)
        // ==========================================
        $dosenPembinaData = [
            [
                'username' => 'dr_ahmad_dahlan',
                'email' => 'ahmad.dahlan@unuja.ac.id',
                'nama' => 'Dr. Ahmad Dahlan, M.Pd',
                'no_hp' => '081334567890',
                'bidang' => 'Pendidikan',
            ],
            [
                'username' => 'prof_siti_nurjanah',
                'email' => 'siti.nurjanah@unuja.ac.id',
                'nama' => 'Prof. Dr. Siti Nurjanah, M.Si',
                'no_hp' => '081334567891',
                'bidang' => 'Ilmu Sosial',
            ],
            [
                'username' => 'dr_abdullah_hasan',
                'email' => 'abdullah.hasan@unuja.ac.id',
                'nama' => 'Dr. Abdullah Hasan, M.Ag',
                'no_hp' => '081334567892',
                'bidang' => 'Agama Islam',
            ],
            [
                'username' => 'ir_bambang_suryanto',
                'email' => 'bambang.suryanto@unuja.ac.id',
                'nama' => 'Ir. Bambang Suryanto, M.Kom',
                'no_hp' => '081334567893',
                'bidang' => 'Teknik Informatika',
            ],
            [
                'username' => 'dr_slamet_riyadi',
                'email' => 'slamet.riyadi@unuja.ac.id',
                'nama' => 'Dr. Ir. Slamet Riyadi, M.T',
                'no_hp' => '081334567894',
                'bidang' => 'Sistem Informasi',
            ],
            [
                'username' => 'dr_eka_prasetya',
                'email' => 'eka.prasetya@unuja.ac.id',
                'nama' => 'Dr. Eka Prasetya, S.E., M.M',
                'no_hp' => '081334567895',
                'bidang' => 'Manajemen',
            ],
            [
                'username' => 'drs_puji_lestari',
                'email' => 'puji.lestari@unuja.ac.id',
                'nama' => 'Drs. Puji Lestari, M.Pd',
                'no_hp' => '081334567896',
                'bidang' => 'Sastra Indonesia',
            ],
            [
                'username' => 'dr_rino_rahmat',
                'email' => 'rino.rahmat@unuja.ac.id',
                'nama' => 'Dr. Rino Rahmat, M.Si',
                'no_hp' => '081334567897',
                'bidang' => 'Hukum',
            ],
        ];

        foreach ($dosenPembinaData as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'dosen',
                    'nama' => $data['nama'],
                    'no_hp' => $data['no_hp'],
                    'is_active' => true,
                ]
            );
        }

        // ==========================================
        // ADDITIONAL DEKAN (Deans) PER FACULTY
        // ==========================================
        $dekanData = [
            [
                'username' => 'dekan_ftik',
                'email' => 'dekan.ftik@unuja.ac.id',
                'nama' => 'Dr. Ir. Sutrisno, M.T',
                'no_hp' => '081334567900',
                'fakultas' => 'Fakultas Teknik dan Ilmu Komputer',
            ],
            [
                'username' => 'dekan_feb',
                'email' => 'dekan.feb@unuja.ac.id',
                'nama' => 'Dr. Hendra Wijaya, S.E., M.M',
                'no_hp' => '081334567901',
                'fakultas' => 'Fakultas Ekonomi dan Bisnis',
            ],
            [
                'username' => 'dekan_fh',
                'email' => 'dekan.fh@unuja.ac.id',
                'nama' => 'Dr. Bambang Sutioso, S.H., M.H',
                'no_hp' => '081334567902',
                'fakultas' => 'Fakultas Hukum',
            ],
        ];

        foreach ($dekanData as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'dekan',
                    'nama' => $data['nama'],
                    'no_hp' => $data['no_hp'],
                    'is_active' => true,
                ]
            );
        }

        // ==========================================
        // ADDITIONAL BAUAK STAFF
        // ==========================================
        $bauakData = [
            [
                'username' => 'bauak_koordinator',
                'email' => 'koordinator.bauak@unuja.ac.id',
                'nama' => 'Siti Nurlinda, S.E',
                'no_hp' => '081334567910',
            ],
            [
                'username' => 'bauak_operator',
                'email' => 'operator.bauak@unuja.ac.id',
                'nama' => 'Hendra Gunawan, S.Kom',
                'no_hp' => '081334567911',
            ],
        ];

        foreach ($bauakData as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'bauak',
                    'nama' => $data['nama'],
                    'no_hp' => $data['no_hp'],
                    'is_active' => true,
                ]
            );
        }

        // ==========================================
        // ADDITIONAL WAREK3 STAFF (Assistant to Warek3)
        // ==========================================
        $warek3Data = [
            [
                'username' => 'warek3_asisten',
                'email' => 'asisten.warek3@unuja.ac.id',
                'nama' => 'Dr. Moh. Rifki Hidayat, M.A',
                'no_hp' => '081334567920',
            ],
        ];

        foreach ($warek3Data as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'warek3',
                    'nama' => $data['nama'],
                    'no_hp' => $data['no_hp'],
                    'is_active' => true,
                ]
            );
        }

        // ==========================================
        // REKTOR OFFICE STAFF
        // ==========================================
        $rektorData = [
            [
                'username' => 'rektor_asisten',
                'email' => 'asisten.rektor@unuja.ac.id',
                'nama' => 'Drs. Wisnu Murti, M.A',
                'no_hp' => '081334567930',
            ],
        ];

        foreach ($rektorData as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'rektor',
                    'nama' => $data['nama'],
                    'no_hp' => $data['no_hp'],
                    'is_active' => true,
                ]
            );
        }

        // ==========================================
        // PP (KEMENTERIAN PP) STAFF
        // ==========================================
        $ppData = [
            [
                'username' => 'pp_koordinator',
                'email' => 'koordinator.pp@unuja.ac.id',
                'nama' => 'Drs. Suparman, M.Psi',
                'no_hp' => '081334567940',
            ],
            [
                'username' => 'pp_asisten',
                'email' => 'asisten.pp@unuja.ac.id',
                'nama' => 'Ningsih, S.Kom',
                'no_hp' => '081334567941',
            ],
        ];

        foreach ($ppData as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'pp',
                    'nama' => $data['nama'],
                    'no_hp' => $data['no_hp'],
                    'is_active' => true,
                ]
            );
        }

        // ==========================================
        // INFO
        // ==========================================
        echo "\n";
        echo "✅ Dosen Pembina & Additional Roles Seeder berhasil dijalankan!\n";
        echo "\n";
        echo "📋 Data yang dibuat:\n";
        echo "   - 8 Dosen Pembina\n";
        echo "   - 3 Dekan (per fakultas)\n";
        echo "   - 2 Staff BAUAK tambahan\n";
        echo "   - 1 Asisten Warek3\n";
        echo "   - 1 Asisten Rektor\n";
        echo "   - 2 Staff PP\n";
        echo "\n";
        echo "🔑 Semua password: password\n";
        echo "\n";
    }
}

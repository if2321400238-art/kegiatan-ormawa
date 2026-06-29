<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DosenPembinaSeeder extends Seeder
{
    /**
     * Seed the dosen pembina and additional roles.
     */
    public function run(): void
    {
        // ==========================================
        // DOSEN UNUJA (snapshot katalog publik LP3M)
        // ==========================================
        $snapshotPath = database_path('seeders/data/dosen_lp3m.json');
        $dosenPembinaData = json_decode(file_get_contents($snapshotPath), true, flags: JSON_THROW_ON_ERROR);

        foreach ($dosenPembinaData as $data) {
            $email = $data['email'] ?: $data['nidn'].'@dosen.unuja.local';
            $dosen = User::withTrashed()->where('nidn', $data['nidn'])->first();

            if (! $dosen && $data['email']) {
                $existingByEmail = User::withTrashed()->where('email', $email)->first();
                if ($existingByEmail?->role === User::ROLE_DOSEN) {
                    $dosen = $existingByEmail;
                } elseif ($existingByEmail) {
                    $email = $data['nidn'].'@dosen.unuja.local';
                }
            }

            if ($dosen) {
                $dosen->restore();
                $dosen->update([
                    'nidn' => $data['nidn'],
                    'nama' => $data['nama'],
                    'email' => $email,
                    'program_studi' => $data['program_studi'],
                    'jabatan_fungsional' => $data['jabatan_fungsional'],
                    'is_active' => true,
                ]);

                continue;
            }

            $username = 'dosen_'.$data['nidn'];
            if (User::withTrashed()->where('username', $username)->exists()) {
                $username .= '_lp3m';
            }

            User::create([
                'username' => $username,
                'nidn' => $data['nidn'],
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'role' => User::ROLE_DOSEN,
                'nama' => $data['nama'],
                'program_studi' => $data['program_studi'],
                'jabatan_fungsional' => $data['jabatan_fungsional'],
                'is_active' => true,
            ]);
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
        echo '   - '.count($dosenPembinaData)." Dosen UNUJA (snapshot LP3M)\n";
        echo "   - 3 Dekan (per fakultas)\n";
        echo "   - 2 Staff BAUAK tambahan\n";
        echo "   - 1 Asisten Warek3\n";
        echo "   - 1 Asisten Rektor\n";
        echo "   - 2 Staff PP\n";
        echo "\n";
        echo "🔑 Akun dosen snapshot memakai password acak dan tidak dapat dipakai login langsung.\n";
        echo "\n";
    }
}

<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            'Pascasarjana',
            'Fakultas Agama Islam',
            'Fakultas Teknik',
            'Fakultas Kesehatan',
            'Fakultas Sosial dan Humaniora',
        ];

        $facultyRecords = [];
        foreach ($faculties as $facultyName) {
            $facultyRecords[$facultyName] = Fakultas::firstOrCreate([
                'nama' => $facultyName,
            ]);
        }

        $deans = [
            [
                'username' => 'dekan_pascasarjana',
                'email' => 'dekan.pascasarjana@unuja.ac.id',
                'nama' => 'Dr. H. Akmal Mundiri, M.Pd.',
                'role' => 'dekan',
                'fakultas' => 'Pascasarjana',
            ],
            [
                'username' => 'dekan_fai',
                'email' => 'dekan.fai@unuja.ac.id',
                'nama' => 'Dr. H. Ahmad Fawaid, M.Th.I.',
                'role' => 'dekan',
                'fakultas' => 'Fakultas Agama Islam',
            ],
            [
                'username' => 'dekan_teknik',
                'email' => 'dekan.teknik@unuja.ac.id',
                'nama' => 'Zainal Arifin, M.Kom.',
                'role' => 'dekan',
                'fakultas' => 'Fakultas Teknik',
            ],
            [
                'username' => 'dekan_kesehatan',
                'email' => 'dekan.kesehatan@unuja.ac.id',
                'nama' => 'Dr. Sri Astutik Andayani, S.Kep., M.Kes.',
                'role' => 'dekan',
                'fakultas' => 'Fakultas Kesehatan',
            ],
            [
                'username' => 'dekan_soshum',
                'email' => 'dekan.soshum@unuja.ac.id',
                'nama' => 'Dr. H. Chusnul Muali, M.Pd.',
                'role' => 'dekan',
                'fakultas' => 'Fakultas Sosial dan Humaniora',
            ],
        ];

        $dosenPembina = [
            [
                'username' => 'dr_ahmad_dahlan',
                'email' => 'ahmad.dahlan@unuja.ac.id',
                'nama' => 'Dr. Ahmad Dahlan, M.Pd',
                'role' => 'dosen',
                'no_hp' => '081334567890',
            ],
            [
                'username' => 'prof_siti_nurjanah',
                'email' => 'siti.nurjanah@unuja.ac.id',
                'nama' => 'Prof. Dr. Siti Nurjanah, M.Si',
                'role' => 'dosen',
                'no_hp' => '081334567891',
            ],
            [
                'username' => 'dr_abdullah_hasan',
                'email' => 'abdullah.hasan@unuja.ac.id',
                'nama' => 'Dr. Abdullah Hasan, M.Ag',
                'role' => 'dosen',
                'no_hp' => '081334567892',
            ],
            [
                'username' => 'ir_bambang_suryanto',
                'email' => 'bambang.suryanto@unuja.ac.id',
                'nama' => 'Ir. Bambang Suryanto, M.Kom',
                'role' => 'dosen',
                'no_hp' => '081334567893',
            ],
            [
                'username' => 'dr_slamet_riyadi',
                'email' => 'slamet.riyadi@unuja.ac.id',
                'nama' => 'Dr. Ir. Slamet Riyadi, M.T',
                'role' => 'dosen',
                'no_hp' => '081334567894',
            ],
            [
                'username' => 'dr_eka_prasetya',
                'email' => 'eka.prasetya@unuja.ac.id',
                'nama' => 'Dr. Eka Prasetya, S.E., M.M',
                'role' => 'dosen',
                'no_hp' => '081334567895',
            ],
            [
                'username' => 'drs_puji_lestari',
                'email' => 'puji.lestari@unuja.ac.id',
                'nama' => 'Drs. Puji Lestari, M.Pd',
                'role' => 'dosen',
                'no_hp' => '081334567896',
            ],
            [
                'username' => 'dr_rino_rahmat',
                'email' => 'rino.rahmat@unuja.ac.id',
                'nama' => 'Dr. Rino Rahmat, M.Si',
                'role' => 'dosen',
                'no_hp' => '081334567897',
            ],
        ];

        $users = [];

        foreach ($deans as $dekanData) {
            $fakultas = $facultyRecords[$dekanData['fakultas']];

            $users[$dekanData['email']] = User::updateOrCreate(
                ['email' => $dekanData['email']],
                [
                    'username' => $dekanData['username'],
                    'email' => $dekanData['email'],
                    'password' => Hash::make('password'),
                    'role' => $dekanData['role'],
                    'nama' => $dekanData['nama'],
                    'no_hp' => $dekanData['no_hp'] ?? null,
                    'fakultas_id' => $fakultas->id,
                    'is_active' => true,
                ]
            );

            $fakultas->dekan_user_id = $users[$dekanData['email']]->id;
            $fakultas->save();
        }

        foreach ($dosenPembina as $dosenData) {
            User::updateOrCreate(
                ['email' => $dosenData['email']],
                [
                    'username' => $dosenData['username'],
                    'email' => $dosenData['email'],
                    'password' => Hash::make('password'),
                    'role' => $dosenData['role'],
                    'nama' => $dosenData['nama'],
                    'no_hp' => $dosenData['no_hp'],
                    'is_active' => true,
                ]
            );
        }
    }
}

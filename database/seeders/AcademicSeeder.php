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

    }
}

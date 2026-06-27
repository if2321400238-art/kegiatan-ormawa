<?php

namespace Database\Seeders;

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mahasiswaData = [
            [
                'nim' => '210001',
                'username' => 'mahasiswa1',
                'email' => '210001@student.unuja.ac.id',
                'password' => 'password',
                'nama' => 'Rizky Pratama',
                'role' => 'mahasiswa',
                'ormawa' => [
                    'Badan Eksekutif Mahasiswa' => ['jabatan' => 'ketua', 'status' => true],
                    'Himpunan Mahasiswa Teknik Informatika' => ['jabatan' => 'anggota', 'status' => true],
                ],
            ],
            [
                'nim' => '210002',
                'username' => 'mahasiswa2',
                'email' => '210002@student.unuja.ac.id',
                'password' => 'password',
                'nama' => 'Laila Hasanah',
                'role' => 'mahasiswa',
                'ormawa' => [
                    'Himpunan Mahasiswa Islam' => ['jabatan' => 'sekretaris', 'status' => true],
                ],
            ],
            [
                'nim' => '210003',
                'username' => 'mahasiswa3',
                'email' => '210003@student.unuja.ac.id',
                'password' => 'password',
                'nama' => 'Aditya Nugraha',
                'role' => 'mahasiswa',
                'ormawa' => [
                    'Pergerakan Mahasiswa Islam Indonesia' => ['jabatan' => 'bendahara', 'status' => true],
                    'Badan Eksekutif Mahasiswa' => ['jabatan' => 'anggota', 'status' => false],
                ],
            ],
        ];

        foreach ($mahasiswaData as $data) {
            $mahasiswa = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nim' => $data['nim'],
                    'username' => $data['username'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'nama' => $data['nama'],
                    'is_active' => true,
                ]
            );

            foreach ($data['ormawa'] as $namaOrmawa => $membership) {
                $ormawa = Ormawa::where('nama_ormawa', $namaOrmawa)->first();

                if (!$ormawa) {
                    continue;
                }

                $mahasiswa->ormawas()->syncWithoutDetaching([
                    $ormawa->id => [
                        'jabatan' => $membership['jabatan'],
                        'status' => $membership['status'],
                    ],
                ]);
            }
        }
    }
}

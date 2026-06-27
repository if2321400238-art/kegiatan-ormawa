<?php

namespace Database\Seeders;

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
            ],
            [
                'nim' => '210002',
                'username' => 'mahasiswa2',
                'email' => '210002@student.unuja.ac.id',
                'password' => 'password',
                'nama' => 'Laila Hasanah',
                'role' => 'mahasiswa',
            ],
            [
                'nim' => '210003',
                'username' => 'mahasiswa3',
                'email' => '210003@student.unuja.ac.id',
                'password' => 'password',
                'nama' => 'Aditya Nugraha',
                'role' => 'mahasiswa',
            ],
        ];

        foreach ($mahasiswaData as $data) {
            User::create([
                'nim' => $data['nim'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'nama' => $data['nama'],
                'is_active' => true,
            ]);
        }
    }
}

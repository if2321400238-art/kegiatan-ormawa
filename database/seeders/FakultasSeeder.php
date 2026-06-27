<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\User;
use Illuminate\Database\Seeder;

class FakultasSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample faculties and optionally assign dekan if exists by email
        $faculties = [
            ['nama' => 'Fakultas Teknik', 'dekan_email' => 'dekan.ftik@unuja.ac.id'],
            ['nama' => 'Fakultas Ekonomi dan Bisnis', 'dekan_email' => 'dekan.feb@unuja.ac.id'],
            ['nama' => 'Fakultas Hukum', 'dekan_email' => 'dekan.fh@unuja.ac.id'],
        ];

        foreach ($faculties as $f) {
            $dekan = User::where('email', $f['dekan_email'])->first();
            
            $fakultas = Fakultas::firstOrCreate(
                ['nama' => $f['nama']],
                ['dekan_user_id' => $dekan?->id]
            );

            // Set fakultas_id on the user so the authorization checks work
            if ($dekan) {
                $dekan->update(['fakultas_id' => $fakultas->id]);
            }
        }
    }
}

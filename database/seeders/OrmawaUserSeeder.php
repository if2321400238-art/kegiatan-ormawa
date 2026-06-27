<?php

namespace Database\Seeders;

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrmawaUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get users and ormawas from database
        $users = User::where('role', 'ormawa')->get();
        $ormawas = Ormawa::all();

        if ($users->isEmpty() || $ormawas->isEmpty()) {
            $this->command->info('No users or ormawas found. Skipping OrmawaUserSeeder.');
            return;
        }

        // Define positions
        $positions = ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara', 'anggota'];

        // Attach users to ormawas with positions
        foreach ($ormawas as $index => $ormawa) {
            // Get 3-5 random users for each ormawa
            $randomUsers = $users->random(rand(3, 5));

            foreach ($randomUsers as $userIndex => $user) {
                // First user is ketua, second is wakil_ketua, rest are members
                $position = $userIndex === 0
                    ? 'ketua'
                    : ($userIndex === 1 ? 'wakil_ketua' : $positions[array_rand($positions)]);

                // Skip if already attached
                if (!$ormawa->users()->where('user_id', $user->id)->exists()) {
                    $ormawa->users()->attach($user->id, [
                        'jabatan' => $position,
                        'status' => true,
                    ]);
                }
            }

            $this->command->info("Attached users to Ormawa: {$ormawa->nama_ormawa}");
        }

        $this->command->info('OrmawaUserSeeder completed successfully!');
    }
}

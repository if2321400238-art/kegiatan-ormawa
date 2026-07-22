<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Pascasarjana', 'S2 PAI', 'Prodi S2 Pendidikan Agama Islam', 'Dr. Mona Novita, M.Pd', null],
            ['Pascasarjana', 'S2 MPI', 'Prodi S2 Manajemen Pendidikan Islam', 'Dr. Abu Hasan Agus R.,M.Pd.I', null],
            ['Pascasarjana', 'S2 SI', 'Prodi S2 Studi Islam', 'Dr. Umar Manshur, MA.', null],
            ['Pascasarjana', 'S3 SI', 'Prodi S3 Studi Islam', 'Dr. Moch. Tohet, M.Pd.I', null],
            ['Fakultas Agama Islam', 'S1 KPI', 'Prodi S1 Komunikasi dan Penyiaran Islam', 'Zakiyah Romadlany, M.Sos.', null],
            ['Fakultas Agama Islam', 'S1 PAI', 'Prodi S1 Pendidikan Agama Islam', 'Dr. H. Muhammad Munif, M.Pd.', 'https://scholar.google.com/citations?hl=id&authuser=1&user=QbaEp_AAAAAJ'],
            ['Fakultas Agama Islam', 'S1 MPI', 'Prodi S1 Manajemen Pendidikan Islam', 'Muhammad Kholil, S.Pd.I., M.Pd.', null],
            ['Fakultas Agama Islam', 'S1 PBA', 'Prodi S1 Pendidikan Bahasa Arab', "Dr. Mu'allim Wijaya, M.Pd.", null],
            ['Fakultas Agama Islam', 'S1 HKI-AS', 'Prodi S1 Hukum Keluarga Islam', 'Muhammad Zainuddin Sunarto, M.Hi.', null],
            ['Fakultas Agama Islam', 'S1 IQT', 'Prodi S1 Ilmu Al-Quran dan Tafsir', 'Abdul Basith, M.Th.I.', null],
            ['Fakultas Agama Islam', 'S1 ES', 'Prodi S1 Ekonomi Syariah', 'Moh. Idil Ghufron, M.E.I.', null],
            ['Fakultas Agama Islam', 'S1 PS', 'Prodi S1 Perbankan Syariah', 'Achmad Febrianto, M.E.', null],
            ['Fakultas Agama Islam', 'S1 PGMI', 'Prodi S1 Pendidikan Guru MI', 'Muhammad Mushfi El Iq Bali, M.Pd.', null],
            ['Fakultas Teknik', 'S1 IF', 'Prodi S1 Teknik Informatika', 'Andi Wijaya, M.Kom.', null],
            ['Fakultas Teknik', 'S1 TI', 'Prodi S1 Teknologi Informasi', 'Mochammad Faid, M.Kom.', null],
            ['Fakultas Teknik', 'S1 TE', 'Prodi S1 Teknik Elektro', 'Fuad Hasan, M.T.', null],
            ['Fakultas Teknik', 'S1 TP', 'Prodi S1 Teknologi Pertanian', 'Nur Elisa Faizaty, M.E', null],
            ['Fakultas Sosial dan Humaniora', 'S1 PBI', 'Prodi S1 Pendidikan Bahasa Inggris', 'Syaiful Islam, M.Pd.', 'https://scholar.google.com/citations?hl=id&authuser=1&user=fBPapo0AAAAJ'],
            ['Fakultas Sosial dan Humaniora', 'S1 MAT', 'Prodi S1 Pendidikan Matematika', 'Moh. Syadidul Itqan, M.Pd.', 'https://scholar.google.com/citations?hl=id&authuser=1&user=SqCCEgIAAAAJ'],
            ['Fakultas Sosial dan Humaniora', 'S1 HK', 'Prodi S1 Hukum', 'Ismail Marzuki, M.H', 'https://scholar.google.com/citations?user=UgaConEAAAAJ&hl=id'],
            ['Fakultas Sosial dan Humaniora', 'S1 EKN', 'Prodi S1 Ekonomi', 'Moh. Fakhri Siddiqi, M.Akun.', null],
            ['Fakultas Kesehatan', 'S1 KEP', 'Prodi S1 Keperawatan', 'Zainal Munir, Ns., M.Kep.', null],
            ['Fakultas Kesehatan', 'D3 KEB', 'Prodi D3 Kebidanan', 'Harwin Holilah Desyanti, S.Keb., BD.', 'https://www.instagram.com/harwin_khalilah/'],
            ['Fakultas Kesehatan', 'NERS', 'Prodi NERS (Profesi)', 'Baitus Sholehah, Ns., M.Kep.', null],
        ];

        foreach ($data as [$facultyName, $code, $name, $head, $url]) {
            $faculty = Fakultas::firstOrCreate(['nama' => $facultyName]);
            $key = Str::lower(str_replace([' ', '/'], ['-', '-'], $code));
            $user = User::updateOrCreate(['email' => "kaprodi.{$key}@unuja.ac.id"], [
                'username' => "kaprodi_".str_replace('-', '_', $key),
                'password' => Hash::make('password'),
                'role' => User::ROLE_KAPRODI,
                'nama' => $head,
                'fakultas_id' => $faculty->id,
                'program_studi' => $name,
                'is_active' => true,
            ]);

            $prodi = ProgramStudi::updateOrCreate(['kode' => $code], [
                'fakultas_id' => $faculty->id,
                'kaprodi_user_id' => $user->id,
                'nama' => $name,
                'profile_url' => $url,
                'is_lainnya' => false,
            ]);
            $user->update(['prodi_id' => $prodi->id]);
        }

        ProgramStudi::updateOrCreate(['kode' => 'LAINNYA'], [
            'nama' => 'Prodi Lainnya',
            'fakultas_id' => null,
            'kaprodi_user_id' => null,
            'is_lainnya' => true,
        ]);

        // Akun dosen warisan yang pernah dikonversi menjadi Kaprodi tetapi tidak
        // memiliki penugasan prodi tidak boleh memperoleh akses portal Kaprodi.
        User::where('role', User::ROLE_KAPRODI)
            ->whereNull('prodi_id')
            ->update(['is_active' => false]);
    }
}

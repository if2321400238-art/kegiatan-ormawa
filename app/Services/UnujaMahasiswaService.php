<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class UnujaMahasiswaService
{
    public function search(string $query, ?string $searchBy = null): array
    {
        $query = trim($query);
        $searchBy ??= preg_match('/^\d+$/', $query) ? 'nim' : 'nama';

        if (! in_array($searchBy, ['nim', 'nama'], true)) {
            throw new RuntimeException('Jenis pencarian mahasiswa tidak valid.');
        }

        [$client, $apiKey] = $this->authenticatedClient();

        $response = $client
            ->withHeader($this->apiKeyHeader(), $apiKey)
            ->get($this->searchUrl($searchBy, $query));

        $response->throw();

        $data = $response->json('data', []);
        if (Arr::isAssoc((array) $data)) {
            $data = [$data];
        }

        return collect($data)
            ->map(fn ($item) => $this->normalize((array) $item))
            ->filter(fn ($item) => filled($item['nim']) && filled($item['nama']))
            ->unique('nim')
            ->values()
            ->all();
    }

    public function findByNim(string $nim): array
    {
        $nim = trim($nim);
        $student = collect($this->search($nim, 'nim'))
            ->first(fn ($item) => strcasecmp($item['nim'], $nim) === 0);

        if (! $student) {
            throw new RuntimeException('NIM tidak ditemukan pada API mahasiswa UNUJA.');
        }

        return $student;
    }

    public function syncUserByNim(string $nim): User
    {
        $student = $this->findByNim($nim);
        $user = User::withTrashed()->where('nim', $student['nim'])->first();

        if ($user) {
            if ($user->role !== User::ROLE_MAHASISWA) {
                throw new RuntimeException('NIM tersebut sudah digunakan oleh akun dengan peran lain.');
            }

            if ($user->trashed()) {
                $user->restore();
            }

            $user->update([
                'nama' => $student['nama'],
                'program_studi' => $student['program_studi'],
                'is_active' => true,
            ]);

            if ($student['email']
                && str_ends_with($user->email, '@unuja.ac.id')
                && ! User::withTrashed()->where('email', $student['email'])->whereKeyNot($user->id)->exists()) {
                $user->update(['email' => $student['email']]);
            }

            return $user;
        }

        $email = $student['email'];
        if (! $email || User::withTrashed()->where('email', $email)->exists()) {
            $email = $student['nim'].'@unuja.ac.id';
        }

        $username = $student['nim'];
        if (User::withTrashed()->where('username', $username)->exists()) {
            $username = 'mhs_'.$student['nim'];
        }

        return User::create([
            'username' => $username,
            'nim' => $student['nim'],
            'email' => $email,
            'password' => Hash::make($student['nim']),
            'must_change_password' => true,
            'role' => User::ROLE_MAHASISWA,
            'nama' => $student['nama'],
            'program_studi' => $student['program_studi'],
            'is_active' => true,
        ]);
    }

    private function authenticatedClient(): array
    {
        $username = config('services.unuja.username');
        $password = config('services.unuja.password');

        if (blank($username) || blank($password)) {
            throw new RuntimeException('Kredensial API mahasiswa UNUJA belum dikonfigurasi.');
        }

        $cookieJar = new CookieJar;
        $client = $this->client()->withOptions(['cookies' => $cookieJar]);
        $response = $client->asForm()->post(
            config('services.unuja.login_url'),
            ['nama_pengguna' => $username, 'kata_kunci' => $password]
        );

        $response->throw();
        $key = $response->json($this->apiKeyHeader());

        if (blank($key)) {
            throw new RuntimeException('API UNUJA tidak mengembalikan kunci autentikasi.');
        }

        return [$client, $key];
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout((int) config('services.unuja.timeout', 10))
            ->retry(2, 250);
    }

    private function searchUrl(string $searchBy, string $query): string
    {
        $baseUrl = rtrim((string) config('services.unuja.base_url'), '/');
        if ($baseUrl === '') {
            throw new RuntimeException('URL API mahasiswa UNUJA belum dikonfigurasi.');
        }

        return $baseUrl.'/mst/mahasiswa/cari/b/'.$searchBy.'/p/'.rawurlencode($query);
    }

    private function normalize(array $student): array
    {
        $value = fn (array $keys) => collect($keys)
            ->map(fn ($key) => data_get($student, $key))
            ->first(fn ($item) => filled($item));

        return [
            'nim' => trim((string) $value(['nim', 'NIM', 'nim_mahasiswa', 'nomor_induk'])),
            'nama' => trim((string) $value(['nama', 'NAMA', 'nama_mahasiswa', 'nm_mhs'])),
            'email' => filter_var($value(['email', 'EMAIL', 'email_mahasiswa']), FILTER_VALIDATE_EMAIL) ?: null,
            'program_studi' => $value(['program_studi', 'prodi', 'nama_prodi', 'nm_prodi']),
            'fakultas' => $value(['fakultas', 'nama_fakultas', 'nm_fakultas']),
        ];
    }

    private function apiKeyHeader(): string
    {
        return (string) config('services.unuja.api_key_header', 'unujasimptapikey');
    }
}

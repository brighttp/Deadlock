<?php

namespace Tests;

use App\Models\Category;
use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\NullHandler;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Pengaman: test memakai RefreshDatabase (menghapus semua tabel),
     * jadi jangan pernah sampai terhubung ke database Supabase bersama.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $default = $app['config']->get('database.default');
        $host = (string) $app['config']->get("database.connections.{$default}.host");

        if (str_contains($host, 'supabase')) {
            throw new RuntimeException("Test ditolak: koneksi {$default} mengarah ke Supabase ({$host}).");
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Log alert dari test tidak boleh masuk ke storage/logs/alert-*.log yang dibaca halaman Monitoring.
        config(['logging.channels.alert' => ['driver' => 'monolog', 'handler' => NullHandler::class]]);

        // Test tidak boleh memanggil API sungguhan (misalnya API kurs); request yang tidak di-fake langsung gagal.
        Http::preventStrayRequests();
    }

    protected function buatAdmin(array $attrs = []): User
    {
        return User::create([
            'name' => 'Admin Uji',
            'email' => 'admin'.uniqid().'@haven.test',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            ...$attrs,
        ]);
    }

    protected function buatAnggota(array $attrs = []): User
    {
        $user = User::create([
            'name' => 'Anggota Uji',
            'email' => 'anggota'.uniqid().'@haven.test',
            'password' => 'password123',
            'role' => User::ROLE_ANGGOTA,
            ...$attrs,
        ]);
        $user->profile()->create(['phone' => '081234567890']);

        return $user;
    }

    protected function buatUnit(array $attrs = []): Unit
    {
        return Unit::create([
            'unit_code' => 'UJI-'.strtoupper(uniqid()),
            'name' => 'Senapan Uji',
            ...$attrs,
        ]);
    }

    protected function buatKategori(string $name = 'Senjata'): Category
    {
        return Category::create(['name' => $name]);
    }

    // Model Loan di memori (tidak disimpan) untuk menguji rumus tanpa database.
    protected function loanDummy(string $loanDate, string $dueDate, ?string $returnDate = null): Loan
    {
        return new Loan([
            'loan_date' => $loanDate,
            'due_date' => $dueDate,
            'return_date' => $returnDate,
            'status' => $returnDate ? Loan::STATUS_SELESAI : Loan::STATUS_AKTIF,
        ]);
    }
}

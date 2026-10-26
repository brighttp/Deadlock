<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Login dan pembatasan halaman per role (otorisasi di server).
 */
class AksesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/kategori')->assertRedirect('/login');
    }

    public function test_halaman_login_tampil(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_login_berhasil_dengan_email_huruf_besar(): void
    {
        $anggota = $this->buatAnggota(['email' => 'ellie@haven.test']);

        $this->post('/login', ['email' => 'ELLIE@haven.test', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($anggota);
    }

    public function test_login_gagal_menampilkan_pesan(): void
    {
        $this->buatAnggota(['email' => 'ellie@haven.test']);

        $this->from('/login')
            ->post('/login', ['email' => 'ellie@haven.test', 'password' => 'salah-total'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']); // MSG-01

        $this->assertGuest();
    }

    public function test_anggota_yang_dihapus_tidak_bisa_login(): void
    {
        $anggota = $this->buatAnggota(['email' => 'joel@haven.test']);
        $anggota->delete();

        $this->post('/login', ['email' => 'joel@haven.test', 'password' => 'password123']);

        $this->assertGuest();
    }

    public function test_anggota_tidak_bisa_membuka_halaman_admin(): void
    {
        $anggota = $this->buatAnggota();

        foreach (['/kategori', '/unit', '/anggota', '/pinjaman', '/riwayat'] as $url) {
            $this->actingAs($anggota)->get($url)->assertForbidden();
        }
    }

    public function test_admin_bisa_membuka_halaman_admin(): void
    {
        $admin = $this->buatAdmin();

        foreach (['/dashboard', '/kategori', '/unit', '/anggota', '/pinjaman', '/riwayat', '/katalog'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_tidak_bisa_meminjam(): void
    {
        $unit = $this->buatUnit();

        $this->actingAs($this->buatAdmin())
            ->post(route('pinjam', $unit))
            ->assertForbidden();
    }
}

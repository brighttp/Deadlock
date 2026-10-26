<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registrasi mandiri (revisi asesor): tamu mendaftar sendiri dan selalu menjadi anggota.
 */
class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $ubah = []): array
    {
        return [
            'name' => 'Rick Grimes',
            'email' => 'rick@haven.org',
            'phone' => '0812-3344-5566',
            'address' => 'Blok C, Haven Sector 04',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            ...$ubah,
        ];
    }

    public function test_tamu_bisa_membuka_halaman_daftar(): void
    {
        $this->get('/daftar')->assertOk()->assertSee('Daftar sebagai Survivor');
        $this->get('/login')->assertOk()->assertSee(route('register'));
    }

    public function test_tamu_bisa_daftar_lalu_diarahkan_ke_login(): void
    {
        $this->post('/daftar', $this->data())
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Registrasi berhasil. Silakan masuk.'); // MSG-21

        $this->assertGuest();

        $user = User::where('email', 'rick@haven.org')->firstOrFail();
        $this->assertTrue($user->isAnggota());
        $this->assertSame('081233445566', $user->profile->phone);
        $this->assertSame('Blok C, Haven Sector 04', $user->profile->address);
    }

    public function test_akun_hasil_daftar_bisa_login(): void
    {
        $this->post('/daftar', $this->data());

        $this->post('/login', ['email' => 'rick@haven.org', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_input_role_admin_diabaikan(): void
    {
        $this->post('/daftar', $this->data(['role' => 'admin']));

        $this->assertSame(User::ROLE_ANGGOTA, User::where('email', 'rick@haven.org')->value('role'));
    }

    public function test_email_terdaftar_ditolak_walau_beda_huruf_besar(): void
    {
        $this->buatAnggota(['email' => 'rick@haven.org']);

        $this->post('/daftar', $this->data(['email' => 'RICK@Haven.org']))
            ->assertSessionHasErrors(['email' => 'Email sudah terdaftar.']); // MSG-04
    }

    public function test_password_pendek_tidak_cocok_dan_nomor_hp_salah_ditolak(): void
    {
        $this->post('/daftar', $this->data([
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
            'phone' => '12345',
        ]))->assertSessionHasErrors([
            'password' => 'Password minimal 8 karakter.', // MSG-06
            'phone' => 'Format nomor HP tidak valid.', // MSG-07
        ]);

        $this->post('/daftar', $this->data(['password_confirmation' => 'beda12345']))
            ->assertSessionHasErrors(['password' => 'Konfirmasi password tidak cocok.']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_yang_sudah_login_tidak_bisa_membuka_halaman_daftar(): void
    {
        $this->actingAs($this->buatAnggota())->get('/daftar')->assertRedirect('/dashboard');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan hapus/tambah pada kategori dan anggota (Admin).
 */
class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_kategori_yang_dipakai_unit_tidak_bisa_dihapus(): void
    {
        $kategori = $this->buatKategori();
        $this->buatUnit()->categories()->attach($kategori);

        $this->actingAs($this->buatAdmin())
            ->delete(route('kategori.destroy', $kategori))
            ->assertSessionHas('error', 'Kategori masih dipakai unit dan tidak dapat dihapus.');

        $this->assertDatabaseHas('categories', ['id' => $kategori->id]);
    }

    public function test_kategori_kosong_bisa_dihapus(): void
    {
        $kategori = $this->buatKategori();

        $this->actingAs($this->buatAdmin())
            ->delete(route('kategori.destroy', $kategori))
            ->assertRedirect(route('kategori.index'));

        $this->assertDatabaseMissing('categories', ['id' => $kategori->id]);
    }

    public function test_anggota_dengan_pinjaman_aktif_tidak_bisa_dihapus(): void
    {
        $anggota = $this->buatAnggota();
        app(LoanService::class)->pinjam($anggota, $this->buatUnit());

        $this->actingAs($this->buatAdmin())
            ->delete(route('anggota.destroy', $anggota))
            ->assertSessionHas('error', 'Anggota masih memiliki pinjaman aktif dan tidak dapat dihapus.'); // MSG-11

        $this->assertNotSoftDeleted($anggota);
    }

    public function test_anggota_tanpa_pinjaman_dihapus_secara_soft_delete(): void
    {
        $anggota = $this->buatAnggota();

        $this->actingAs($this->buatAdmin())
            ->delete(route('anggota.destroy', $anggota))
            ->assertRedirect(route('anggota.index'));

        $this->assertSoftDeleted($anggota);
    }

    public function test_tambah_anggota_baru_selalu_ber_role_anggota(): void
    {
        $this->actingAs($this->buatAdmin())
            ->post(route('anggota.store'), [
                'name' => 'Tommy',
                'email' => 'Tommy@Haven.test',
                'password' => 'password123',
                'phone' => '0812-3344-5566',
                'role' => 'admin', // dicoba disisipkan, harus diabaikan
            ])
            ->assertRedirect(route('anggota.index'));

        $tommy = User::where('email', 'tommy@haven.test')->firstOrFail();
        $this->assertSame(User::ROLE_ANGGOTA, $tommy->role);
        $this->assertSame('081233445566', $tommy->profile->phone);
    }

    public function test_validasi_email_duplikat_dan_nomor_hp(): void
    {
        $this->buatAnggota(['email' => 'ellie@haven.test']);

        $this->actingAs($this->buatAdmin())
            ->post(route('anggota.store'), [
                'name' => 'Ellie Palsu',
                'email' => 'ELLIE@haven.test',
                'password' => 'pendek',
                'phone' => '0712345',
            ])
            ->assertSessionHasErrors([
                'email' => 'Email sudah terdaftar.', // MSG-04
                'password' => 'Password minimal 8 karakter.', // MSG-06
                'phone' => 'Format nomor HP tidak valid.', // MSG-07
            ]);
    }

    public function test_email_anggota_yang_dihapus_bisa_dipakai_lagi(): void
    {
        $this->buatAnggota(['email' => 'riley@haven.test'])->delete();

        $this->actingAs($this->buatAdmin())
            ->post(route('anggota.store'), [
                'name' => 'Riley Baru',
                'email' => 'riley@haven.test',
                'password' => 'password123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, User::withTrashed()->where('email', 'riley@haven.test')->count());
    }
}

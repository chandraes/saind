<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;

/** Modul 5a: Admin & SU mengolah Nota Tagihan sampai Invoice Tagihan dari mobile, sama dengan web. */
class MobileCustomerBillingTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('singkatan')->nullable();
            foreach (['is_kompensasi_jr', 'is_penyesuaian_bbm', 'is_achievement', 'ppn', 'pph', 'tagihan_dari', 'csr', 'nominal_penalty'] as $flag) {
                $table->integer($flag)->default(0);
            }
            $table->integer('status')->default(1);
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            foreach (['vendor_id', 'vehicle_id', 'customer_id', 'rute_id', 'nomor_uang_jalan'] as $field) {
                $table->integer($field);
            }
            $table->date('tanggal')->nullable();
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            foreach (['status', 'void', 'tagihan', 'keranjang', 'nota_fisik'] as $flag) {
                $table->integer($flag)->default(0);
            }
            $table->integer('do_checker_id')->nullable();
            foreach (['harga_customer', 'harga_vendor', 'harga_csr', 'nominal_tagihan', 'nominal_bayar', 'nominal_csr', 'nominal_bonus', 'profit'] as $money) {
                $table->decimal($money, 15, 2)->default(0);
            }
            $table->string('nota_muat')->nullable()->unique();
            $table->string('nota_bongkar')->nullable()->unique();
            foreach (['tanggal_muat', 'tanggal_bongkar', 'tonase', 'timbangan_bongkar', 'gross_muat', 'tarra_muat', 'gross_bongkar', 'tarra_bongkar'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('transaksi_additionals', function (Blueprint $table): void {
            $table->id();
            $table->integer('transaksi_id');
            $table->integer('customer_id');
            $table->integer('vendor_id')->nullable();
            $table->integer('rute_id')->nullable();
            $table->integer('jarak')->nullable();
            $table->string('jenis');
            $table->integer('status')->default(0);
            $table->unique(['transaksi_id', 'jenis']);
            $table->timestamps();
        });
        Schema::create('invoice_additionals', function (Blueprint $table): void {
            $table->id();
            $table->integer('customer_id');
            $table->string('jenis');
            $table->integer('status')->default(1);
            $table->boolean('is_finished')->default(false);
            $table->bigInteger('nominal')->default(0);
            $table->timestamps();
        });
        Schema::create('invoice_additional_details', function (Blueprint $table): void {
            $table->id();
            $table->integer('invoice_additional_id');
            $table->integer('transaksi_id');
            $table->integer('transaksi_additional_id')->nullable();
        });
        Schema::create('invoice_tagihans', function (Blueprint $table): void {
            $table->id();
            $table->integer('customer_id');
            $table->integer('no_invoice');
            $table->string('periode');
            $table->integer('lunas')->default(0);
            $table->integer('ppn_dipungut')->default(0);
            foreach (['tanggal', 'tanggal_hardcopy', 'estimasi_pembayaran'] as $date) {
                $table->date($date)->nullable();
            }
            foreach (['penyesuaian', 'penalty', 'penalty_akhir', 'ppn', 'pph', 'total_awal', 'kompensasi_jr', 'penyesuaian_bbm', 'achievement', 'total_tagihan', 'total_bayar', 'sisa_tagihan'] as $money) {
                $table->decimal($money, 15, 2)->default(0);
            }
            foreach (['invoice_kompensasi_jr_id', 'invoice_penyesuaian_bbm_id', 'invoice_achievement_id'] as $field) {
                $table->integer($field)->nullable();
            }
            $table->string('no_resi')->nullable();
            $table->string('no_validasi')->nullable();
            $table->timestamps();
        });
        Schema::create('invoice_tagihan_details', function (Blueprint $table): void {
            $table->id();
            $table->integer('invoice_tagihan_id');
            $table->integer('transaksi_id');
            $table->timestamps();
        });
        foreach (['ppn_keluarans', 'pph_perusahaans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->integer('invoice_tagihan_id');
                $table->string('uraian');
                $table->decimal('nominal', 15, 2);
                $table->integer('dipungut')->nullable();
                $table->timestamps();
            });
        }
        Schema::create('password_konfirmasis', function (Blueprint $table): void {
            $table->id();
            $table->string('password');
            $table->timestamps();
        });
        DB::table('password_konfirmasis')->insert(['password' => 'konfirmasi-123']);
        Schema::create('kas_besars', function (Blueprint $table): void {
            $table->id();
            $table->string('uraian');
            $table->integer('jenis_transaksi_id');
            $table->bigInteger('nominal_transaksi');
            $table->bigInteger('saldo');
            $table->bigInteger('modal_investor_terakhir')->default(0);
            $table->integer('nomor_kode_tagihan')->nullable();
            $table->date('tanggal');
            foreach (['transfer_ke', 'no_rekening', 'bank'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('rekenings', function (Blueprint $table): void {
            $table->id();
            foreach (['untuk', 'nama_rekening', 'nomor_rekening', 'nama_bank'] as $field) {
                $table->string($field);
            }
        });
        Schema::create('group_was', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->string('nama_group');
        });
        foreach (['ppn_keluarans', 'pph_perusahaans'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->boolean('onhold')->default(true));
        }
        DB::table('kas_besars')->insert(['uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 1000, 'saldo' => 1000000, 'modal_investor_terakhir' => 50,
            'nomor_kode_tagihan' => 4, 'tanggal' => '2026-11-01', 'created_at' => '2026-11-01 08:00:00']);
        DB::table('rekenings')->insert(['untuk' => 'kas-besar', 'nama_rekening' => 'PT DS Groups Indonesia', 'nomor_rekening' => '123', 'nama_bank' => 'BCA']);
        DB::table('group_was')->insert(['untuk' => 'kas-besar', 'nama_group' => 'kas-besar-test']);
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor A', 'nickname' => 'VA']);
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288', 'do_count' => 5]);
        DB::table('customers')->insert([
            ['id' => 1, 'nama' => 'Tambang A', 'singkatan' => 'TA', 'is_kompensasi_jr' => 1, 'ppn' => 1, 'pph' => 1, 'tagihan_dari' => 2, 'nominal_penalty' => 0],
            ['id' => 2, 'nama' => 'Tambang B', 'singkatan' => 'TB', 'is_kompensasi_jr' => 0, 'ppn' => 0, 'pph' => 0, 'tagihan_dari' => 1, 'nominal_penalty' => 0],
        ]);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Pit - Port', 'jarak' => 40]);
        foreach ([[1, 1], [2, 1], [3, 2]] as [$id, $customer]) {
            DB::table('kas_uang_jalans')->insert(['id' => $id, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => $customer, 'rute_id' => 1, 'nomor_uang_jalan' => $id + 6, 'tanggal' => '2026-11-0'.$id]);
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => $id, 'status' => 3, 'harga_customer' => 100, 'harga_vendor' => 80,
                'nominal_tagihan' => 1000000 * $id, 'tonase' => '30.5', 'timbangan_bongkar' => '30', 'nota_muat' => 'M'.$id, 'nota_bongkar' => 'B'.$id,
                'tanggal_muat' => '2026-11-0'.$id, 'tanggal_bongkar' => '2026-11-0'.$id.' 10:00:00']);
        }
    }

    private function headers(string $role): array
    {
        $user = User::factory()->create(['username' => Str::uuid()->toString(), 'password' => 'secret-pass', 'role' => $role]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    private function invoiceForm(array $ids, array $overrides = []): array
    {
        return $overrides + ['penyesuaian' => 50000, 'penalty' => 10000, 'penalty_akhir' => 5000, 'tanggal_hardcopy' => '2026-11-18',
            'estimasi_pembayaran' => '2026-12-18', 'no_resi' => 'RESI-1', 'no_validasi' => 'VAL-1', 'ppn_dipungut' => true, 'transaction_ids' => $ids];
    }

    public function test_list_gives_admins_the_edit_fields_and_cart_count(): void
    {
        DB::table('transaksis')->where('id', 2)->update(['keranjang' => 1]);
        $this->getJson('/api/v1/customers/1/bills', $this->headers('admin'))->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('meta.cart_count', 1)
            ->assertJsonPath('data.0.edit', ['nota_muat' => 'M1', 'nota_bongkar' => 'B1', 'tanggal_muat' => '2026-11-01', 'tanggal_bongkar' => '2026-11-01',
                'tonase' => 30.5, 'gross_muat' => null, 'tarra_muat' => null, 'timbangan_bongkar' => 30, 'gross_bongkar' => null, 'tarra_bongkar' => null, 'physical_note' => false]);
        $this->getJson('/api/v1/customers/1/bills', $this->headers('general-manager'))->assertOk()
            ->assertJsonMissingPath('data.0.edit')->assertJsonMissingPath('meta.cart_count');
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_physical_note_is_set_idempotently_and_unchecking_needs_the_password(string $role): void
    {
        $headers = $this->headers($role);
        $this->postJson('/api/v1/customers/1/bills/1/physical-note', ['checked' => true], $headers)->assertOk();
        $this->postJson('/api/v1/customers/1/bills/1/physical-note', ['checked' => true], $headers)->assertOk();
        $this->assertDatabaseHas('transaksis', ['id' => 1, 'nota_fisik' => 1]);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 4]);

        $this->postJson('/api/v1/customers/1/bills/1/physical-note', ['checked' => false, 'password' => 'salah'], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.password.0', 'Password yang anda masukan Salah');
        $this->postJson('/api/v1/customers/1/bills/1/physical-note', ['checked' => false, 'password' => 'konfirmasi-123'], $headers)->assertOk();
        $this->assertDatabaseHas('transaksis', ['id' => 1, 'nota_fisik' => 0, 'do_checker_id' => null]);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 5]);
        // Nota customer lain: tidak ditemukan.
        $this->postJson('/api/v1/customers/1/bills/3/physical-note', ['checked' => true], $headers)->assertNotFound();
    }

    public function test_update_recalculates_the_bill_like_the_web(): void
    {
        $headers = $this->headers('admin');
        $form = ['nota_muat' => 'M1-FIX', 'nota_bongkar' => 'B1-FIX', 'tanggal_muat' => '2026-11-01', 'tanggal_bongkar' => '2026-11-02',
            'tonase' => 31, 'timbangan_bongkar' => 29.5, 'gross_bongkar' => 44.5, 'tarra_bongkar' => 15];
        $this->patchJson('/api/v1/customers/1/bills/1', $form, $headers)->assertOk()->assertJsonPath('message', 'Nota tagihan berhasil diubah.');
        $row = DB::table('transaksis')->find(1);
        $this->assertSame('M1-FIX', $row->nota_muat);
        $this->assertStringStartsWith('2026-11-02 ', $row->tanggal_bongkar);
        // tagihan_dari 2: timbangan bongkar × jarak × harga.
        $this->assertEquals(29.5 * 40 * 100, (float) $row->nominal_tagihan);
        $this->assertEquals(29.5 * 40 * 80, (float) $row->nominal_bayar);
        $this->assertEquals(29.5 * 500, (float) $row->nominal_bonus);

        $this->patchJson('/api/v1/customers/1/bills/1', ['nota_bongkar' => 'B2'] + $form, $headers)
            ->assertUnprocessable()->assertJsonPath('errors.nota.0', 'Terdapat nota yang sama!!');
        $this->patchJson('/api/v1/customers/1/bills/1', ['tanggal_bongkar' => '2026-10-30'] + $form, $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('tanggal_bongkar');
    }

    public function test_cart_then_checkout_creates_the_invoice_like_the_web(): void
    {
        $headers = $this->headers('su');
        $this->postJson('/api/v1/customers/1/bill-cart', ['ids' => [1, 2, 3]], $headers)->assertOk()
            ->assertJsonPath('message', '2 nota dimasukkan ke keranjang tagihan.');
        $this->assertSame(0, (int) DB::table('transaksis')->find(3)->keranjang);
        $this->assertDatabaseHas('transaksi_additionals', ['transaksi_id' => 1, 'jenis' => 'kompensasi_jr', 'vendor_id' => 1, 'jarak' => 40]);
        // Kompensasi JR yang sudah disetujui ikut ditagih.
        $additional = DB::table('invoice_additionals')->insertGetId(['customer_id' => 1, 'jenis' => 'kompensasi_jr', 'nominal' => 200000]);
        $extra = DB::table('transaksi_additionals')->where('transaksi_id', 1)->value('id');
        DB::table('invoice_additional_details')->insert(['invoice_additional_id' => $additional, 'transaksi_id' => 1, 'transaksi_additional_id' => $extra]);

        $this->getJson('/api/v1/customers/1/bill-cart', $headers)->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.transaction_ids', [1, 2])->assertJsonPath('meta.total_bill', 3000000)
            ->assertJsonPath('meta.customer.ppn', true)->assertJsonPath('meta.additionals.0', ['kind' => 'kompensasi_jr', 'label' => 'Kompensasi Jalan Rusak', 'amount' => 200000]);

        $this->postJson('/api/v1/customers/1/bill-cart/checkout', $this->invoiceForm([1]), $headers)
            ->assertUnprocessable()->assertJsonPath('errors.transaction_ids.0', 'Isi keranjang berubah sejak dibuka. Muat ulang dan periksa kembali.');
        $this->postJson('/api/v1/customers/1/bill-cart/checkout', $this->invoiceForm([1, 2]), $headers)
            ->assertOk()->assertJsonPath('message', 'Invoice tagihan Periode 1 berhasil dibuat.');

        // total = 3.000.000 + 200.000 + 50.000 − 10.000 = 3.240.000; PPN 11%, PPh 2%, PPN dipungut, penalty akhir 5.000.
        $total = 3240000;
        $invoice = DB::table('invoice_tagihans')->first();
        $this->assertEquals($total + $total * 0.11 - $total * 0.02 - 5000, (float) $invoice->total_tagihan);
        $this->assertEquals((float) $invoice->total_tagihan, (float) $invoice->sisa_tagihan);
        $this->assertEquals(200000, (float) $invoice->kompensasi_jr);
        $this->assertSame($additional, (int) $invoice->invoice_kompensasi_jr_id);
        $this->assertSame('2026-12-18', $invoice->estimasi_pembayaran);
        $this->assertDatabaseHas('ppn_keluarans', ['invoice_tagihan_id' => $invoice->id, 'uraian' => 'PPN Periode 1', 'dipungut' => 1]);
        $this->assertDatabaseHas('pph_perusahaans', ['invoice_tagihan_id' => $invoice->id, 'uraian' => 'PPh Periode 1']);
        $this->assertSame([1, 2], DB::table('invoice_tagihan_details')->orderBy('transaksi_id')->pluck('transaksi_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame(2, DB::table('transaksis')->where('tagihan', 1)->where('keranjang', 0)->count());
        $this->assertDatabaseHas('invoice_additionals', ['id' => $additional, 'is_finished' => 1]);
        $this->assertDatabaseHas('transaksi_additionals', ['id' => $extra, 'status' => 3]);

        // Diketuk ulang: keranjang sudah kosong, tidak ada invoice kedua.
        $this->postJson('/api/v1/customers/1/bill-cart/checkout', $this->invoiceForm([1, 2]), $headers)->assertUnprocessable();
        $this->assertSame(1, DB::table('invoice_tagihans')->count());
    }

    public function test_removing_from_the_cart_cancels_its_additional_invoice(): void
    {
        $headers = $this->headers('admin');
        $this->postJson('/api/v1/customers/1/bill-cart', ['ids' => [1]], $headers)->assertOk();
        $additional = DB::table('invoice_additionals')->insertGetId(['customer_id' => 1, 'jenis' => 'kompensasi_jr', 'nominal' => 200000]);
        DB::table('invoice_additional_details')->insert(['invoice_additional_id' => $additional, 'transaksi_id' => 1]);

        $this->deleteJson('/api/v1/customers/1/bill-cart/1', [], $headers)->assertOk();
        $this->assertDatabaseHas('transaksis', ['id' => 1, 'keranjang' => 0]);
        $this->assertDatabaseMissing('transaksi_additionals', ['transaksi_id' => 1]);
        $this->assertDatabaseMissing('invoice_additionals', ['id' => $additional]);
        $this->deleteJson('/api/v1/customers/1/bill-cart/1', [], $headers)->assertNotFound();
    }

    public function test_other_roles_cannot_change_customer_bills(): void
    {
        foreach (['general-manager', 'direksi', 'operasional', 'vendor'] as $role) {
            $headers = $this->headers($role);
            $this->postJson('/api/v1/customers/1/bills/1/physical-note', ['checked' => true], $headers)->assertForbidden();
            $this->postJson('/api/v1/customers/1/bill-cart', ['ids' => [1]], $headers)->assertForbidden();
            $this->getJson('/api/v1/customers/1/bill-cart', $headers)->assertForbidden();
            $this->postJson('/api/v1/customers/1/bill-cart/checkout', $this->invoiceForm([1]), $headers)->assertForbidden();
        }
        $this->assertDatabaseHas('transaksis', ['id' => 1, 'nota_fisik' => 0, 'keranjang' => 0]);
    }

    private function openInvoice(): int
    {
        $id = DB::table('invoice_tagihans')->insertGetId(['customer_id' => 1, 'no_invoice' => 3, 'periode' => 'Periode 3', 'tanggal' => '2026-11-10',
            'total_tagihan' => 1000000, 'sisa_tagihan' => 1000000, 'total_bayar' => 0]);
        DB::table('ppn_keluarans')->insert(['invoice_tagihan_id' => $id, 'uraian' => 'PPN', 'nominal' => 110000]);
        DB::table('pph_perusahaans')->insert(['invoice_tagihan_id' => $id, 'uraian' => 'PPh', 'nominal' => 20000]);

        return $id;
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_installments_then_paying_off_record_kas_besar_once(string $role): void
    {
        $id = $this->openInvoice();
        $sent = [];
        $this->mock(GroupWa::class)->shouldReceive('sendWa')->twice()->andReturnUsing(function (string $to, string $text) use (&$sent): bool {
            $sent[] = [$to, $text];

            return true;
        });
        $headers = $this->headers($role);
        $this->getJson('/api/v1/billing-invoices', $headers)->assertOk()->assertJsonPath('invoices.0.remaining', 1000000);

        $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 1000000, 'installment' => 1200000], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.cicilan.0', 'Cicilan tidak boleh lebih besar dari sisa tagihan');
        $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 1000000, 'installment' => 400000], $headers)
            ->assertOk()->assertJsonPath('message', 'Cicilan berhasil dicatat.')->assertJsonMissingPath('warning');
        $this->assertDatabaseHas('invoice_tagihans', ['id' => $id, 'lunas' => 0, 'total_bayar' => 400000, 'sisa_tagihan' => 600000]);
        $this->assertDatabaseHas('kas_besars', ['uraian' => 'Cicil TA - Periode 3', 'nominal_transaksi' => 400000, 'saldo' => 1400000,
            'modal_investor_terakhir' => 50, 'nomor_kode_tagihan' => null, 'bank' => 'BCA', 'transfer_ke' => 'PT DS Groups In']);
        $this->assertDatabaseHas('ppn_keluarans', ['invoice_tagihan_id' => $id, 'onhold' => 1]);

        // Sisa yang sudah usang ditolak.
        $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 1000000], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.amount.0', 'Sisa tagihan berubah sejak dibuka. Muat ulang dan periksa kembali.');
        $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 600000], $headers)
            ->assertOk()->assertJsonPath('message', 'Invoice berhasil dilunasi.');
        $this->assertDatabaseHas('invoice_tagihans', ['id' => $id, 'lunas' => 1, 'total_bayar' => 1000000, 'sisa_tagihan' => 0]);
        $this->assertDatabaseHas('kas_besars', ['uraian' => 'TA - Periode 3', 'nominal_transaksi' => 600000, 'saldo' => 2000000, 'nomor_kode_tagihan' => 5]);
        $this->assertDatabaseHas('ppn_keluarans', ['invoice_tagihan_id' => $id, 'onhold' => 0]);
        $this->assertDatabaseHas('pph_perusahaans', ['invoice_tagihan_id' => $id, 'onhold' => 0]);
        $this->assertSame('kas-besar-test', $sent[0][0]);
        $this->assertStringContainsString('*CICILAN INVOICE*', $sent[0][1]);
        $this->assertStringContainsString('*PEMBAYARAN INVOICE*', $sent[1][1]);
        $this->assertStringContainsString('Rp. 2.000.000', $sent[1][1]);

        // Diketuk ulang: sudah lunas, tidak ada uang masuk kedua.
        $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 0], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.invoice.0', 'Invoice ini sudah lunas.');
        $this->assertSame(3, DB::table('kas_besars')->count());
    }

    public function test_a_failed_whatsapp_keeps_the_payment_and_warns(): void
    {
        $id = $this->openInvoice();
        $this->mock(GroupWa::class)->shouldReceive('sendWa')->once()->andThrow(new \RuntimeException('Simulated failure'));
        $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 1000000], $this->headers('admin'))
            ->assertOk()->assertJsonPath('warning', 'Notifikasi WA Kas Besar belum berhasil dikirim. Pembayaran sudah tersimpan; jangan ulangi.');
        $this->assertDatabaseHas('invoice_tagihans', ['id' => $id, 'lunas' => 1]);
    }

    public function test_executives_cannot_pay_invoices_and_see_no_remaining_amount(): void
    {
        $id = $this->openInvoice();
        foreach (['general-manager', 'direksi'] as $role) {
            $headers = $this->headers($role);
            $this->getJson('/api/v1/billing-invoices', $headers)->assertOk()->assertJsonMissingPath('invoices.0.remaining');
            $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 1000000], $headers)->assertForbidden();
        }
        $this->postJson("/api/v1/billing-invoices/$id/pay", ['remaining' => 1000000], $this->headers('operasional'))->assertForbidden();
        $this->assertDatabaseHas('invoice_tagihans', ['id' => $id, 'lunas' => 0]);
    }
}

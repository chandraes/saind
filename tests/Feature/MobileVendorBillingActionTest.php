<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorPaymentCart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;

/** Modul 4: Admin & SU mengisi Nota Bongkar, melunasi dan membatalkan Invoice Vendor dari mobile. */
class MobileVendorBillingActionTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->unsignedTinyInteger('tagihan_dari')->default(2);
            $table->integer('nominal_penalty')->default(0);
            $table->boolean('csr')->default(false);
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            foreach (['vendor_id', 'vehicle_id', 'customer_id', 'rute_id'] as $field) {
                $table->unsignedBigInteger($field);
            }
            $table->bigInteger('nominal_transaksi')->default(0);
        });
        Schema::create('transaksi_additionals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transaksi_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('jenis');
            $table->unsignedBigInteger('customer_id')->default(1);
            $table->unsignedInteger('status')->default(3);
            $table->unsignedInteger('jarak')->default(1);
            $table->timestamps();
        });
        Schema::create('invoice_add_vendor_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_add_vendor_id');
            $table->unsignedBigInteger('transaksi_additional_id');
            $table->unsignedBigInteger('transaksi_id')->nullable();
            $table->timestamps();
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id');
            $table->integer('status')->default(2);
            $table->boolean('void')->default(false);
            $table->boolean('bayar')->default(false);
            $table->string('nota_bongkar')->nullable()->unique();
            $table->string('tanggal_muat')->nullable();
            $table->string('tanggal_bongkar')->nullable();
            foreach (['tonase', 'timbangan_bongkar', 'gross_bongkar', 'tarra_bongkar', 'harga_customer', 'harga_vendor', 'harga_csr',
                'nominal_tagihan', 'nominal_bayar', 'nominal_csr', 'nominal_bonus', 'profit'] as $field) {
                $table->decimal($field, 15, 3)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('invoice_bayars', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('periode');
            $table->unsignedInteger('no_invoice')->default(0);
            $table->date('tanggal')->nullable();
            $table->date('tempo')->nullable();
            $table->bigInteger('total_bayar');
            $table->bigInteger('sisa_bayar');
            $table->bigInteger('bayar')->default(0);
            $table->boolean('lunas')->default(false);
            $table->timestamps();
        });
        Schema::create('keranjang_nota_bayars', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('transaksi_id');
            $table->timestamps();
        });
        Schema::create('invoice_bayar_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_bayar_id');
            $table->unsignedBigInteger('transaksi_id');
            $table->timestamps();
        });
        Schema::create('invoice_add_vendors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('jenis');
            $table->integer('periode');
            $table->boolean('status')->default(true);
            $table->boolean('is_finished')->default(false);
            foreach (['dpp', 'nominal', 'ppn', 'pph', 'total'] as $field) {
                $table->bigInteger($field)->default(0);
            }
            $table->date('tempo')->nullable();
            $table->timestamps();
        });
        Schema::create('kas_vendors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('invoice_bayar_id')->nullable();
            $table->unsignedBigInteger('invoice_add_vendor_id')->nullable();
            $table->dateTime('tanggal');
            $table->string('uraian');
            $table->bigInteger('bayar')->default(0);
            $table->bigInteger('sisa');
            $table->timestamps();
        });
        foreach (['ppn_masukans', 'pph_simpans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('invoice_bayar_id')->nullable();
                $table->unsignedBigInteger('invoice_add_vendor_id')->nullable();
                $table->string('uraian');
                $table->bigInteger('nominal');
                $table->boolean('onhold')->default(true);
                $table->timestamps();
            });
        }
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor A', 'nickname' => 'VA']);
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288', 'status' => 'nonaktif', 'do_count' => 4]);
        DB::table('customers')->insert(['id' => 1, 'nama' => 'Mine A', 'tagihan_dari' => 2, 'nominal_penalty' => 1]);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Mine - Port', 'jarak' => 60]);
        DB::table('kas_uang_jalans')->insert(['id' => 1, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 1]);
        DB::table('kas_vendors')->insert(['vendor_id' => 1, 'tanggal' => '2026-11-01', 'uraian' => 'Awal', 'sisa' => 500000, 'created_at' => '2026-11-01']);
    }

    public function test_vendor_payment_summary_preserves_web_rounding_and_fingerprints_records(): void
    {
        $service = app(VendorPaymentCart::class);
        $vendor = new Vendor;
        $vendor->forceFill(['ppn' => 1, 'pph' => 1, 'pph_val' => 2]);
        $note = new Transaksi;
        $note->forceFill(['id' => 1, 'nominal_bayar' => 1019]);
        $note->setRelation('kas_uang_jalan', (object) ['nominal_transaksi' => 100]);
        $notes = new Collection([$note]);

        $quote = $service->quote($vendor, $notes);
        $this->assertSame(112, $quote['ppn']);
        $this->assertSame(20, $quote['pph']);
        $this->assertEquals(1011, $quote['total']);
        $note->id = 2;
        $this->assertNotSame($quote['fingerprint'], $service->quote($vendor, $notes)['fingerprint']);
    }

    public function test_primary_cart_is_shared_and_checkout_requires_a_current_quote(): void
    {
        $headers = $this->headers('admin');
        $user = User::query()->latest('id')->firstOrFail();
        $vendor = Vendor::query()->findOrFail(1);
        $note = $this->journey(['status' => 3, 'nominal_bayar' => 1000]);
        $path = '/api/v1/vendors/1/payment-cart';
        $this->postJson($path, ['transaksi_ids' => [$note]], $headers)->assertOk();
        $this->assertSame([$note], app(VendorPaymentCart::class)->notes($user, $vendor)->modelKeys());

        $quote = $this->getJson($path, $headers)->assertOk()
            ->assertJsonPath('data.0.id', $note)->json('quote');
        DB::table('transaksis')->where('id', $note)->update(['nominal_bayar' => 1200]);

        $this->postJson($path.'/checkout', ['tempo' => '2026-12-01', 'fingerprint' => $quote['fingerprint']], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('fingerprint');
        $this->assertDatabaseCount('invoice_bayars', 0);

        $quote = $this->getJson($path, $headers)->assertOk()->json('quote');
        $this->postJson($path.'/checkout', [
            'tempo' => '2026-12-01', 'fingerprint' => $quote['fingerprint'], 'total_bayar' => 1,
        ], $headers)->assertCreated();
        $this->assertDatabaseHas('invoice_bayars', ['vendor_id' => 1, 'total_bayar' => 1200]);
        $this->assertDatabaseHas('transaksis', ['id' => $note, 'bayar' => 1]);
        $this->assertDatabaseHas('invoice_bayars', ['vendor_id' => 1, 'tempo' => '2026-12-01']);
        $this->assertDatabaseCount('keranjang_nota_bayars', 0);
        $this->getJson($path, $this->headers('vendor'))->assertForbidden();

    }

    public function test_additional_cart_can_be_returned_or_issued_but_cannot_return_an_issued_invoice(): void
    {
        $headers = $this->headers('su');
        $note = $this->journey(['status' => 3, 'timbangan_bongkar' => 25, 'nota_bongkar' => 'NB-ADDITIONAL']);
        $id = DB::table('transaksi_additionals')->insertGetId([
            'transaksi_id' => $note, 'vendor_id' => 1, 'jenis' => 'kompensasi_jr', 'jarak' => 2,
        ]);
        Schema::create('invoice_additionals', function (Blueprint $table): void {
            $table->id();
            $table->decimal('dpp', 12, 4);
        });
        Schema::create('invoice_additional_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_additional_id');
            $table->unsignedBigInteger('transaksi_additional_id');
        });

        $path = '/api/v1/vendors/1/payment-cart/additional/kompensasi_jr';
        $input = ['dpp' => 10, 'transaksi_ids' => [$id]];
        $this->postJson($path, $input, $headers)->assertOk();
        $cart = $this->getJson($path, $headers)->assertOk()
            ->assertJsonPath('data.total', 500)->assertJsonPath('data.notes.0.nota_bongkar', 'NB-ADDITIONAL')->json('data');
        $this->deleteJson($path.'/'.$cart['id'], ['fingerprint' => $cart['fingerprint']], $headers)->assertOk();
        $this->assertDatabaseHas('transaksi_additionals', ['id' => $id, 'status' => 3]);

        $this->postJson($path, $input, $headers)->assertOk();
        $cart = $this->getJson($path, $headers)->assertOk()->json('data');
        $this->postJson($path.'/'.$cart['id'].'/checkout', [
            'tempo' => '2026-12-01', 'fingerprint' => $cart['fingerprint'],
        ], $headers)->assertCreated();
        $this->deleteJson($path.'/'.$cart['id'], ['fingerprint' => $cart['fingerprint']], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('invoice');
        $this->assertDatabaseHas('transaksi_additionals', ['id' => $id, 'status' => 5]);
    }

    private function headers(string $role): array
    {
        $user = User::factory()->create(['username' => Str::uuid()->toString(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $role === 'vendor' ? 1 : null]);
        $this->app['auth']->forgetGuards();

        return ['X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    private function journey(array $attributes = []): int
    {
        return DB::table('transaksis')->insertGetId($attributes + ['kas_uang_jalan_id' => 1, 'status' => 2, 'tanggal_muat' => '2026-11-03',
            'tonase' => 30, 'harga_customer' => 100, 'harga_vendor' => 80, 'harga_csr' => 0]);
    }

    private function unloading(array $overrides = []): array
    {
        return $overrides + ['nota_bongkar' => 'NB-01', 'timbangan_bongkar' => 29.5, 'tanggal_bongkar' => '2026-11-04', 'gross_bongkar' => 44, 'tarra_bongkar' => 14.5];
    }

    private function invoice(array $attributes = []): int
    {
        $id = DB::table('invoice_bayars')->insertGetId($attributes + ['vendor_id' => 1, 'periode' => 'Periode 3', 'tanggal' => '2026-11-10',
            'total_bayar' => 200000, 'sisa_bayar' => 200000, 'lunas' => 0]);
        $journey = $this->journey(['status' => 3, 'bayar' => 1, 'nota_bongkar' => 'NB-INV-'.$id]);
        DB::table('invoice_bayar_details')->insert(['invoice_bayar_id' => $id, 'transaksi_id' => $journey]);
        DB::table('ppn_masukans')->insert(['invoice_bayar_id' => $id, 'uraian' => 'PPN', 'nominal' => 2000]);
        DB::table('pph_simpans')->insert(['invoice_bayar_id' => $id, 'uraian' => 'PPh', 'nominal' => 400]);

        return $id;
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_admin_and_su_complete_an_unloading_note_like_the_web(string $role): void
    {
        $id = $this->journey();
        $this->postJson("/api/v1/unloading-notes/$id/complete", $this->unloading(), $this->headers($role))
            ->assertOk()->assertJsonPath('message', 'Nota bongkar berhasil disimpan.');

        $row = DB::table('transaksis')->find($id);
        $this->assertSame(3, (int) $row->status);
        $this->assertSame('NB-01', $row->nota_bongkar);
        $this->assertStringStartsWith('2026-11-04 ', $row->tanggal_bongkar);
        // tagihan_dari 2: timbangan bongkar × jarak × harga.
        $this->assertEquals(29.5 * 60 * 100, (float) $row->nominal_tagihan);
        $this->assertEquals(29.5 * 60 * 80, (float) $row->nominal_bayar);
        $this->assertEquals(29.5 * 1000, (float) $row->nominal_bonus);
        $this->assertEquals(177000 * 0.98 - 141600 - 29500 - 0 - 29.5 * 60, (float) $row->profit);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 5, 'status' => 'aktif']);

        // Diketuk ulang: ditolak, DO tidak bertambah lagi.
        $this->postJson("/api/v1/unloading-notes/$id/complete", $this->unloading(), $this->headers($role))
            ->assertUnprocessable()->assertJsonPath('errors.transaksi.0', 'Nota bongkar ini sudah diisi atau dibatalkan.');
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 5]);
    }

    public function test_unloading_rejects_early_dates_duplicate_notes_and_other_roles(): void
    {
        $id = $this->journey();
        $this->journey(['status' => 3, 'nota_bongkar' => 'DUP']);
        $admin = $this->headers('admin');
        $this->postJson("/api/v1/unloading-notes/$id/complete", $this->unloading(['tanggal_bongkar' => '2026-11-02']), $admin)
            ->assertUnprocessable()->assertJsonPath('errors.tanggal_bongkar.0', 'Tanggal bongkar tidak boleh lebih kecil dari tanggal muat!!');
        $this->postJson("/api/v1/unloading-notes/$id/complete", $this->unloading(['nota_bongkar' => 'DUP']), $admin)
            ->assertUnprocessable()->assertJsonPath('errors.nota_bongkar.0', 'Terdapat nota yang sama!!');
        $this->postJson("/api/v1/unloading-notes/$id/complete", $this->unloading(['tanggal_bongkar' => '04-11-2026']), $admin)
            ->assertUnprocessable()->assertJsonValidationErrors('tanggal_bongkar');
        foreach (['operasional', 'general-manager', 'vendor'] as $role) {
            $this->postJson("/api/v1/unloading-notes/$id/complete", $this->unloading(), $this->headers($role))->assertForbidden();
        }
        $this->assertSame(2, (int) DB::table('transaksis')->find($id)->status);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 4]);
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_settling_the_main_invoice_records_vendor_cash_once(string $role): void
    {
        $id = $this->invoice();
        $headers = $this->headers($role);
        $this->getJson("/api/v1/vendor-invoices/utama/$id", $headers)->assertOk()
            ->assertJsonPath('invoice.actions', ['settle_amount' => 200000, 'cancel_amount' => 200000]);
        $this->postJson("/api/v1/vendor-invoices/utama/$id/settle", ['amount' => 150000], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.amount.0', 'Nominal invoice berubah sejak dibuka. Muat ulang dan periksa kembali.');
        $this->postJson("/api/v1/vendor-invoices/utama/$id/settle", ['amount' => 200000], $headers)
            ->assertOk()->assertJsonPath('message', 'Invoice berhasil dilunasi.');

        $this->assertDatabaseHas('invoice_bayars', ['id' => $id, 'lunas' => 1, 'bayar' => 200000, 'sisa_bayar' => 0]);
        $this->assertDatabaseHas('kas_vendors', ['invoice_bayar_id' => $id, 'bayar' => 200000, 'sisa' => 300000, 'uraian' => 'Pembayaran  - Periode 3']);
        $this->assertDatabaseHas('ppn_masukans', ['invoice_bayar_id' => $id, 'onhold' => 0]);
        $this->assertDatabaseHas('pph_simpans', ['invoice_bayar_id' => $id, 'onhold' => 0]);

        $this->postJson("/api/v1/vendor-invoices/utama/$id/settle", ['amount' => 0], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.invoice.0', 'Invoice ini sudah lunas.');
        $this->assertSame(1, DB::table('kas_vendors')->where('invoice_bayar_id', $id)->count());
        $this->getJson("/api/v1/vendor-invoices/utama/$id", $headers)->assertOk()->assertJsonMissingPath('invoice.actions');
    }

    public function test_settling_an_additional_invoice_records_taxes_and_vendor_cash(): void
    {
        $id = DB::table('invoice_add_vendors')->insertGetId(['vendor_id' => 1, 'jenis' => 'kompensasi_jr', 'periode' => 2,
            'nominal' => 100000, 'ppn' => 11000, 'pph' => 2000, 'total' => 109000, 'created_at' => '2026-11-10']);
        $headers = $this->headers('admin');
        $this->getJson("/api/v1/vendor-invoices/kompensasi_jr/$id", $headers)->assertOk()
            ->assertJsonPath('invoice.actions', ['settle_amount' => 109000, 'cancel_amount' => null]);
        $this->postJson("/api/v1/vendor-invoices/kompensasi_jr/$id/settle", ['amount' => 109000], $headers)->assertOk();
        $this->postJson("/api/v1/vendor-invoices/kompensasi_jr/$id/settle", ['amount' => 109000], $headers)->assertUnprocessable();

        $this->assertDatabaseHas('invoice_add_vendors', ['id' => $id, 'is_finished' => 1]);
        $this->assertDatabaseHas('ppn_masukans', ['invoice_add_vendor_id' => $id, 'nominal' => 11000, 'onhold' => 0, 'uraian' => 'Vendor A Komp. Jalan Rusak 2']);
        $this->assertDatabaseHas('pph_simpans', ['invoice_add_vendor_id' => $id, 'nominal' => 2000]);
        $this->assertSame(1, DB::table('kas_vendors')->where('invoice_add_vendor_id', $id)->count());
        $this->assertDatabaseHas('kas_vendors', ['invoice_add_vendor_id' => $id, 'bayar' => 109000, 'sisa' => 391000]);
        $this->postJson('/api/v1/vendor-invoices/unknown/'.$id.'/settle', ['amount' => 1], $headers)->assertNotFound();
    }

    public function test_cancelling_returns_journeys_to_the_payment_note_list(): void
    {
        $id = $this->invoice();
        $journey = DB::table('invoice_bayar_details')->where('invoice_bayar_id', $id)->value('transaksi_id');
        $headers = $this->headers('su');
        $this->postJson("/api/v1/vendor-invoices/utama/$id/cancel", ['amount' => 200000], $headers)
            ->assertOk()->assertJsonPath('message', 'Invoice berhasil dibatalkan. Transaksinya kembali ke Nota Bayar.');

        $this->assertDatabaseMissing('invoice_bayars', ['id' => $id]);
        $this->assertSame(0, (int) DB::table('transaksis')->find($journey)->bayar);
        $this->assertDatabaseMissing('ppn_masukans', ['invoice_bayar_id' => $id]);
        $this->assertSame(1, DB::table('kas_vendors')->count());
        $this->postJson("/api/v1/vendor-invoices/utama/$id/cancel", ['amount' => 200000], $headers)->assertNotFound();
    }

    public function test_paid_invoices_cannot_be_cancelled_and_other_roles_are_refused(): void
    {
        $paid = $this->invoice(['lunas' => 1, 'sisa_bayar' => 0, 'bayar' => 200000]);
        $this->postJson("/api/v1/vendor-invoices/utama/$paid/cancel", ['amount' => 200000], $this->headers('admin'))
            ->assertUnprocessable()->assertJsonPath('errors.invoice.0', 'Invoice sudah ada pembayaran');
        $open = $this->invoice();
        $this->postJson("/api/v1/vendor-invoices/utama/$open/settle", [], $this->headers('admin'))->assertUnprocessable()->assertJsonValidationErrors('amount');
        foreach (['operasional', 'direksi', 'vendor'] as $role) {
            $headers = $this->headers($role);
            $this->postJson("/api/v1/vendor-invoices/utama/$open/settle", ['amount' => 200000], $headers)->assertForbidden();
            $this->postJson("/api/v1/vendor-invoices/utama/$open/cancel", ['amount' => 200000], $headers)->assertForbidden();
        }
        $this->assertDatabaseHas('invoice_bayars', ['id' => $open, 'lunas' => 0]);
    }
}

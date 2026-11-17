<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

/** PDF Nota Tagihan memakai CSS ringan (Bootstrap penuh membuat DomPDF kehabisan memori); dokumen lain tetap Bootstrap. */
class NotaTagihanPdfStylesheetTest extends TestCase
{
    #[TestWith(['billing.transaksi.tagihan.export-admin'])]
    #[TestWith(['billing.transaksi.tagihan.export'])]
    public function test_nota_tagihan_pdf_uses_the_light_stylesheet(string $view): void
    {
        $html = view($view, ['data' => new Collection, 'customer' => new Customer(['nama' => 'PT Uji', 'singkatan' => 'UJI'])])->render();

        $this->assertStringContainsString(public_path('assets/css/pdf-nota-tagihan.css'), $html);
        $this->assertStringNotContainsString('bootstrap.min.css', $html);
        $this->assertFileExists(public_path('assets/css/pdf-nota-tagihan.css'));
    }

    #[TestWith(['layouts.doc-nologo-1'])]
    #[TestWith(['layouts.doc-nologo-2'])]
    public function test_other_documents_keep_full_bootstrap(string $layout): void
    {
        $this->assertStringContainsString(public_path('assets/plugins/bootstrap/css/bootstrap.min.css'), view($layout)->render());
    }
}

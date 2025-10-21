<?php

use App\Models\Vendor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('pph_val', 5, 2)->nullble()->after('pph')->comment('PPH Value in percentage');
        });

        $data = Vendor::select('id', 'pph')->get();
        foreach ($data as $item) {
            if ($item->pph == 1) {
                $item->update(['pph_val' => 2]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('pph_val');
        });
    }
};

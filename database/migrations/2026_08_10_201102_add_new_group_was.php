<?php

use App\Models\GroupWa;
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
        GroupWa::updateOrCreate([
            'untuk' => 'kas-uj-ditahan'
        ],[
            'nama_group' => '120363151844351865@g.us',
            'group_id' => 'Testing Group',
        ]);

         Schema::table('uj_ditahan_details', function (Blueprint $table) {
            $table->string('file_pdf')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        GroupWa::where('untuk', 'kas-uj-ditahan')->delete();

        Schema::table('uj_ditahan_details', function (Blueprint $table) {
            $table->dropColumn(['file_pdf']);
        });
    }
};

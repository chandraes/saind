<?php

use App\Models\User;
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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['su','admin', 'user', 'vendor', 'customer', 'operasional'])->change();
        });

        User::where('id', 1)->update(['role' => 'su']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('id', 1)->update(['role' => 'admin']);
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user', 'vendor', 'customer', 'operasional'])->change();
        });

    }
};

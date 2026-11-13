<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ROLES = ['su', 'admin', 'user', 'vendor', 'vendor-operational', 'customer', 'customer-admin', 'operasional', 'investor', 'asisten-user', 'vendor-induk'];

    public function up(): void
    {
        $this->setRoles([...self::ROLES, 'driver']);
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('driver_id')->nullable()->unique()->constrained('drivers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('users')->where('role', 'driver')->exists()) {
            throw new RuntimeException('Selesaikan akun Driver sebelum rollback migrasi.');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['driver_id']);
            $table->dropUnique(['driver_id']);
            $table->dropColumn('driver_id');
        });
        $this->setRoles(self::ROLES);
    }

    /** @param list<string> $roles */
    private function setRoles(array $roles): void
    {
        if (DB::getDriverName() === 'mysql') {
            $list = implode(',', array_map(fn (string $role): string => DB::getPdo()->quote($role), $roles));
            DB::statement("ALTER TABLE users MODIFY role ENUM({$list}) NOT NULL");
        }
    }
};

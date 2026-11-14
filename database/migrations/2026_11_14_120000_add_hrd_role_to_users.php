<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLES = ['su', 'admin', 'user', 'vendor', 'vendor-operational', 'customer', 'customer-admin', 'operasional', 'investor', 'asisten-user', 'vendor-induk', 'driver'];

    public function up(): void
    {
        $this->setRoles([...self::ROLES, 'hrd']);
    }

    public function down(): void
    {
        if (DB::table('users')->where('role', 'hrd')->exists()) {
            throw new RuntimeException('Ubah atau hapus akun HRD sebelum rollback migrasi.');
        }
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

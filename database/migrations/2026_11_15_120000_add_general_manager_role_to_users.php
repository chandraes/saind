<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLES = ['su', 'admin', 'user', 'vendor', 'vendor-operational', 'customer', 'customer-admin', 'operasional', 'investor', 'asisten-user', 'vendor-induk', 'driver', 'hrd'];

    public function up(): void
    {
        $this->setRoles([...self::ROLES, 'general-manager']);
    }

    public function down(): void
    {
        if (DB::table('users')->where('role', 'general-manager')->exists()) {
            throw new RuntimeException('Ubah atau hapus akun General Manager sebelum rollback migrasi.');
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

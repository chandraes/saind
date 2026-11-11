<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Tambah role vendor-induk ke ENUM users.role (MySQL); driver lain menyimpan role sebagai teks. */
return new class extends Migration
{
    private const ROLES = ['su', 'admin', 'user', 'vendor', 'vendor-operational', 'customer', 'customer-admin', 'operasional', 'investor', 'asisten-user'];

    public function up(): void
    {
        $this->setRoles([...self::ROLES, 'vendor-induk']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'vendor-induk')->update(['role' => 'vendor']);
        $this->setRoles(self::ROLES);
    }

    /** @param list<string> $roles */
    private function setRoles(array $roles): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        $list = implode(',', array_map(fn (string $role): string => DB::getPdo()->quote($role), $roles));
        DB::statement("ALTER TABLE users MODIFY role ENUM({$list}) NOT NULL");
    }
};

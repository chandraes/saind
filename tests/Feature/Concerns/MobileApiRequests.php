<?php

namespace Tests\Feature\Concerns;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Request API mobile (token Sanctum + ruang kerja) dari tes fitur web yang sudah ada. */
trait MobileApiRequests
{
    private string $mobileWorkspace = 'b1bca759-224f-4f48-bbe7-76904c3434fa';

    /** @return array<string, string> */
    protected function mobileHeaders(User $user): array
    {
        config(['mobile.workspace_id' => $this->mobileWorkspace, 'mobile.workspace_name' => 'SAIND Hauling',
            'mobile.company_name' => 'DS Groups', 'mobile.token_expiration_minutes' => 60]);
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('personal_access_tokens')) {
            (require database_path('migrations/2019_12_14_000001_create_personal_access_tokens_table.php'))->up();
        }
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => $this->mobileWorkspace,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.$this->mobileWorkspace])->plainTextToken];
    }
}

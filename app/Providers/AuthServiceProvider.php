<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Menu API mobile yang aksesnya ditentukan helper User (bukan daftar role rute web).
        Gate::define('mobile-customer-bills', fn (User $user): bool => $user->readsCustomerBills());
        Gate::define('mobile-billing-invoices', fn (User $user): bool => $user->readsBillingInvoices());
        Gate::define('mobile-tonnage-report', fn (User $user): bool => $user->canViewTonnageReport());
    }
}

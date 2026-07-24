<?php

namespace App\Providers;

use App\Models\DailyCashBalance;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use App\Observers\DailyCashBalanceObserver;
use App\Observers\InventoryItemObserver;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\OrderPaymentObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.debug')) {
            Model::preventLazyLoading();
        }

        // ── Observers de Auditoría ─────────────────────────
        Order::observe(OrderObserver::class);
        OrderPayment::observe(OrderPaymentObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        InventoryItem::observe(InventoryItemObserver::class);
        User::observe(UserObserver::class);
        DailyCashBalance::observe(DailyCashBalanceObserver::class);
        Product::observe(ProductObserver::class);
    }
}

<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Core\Providers\CoreServiceProvider::class,

    Modules\Inventory\Providers\InventoryServiceProvider::class,
    Modules\Sales\Providers\SalesServiceProvider::class,
    Modules\Customer\Providers\CustomerServiceProvider::class,
    Modules\Finance\Providers\FinanceServiceProvider::class,
];

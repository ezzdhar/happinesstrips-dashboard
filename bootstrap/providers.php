<?php

use App\Providers\AuthServiceProvider;
use App\Providers\ExceptionServiceProvider;

return [
    ExceptionServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    AuthServiceProvider::class,

];

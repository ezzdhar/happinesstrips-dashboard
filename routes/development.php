<?php

Route::get('container', function () {
    dd(app()->getBindings());
});

Route::get('routes', function () {
    return Route::getRoutes()->get();
});

Route::get('middlewares', function () {
    return app('router')->getMiddleware();
});

Route::get('test', function () {
    //
});

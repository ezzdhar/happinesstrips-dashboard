<?php

use App\Http\Controllers\LanguageController;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\Employee\EmployeeData;
use App\Livewire\Dashboard\MainCategory\MainCategoryData;
use App\Livewire\Dashboard\Profile\Profile;
use App\Livewire\Dashboard\Role\RoleData;
use App\Livewire\Dashboard\SubCategory\SubCategoryData;
use App\Livewire\Dashboard\User\UserData;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

Route::get('test', function (){
	foreach (['create', 'show', 'update', 'delete'] as $action) {
		Permission::create(['name' => $action.'_employee', 'type' => 'employees_mng']);
	}

}); // profile

Route::middleware(['web-language'])->group(function () {
    Route::get('web-language/{lang}', LanguageController::class)->name('web-language');
    Route::view('/', 'welcome')->name('home');

    //authentication routes
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('profile', Profile::class)->name('profile'); // profile
	    Route::get('dashboard', Dashboard::class)->name('dashboard'); // dashboard
	    Route::get('users', UserData::class)->name('users')->middleware('permission:show_user'); // users
	    Route::get('employees', EmployeeData::class)->name('employees')->middleware('permission:show_employee'); // employees
	    Route::get('roles', RoleData::class)->name('roles')->middleware('permission:show_role');// roles
	    Route::get('main-categories', MainCategoryData::class)->name('main-categories')->middleware('permission:show_main_category'); // main categories
	    Route::get('sub-categories', SubCategoryData::class)->name('sub-categories')->middleware('permission:show_sub_category'); // sub categories
    });

    // guest routes
    require __DIR__.'/auth.php';
});
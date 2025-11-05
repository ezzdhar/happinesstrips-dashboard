<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

	    // roles
	    $roles = ['admin', 'user', 'hotel'];
	    foreach ($roles as $role) {
		    Role::create(['name' => $role, 'is_main'=>true]);
	    }
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    // permissions
	    // role
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_role', 'type' => 'roles_mng']);
	    }

	    // user
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_user', 'type' => 'users_mng']);
	    }

	    // employee
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_employee', 'type' => 'employees_mng']);
	    }

	    //main category
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_main_category', 'type' => 'main_categories_mng']);
	    }

	    //sub category
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_sub_category', 'type' => 'sub_categories_mng']);
	    }

	    //city
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_city', 'type' => 'cities_mng']);
	    }

		//hotel
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_hotel', 'type' => 'hotels_mng'])->assignRole('hotel');
	    }

		//room
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_room', 'type' => 'rooms_mng'])->assignRole('hotel');
	    }

		//trip
	    foreach (['create', 'show', 'update', 'delete'] as $action) {
		    Permission::create(['name' => $action.'_trip', 'type' => 'trips_mng'])->assignRole('hotel');
	    }



	    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//        $masterAdminRole->givePermissionTo(Permission::all()->pluck('name')->toArray());
    }
}

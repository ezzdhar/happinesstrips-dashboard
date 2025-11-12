<x-menu activate-by-route>
	<x-menu-item noWireNavigate title="{{__('lang.home')}}" icon="o-home" link="{{route('dashboard')}}"/>
	@can('show_role')
		<x-menu-item noWireNavigate title="{{__('lang.roles')}}" icon="o-shield-check" link="{{route('roles')}}"/>
	@endcan

	@can('show_employee')
		<x-menu-item noWireNavigate title="{{__('lang.employees')}}" icon="o-user-circle" link="{{route('employees')}}"/>
	@endcan

	@can('show_user')
		<x-menu-item noWireNavigate title="{{__('lang.users')}}" icon="o-users" link="{{route('users')}}"/>
	@endcan

	@can('show_main_category')
		<x-menu-item noWireNavigate title="{{__('lang.main_categories')}}" icon="o-rectangle-stack" link="{{route('main-categories')}}"/>
	@endcan

	@can('show_sub_category')
		<x-menu-item noWireNavigate title="{{__('lang.sub_categories')}}" icon="o-squares-2x2" link="{{route('sub-categories')}}"/>
	@endcan

	@can('show_city')
		<x-menu-item noWireNavigate title="{{__('lang.cities')}}" icon="o-map-pin" link="{{route('cities')}}"/>
	@endcan

	@can('show_amenity')
		<x-menu-item noWireNavigate title="{{__('lang.amenities')}}" icon="o-sparkles" link="{{route('amenities')}}"/>
	@endcan

	@can('show_hotel')
		<x-menu-item noWireNavigate title="{{__('lang.hotels')}}" icon="o-building-office-2" link="{{route('hotels')}}"/>
	@endcan

	@can('show_room')
		<x-menu-item noWireNavigate title="{{__('lang.rooms')}}" icon="ionicon.bed-outline" link="{{route('rooms')}}"/>
	@endcan

	@can('show_trip')
		<x-menu-item noWireNavigate title="{{__('lang.trips')}}" icon="o-briefcase" link="{{route('trips')}}"/>
	@endcan

	@can('show_booking_hotel')
		<x-menu-item noWireNavigate title="{{__('lang.hotel_bookings')}}" icon="fas.hotel" link="{{route('bookings.hotels')}}"/>
	@endcan

	@can('show_booking_trip')
		<x-menu-item noWireNavigate title="{{__('lang.trip_bookings')}}" icon="fas.plane-departure" link="{{route('bookings.trips')}}"/>
	@endcan


</x-menu>
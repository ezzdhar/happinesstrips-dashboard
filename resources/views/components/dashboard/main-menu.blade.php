<x-menu activate-by-route>
	<x-menu-item noWireNavigate title="{{__('lang.home')}}" icon="fas.home" link="{{route('dashboard')}}" />
	@can('show_role')
		<x-menu-item noWireNavigate title="{{__('lang.roles')}}" icon="fas.shield-alt" link="{{route('roles')}}"/>
	@endcan
	@can('show_employee')
		<x-menu-item noWireNavigate title="{{__('lang.employees')}}" icon="fas.user-tie" link="{{route('employees')}}"/>
	@endcan
	@can('show_user')
		<x-menu-item noWireNavigate title="{{__('lang.users')}}" icon="fas.users" link="{{route('users')}}"/>
	@endcan
	@can('show_main_category')
		<x-menu-item noWireNavigate title="{{__('lang.main_categories')}}" icon="o-rectangle-stack" link="{{route('main-categories')}}"/>
	@endcan
	@can('show_sub_category')
		<x-menu-item noWireNavigate title="{{__('lang.sub_categories')}}" icon="o-squares-2x2" link="{{route('sub-categories')}}"/>
	@endcan
</x-menu>
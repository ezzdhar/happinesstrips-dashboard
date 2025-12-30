@php use App\Services\FileService; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light" data-theme="light" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('partials.head')

{{--<body class="min-h-screen font-sans antialiased bg-base-200">--}}
<body class="min-h-screen flex flex-col font-sans antialiased bg-base-200">
{{--<div class="loader-overlay" id="loader-overlay">--}}
{{--	<span class="loader"></span>--}}
{{--</div>--}}
<x-nav sticky full-width>
	<x-slot:brand>
		{{-- Drawer toggle for "main-drawer" --}}
		<label for="main-drawer" class="lg:hidden mr-3">
			<x-icon name="o-bars-3" class="cursor-pointer"/>
		</label>
		{{-- Brand --}}
		<a href="{{config('app.url')}}" class="ms-2 text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600">
			{{config('app.name')}}
		</a>
	</x-slot:brand>

	<x-slot:actions class="!gap-2">
		{{--theme--}}
		<x-theme-toggle class="btn btn-ghost btn-circle  btn-sm"/>

		{{--Language--}}
		<div class="dropdown dropdown-end">
			<label tabindex="0" class="btn btn-ghost btn-circle  btn-sm">
				<i class='bx bx-translate text-lg sm:text-xl'></i>
			</label>
			<ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 max-w-6xl p-2 shadow">
				<li>
					<a class="flex items-center w-full px-3 py-2  rounded-md transition-colors duration-200 text-base  {{app()->getLocale() === 'en' ? 'text-indigo-600 dark:text-indigo-400' :
					'text-gray-700 dark:text-gray-300'}}"
					   href="{{route('web-language','en')}}" >
						<x-flag-country-us class="w-[20px] h-auto me-3"/>
						<span>English</span>
					</a>
				</li>
				<li>
					<a class="flex items-center w-full px-3 py-2  rounded-md transition-colors duration-200 text-base  {{app()->getLocale() === 'ar' ? 'text-indigo-600 dark:text-indigo-400' :
					'text-gray-700 dark:text-gray-300'}}"
					   href="{{route('web-language','ar')}}" >
						<x-flag-country-eg class="w-[20px] h-auto me-3"/>
						<span>العربية</span>
					</a>
				</li>
			</ul>
		</div>

		{{--Notifications--}}
		<livewire:dashboard.notifications></livewire:dashboard.notifications>

		{{--profile--}}
		<div class="dropdown dropdown-end">
			<div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
				<div class="w-8 rounded-full">
					<img alt="Tailwind CSS Navbar component" src="{{ FileService::get(auth()->user()->image) }}"/>
				</div>
			</div>
			<ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-xl shadow-lg mt-4 p-3 w-auto min-w-[10rem] z-[999]">
				<li>
					<a href="{{ route('profile') }}"  class="flex items-center gap-2 whitespace-nowrap">
						<x-fas-user-cog class="h-4 w-4"/>
						<span class="font-bold">{{ __('lang.profile') }}</span>
					</a>
				</li>
				<li>
					<a onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="cursor: pointer"  class="flex items-center gap-2 whitespace-nowrap">
						<x-fas-sign-out-alt class="h-4 w-4"/>
						<span class="font-bold">{{ __('lang.logout') }}</span>
					</a>
				</li>
			</ul>
			<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
				@csrf
			</form>
		</div>

	</x-slot:actions>
</x-nav>

<x-main with-nav full-width class="flex flex-col flex-grow">
	{{--<x-main with-nav full-width>--}}

	<x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100">
		<x-dashboard.main-menu></x-dashboard.main-menu>
	</x-slot:sidebar>

	<x-slot:content class="flex-grow flex flex-col">
		<div class="flex-grow">
			@if(isset($breadcrumbs))
				<x-ui.breadcrumb
						:items="$breadcrumbs"
						separator="fas.chevron-{{app()->getLocale() === 'ar' ? 'left' : 'right'}}"
						class="bg-base-300 p-3 rounded-box mb-3"
						icon-class="dark:text-white w-4 h-4"
						link-item-class="text-sm font-bold"
				/>
			@endif
			@session('error')
				<x-alert title="{{session('error')}}" icon="o-exclamation-triangle" class="alert-warning mb-3 text-white"></x-alert>
			@endsession
			{{ $slot }}
		</div>
		<footer class="text-center p-4">
			<p class="text-gray-400 text-center  text-xs mt-3">
				{{ __('lang.footer_copyright', ['year' => now()->year,'app_name' => config('app.name')]) }}
				{{ __('lang.footer_developed_by') }}
				<a href="" class="font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600">
					{{config('app.name')}}
				</a>
			</p>
		</footer>
	</x-slot:content>
</x-main>
<x-toast position="toast-top toast-center"/>
@include('partials.scripts')
</body>
</html>
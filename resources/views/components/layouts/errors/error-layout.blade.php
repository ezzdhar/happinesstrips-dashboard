<!DOCTYPE html>
<html lang="{{ app()->getLocale()}}" class="scroll-smooth dark" dir="{{app()->getLocale() === 'ar' ? 'rtl' : 'ltr'}}" data-theme="dark">
@include('partials.head')
<body class="min-h-screen antialiased dark:bg-linear-to-b  font-sans text-gray-900 dark:text-gray-100 transition-colors duration-300 relative">
<div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-950 dark:to-indigo-950 -z-10"></div>
<div class="absolute top-4 right-4 left-4 z-50 flex items-center gap-2 ">
	<div class="dropdown dropdown-start">
		<div tabindex="0" role="button" class="flex items-center justify-center p-1.5 sm:p-2 rounded-full text-gray-700 dark:text-gray-300  dark:bg-gray-900 transition-colors duration-200">
			<i class='bx bx-translate text-lg sm:text-2xl'></i>
		</div>
		<ul tabindex="0" class="dropdown-content menu mt-2 dark:bg-gray-900 rounded-lg z-50 w-max min-w-[10rem] p-2 shadow-md bg-white border border-gray-100 dark:border-gray-800 origin-top">
			<li>
				<a class="flex items-center w-full px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors duration-200 text-base font-bold {{app()->getLocale() === 'en' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300'}}"
				   href="{{route('web-language','en')}}">
					<x-flag-country-us class="w-[20px] h-auto me-3"/>
					<span>English</span>
				</a>
			</li>
			<li>
				<a class="flex items-center w-full px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors duration-200 text-base font-bold {{app()->getLocale() === 'ar' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300'}}"
				   href="{{route('web-language','ar')}}">
					<x-flag-country-eg class="w-[20px] h-auto me-3"/>
					<span>العربية</span>
				</a>
			</li>
		</ul>
	</div>
</div>
<div class="bg-background flex min-h-[100svh] flex-col items-center justify-center gap-6 p-6 md:p-10">
	<div class="w-full max-w-full md:max-w-4xl px-4 md:px-0">
		<a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium mb-4" >
      <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
        <x-app-logo-icon class="size-9 fill-current text-white dark:text-white"/>
      </span>
	<span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
		</a>
		<div class="flex flex-col gap-6">
			{{ $slot }}
		</div>
	</div>
</div>

</body>
</html>


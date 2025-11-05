<!DOCTYPE html>
<html lang="{{ app()->getLocale()}}" class="scroll-smooth dark" dir="{{app()->getLocale() === 'ar' ? 'rtl' : 'ltr'}}">
@include('partials.head')
<body class="min-h-screen  antialiased ">
<div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
	<div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-950 dark:to-indigo-950 -z-10"></div>
	<div class="w-full lg:p-8 relative overflow-hidden">
		<div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
			<a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" >
                        <span class="flex h-9 w-9 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-black dark:text-white"/>
                        </span>
				<span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
			</a>
			{{ $slot }}
		</div>
	</div>
	<div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
		<div class="absolute inset-0 " style="background-image: url('{{ asset('landing-page/images/hero_20.png') }}'); background-size: cover; background-position: center;"></div>
		<a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" >
                    <span class="flex h-10 w-10 items-center justify-center rounded-md">
                        <x-app-logo-icon class="me-2 h-7 fill-current text-white"/>
                    </span>
			{{ config('app.name', 'Laravel') }}
		</a>
	</div>
</div>
</body>
</html>

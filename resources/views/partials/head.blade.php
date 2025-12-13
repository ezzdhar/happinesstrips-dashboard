<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="{{config('app.name')}} - Build, showcase, and impress with your professional portfolio">

	<title>{{config('app.name')}} | {{ isset($title) ? __("lang.$title") : __('lang.home') }}</title>
	<link rel="icon" href="{{asset('icon.ico')}}" sizes="any">
	<link rel="icon" href="{{asset('icon.ico')}}" type="image/svg+xml">
	<link rel="apple-touch-icon" href="{{asset('logo.svg')}}">

	<link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
	<link href='https://cdn.boxicons.com/fonts/brands/boxicons-brands.min.css' rel='stylesheet'>
	
	{{-- Font Awesome 6 --}}
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
	@vite(['resources/css/app.css', 'resources/js/app.js'])

	{{-- Cropper.js --}}
	<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />

	{{-- Sortable.js --}}
	<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.1/Sortable.min.js"></script>

	{{-- Ceasymde --}}
	<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">


	@yield('style')

	{{-- Custom CSS --}}
	<link rel="stylesheet" href="{{asset('dashboard-asset/css/css.css')}}">

</head>
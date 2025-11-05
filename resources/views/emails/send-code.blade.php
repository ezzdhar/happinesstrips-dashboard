@php use App\Services\FileService; use function App\Handlers\setting; @endphp
		<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ __('lang.verification_code') }}</title>

	<!-- Bootstrap 5 -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

	<style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fc;
            color: #333;
        }

        .container {
            max-width: 500px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .otp-code {
            font-size: 26px;
            font-weight: bold;
            color: #007bff;
            background: #eef4ff;
            padding: 10px 15px;
            display: inline-block;
            border-radius: 5px;
            letter-spacing: 3px;
            margin-top: 10px;
        }

        .footer {
            font-size: 14px;
            color: #555;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .copyright {
            font-size: 12px;
            color: #888;
            margin-top: 8px;
            text-align: center;
        }
	</style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
<div class="container text-center">
	<!-- الشعار -->
	<div class="header my-3">
		<img src="{{ FileService::get(setting()->logo) }}" alt="{{ __('lang.app_logo') }}" class="img-fluid" style="max-width: 140px;">
	</div>

	<!-- المحتوى -->
	<div class="content">
		<h4 class="mb-2">{{ __('lang.hello', ['name' => $user->name]) }}</h4>
		<p class="mb-2">{{ __('lang.verification_message') }}</p>
		<p class="otp-code">{{ $code }}</p>
		<p class="mb-2">{{ __('lang.ignore_message') }}</p>
	</div>

	<!-- التذييل -->
	<div class="footer">
		<p>{{ __('lang.thank_you') }} <strong>{{ config('app.name') }}</strong>!</p>
		<p>{{ __('lang.contact_us_to_help') }} <a href="{{ config('app.url') }}">{{ __('lang.support_link') }}</a></p>
	</div>
	<div class="copyright">
		<p>© {{ date('Y') }} {{ __('lang.all_rights_reserved') }} <strong>{{ config('app.name') }}</strong></p>
	</div>
</div>

<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

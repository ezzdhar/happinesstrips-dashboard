<x-layouts.errors.error-layout title="error_403">
	<x-card class="flex flex-col border border-gray-300 dark:border-gray-700 text-lg font-medium rounded-xl bg-white dark:bg-gray-900
	 transition-colors duration-200 " shadow separator>
		<div class="flex flex-col items-center justify-center text-center">
			{{-- أيقونة جذابة للخطأ --}}
			<div class="mb-6 text-red-500 dark:text-red-400" d>
				<svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 sm:h-24 sm:w-24" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round"
					      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
				</svg>
			</div>

			{{-- كود الخطأ بشكل بارز --}}
			<p class="text-6xl sm:text-7xl lg:text-8xl font-extrabold text-indigo-600 dark:text-indigo-400 mb-3 tracking-tight">
				403
			</p>

			{{-- عنوان الخطأ --}}
			<h1 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-white mb-4">
				{{ __('lang.error_403_card_title') }}
			</h1>

			{{-- رسالة توضيحية --}}
			<p class="text-gray-600 dark:text-gray-400  mb-3 text-base sm:text-lg">
				{{ __('lang.error_403_card_message_1') }}
			</p>
			<p class="text-gray-600 dark:text-gray-400  mb-8 sm:mb-10 text-base sm:text-lg">
				{{ __('lang.error_403_card_message_2') }}
			</p>

			{{-- أزرار الإجراءات --}}
			<div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 w-full max-w-sm">
				<a href="{{  route('home') }}" {{-- هذا سيعيد تحميل الصفحة الحالية --}}
				class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900 transition-all duration-150 ease-in-out transform hover:scale-105">
					{{ __('lang.go_home') }}
				</a>
			</div>


			<div class="mt-10">
				<p class="text-sm text-gray-500 dark:text-gray-400">
					{{ __('lang.if_problem_persists') }}
					<a href="{{ route('home')}}#contact"
					   class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline">
						{{ __('lang.contact_support') }}
					</a>.
				</p>
			</div>
		</div>
	</x-card>
</x-layouts.errors.error-layout>

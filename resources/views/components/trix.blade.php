@props([
	'label' => null,
	'required' => false,
	'dir' => 'rtl',
	])
@php
	$model = $attributes->wire('model')->value(); // دي اللي ترجع اسم الحقل كـ string
@endphp

<div class="mb-3">
	@if($label)
		<label class="font-bold mb-0.5">
			{{ $label }}
			@if($required)
				<span class="text-danger">*</span>
			@endif
		</label>
	@endif

	<div x-data="{
        value: @entangle($attributes->wire('model')),
        isFocused() { return document.activeElement !== this.$refs.trix },
        setValue() {
            if (this.$refs.trix && this.$refs.trix.editor) {
                this.$refs.trix.editor.loadHTML(this.value);
            }
        },
    }"
	     x-init="setValue(); $watch('value', () => isFocused() && setValue())"
	     x-on:trix-initialize="setValue()"
	     x-on:trix-change="value = $event.target.value"
	     x-on:trix-focus="setValue()"
	     {{ $attributes->whereDoesntStartWith('wire:model') }}
	     wire:ignore
	     class="my-1 rounded-md">
		<input id="x-{{ $model }}" type="hidden" dir="{{ $dir }}">
		<trix-editor x-ref="trix" input="x-{{ $model }}" class="trix-content overflow-auto" style="min-height: 15em !important;" dir="{{ $dir }}"/>
	</div>

	@error($model)
		<small class="text-danger mt-2">{{ $message }}</small>
	@enderror
</div>

{{--<x-trix wire:model="content" label="{{ __('lang.content') }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>--}}
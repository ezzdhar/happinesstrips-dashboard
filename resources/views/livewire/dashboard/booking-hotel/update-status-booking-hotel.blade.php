@php use App\Enums\Status; @endphp
<div>
	<x-button icon="o-pencil-square" class="btn-sm btn-ghost" @click="$wire.modalUpdate = true"  tooltip="{{__('lang.update_status')}}"/>
	<x-modal wire:model="modalUpdate" title="{{__('lang.update').' '.__('lang.status')}} ({{$booking->booking_number}})" class="backdrop-blur" persistent>
		<x-select label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" clearable :options="[
                          ['id' => Status::Pending, 'name' => __('lang.pending')],
                          ['id' => Status::UnderPayment, 'name' => __('lang.under_payment')],
                          ['id' => Status::UnderCancellation, 'name' => __('lang.under_cancellation')],
                          ['id' => Status::Cancelled, 'name' => __('lang.cancelled')],
                          ['id' => Status::Completed, 'name' => __('lang.completed')],
                      ]"/>
		<x-slot:actions>
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalUpdate = false;$wire.resetError()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.update')}}" class="btn-primary" wire:click="saveUpdate" wire:loading.attr="disabled" spinner="saveUpdate"/>
		</x-slot:actions>
	</x-modal>

</div>
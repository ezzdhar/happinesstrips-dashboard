<div>
	<x-button icon="o-plus" class="btn-primary btn-sm mt-2 md:mt-0" label="{{__('lang.add')}}" @click="$wire.modalAdd = true" wire:click="resetData"/>
	{{--modalAdd--}}
	<x-modal wire:model="modalAdd" title="{{__('lang.add')}}" box-class="modal-box-850 p-3">
		<x-form wire:submit="saveCreate">
			<x-input label="{{__('lang.name')}}" wire:model="name"/>
			@error('selected_permissions')
				<x-alert type="error" :title="$message" class="mb-2 bg-red-500 text-white"/>
			@enderror
			@error('selected_permissions.*')
				<x-alert type="error" :title="$message" class="mb-2 bg-red-500 text-white"/>
			@enderror
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				@forelse($get_permissions as $type => $permission)
					<div>
						<h3 class="font-bold mb-2 text-lg">{{__("lang.$type")}}</h3>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-base-200 p-2 rounded">
							@foreach($permission as $item)
								<div class="form-control ">
									<label class="cursor-pointer label">
										<input type="checkbox" class="checkbox checkbox-primary" wire:model="selected_permissions" value="{{$item['id']}}"/>
										<span class="label-text ml-2">{{__('lang.'.$item['name'])}}</span>
									</label>

								</div>
							@endforeach

						</div>
					</div>
				@empty
					<span class="text-sm text-error">{{__('lang.no_permission_available')}}</span>
				@endforelse
			</div>

			<x-slot:actions>
				<x-button label="{{__('lang.cancel')}}" @click="$wire.modalAdd = false"/>
				<x-button label="{{__('lang.save')}}" class="btn btn-primary" wire:loading.attr="disabled" type="submit" spinner="saveCreate"/>
			</x-slot:actions>
		</x-form>
	</x-modal>
</div>
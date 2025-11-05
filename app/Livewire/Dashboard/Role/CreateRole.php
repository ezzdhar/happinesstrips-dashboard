<?php

namespace App\Livewire\Dashboard\Role;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRole extends Component
{
    public bool $modalAdd = false;

    public ?string $name = null;

    public $get_permissions;

    public array $selected_permissions = [];

    public function mount(): void
    {
        $this->get_permissions = Permission::get(['id', 'name', 'type'])->groupBy('type')->toArray();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'selected_permissions' => 'nullable|array|min:1',
            'selected_permissions.*' => 'nullable|integer|exists:permissions,id',
        ];
    }

    public function saveCreate(): void
    {
        $this->validate();
        $role = Role::create(['name' => $this->name]);
        $permissions = Permission::whereIn('id', $this->selected_permissions)->pluck('name')->toArray();
        $role->givePermissionTo($permissions);
        $this->modalAdd = false;
        $this->resetData();
        $this->dispatch('render');
        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.role')]));
    }

    public function resetData(): void
    {
        $this->reset(['name', 'selected_permissions']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.dashboard.role.create-role');
    }
}

<?php

namespace App\Livewire\Dashboard\Role;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateRole extends Component
{
    public bool $modalUpdate = false;

    public ?string $name = null;

    public $get_permissions;

    public array $selected_permissions = [];

    public Role $role;

    public function mount(): void
    {
        $this->name = $this->role->name;
        $this->get_permissions = Permission::get(['id', 'name', 'type'])->groupBy('type')->toArray();
        $this->selected_permissions = $this->role->permissions->pluck('id')->toArray();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'selected_permissions' => 'nullable|array|min:1',
            'selected_permissions.*' => 'nullable|integer|exists:permissions,id',
        ];
    }

    public function saveUpdate(): void
    {
        $this->validate();
        $this->role->update(['name' => $this->name]);
        $permissions = Permission::whereIn('id', $this->selected_permissions)->pluck('name')->toArray();
        $this->role->syncPermissions($permissions);
        $this->modalUpdate = false;
        $this->dispatch('render');
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.role')]));
    }

    public function resetData(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.dashboard.role.update-role');
    }
}

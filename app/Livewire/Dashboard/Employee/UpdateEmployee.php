<?php

namespace App\Livewire\Dashboard\Employee;

use App\Models\User;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class UpdateEmployee extends Component
{
    use WithFileUploads;

    public bool $modalUpdate = false;

    public User $employee;

    public $name;

    public $email;

    public $password;

    public $image;

    public $phone;

    public $phone_key;

    public $status;

    public $password_confirmation;

    public $roles;

    public array $selected_roles = [];

    public $get_branches;

    public $branch_id;

    public function mount(): void
    {
        $this->roles = Role::where('is_main', false)->get(['id', 'name'])->toArray();
        $this->selected_roles = $this->employee->roles->where('is_main', false)->pluck('id')->toArray();
        $this->name = $this->employee->name;
        $this->email = $this->employee->email;
        $this->phone = $this->employee->phone;
        $this->phone_key = $this->employee->phone_key;
        $this->status = $this->employee->status->value;
        $this->branch_id = $this->employee->branch_id;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email:filter|max:255|unique:users,email,'.$this->employee->id,
            'password' => 'nullable|string|min:5|confirmed',
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
            'phone' => 'required|string|max:20|unique:users,phone,'.$this->employee->id,
            'phone_key' => 'required|string|max:5',
            'status' => 'required|in:active,inactive',
            'selected_roles' => 'nullable|array',
            'selected_roles.*' => 'nullable|integer|exists:roles,id',
        ];
    }

    public function saveUpdate(): void
    {
        $this->validate();
        $this->employee->update([
            'name' => $this->name,
	        'email' => $this->email ?: null,
            'image' => FileService::update($this->employee->image, $this->image, 'users'),
            'phone' => $this->phone,
            'phone_key' => $this->phone_key,
            'status' => $this->status,
        ]);
        $roles = Role::whereIn('id', $this->selected_roles)->pluck('name')->toArray();
        $this->employee->syncRoles(array_merge(['admin'], $roles));
        if ($this->password) {
            $this->employee->update(['password' => Hash::make($this->password)]);
        }
        $this->modalUpdate = false;
        $this->dispatch('render')->component(EmployeeData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.employee')]));
    }

    public function render(): View
    {
        return view('livewire.dashboard.employee.update-employee');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}

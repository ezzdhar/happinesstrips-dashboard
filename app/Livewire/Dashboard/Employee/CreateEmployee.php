<?php

namespace App\Livewire\Dashboard\Employee;

use App\Models\User;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class CreateEmployee extends Component
{
    use WithFileUploads;

    public bool $modalAdd = false;

    public $name;

    public $email;

    public $password;

    public $image;

    public $phone;

    public $phone_key;

    public $branch_id;

    public $password_confirmation;

    public $get_roles = [];

    public $get_branches;

    public array $selected_roles = [];

    public function mount(): void
    {
        $this->get_roles = Role::where('is_main', false)->get(['id', 'name'])->toArray() ?: [];
    }

    public function render(): View
    {
        return view('livewire.dashboard.employee.create-employee');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email:filter|max:255|unique:users,email',
            'password' => 'required|string|min:5|confirmed',
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
            'phone' => 'required|string|max:20|unique:users,phone',
            'phone_key' => 'required|string|max:5',
            'selected_roles' => 'nullable|array',
            'selected_roles.*' => 'nullable|integer|exists:roles,id',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();
        $employee = User::create([
            'name' => $this->name,
	        'email' => $this->email ?: null,
            'image' => FileService::save($this->image, 'users'),
            'password' => Hash::make($this->password),
            'phone' => $this->phone,
            'phone_key' => $this->phone_key,
            'email_verified_at' => now(),
        ])->assignRole('admin');
        if ($this->selected_roles) {
            $roles = Role::whereIn('id', $this->selected_roles)->pluck('name')->toArray();
            $employee->assignRole($roles);
        }
        $this->modalAdd = false;
        $this->dispatch('render')->component(EmployeeData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.employee')]));
    }

    public function resetData(): void
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'image', 'phone', 'phone_key', 'selected_roles']);
        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('tel-reset');
    }
}

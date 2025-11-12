<?php

namespace App\Livewire\Dashboard\User;

use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateUser extends Component
{
    use WithFileUploads;

    public bool $modalAdd = false;

    public $name;

    public $email;

    public $password;

    public $image;

    public $phone;

    public $phone_key;

    public $password_confirmation;

    public function render()
    {
        return view('livewire.dashboard.user.create-user');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email:filter|max:255|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
            'phone' => 'required|string|max:20|unique:users,phone',
            'phone_key' => 'required|string|max:5',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();
        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'image' => FileService::save($this->image, 'users'),
            'password' => Hash::make($this->password),
            'phone' => $this->phone,
            'phone_key' => $this->phone_key,
        ]);
        $this->modalAdd = false;
        $this->dispatch('render')->component(UserData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.user')]));
    }

    public function resetData(): void
    {
        $this->reset(['name', 'email', 'password', 'image', 'password_confirmation']);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}

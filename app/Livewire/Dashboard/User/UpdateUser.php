<?php

namespace App\Livewire\Dashboard\User;

use App\Models\User;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateUser extends Component
{
    use WithFileUploads;

    public bool $modalUpdate = false;

    public User $user;

    public $name;

    public $username;

    public $email;

    public $password;

    public $image;

    public $password_confirmation;

    public function mount(): void
    {

        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$this->user->id,
            'email' => 'required|email:filter|max:255|unique:users,email,'.$this->user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

    public function saveUpdate(): void
    {
        $this->validate();
        $this->user->update([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'image' => FileService::update($this->user->image, $this->image, 'users'),
        ]);
        if ($this->password) {
            $this->user->update(['password' => Hash::make($this->password)]);
        }
        $this->modalUpdate = false;
        $this->dispatch('render')->component(UserData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.user')]));
    }

    public function render(): View
    {
        return view('livewire.dashboard.user.update-user');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}

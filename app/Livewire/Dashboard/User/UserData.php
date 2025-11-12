<?php

namespace App\Livewire\Dashboard\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('users')]
class UserData extends Component
{
    use WithPagination;

    public $all_user;

    public $search_user_id;

    public function mount(): void
    {
        $this->all_user = User::role('user')->get(['id', 'name', 'username'])->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.users'),
                'icon' => 'o-users',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['users'] = User::role('user')->when($this->search_user_id, fn (Builder $query) => $query->where('id', $this->search_user_id))->latest()->paginate(10);

        return view('livewire.dashboard.user.user-data', $data);
    }

    public function deleteSweetAlert($id): void
    {
        sweetalert()
            ->showDenyButton()
            ->timer(0)
            ->iconColor('#FFA500')
            ->option('confirmButtonText', __('lang.confirm'))
            ->option('denyButtonText', __('lang.cancel'))
            ->option('id', $id)
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.user')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        User::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.user')]));
    }
}

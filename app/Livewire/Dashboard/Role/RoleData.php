<?php

namespace App\Livewire\Dashboard\Role;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('roles')]
class RoleData extends Component
{
    use WithPagination;

    public $search_role_id;

    public $all_roles;

    public function mount(): void
    {
        $this->all_roles = Role::where('is_main', false)->get(['id', 'name'])->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.roles'),
                'icon' => 'bi.shield-lock',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['roles'] = Role::when($this->search_role_id, fn (Builder $query) => $query->where('id', $this->search_role_id))
            ->where('is_main', false)
            ->with('permissions')
            ->withCount(['users', 'permissions'])
            ->latest()->paginate(30);

        return view('livewire.dashboard.role.role-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.role')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        Role::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.role')]));
    }
}

<?php

namespace App\Livewire\Dashboard\Employee;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('employees')]
class EmployeeData extends Component
{
    use WithPagination;

    public $all_employees;

    public $search_employee_id;

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'search_')) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        $this->all_employees = User::role('admin')->whereNot('id',1)->get(['id', 'name', 'phone'])->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.employees'),
                'icon' => 'o-briefcase',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['employees'] = User::role('admin')->whereNot('id',1)
            ->when($this->search_employee_id, fn (Builder $query) => $query->where('id', $this->search_employee_id))
            ->with(['roles'])->latest()->paginate(30);

        return view('livewire.dashboard.employee.employee-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.employee')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        User::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.employee')]));
    }
}

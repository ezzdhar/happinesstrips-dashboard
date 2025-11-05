<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('home')]
class Dashboard extends Component
{
	use WithPagination;

	public function mount(): void
	{
		view()->share('breadcrumbs', $this->breadcrumbs());
	}
	public function breadcrumbs(): array
	{
		return [
			[
				'label' => __('lang.home'),
				'icon' => 'o-home',
			],
		];
	}
	public function render()
	{
		return view('livewire.dashboard.dashboard');
	}
}

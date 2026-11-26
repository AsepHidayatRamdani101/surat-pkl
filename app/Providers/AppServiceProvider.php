<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $options = request()->attributes->get('peran_options', []);

            if (count($options) < 2) {
                return;
            }

            $aktif = request()->attributes->get('peran_aktif', 'pembimbing');

            $event->menu->add([
                'text' => 'Peran: ' . ($options[$aktif] ?? 'Pembimbing'),
                'icon' => 'fas fa-exchange-alt',
                'topnav_right' => true,
                'submenu' => collect($options)->map(fn ($label, $key) => [
                    'text' => $label . ($key === $aktif ? ' (aktif)' : ''),
                    'url' => route('peran.ganti', $key),
                    'icon' => $key === $aktif ? 'fas fa-check' : 'far fa-circle',
                ])->values()->all(),
            ]);
        });
    }
}

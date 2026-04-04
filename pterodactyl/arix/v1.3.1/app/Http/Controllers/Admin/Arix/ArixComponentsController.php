<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixComponentsRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixComponentsController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings
    ) {}

    private function get(string $key, mixed $default = null): mixed
    {
        try { return $this->settings->get('arix::' . $key) ?? $default; }
        catch (\Exception $e) { return $default; }
    }

    public function index(): View
    {
        return view('admin.arix.components', [
            'serverRow'     => $this->get('serverRow', config('arix.serverRow', 1)),
            'socialButtons' => $this->get('socialButtons', config('arix.socialButtons', false)),
            'discordBox'    => $this->get('discordBox', config('arix.discordBox', true)),
            'statsCards'    => $this->get('statsCards', config('arix.statsCards', 1)),
            'sideGraphs'    => $this->get('sideGraphs', config('arix.sideGraphs', 1)),
            'graphs'        => $this->get('graphs', config('arix.graphs', 1)),
            'slot1'         => $this->get('slot1', config('arix.slot1', 'disabled')),
            'slot2'         => $this->get('slot2', config('arix.slot2', 'disabled')),
            'slot3'         => $this->get('slot3', config('arix.slot3', 'disabled')),
            'slot4'         => $this->get('slot4', config('arix.slot4', 'disabled')),
            'slot5'         => $this->get('slot5', config('arix.slot5', 'disabled')),
            'slot6'         => $this->get('slot6', config('arix.slot6', 'disabled')),
            'slot7'         => $this->get('slot7', config('arix.slot7', 'disabled')),
        ]);
    }

    public function store(ArixComponentsRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Components settings saved.')->flash();
        return redirect()->route('admin.arix.components');
    }
}

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

    public function index(): View
    {
        $arix = config('arix');
        return view('admin.arix.components', [
            'serverRow'     => $arix['serverRow'] ?? 1,
            'socialButtons' => $arix['socialButtons'] ?? false,
            'discordBox'    => $arix['discordBox'] ?? true,
            'statsCards'    => $arix['statsCards'] ?? 1,
            'sideGraphs'    => $arix['sideGraphs'] ?? 1,
            'graphs'        => $arix['graphs'] ?? 1,
            'slot1'         => $arix['slot1'] ?? 'disabled',
            'slot2'         => $arix['slot2'] ?? 'disabled',
            'slot3'         => $arix['slot3'] ?? 'disabled',
            'slot4'         => $arix['slot4'] ?? 'disabled',
            'slot5'         => $arix['slot5'] ?? 'disabled',
            'slot6'         => $arix['slot6'] ?? 'disabled',
            'slot7'         => $arix['slot7'] ?? 'disabled',
        ]);
    }

    public function store(ArixComponentsRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace(':', '::', $key), $value);
        }
        $this->alert->success('Components settings saved.')->flash();
        return redirect()->route('admin.arix.components');
    }
}

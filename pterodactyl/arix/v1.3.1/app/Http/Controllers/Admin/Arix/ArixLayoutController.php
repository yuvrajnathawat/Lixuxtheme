<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixLayoutRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixLayoutController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings
    ) {}

    public function index(): View
    {
        $arix = config('arix');
        return view('admin.arix.layout', [
            'layout'          => $arix['layout'] ?? 1,
            'searchComponent' => $arix['searchComponent'] ?? 1,
            'logoPosition'    => $arix['logoPosition'] ?? 1,
            'socialPosition'  => $arix['socialPosition'] ?? 1,
            'loginLayout'     => $arix['loginLayout'] ?? 1,
        ]);
    }

    public function store(ArixLayoutRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace(':', '::', $key), $value);
        }
        $this->alert->success('Layout settings saved.')->flash();
        return redirect()->route('admin.arix.layout');
    }
}

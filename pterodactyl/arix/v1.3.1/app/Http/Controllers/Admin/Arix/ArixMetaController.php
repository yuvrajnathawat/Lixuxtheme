<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixMetaRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixMetaController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings
    ) {}

    public function index(): View
    {
        return view('admin.arix.meta', ['arix' => config('arix')]);
    }

    public function store(ArixMetaRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace(':', '::', $key), $value);
        }
        $this->alert->success('Meta settings saved.')->flash();
        return redirect()->route('admin.arix.meta');
    }
}

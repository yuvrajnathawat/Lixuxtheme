<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixAdvancedRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixAdvancedController extends Controller
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
        return view('admin.arix.advanced', [
            'profileType'       => $this->get('profileType', config('arix.profileType', 'boring')),
            'modeToggler'       => $this->get('modeToggler', config('arix.modeToggler', false)),
            'langSwitch'        => $this->get('langSwitch', config('arix.langSwitch', false)),
            'ipFlag'            => $this->get('ipFlag', config('arix.ipFlag', false)),
            'lowResourcesAlert' => $this->get('lowResourcesAlert', config('arix.lowResourcesAlert', false)),
        ]);
    }

    public function store(ArixAdvancedRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Advanced settings saved.')->flash();
        return redirect()->route('admin.arix.advanced');
    }
}

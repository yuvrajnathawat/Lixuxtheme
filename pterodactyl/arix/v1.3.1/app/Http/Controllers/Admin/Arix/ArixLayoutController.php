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

    private function get(string $key, mixed $default = null): mixed
    {
        try { return $this->settings->get('arix::' . $key) ?? $default; }
        catch (\Exception $e) { return $default; }
    }

    public function index(): View
    {
        return view('admin.arix.layout', [
            'layout'          => $this->get('layout', config('arix.layout', 1)),
            'searchComponent' => $this->get('searchComponent', config('arix.searchComponent', 1)),
            'logoPosition'    => $this->get('logoPosition', config('arix.logoPosition', 1)),
            'socialPosition'  => $this->get('socialPosition', config('arix.socialPosition', 1)),
            'loginLayout'     => $this->get('loginLayout', config('arix.loginLayout', 1)),
        ]);
    }

    public function store(ArixLayoutRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Layout settings saved.')->flash();
        return redirect()->route('admin.arix.layout');
    }
}

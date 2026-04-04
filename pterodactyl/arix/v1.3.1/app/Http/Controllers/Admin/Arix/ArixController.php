<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings
    ) {}

    private function get(string $key, mixed $default = null): mixed
    {
        try {
            $value = $this->settings->get('arix::' . $key);
            return $value ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public function index(): View
    {
        return view('admin.arix.index', [
            'logo'       => $this->get('logo', config('arix.logo', '/arix/Arix.png')),
            'fullLogo'   => $this->get('fullLogo', config('arix.fullLogo', false)),
            'logoHeight' => $this->get('logoHeight', config('arix.logoHeight', '32px')),
            'discord'    => $this->get('discord', config('arix.discord', '')),
            'support'    => $this->get('support', config('arix.support', '')),
            'status'     => $this->get('status', config('arix.status', '')),
            'billing'    => $this->get('billing', config('arix.billing', '')),
        ]);
    }

    public function store(ArixRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            // key format from form: "arix:logo" -> store as "arix::logo"
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Arix settings saved.')->flash();
        return redirect()->route('admin.arix');
    }
}

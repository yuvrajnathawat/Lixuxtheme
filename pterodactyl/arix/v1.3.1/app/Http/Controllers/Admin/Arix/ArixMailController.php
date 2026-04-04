<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixMailRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixMailController extends Controller
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
        return view('admin.arix.mail', [
            'mail_color'           => $this->get('mail_color', config('arix.mail_color', '#4a35cf')),
            'mail_backgroundColor' => $this->get('mail_backgroundColor', config('arix.mail_backgroundColor', '#F5F5FF')),
            'mail_logo'            => $this->get('mail_logo', config('arix.mail_logo', '')),
            'mail_logoFull'        => $this->get('mail_logoFull', config('arix.mail_logoFull', false)),
            'mail_mode'            => $this->get('mail_mode', config('arix.mail_mode', 'light')),
            'mail_discord'         => $this->get('mail_discord', config('arix.mail_discord', '')),
            'mail_twitter'         => $this->get('mail_twitter', config('arix.mail_twitter', '')),
            'mail_facebook'        => $this->get('mail_facebook', config('arix.mail_facebook', '')),
            'mail_instagram'       => $this->get('mail_instagram', config('arix.mail_instagram', '')),
            'mail_linkedin'        => $this->get('mail_linkedin', config('arix.mail_linkedin', '')),
            'mail_youtube'         => $this->get('mail_youtube', config('arix.mail_youtube', '')),
            'mail_status'          => $this->get('mail_status', config('arix.mail_status', '')),
            'mail_billing'         => $this->get('mail_billing', config('arix.mail_billing', '')),
            'mail_support'         => $this->get('mail_support', config('arix.mail_support', '')),
        ]);
    }

    public function store(ArixMailRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Mail settings saved.')->flash();
        return redirect()->route('admin.arix.mail');
    }
}

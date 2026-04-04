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

    public function index(): View
    {
        $arix = config('arix');
        return view('admin.arix.mail', [
            'mail_color'           => $arix['mail_color'] ?? '#4a35cf',
            'mail_backgroundColor' => $arix['mail_backgroundColor'] ?? '#F5F5FF',
            'mail_logo'            => $arix['mail_logo'] ?? '',
            'mail_logoFull'        => $arix['mail_logoFull'] ?? false,
            'mail_mode'            => $arix['mail_mode'] ?? 'light',
            'mail_discord'         => $arix['mail_discord'] ?? '',
            'mail_twitter'         => $arix['mail_twitter'] ?? '',
            'mail_facebook'        => $arix['mail_facebook'] ?? '',
            'mail_instagram'       => $arix['mail_instagram'] ?? '',
            'mail_linkedin'        => $arix['mail_linkedin'] ?? '',
            'mail_youtube'         => $arix['mail_youtube'] ?? '',
            'mail_status'          => $arix['mail_status'] ?? '',
            'mail_billing'         => $arix['mail_billing'] ?? '',
            'mail_support'         => $arix['mail_support'] ?? '',
        ]);
    }

    public function store(ArixMailRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace(':', '::', $key), $value);
        }
        $this->alert->success('Mail settings saved.')->flash();
        return redirect()->route('admin.arix.mail');
    }
}

<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixStylingRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixStylingController extends Controller
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
        return view('admin.arix.styling', [
            'backgroundImage'      => $this->get('backgroundImage', config('arix.backgroundImage', '')),
            'backgroundImageLight' => $this->get('backgroundImageLight', config('arix.backgroundImageLight', '')),
            'backdrop'             => $this->get('backdrop', config('arix.backdrop', false)),
            'backdropPercentage'   => $this->get('backdropPercentage', config('arix.backdropPercentage', '100%')),
            'defaultMode'          => $this->get('defaultMode', config('arix.defaultMode', 'darkmode')),
            'copyright'            => $this->get('copyright', config('arix.copyright', '')),
            'radiusInput'          => $this->get('radiusInput', config('arix.radiusInput', '5px')),
            'borderInput'          => $this->get('borderInput', config('arix.borderInput', false)),
            'radiusBox'            => $this->get('radiusBox', config('arix.radiusBox', '10px')),
            'flashMessage'         => $this->get('flashMessage', config('arix.flashMessage', 1)),
            'pageTitle'            => $this->get('pageTitle', config('arix.pageTitle', false)),
            'loginBackground'      => $this->get('loginBackground', config('arix.loginBackground', '')),
            'loginGradient'        => $this->get('loginGradient', config('arix.loginGradient', false)),
        ]);
    }

    public function store(ArixStylingRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Styling settings saved.')->flash();
        return redirect()->route('admin.arix.styling');
    }
}

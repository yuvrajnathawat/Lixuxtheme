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

    public function index(): View
    {
        $arix = config('arix');
        return view('admin.arix.styling', [
            'backgroundImage'      => $arix['backgroundImage'] ?? '',
            'backgroundImageLight' => $arix['backgroundImageLight'] ?? '',
            'backdrop'             => $arix['backdrop'] ?? false,
            'backdropPercentage'   => $arix['backdropPercentage'] ?? '100%',
            'defaultMode'          => $arix['defaultMode'] ?? 'darkmode',
            'copyright'            => $arix['copyright'] ?? '',
            'radiusInput'          => $arix['radiusInput'] ?? '5px',
            'borderInput'          => $arix['borderInput'] ?? false,
            'radiusBox'            => $arix['radiusBox'] ?? '10px',
            'flashMessage'         => $arix['flashMessage'] ?? 1,
            'pageTitle'            => $arix['pageTitle'] ?? false,
            'loginBackground'      => $arix['loginBackground'] ?? '',
            'loginGradient'        => $arix['loginGradient'] ?? false,
        ]);
    }

    public function store(ArixStylingRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace(':', '::', $key), $value);
        }
        $this->alert->success('Styling settings saved.')->flash();
        return redirect()->route('admin.arix.styling');
    }
}

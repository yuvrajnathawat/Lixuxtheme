<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixColorsRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixColorsController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings
    ) {}

    public function index(): View
    {
        $arix = config('arix');
        return view('admin.arix.colors', [
            'primary'                      => $arix['primary'] ?? '#4a35cf',
            'successText'                  => $arix['successText'] ?? '#e1ffd8',
            'successBorder'                => $arix['successBorder'] ?? '#56aa2b',
            'successBackground'            => $arix['successBackground'] ?? '#3d8f1f',
            'dangerText'                   => $arix['dangerText'] ?? '#ffd8d8',
            'dangerBorder'                 => $arix['dangerBorder'] ?? '#aa2a2a',
            'dangerBackground'             => $arix['dangerBackground'] ?? '#8f1f20',
            'secondaryText'                => $arix['secondaryText'] ?? '#b2b2c1',
            'secondaryBorder'              => $arix['secondaryBorder'] ?? '#42425b',
            'secondaryBackground'          => $arix['secondaryBackground'] ?? '#2b2b40',
            'gray50'                       => $arix['gray50'] ?? '#f4f4f4',
            'gray100'                      => $arix['gray100'] ?? '#d5d5db',
            'gray200'                      => $arix['gray200'] ?? '#b2b2c1',
            'gray300'                      => $arix['gray300'] ?? '#8282a4',
            'gray400'                      => $arix['gray400'] ?? '#5e5e7f',
            'gray500'                      => $arix['gray500'] ?? '#42425b',
            'gray600'                      => $arix['gray600'] ?? '#2b2b40',
            'gray700'                      => $arix['gray700'] ?? '#1d1d37',
            'gray800'                      => $arix['gray800'] ?? '#0b0d2a',
            'gray900'                      => $arix['gray900'] ?? '#040519',
            'lightmode_primary'            => $arix['lightmode_primary'] ?? '#4a35cf',
            'lightmode_successText'        => $arix['lightmode_successText'] ?? '#e1ffd8',
            'lightmode_successBorder'      => $arix['lightmode_successBorder'] ?? '#56aa2b',
            'lightmode_successBackground'  => $arix['lightmode_successBackground'] ?? '#3d8f1f',
            'lightmode_dangerText'         => $arix['lightmode_dangerText'] ?? '#ffd8d8',
            'lightmode_dangerBorder'       => $arix['lightmode_dangerBorder'] ?? '#aa2a2a',
            'lightmode_dangerBackground'   => $arix['lightmode_dangerBackground'] ?? '#8f1f20',
            'lightmode_secondaryText'      => $arix['lightmode_secondaryText'] ?? '#b2b2c1',
            'lightmode_secondaryBorder'    => $arix['lightmode_secondaryBorder'] ?? '#42425b',
            'lightmode_secondaryBackground'=> $arix['lightmode_secondaryBackground'] ?? '#2b2b40',
            'lightmode_gray50'             => $arix['lightmode_gray50'] ?? '#040519',
            'lightmode_gray100'            => $arix['lightmode_gray100'] ?? '#0b0d2a',
            'lightmode_gray200'            => $arix['lightmode_gray200'] ?? '#1d1d37',
            'lightmode_gray300'            => $arix['lightmode_gray300'] ?? '#2b2b40',
            'lightmode_gray400'            => $arix['lightmode_gray400'] ?? '#42425b',
            'lightmode_gray500'            => $arix['lightmode_gray500'] ?? '#5e5e7f',
            'lightmode_gray600'            => $arix['lightmode_gray600'] ?? '#8282a4',
            'lightmode_gray700'            => $arix['lightmode_gray700'] ?? '#b2b2c1',
            'lightmode_gray800'            => $arix['lightmode_gray800'] ?? '#d5d5db',
            'lightmode_gray900'            => $arix['lightmode_gray900'] ?? '#f4f4f4',
        ]);
    }

    public function store(ArixColorsRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace(':', '::', $key), $value);
        }
        $this->alert->success('Color settings saved.')->flash();
        return redirect()->route('admin.arix.colors');
    }
}

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

    private function get(string $key, mixed $default = null): mixed
    {
        try { return $this->settings->get('arix::' . $key) ?? $default; }
        catch (\Exception $e) { return $default; }
    }

    public function index(): View
    {
        return view('admin.arix.colors', [
            'primary'                       => $this->get('primary', config('arix.primary', '#4a35cf')),
            'successText'                   => $this->get('successText', '#e1ffd8'),
            'successBorder'                 => $this->get('successBorder', '#56aa2b'),
            'successBackground'             => $this->get('successBackground', '#3d8f1f'),
            'dangerText'                    => $this->get('dangerText', '#ffd8d8'),
            'dangerBorder'                  => $this->get('dangerBorder', '#aa2a2a'),
            'dangerBackground'              => $this->get('dangerBackground', '#8f1f20'),
            'secondaryText'                 => $this->get('secondaryText', '#b2b2c1'),
            'secondaryBorder'               => $this->get('secondaryBorder', '#42425b'),
            'secondaryBackground'           => $this->get('secondaryBackground', '#2b2b40'),
            'gray50'                        => $this->get('gray50', '#f4f4f4'),
            'gray100'                       => $this->get('gray100', '#d5d5db'),
            'gray200'                       => $this->get('gray200', '#b2b2c1'),
            'gray300'                       => $this->get('gray300', '#8282a4'),
            'gray400'                       => $this->get('gray400', '#5e5e7f'),
            'gray500'                       => $this->get('gray500', '#42425b'),
            'gray600'                       => $this->get('gray600', '#2b2b40'),
            'gray700'                       => $this->get('gray700', '#1d1d37'),
            'gray800'                       => $this->get('gray800', '#0b0d2a'),
            'gray900'                       => $this->get('gray900', '#040519'),
            'lightmode_primary'             => $this->get('lightmode_primary', '#4a35cf'),
            'lightmode_successText'         => $this->get('lightmode_successText', '#e1ffd8'),
            'lightmode_successBorder'       => $this->get('lightmode_successBorder', '#56aa2b'),
            'lightmode_successBackground'   => $this->get('lightmode_successBackground', '#3d8f1f'),
            'lightmode_dangerText'          => $this->get('lightmode_dangerText', '#ffd8d8'),
            'lightmode_dangerBorder'        => $this->get('lightmode_dangerBorder', '#aa2a2a'),
            'lightmode_dangerBackground'    => $this->get('lightmode_dangerBackground', '#8f1f20'),
            'lightmode_secondaryText'       => $this->get('lightmode_secondaryText', '#b2b2c1'),
            'lightmode_secondaryBorder'     => $this->get('lightmode_secondaryBorder', '#42425b'),
            'lightmode_secondaryBackground' => $this->get('lightmode_secondaryBackground', '#2b2b40'),
            'lightmode_gray50'              => $this->get('lightmode_gray50', '#040519'),
            'lightmode_gray100'             => $this->get('lightmode_gray100', '#0b0d2a'),
            'lightmode_gray200'             => $this->get('lightmode_gray200', '#1d1d37'),
            'lightmode_gray300'             => $this->get('lightmode_gray300', '#2b2b40'),
            'lightmode_gray400'             => $this->get('lightmode_gray400', '#42425b'),
            'lightmode_gray500'             => $this->get('lightmode_gray500', '#5e5e7f'),
            'lightmode_gray600'             => $this->get('lightmode_gray600', '#8282a4'),
            'lightmode_gray700'             => $this->get('lightmode_gray700', '#b2b2c1'),
            'lightmode_gray800'             => $this->get('lightmode_gray800', '#d5d5db'),
            'lightmode_gray900'             => $this->get('lightmode_gray900', '#f4f4f4'),
        ]);
    }

    public function store(ArixColorsRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Color settings saved.')->flash();
        return redirect()->route('admin.arix.colors');
    }
}

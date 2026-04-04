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
        $arix = config('arix');
        return view('admin.arix.meta', [
            'meta_color'       => $arix['meta_color'] ?? '#4a35cf',
            'meta_title'       => $arix['meta_title'] ?? 'Pterodactyl',
            'meta_description' => $arix['meta_description'] ?? '',
            'meta_image'       => $arix['meta_image'] ?? '',
            'meta_favicon'     => $arix['meta_favicon'] ?? '',
        ]);
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

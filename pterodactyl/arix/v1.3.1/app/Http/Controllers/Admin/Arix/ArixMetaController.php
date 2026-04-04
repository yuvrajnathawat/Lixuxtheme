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

    private function get(string $key, mixed $default = null): mixed
    {
        try { return $this->settings->get('arix::' . $key) ?? $default; }
        catch (\Exception $e) { return $default; }
    }

    public function index(): View
    {
        return view('admin.arix.meta', [
            'meta_color'       => $this->get('meta_color', config('arix.meta_color', '#4a35cf')),
            'meta_title'       => $this->get('meta_title', config('arix.meta_title', 'Pterodactyl')),
            'meta_description' => $this->get('meta_description', config('arix.meta_description', '')),
            'meta_image'       => $this->get('meta_image', config('arix.meta_image', '')),
            'meta_favicon'     => $this->get('meta_favicon', config('arix.meta_favicon', '')),
        ]);
    }

    public function store(ArixMetaRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace('arix:', 'arix::', $key), $value);
        }
        $this->alert->success('Meta settings saved.')->flash();
        return redirect()->route('admin.arix.meta');
    }
}

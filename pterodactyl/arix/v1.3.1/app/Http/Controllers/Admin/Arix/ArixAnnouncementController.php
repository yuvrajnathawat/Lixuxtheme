<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixAnnouncementRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixAnnouncementController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings
    ) {}

    public function index(): View
    {
        $arix = config('arix');
        return view('admin.arix.announcement', [
            'announcementType'      => $arix['announcementType'] ?? 'disabled',
            'announcementMessage'   => $arix['announcementMessage'] ?? '',
            'announcementCloseable' => $arix['announcementCloseable'] ?? false,
        ]);
    }

    public function store(ArixAnnouncementRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set(str_replace(':', '::', $key), $value);
        }
        $this->alert->success('Announcement settings saved.')->flash();
        return redirect()->route('admin.arix.announcement');
    }
}

<?php

namespace Pterodactyl\Http\ViewComposers;

use Illuminate\View\View;
use Pterodactyl\Services\Helpers\AssetHashService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class AssetComposer
{
    public function __construct(
        private AssetHashService $assetHashService,
        private SettingsRepositoryInterface $settings
    ) {}

    public function compose(View $view): void
    {
        $view->with('asset', $this->assetHashService);
        $view->with('siteConfiguration', [
            'name'   => config('app.name') ?? 'Pterodactyl',
            'arix'   => $this->getArixConfig(),
            'locale' => config('app.locale') ?? 'en',
            'recaptcha' => [
                'enabled' => config('recaptcha.enabled', false),
                'siteKey' => config('recaptcha.website_key') ?? '',
            ],
        ]);
    }

    private function getArixConfig(): array
    {
        $defaults = config('arix', []);
        $keys = array_keys($defaults);

        foreach ($keys as $key) {
            try {
                $value = $this->settings->get('arix::' . $key);
                if ($value !== null) {
                    $defaults[$key] = $value;
                }
            } catch (\Exception $e) {
                // keep default
            }
        }

        return $defaults;
    }
}

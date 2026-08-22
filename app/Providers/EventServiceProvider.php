<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Application-wide event subscribers.
 *
 * The Microsoft Socialite driver is provided by the
 * `socialiteproviders/microsoft` package and registered via the
 * `SocialiteWasCalled` event hook. We register the listener only when
 * the upstream class is present so the app boots cleanly during
 * `composer install` (chicken-and-egg: providers boot before
 * dependencies are available).
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event => listeners map.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];

    public function boot(): void
    {
        parent::boot();

        if (
            class_exists(\SocialiteProviders\Manager\SocialiteWasCalled::class)
            && class_exists(\SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class)
        ) {
            \Illuminate\Support\Facades\Event::listen(
                \SocialiteProviders\Manager\SocialiteWasCalled::class,
                \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class.'@handle'
            );
        }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

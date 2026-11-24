<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        $this->configureDefaults();
        if (! app()->isLocal()) {
            $url->forceScheme('https');
        } else {
            $url->forceScheme('http');
        }
        RateLimiter::for('alumni-registration', fn ($request): Limit => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('alumni-video-presign', fn ($request): Limit => Limit::perMinute(3)->by($request->ip().'|'.$request->session()->getId()));
        RateLimiter::for('alumni-video-confirm', fn ($request): Limit => Limit::perMinute(5)->by($request->ip().'|'.$request->session()->getId()));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}

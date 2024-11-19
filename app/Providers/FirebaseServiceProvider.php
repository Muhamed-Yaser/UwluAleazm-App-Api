<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebase.messaging', function ($app) {
            $serviceAccount = base_path('storage/app/firebase/uwlualeazm-firebase-adminsdk-zy06a-5a6fadcc03.json');

            return (new Factory)
                ->withServiceAccount($serviceAccount)
                ->createMessaging();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

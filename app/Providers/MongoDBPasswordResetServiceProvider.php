<?php

namespace App\Providers;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Passwords\PasswordBrokerManager;

class MongoDBPasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->extend('auth.password', function ($service, $app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->bind('db.connection.mongodb', function ($app) {
            return $app->make('db')->connection('mongodb');
        });

        $this->app->bind('auth.password.broker', function ($app) {
            $connection = $app->make('db.connection.mongodb');
            return new DatabaseTokenRepository(
                $connection,
                $app['hash'],
                'password_reset_tokens',
                $app['config']['auth.passwords.users.options'] ?? []
            );
        });
    }
}

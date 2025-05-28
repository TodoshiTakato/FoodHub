<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Passport\MongoAuthCode;
use App\Models\Passport\MongoClient;
use App\Models\Passport\MongoRefreshToken;
use App\Models\Passport\MongoToken;
use Laravel\Passport\Passport;

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
    public function boot(): void
    {
        Passport::useAuthCodeModel(authCodeModel: MongoAuthCode::class);
        Passport::useClientModel(clientModel: MongoClient::class);
        Passport::useRefreshTokenModel(refreshTokenModel: MongoRefreshToken::class);
        Passport::useTokenModel(tokenModel: MongoToken::class);
    }
}

<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MongoDBPasswordResetServiceProvider::class,
    MongoDB\Laravel\MongoDBServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
];

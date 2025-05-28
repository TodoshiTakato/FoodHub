<?php

namespace App\Models\Passport;

use MongoDB\Laravel\Eloquent\DocumentModel;
use Laravel\Passport\RefreshToken as PassportRefreshToken;
class MongoRefreshToken extends PassportRefreshToken
{
    use DocumentModel;
    protected $primaryKey = '_id';
    protected $keyType = 'string';
}

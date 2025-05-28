<?php

namespace App\Models\Passport;

use Laravel\Passport\AuthCode as PassportAuthCode;
use MongoDB\Laravel\Eloquent\DocumentModel;

class MongoAuthCode extends PassportAuthCode
{
    use DocumentModel;
    protected $primaryKey = '_id';
    protected $keyType = 'string';
}

<?php

namespace App\Models\Passport;

use MongoDB\Laravel\Eloquent\DocumentModel;
use Laravel\Passport\Client as PassportClient;

class MongoClient extends PassportClient
{
    use DocumentModel;
    protected $primaryKey = '_id';
    protected $keyType = 'string';
}

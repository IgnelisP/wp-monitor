<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    // Defining fillable attributes to protect against mass assignment vulnerabilities
    protected $fillable = [
        'user_id',
        'subscription_id',
        'name',
        'scheme',
        'domain',
        'path',
        'api_key',
    ];
}

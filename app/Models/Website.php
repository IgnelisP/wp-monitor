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

    // Return the full URL as stored in the database
    public function getFullUrlAttribute()
    {
    return $this->scheme . $this->domain . $this->path;
    }

    /**
     * Get the user that owns the website.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription associated with the website.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}

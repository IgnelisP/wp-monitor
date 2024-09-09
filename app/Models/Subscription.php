<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;
use Carbon\Carbon;

class Subscription extends CashierSubscription
{
    /**
     * Get the website associated with the subscription.
     */
    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function getRenewsAtAttribute()
    {
        return Carbon::parse($this->asStripeSubscription()->current_period_end);
    }
}
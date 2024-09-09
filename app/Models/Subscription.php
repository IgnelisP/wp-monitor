<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;
use Carbon\Carbon;

class Subscription extends CashierSubscription
{
    public function getRenewsAtAttribute()
    {
        return Carbon::parse($this->asStripeSubscription()->current_period_end);
    }
}
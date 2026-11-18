<?php

namespace App\Console\Commands;

use App\Support\CustomerDiscounts;
use Illuminate\Console\Command;

class IssueCustomerDiscounts extends Command
{
    protected $signature = 'discounts:issue';

    protected $description = 'Issue loyalty and comeback coupons and warn customers before a coupon expires';

    public function handle(CustomerDiscounts $discounts): int
    {
        $discounts->issueScheduled();
        $this->info('Customer discounts checked.');

        return self::SUCCESS;
    }
}

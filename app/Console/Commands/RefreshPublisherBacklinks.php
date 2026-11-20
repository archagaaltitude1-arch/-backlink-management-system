<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\DataForSeo\BacklinkSummary;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('publishers:refresh-backlinks {domain : Existing publisher domain}')]
#[Description('Refresh domain rating, traffic, spam score, and backlink counts for one publisher using billable DataForSEO requests')]
class RefreshPublisherBacklinks extends Command
{
    public function handle(BacklinkSummary $summary): int
    {
        $website = Website::query()->where('domain', mb_strtolower(trim($this->argument('domain'))))->first();
        $login = config('services.dataforseo.login');
        $password = config('services.dataforseo.password');

        if (! $website || ! $login || ! $password) {
            $this->error('An existing publisher domain and DataForSEO credentials are required.');

            return self::FAILURE;
        }

        $counts = $summary->fetch($website->domain);

        if ($counts['error'] === 'connection') {
            $this->error('Could not reach DataForSEO.');

            return self::FAILURE;
        }

        if (! $counts['ok']) {
            $this->error('DataForSEO did not return valid backlink counts.');

            return self::FAILURE;
        }

        $website->backlinks = $counts['backlinks'];
        $website->referring_domains = $counts['referring_domains'];
        $website->domain_rating = $counts['domain_rating'];

        if ($counts['spam_score'] !== null) {
            $website->spam_score = $counts['spam_score'];
        }

        if ($counts['monthly_traffic'] !== null) {
            $website->monthly_traffic = $counts['monthly_traffic'];
        }

        $website->backlinks_refreshed_at = now();
        $website->save();

        $this->info('Publisher backlink counts refreshed.');

        return self::SUCCESS;
    }
}

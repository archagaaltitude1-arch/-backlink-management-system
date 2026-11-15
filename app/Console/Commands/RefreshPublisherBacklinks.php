<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

#[Signature('publishers:refresh-backlinks {domain : Existing publisher domain}')]
#[Description('Refresh backlink counts for one publisher using a billable DataForSEO request')]
class RefreshPublisherBacklinks extends Command
{
    public function handle(): int
    {
        $website = Website::query()->where('domain', mb_strtolower(trim($this->argument('domain'))))->first();
        $login = config('services.dataforseo.login');
        $password = config('services.dataforseo.password');

        if (! $website || ! $login || ! $password) {
            $this->error('An existing publisher domain and DataForSEO credentials are required.');

            return self::FAILURE;
        }

        try {
            $response = Http::withBasicAuth($login, $password)->withoutRedirecting()
                ->connectTimeout(3)->timeout(15)
                ->post('https://api.dataforseo.com/v3/backlinks/summary/live', [
                    ['target' => $website->domain],
                ]);
        } catch (ConnectionException) {
            $this->error('Could not reach DataForSEO.');

            return self::FAILURE;
        }

        $result = $response->json('tasks.0.result.0');
        $backlinks = data_get($result, 'backlinks');
        $referringDomains = data_get($result, 'referring_domains');

        if (! $response->successful() || $response->json('tasks.0.status_code') !== 20000
            || ! is_int($backlinks) || $backlinks < 0 || ! is_int($referringDomains) || $referringDomains < 0) {
            $this->error('DataForSEO did not return valid backlink counts.');

            return self::FAILURE;
        }

        $website->backlinks = $backlinks;
        $website->referring_domains = $referringDomains;
        $website->backlinks_refreshed_at = now();
        $website->save();

        $this->info('Publisher backlink counts refreshed.');

        return self::SUCCESS;
    }
}

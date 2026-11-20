<?php

namespace App\Services\DataForSeo;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class BacklinkSummary
{
    /**
     * Live metrics used by the niche specification filters.
     *
     * Domain Rating is DataForSEO rank (0–1000) scaled to the 0–100 filter.
     * Monthly traffic is estimated organic traffic. Spam score is the target
     * spam score when DataForSEO returns one.
     *
     * @return array{ok: bool, error: string, backlinks: int, referring_domains: int, domain_rating: int, spam_score: ?int, monthly_traffic: ?int}
     */
    public function fetch(string $domain): array
    {
        $failed = [
            'ok' => false,
            'error' => 'invalid',
            'backlinks' => 0,
            'referring_domains' => 0,
            'domain_rating' => 0,
            'spam_score' => null,
            'monthly_traffic' => null,
        ];
        $login = config('services.dataforseo.login');
        $password = config('services.dataforseo.password');

        if (! is_string($login) || $login === '' || ! is_string($password) || $password === '') {
            return $failed;
        }

        try {
            $summary = $this->client($login, $password)
                ->post('https://api.dataforseo.com/v3/backlinks/summary/live', [
                    ['target' => $domain],
                ]);
            $overview = $this->client($login, $password)
                ->post('https://api.dataforseo.com/v3/dataforseo_labs/google/domain_rank_overview/live', [[
                    'target' => $domain,
                    'location_code' => 2840,
                    'language_code' => 'en',
                ]]);
        } catch (ConnectionException) {
            return ['ok' => false, 'error' => 'connection', 'backlinks' => 0, 'referring_domains' => 0, 'domain_rating' => 0, 'spam_score' => null, 'monthly_traffic' => null];
        }

        $backlinks = $summary->json('tasks.0.result.0.backlinks');
        $referringDomains = $summary->json('tasks.0.result.0.referring_domains');
        $rank = $summary->json('tasks.0.result.0.rank');
        $spam = $summary->json('tasks.0.result.0.target_spam_score');

        if (! $summary->successful() || $summary->json('tasks.0.status_code') !== 20000
            || ! is_int($backlinks) || $backlinks < 0
            || ! is_int($referringDomains) || $referringDomains < 0
            || ! is_int($rank) || $rank < 0) {
            return $failed;
        }

        return [
            'ok' => true,
            'error' => '',
            'backlinks' => $backlinks,
            'referring_domains' => $referringDomains,
            'domain_rating' => min(100, (int) round($rank / 10)),
            'spam_score' => is_int($spam) && $spam >= 0 && $spam <= 100 ? $spam : null,
            'monthly_traffic' => $this->traffic($overview->json('tasks.0.result.0.items.0.metrics.organic.etv'), $overview->successful(), $overview->json('tasks.0.status_code')),
        ];
    }

    private function client(string $login, string $password): PendingRequest
    {
        return Http::withBasicAuth($login, $password)
            ->withOptions(['verify' => (bool) config('services.dataforseo.verify')])
            ->withoutRedirecting()
            ->connectTimeout(3)
            ->timeout(20);
    }

    private function traffic(mixed $etv, bool $successful, mixed $status): ?int
    {
        if (! $successful || $status !== 20000 || ! is_numeric($etv) || (float) $etv < 0) {
            return null;
        }

        return (int) round((float) $etv);
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class GmailTransport extends AbstractTransport
{
    public function __construct(
        private readonly ?string $clientId,
        private readonly ?string $clientSecret,
        private readonly ?string $refreshToken,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if (! $this->clientId || ! $this->clientSecret || ! $this->refreshToken) {
            throw new TransportException('Gmail OAuth credentials are missing.');
        }

        try {
            $authorization = Http::asForm()->connectTimeout(3)->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $this->refreshToken,
                    'grant_type' => 'refresh_token',
                ]);

            $accessToken = $authorization->json('access_token');

            if (! $authorization->successful() || ! is_string($accessToken) || $accessToken === '') {
                throw new TransportException('Google rejected the Gmail sender authorization (HTTP '.$authorization->status().').');
            }

            $raw = rtrim(strtr(base64_encode($message->toString()), '+/', '-_'), '=');
            $delivery = Http::withToken($accessToken)->connectTimeout(3)->timeout(15)
                ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', ['raw' => $raw]);
        } catch (ConnectionException) {
            throw new TransportException('Could not connect to Google to send email.');
        }

        if (! $delivery->successful() || ! is_string($delivery->json('id'))) {
            throw new TransportException('Gmail did not confirm message acceptance (HTTP '.$delivery->status().').');
        }
    }

    public function __toString(): string
    {
        return 'gmail';
    }
}

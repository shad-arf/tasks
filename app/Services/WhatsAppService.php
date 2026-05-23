<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppService
{
    /**
     * @return array<string, mixed>
     */
    public function sendTextMessage(string $phone, string $message, ?string $orderId = null, ?string $account = null): array
    {
        $account = trim($account ?? (string) config('services.whatsapp.account'));

        if ($account === '') {
            throw new RuntimeException('WhatsApp account is not configured.');
        }

        $payload = [
            'phone' => $this->normalizePhone($phone),
            'message' => $message,
            'message_type' => 'text',
            'account' => $account,
        ];

        if ($orderId !== null && $orderId !== '') {
            $payload['order_id'] = $orderId;
        }

        $response = Http::acceptJson()
            ->withToken($this->resolveAccessToken())
            ->timeout(15)
            ->post($this->resolveUrl((string) config('services.whatsapp.send_endpoint', '/api/send')), $payload);

        $response->throw();

        return $response->json();
    }

    private function resolveAccessToken(): string
    {
        $token = trim((string) config('services.whatsapp.token'));

        if ($token !== '') {
            return $token;
        }

        $tokenUrl = trim((string) config('services.whatsapp.token_url'));
        $clientId = trim((string) config('services.whatsapp.client_id'));
        $clientSecret = trim((string) config('services.whatsapp.client_secret'));

        if ($tokenUrl === '' || $clientId === '' || $clientSecret === '') {
            throw new RuntimeException('WhatsApp credentials are incomplete. Set WHATSAPP_TOKEN or configure token URL, client ID, and client secret.');
        }

        $cacheKey = 'whatsapp.access_token.'.md5($this->resolveUrl($tokenUrl).'|'.$clientId);
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && trim($cachedToken) !== '') {
            return trim($cachedToken);
        }

        $response = Http::acceptJson()
            ->timeout(15)
            ->post($this->resolveUrl($tokenUrl), [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

        $response->throw();

        $token = $response->json('access_token') ?? $response->json('token');

        if (! is_string($token) || trim($token) === '') {
            throw new RuntimeException('WhatsApp token response did not contain a token.');
        }

        $expiresIn = (int) ($response->json('expires_in') ?? 86400);
        $ttl = max(60, $expiresIn - 60);

        Cache::put($cacheKey, trim($token), now()->addSeconds($ttl));

        return trim($token);
    }

    private function resolveUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $baseUrl = rtrim((string) config('services.whatsapp.base_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('WhatsApp base URL is not configured.');
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}

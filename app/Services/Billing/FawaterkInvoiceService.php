<?php

namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FawaterkInvoiceService
{
    /**
     * Fawaterak staging host (test cycle). Production uses app.fawaterk.com.
     *
     * @see https://fawaterak-api.readme.io/
     */
    protected const SANDBOX_API_BASE = 'https://staging.fawaterk.com/api/v2';

    protected const LIVE_API_BASE = 'https://app.fawaterk.com/api/v2';

    protected function createInvoiceEndpoint(): string
    {
        $override = config('services.fawaterk.api_base_url');
        if (is_string($override) && $override !== '') {
            return rtrim($override, '/').'/createInvoiceLink';
        }

        $base = config('services.fawaterk.sandbox', false)
            ? self::SANDBOX_API_BASE
            : self::LIVE_API_BASE;

        return $base.'/createInvoiceLink';
    }

    /**
     * Catalog line items may use a string name or (from settings / i18n) a nested structure.
     */
    protected function stringifyCatalogLabel(mixed $name): string
    {
        if (is_string($name)) {
            return $name;
        }
        if (is_array($name)) {
            foreach ($name as $v) {
                if (is_string($v) && $v !== '') {
                    return $v;
                }
                if (is_array($v)) {
                    $inner = $this->stringifyCatalogLabel($v);
                    if ($inner !== '') {
                        return $inner;
                    }
                }
            }

            return 'VidaNexus item';
        }

        return is_scalar($name) ? (string) $name : 'VidaNexus item';
    }

    protected function stringifyPrice(mixed $price): string
    {
        if (is_int($price) || is_float($price)) {
            return (string) round($price);
        }
        if (is_string($price)) {
            return (string) round((float) str_replace(',', '', $price));
        }
        if (is_array($price)) {
            foreach ($price as $v) {
                if (is_numeric($v)) {
                    return (string) round((float) $v);
                }
            }
        }

        return '0';
    }

    /**
     * Build a readable error string from Fawaterak JSON (message, errors, or field arrays like "token").
     */
    protected function formatFawaterkErrorPayload(array $data): string
    {
        $parts = [];
        if (isset($data['message'])) {
            $m = $data['message'];
            $parts[] = is_scalar($m) ? (string) $m : json_encode($m, JSON_UNESCAPED_UNICODE);
        }
        foreach (['errors', 'error'] as $k) {
            if (! empty($data[$k]) && is_array($data[$k])) {
                $parts[] = json_encode($data[$k], JSON_UNESCAPED_UNICODE);
            }
        }
        foreach (['token', 'vendor', 'cartTotal', 'currency'] as $k) {
            if (isset($data[$k]) && is_array($data[$k])) {
                $parts[] = $k.': '.implode('; ', array_map('strval', $data[$k]));
            }
        }

        return implode(' ', array_filter($parts)) ?: 'Unknown gateway error.';
    }

    /**
     * Expose gateway JSON and setup hints only outside production (local, staging, etc.).
     */
    protected function shouldExposeFawaterkDetailsToUser(): bool
    {
        return ! in_array((string) config('app.env'), ['production'], true);
    }

    protected function fawaterkUserFacingError(string $detailed): string
    {
        return $this->shouldExposeFawaterkDetailsToUser()
            ? $detailed
            : 'Could not initiate payment. Please try again later.';
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{url: string|null, error: string|null, order_ref: string}
     */
    public function createInvoiceLink(
        ?User $user,
        string $type,
        string $id,
        array $item,
        string $orderRef,
        ?string $guestName,
        ?string $guestEmail,
        bool $newAccountFlow
    ): array {
        $apiKeyRaw = config('services.fawaterk.api_key', env('FAWATERK_API_KEY'));
        $apiKey = is_string($apiKeyRaw) ? trim($apiKeyRaw) : '';
        // withToken() adds "Bearer "; strip if the key was pasted including the prefix.
        $apiKey = (string) preg_replace('#^Bearer\s+#i', '', $apiKey);
        if ($apiKey === '') {
            $missingKeyMsg = 'Payment is not configured (missing FAWATERK_API_KEY). Add your key from the Fawaterak dashboard.';

            return [
                'url' => null,
                'error' => $this->fawaterkUserFacingError($missingKeyMsg),
                'order_ref' => $orderRef,
            ];
        }

        $fullName = $user ? $user->name : ($guestName ?? 'User VidaNexus');
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?: 'User';
        $lastName = $nameParts[1] ?? 'VidaNexus';
        $email = $user ? $user->email : ($guestEmail ?? 'guest@vidanexus.com');
        $phone = $user ? ($user->phone ?? '01000000000') : '01000000000';
        $address = $user ? ($user->country ?? 'Online Platform') : 'Online Platform';

        $newAccountFlag = $newAccountFlow ? '&new_account=1' : '';

        $lineName = str_replace('—', '-', $this->stringifyCatalogLabel($item['name'] ?? ''));
        $linePrice = $this->stringifyPrice($item['price'] ?? 0);

        $payload = [
            'cartTotal' => $linePrice,
            'currency' => 'EGP',
            'customer' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ],
            'redirectionUrls' => [
                'successUrl' => url('/payment/fawaterk/callback?status=success&type='.$type.'&id='.$id.'&ref='.$orderRef.$newAccountFlag),
                'failUrl' => url('/payment/fawaterk/callback?status=fail&ref='.$orderRef.$newAccountFlag),
                'pendingUrl' => url('/payment/fawaterk/callback?status=pending&ref='.$orderRef.$newAccountFlag),
            ],
            'cartItems' => [
                [
                    'name' => 'VidaNexus '.$lineName.($type === 'tool' ? ' - Monthly Subscription' : ' Package'),
                    'price' => $linePrice,
                    'quantity' => '1',
                ],
            ],
            // Fawaterak docs use camelCase payLoad (custom data echoed on webhooks).
            'payLoad' => $orderRef,
        ];

        $endpoint = $this->createInvoiceEndpoint();

        try {
            $response = Http::timeout(60)
                ->withToken($apiKey)
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ])
                ->post($endpoint, $payload);

            $data = $response->json();

            Log::info('Fawaterk createInvoiceLink Response', [
                'status_code' => $response->status(),
                'response' => $data,
                'order_ref' => $orderRef,
                'user_id' => $user?->id ?? 'NEW',
            ]);

            if ($response->successful() && isset($data['data']['url'])) {
                return ['url' => $data['data']['url'], 'error' => null, 'order_ref' => $orderRef];
            }

            if (isset($data['status']) && $data['status'] === 'success' && isset($data['data']['url'])) {
                return ['url' => $data['data']['url'], 'error' => null, 'order_ref' => $orderRef];
            }

            $detail = $this->formatFawaterkErrorPayload($data);
            $errorMsg = 'Could not initiate payment. '.$detail;

            $lower = strtolower($detail);
            if (str_contains($lower, 'invalid token') || str_contains($lower, 'inactive vendor')) {
                $errorMsg .= ' Generate a new API key in the Fawaterak dashboard that matches this request URL (staging vs live), save it as FAWATERK_API_KEY, run php artisan config:clear, and retry.';
            }

            Log::error('Fawaterk API Error', [
                'response' => $data,
                'payload' => $payload,
                'endpoint' => $endpoint,
                'fawaterk_sandbox_mode' => config('services.fawaterk.sandbox_mode'),
                'fawaterk_sandbox' => config('services.fawaterk.sandbox'),
                'api_key_length' => strlen($apiKey),
            ]);

            return ['url' => null, 'error' => $this->fawaterkUserFacingError($errorMsg), 'order_ref' => $orderRef];
        } catch (\Throwable $e) {
            Log::error('Fawaterk Exception', [
                'msg' => $e->getMessage(),
                'api_endpoint' => $endpoint,
                'exception' => $e::class,
            ]);

            $detailed = 'Payment gateway error: '.$e->getMessage();
            $userMessage = $this->shouldExposeFawaterkDetailsToUser()
                ? $detailed
                : 'Payment service is temporarily unavailable. Please try again later.';

            return ['url' => null, 'error' => $userMessage, 'order_ref' => $orderRef];
        }
    }
}

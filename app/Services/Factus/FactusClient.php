<?php

namespace App\Services\Factus;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FactusClient
{
    private string $baseUrl;
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $username;
    private ?string $password;

    public function __construct()
    {
        $config = config('services.factus');
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api-sandbox.factus.com.co', '/');
        $this->clientId = $config['client_id'] ?? null;
        $this->clientSecret = $config['client_secret'] ?? null;
        $this->username = $config['username'] ?? null;
        $this->password = $config['password'] ?? null;
    }

    public function getAccessToken(): string
    {
        return Cache::remember('factus_oauth_token', 3500, function () {
            $response = Http::asForm()
                ->timeout(15)
                ->retry(2, 200, throw: false)
                ->post("{$this->baseUrl}/oauth/token", [
                    'grant_type' => 'password',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username' => $this->username,
                    'password' => $this->password,
                ]);

            if ($response->failed()) {
                Log::error('Factus OAuth authentication failed', ['body' => $response->body()]);
                throw new RuntimeException('Error de autenticación con la API de Factus: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Emite una factura electrónica validada ante la DIAN
     */
    public function emitBill(array $billData): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500, throw: false)
            ->post("{$this->baseUrl}/v1/bills/validate", $billData);

        if ($response->failed()) {
            $errorMessage = $response->json('message') ?? $response->body();
            Log::error('Factus Bill emission failed', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'payload' => $billData,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }

        $data = $response->json('data') ?? $response->json();

        return [
            'success' => true,
            'bill_number' => $data['bill']['number'] ?? $data['number'] ?? 'FAC-TEST',
            'cufe' => $data['bill']['cufe'] ?? $data['cufe'] ?? 'CUFE-TEST-HASH',
            'qr_url' => $data['bill']['qr'] ?? $data['qr'] ?? 'https://catalogo-vpfe.dian.gov.co/test',
            'pdf_url' => $data['bill']['public_url'] ?? $data['pdf_url'] ?? null,
            'status' => 'sent_valid',
        ];
    }
}

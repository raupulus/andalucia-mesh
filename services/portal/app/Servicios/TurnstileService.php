<?php

declare(strict_types=1);

namespace App\Servicios;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Servicio de verificación de Cloudflare Turnstile para protección contra spam y bots.
 * Si no hay claves configuradas en el entorno (.env), el servicio opera en modo desactivado (degradado)
 * permitiendo la validación transparente.
 */
class TurnstileService
{
    /**
     * Endpoint oficial de verificación de Cloudflare Turnstile.
     */
    protected const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Determina si la verificación de Turnstile está activa en el entorno actual.
     */
    public function isEnabled(): bool
    {
        $secret = config('services.turnstile.secret_key');

        return ! empty($secret) && is_string($secret);
    }

    /**
     * Devuelve la clave pública (Site Key) para renderizar el widget en la vista.
     */
    public function getSiteKey(): ?string
    {
        $siteKey = config('services.turnstile.site_key');

        return is_string($siteKey) && ! empty($siteKey) ? $siteKey : null;
    }

    /**
     * Verifica la validez de un token entregado por el widget de Turnstile.
     *
     * @param  string|null  $token  Token devuelto por Cloudflare ('cf-turnstile-response')
     * @param  string|null  $ip  Dirección IP remota del cliente
     * @return bool True si es válido o si el servicio está desactivado; False si falla
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        // Si no está habilitado (sin clave en .env), se omite la validación
        if (! $this->isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $params = [
                'secret' => (string) config('services.turnstile.secret_key'),
                'response' => $token,
            ];

            if (! empty($ip)) {
                $params['remoteip'] = $ip;
            }

            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, $params);

            if (! $response->successful()) {
                Log::warning('Turnstile HTTP verification request returned unsuccessful status', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            $success = (bool) $response->json('success', false);

            if (! $success) {
                Log::notice('Turnstile challenge verification failed', [
                    'errors' => $response->json('error-codes', []),
                ]);
            }

            return $success;
        } catch (Throwable $e) {
            Log::error('Error connecting to Cloudflare Turnstile API', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

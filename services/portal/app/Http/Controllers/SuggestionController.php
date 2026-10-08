<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Servicios\TurnstileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controlador para la recepción y visualización del buzón de sugerencias ciudadanas.
 */
class SuggestionController extends Controller
{
    public function __construct(
        protected TurnstileService $turnstile,
    ) {}

    /**
     * Muestra la vista con el formulario público de sugerencias.
     */
    public function create(Request $request): View
    {
        $enviada = $request->query('enviada') === '1';

        return view('sugerencias', [
            'enviada' => $enviada,
            'siteKey' => $this->turnstile->getSiteKey(),
            'error' => null,
            'old' => [],
        ]);
    }

    /**
     * Procesa y valida el envío de una nueva sugerencia.
     */
    public function store(Request $request): View|JsonResponse|Response
    {
        // 1. Control de tasa de envíos para mitigar abusos por IP (hash anonimizado con salt de APP_KEY)
        $ip = (string) ($request->ip() ?? '127.0.0.1');
        $ipHash = hash('sha256', $ip.(string) config('app.key'));

        $recentCount = Suggestion::where('ip_hash', $ipHash)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentCount >= 10) {
            $msg = __('portal.suggestions.err_too_many');
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => $msg], 429);
            }

            return view('sugerencias', [
                'enviada' => false,
                'siteKey' => $this->turnstile->getSiteKey(),
                'error' => $msg,
                'old' => $request->all(),
            ]);
        }

        // 2. Validación de campos del formulario
        $categoriaValida = array_key_exists((string) $request->input('category'), Suggestion::CATEGORIES);
        $contenido = trim((string) $request->input('content', ''));

        if (! $categoriaValida) {
            $msg = __('portal.suggestions.err_invalid_category');
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => $msg], 422);
            }

            return view('sugerencias', [
                'enviada' => false,
                'siteKey' => $this->turnstile->getSiteKey(),
                'error' => $msg,
                'old' => $request->all(),
            ]);
        }

        if (mb_strlen($contenido) < 10) {
            $msg = __('portal.suggestions.err_content_min');
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => $msg], 422);
            }

            return view('sugerencias', [
                'enviada' => false,
                'siteKey' => $this->turnstile->getSiteKey(),
                'error' => $msg,
                'old' => $request->all(),
            ]);
        }

        if (mb_strlen($contenido) > 3000) {
            $msg = __('portal.suggestions.err_content_max');
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => $msg], 422);
            }

            return view('sugerencias', [
                'enviada' => false,
                'siteKey' => $this->turnstile->getSiteKey(),
                'error' => $msg,
                'old' => $request->all(),
            ]);
        }

        // 3. Verificación de Cloudflare Turnstile si está activo
        if ($this->turnstile->isEnabled()) {
            $token = $request->input('cf-turnstile-response');
            if (empty($token) || ! is_string($token) || ! $this->turnstile->verify($token, $ip)) {
                $msg = __('portal.suggestions.err_turnstile_fail');
                if ($request->wantsJson()) {
                    return response()->json(['ok' => false, 'error' => $msg], 422);
                }

                return view('sugerencias', [
                    'enviada' => false,
                    'siteKey' => $this->turnstile->getSiteKey(),
                    'error' => $msg,
                    'old' => $request->all(),
                ]);
            }
        }

        // 4. Registro de la sugerencia en la base de datos
        Suggestion::create([
            'category' => (string) $request->input('category'),
            'content' => $contenido,
            'status' => Suggestion::STATUS_PENDING,
            'operator_notes' => null,
            'ip_hash' => $ipHash,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => __('portal.suggestions.success_message'),
            ]);
        }

        return view('sugerencias', [
            'enviada' => true,
            'siteKey' => $this->turnstile->getSiteKey(),
            'error' => null,
            'old' => [],
        ]);
    }
}

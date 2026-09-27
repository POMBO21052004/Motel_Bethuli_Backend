<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de rate limiting personnalisé.
 *
 * Paramètres (passés via la définition de route) :
 *   $maxAttempts  — nombre de requêtes autorisées sur la fenêtre (défaut : 60)
 *   $decayMinutes — durée de la fenêtre en minutes (défaut : 1)
 *   $prefix       — préfixe de la clé de cache, pour isoler les groupes de routes (défaut : '')
 *
 * Améliorations par rapport à la version initiale :
 *   - S'applique à toutes les méthodes HTTP (GET, POST, PUT, DELETE…)
 *   - Identifie l'utilisateur connecté (via son ID) plutôt que seulement l'IP
 *     pour les routes privées, afin d'éviter que plusieurs utilisateurs derrière
 *     le même proxy/NAT partagent le même compteur.
 *   - Ajoute l'en-tête standard "Retry-After" dans la réponse 429.
 *   - Log optionnel des dépassements (compatible avec les logs Laravel).
 */
class ThrottleRequestsCustom
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  int|string  $maxAttempts
     * @param  int|string  $decayMinutes
     * @param  string      $prefix
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = ''): Response
    {
        $maxAttempts  = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;

        $key = $prefix . $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($request, $key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Construit la clé unique pour identifier le « client ».
     *
     * Pour un utilisateur authentifié, on utilise son ID (plus stable et précis
     * que l'IP derrière un proxy). Pour un visiteur non authentifié, on utilise
     * l'IP + le chemin de la route pour avoir une granularité par endpoint.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Utilisateur connecté → clé basée sur l'ID utilisateur + route
        if ($request->user()) {
            return sha1(
                $request->user()->getAuthIdentifier() .
                '|' . $request->route()?->getName() .
                '|' . $request->path()
            );
        }

        // Visiteur non connecté → clé basée sur l'IP + méthode + route
        return sha1(
            $request->ip() .
            '|' . $request->method() .
            '|' . $request->server('SERVER_NAME', 'localhost') .
            '|' . $request->path()
        );
    }

    /**
     * Construit la réponse 429 Too Many Requests.
     */
    protected function buildTooManyAttemptsResponse(Request $request, string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        $timeMessage = $this->formatRetryTime($retryAfter);
        $errorMessage = "Trop de tentatives. Veuillez réessayer dans {$timeMessage}.";

        // Log pour l'audit (visible dans storage/logs/laravel.log)
        \Illuminate\Support\Facades\Log::warning('Rate limit atteint', [
            'ip'    => $request->ip(),
            'path'  => $request->path(),
            'user'  => $request->user()?->id ?? 'anonyme',
        ]);

        // Toutes les requêtes API retournent du JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message'     => $errorMessage,
                'retry_after' => $retryAfter,
            ], 429)->withHeaders([
                'Retry-After'          => $retryAfter,
                'X-RateLimit-Limit'    => $maxAttempts,
                'X-RateLimit-Remaining'=> 0,
            ]);
        }

        // Pour les requêtes web classiques (si jamais utilisé hors API)
        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage, 'email' => $errorMessage])
            ->with('error', $errorMessage);
    }

    /**
     * Formate la durée restante en message lisible en français.
     */
    protected function formatRetryTime(int $seconds): string
    {
        $minutes = (int) floor($seconds / 60);
        $secs    = $seconds % 60;

        if ($minutes > 0 && $secs > 0) {
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '') .
                   " et {$secs} seconde" . ($secs > 1 ? 's' : '');
        }

        if ($minutes > 0) {
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '');
        }

        return "{$seconds} seconde" . ($seconds > 1 ? 's' : '');
    }

    /**
     * Ajoute les headers X-RateLimit-* à la réponse.
     */
    protected function addRateLimitHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit'     => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
        ]);

        return $response;
    }

    /**
     * Calcule le nombre de tentatives restantes.
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key);
    }
}

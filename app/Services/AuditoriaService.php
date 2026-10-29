<?php

namespace App\Services;

use App\Models\AuditoriaLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    public function registar(
        string $modulo,
        string|int|null $objetoId,
        string $alteracao,
        ?User $user = null
    ): AuditoriaLog {
        $usuario = $user ?? auth::user();
        $ip = request()?->ip() ?? '127.0.0.1';
        $userAgent = request()?->userAgent();
        $browser = $this->simplificarBrowser($userAgent);

        return AuditoriaLog::create([
            'user_id' => $usuario?->id,
            'modulo' => $modulo,
            'objeto_id' => $objetoId !== null ? (string) $objetoId : null,
            'alteracao' => $alteracao,
            'ip' => $ip,
            'browser' => $browser,
        ]);
    }

    public function simplificarBrowser(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Desconhecido';
        }

        if (str_contains($userAgent, 'Edg/') || str_contains($userAgent, 'Edge/')) {
            return 'Microsoft Edge';
        }

        if (str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera/')) {
            return 'Opera';
        }

        if (str_contains($userAgent, 'Firefox/')) {
            return 'Mozilla Firefox';
        }

        if (str_contains($userAgent, 'Chrome/') || str_contains($userAgent, 'CriOS/')) {
            return 'Google Chrome';
        }

        if (str_contains($userAgent, 'Safari/')) {
            return 'Apple Safari';
        }

        return 'Outro';
    }
}
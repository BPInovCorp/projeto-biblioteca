<?php

namespace App\Services;

use App\Models\Livro;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RecomendacaoLivroService
{
    public function recomendar(Livro $livro, int $limite = 3): array
    {
        $limiteSeguro = max(1, min(10, $limite));

        $cacheKey = "rec_livro_{$livro->id}_{$limiteSeguro}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($livro, $limiteSeguro) {
            $textoLivro = ($livro->nome ?? '') . ' ' . ($livro->bibliografia ?? '');
            $termosLivro = $this->extrairPalavrasChave($textoLivro);
            $autoresLivroIds = $livro->autores->pluck('id')->all();

            if (empty($termosLivro) && empty($autoresLivroIds)) {
                return [];
            }

            $candidatos = Livro::with(['autores'])->where('id', '!=', $livro->id)->get();

        $pontuados = [];

        foreach ($candidatos as $candidato) {
            $textoCandidato = ($candidato->nome ?? '') . ' ' . ($candidato->bibliografia ?? '');
            $termosCandidato = $this->extrairPalavrasChave($textoCandidato);

            $similaridadeJaccard = $this->calcularSimilaridade($termosLivro, $termosCandidato);
            $pontuacao = $similaridadeJaccard;

            $autoresCandidatoIds = $candidato->autores->pluck('id')->all();
            if (!empty(array_intersect($autoresLivroIds, $autoresCandidatoIds))) {
                $pontuacao += 0.25;
            }

            if ($pontuacao >= 0.05) {
                $capaUrl = $candidato->imagem_capa
                    ? (Str::startsWith($candidato->imagem_capa, ['http://', 'https://']) ? $candidato->imagem_capa : asset($candidato->imagem_capa))
                    : '';

                $pontuados[] = [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'capa' => $capaUrl,
                    'autor' => $candidato->autores->pluck('nome')->implode(', ') ?: 'Autor Desconhecido',
                    'pontuacao' => round($pontuacao, 2),
                ];
            }
        }

        usort($pontuados, function ($a, $b) {
            if ($b['pontuacao'] === $a['pontuacao']) {
                return strcmp($a['nome'], $b['nome']);
            }
            return $b['pontuacao'] <=> $a['pontuacao'];
        });

        return array_slice($pontuados, 0, $limiteSeguro);
        });
    }

    public function extrairPalavrasChave(string $texto): array
    {
        $limpo = Str::ascii(mb_strtolower(trim($texto)));
        $limpo = preg_replace('/[^a-z0-9\s]/', ' ', $limpo) ?? '';
        $palavras = preg_split('/\s+/', $limpo, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $stopWords = $this->obterStopWords();

        $filtradas = array_filter($palavras, function ($p) use ($stopWords) {
            return strlen($p) >= 3 && !in_array($p, $stopWords, true);
        });

        return array_values(array_unique($filtradas));
    }

    public function calcularSimilaridade(array $termosA, array $termosB): float
    {
        if (empty($termosA) || empty($termosB)) {
            return 0.0;
        }

        $intersecao = count(array_intersect($termosA, $termosB));
        $uniao = count(array_unique(array_merge($termosA, $termosB)));

        return $uniao > 0 ? (float) ($intersecao / $uniao) : 0.0;
    }

    public function obterStopWords(): array
    {
        return [
            'de', 'da', 'do', 'das', 'dos', 'em', 'no', 'na', 'nos', 'nas',
            'por', 'pelo', 'pela', 'pelos', 'pelas', 'para', 'com', 'sem',
            'sob', 'sobre', 'que', 'uma', 'umas', 'um', 'uns', 'este', 'esta',
            'estes', 'estas', 'esse', 'essa', 'esses', 'essas', 'aquele', 'aquela',
            'aqueles', 'aquelas', 'isto', 'isso', 'aquilo', 'ele', 'ela', 'eles',
            'elas', 'seu', 'sua', 'seus', 'suas', 'como', 'mais', 'muito',
            'muitos', 'muita', 'muitas', 'quando', 'onde', 'quem', 'qual',
            'quais', 'mas', 'nem', 'tambem', 'entre', 'ate', 'apos', 'durante',
            'livro', 'obra', 'historia', 'romance', 'autor', 'escritor', 'edicao',
            'publicado', 'ano', 'seculo', 'pagina', 'paginas', 'vida', 'mundo',
        ];
    }
}
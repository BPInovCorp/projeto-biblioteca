<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleBooksService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.google_books.base_url', 'https://www.googleapis.com/books/v1');
        $this->apiKey = config('services.google_books.api_key');
        $this->timeout = (int) config('services.google_books.timeout', 10);
    }

    public function search(string $query, int $maxResults = 20, int $startIndex = 0): array
    {
        $cleanQuery = trim($query);

        if ($cleanQuery === '') {
            return [
                'total' => 0,
                'items' => [],
            ];
        }

        $cacheKey = 'gb_search_' . md5($cleanQuery . '_' . $maxResults . '_' . $startIndex);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($cleanQuery, $maxResults, $startIndex) {
            $params = [
                'q' => $cleanQuery,
                'maxResults' => max(1, min(20, $maxResults)),
                'startIndex' => max(0, $startIndex),
            ];

            if ($this->apiKey) {
                $params['key'] = $this->apiKey;
            }

            try {
                $response = Http::timeout($this->timeout)->retry(2, 200)->get("{$this->baseUrl}/volumes", $params);

            if (!$response->successful()) {
                Log::warning('Google Books API retornou erro HTTP', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'total' => 0,
                    'items' => [],
                ];
            }

            $data = $response->json();
            $total = (int) ($data['totalItems'] ?? 0);
            $rawItems = (array) ($data['items'] ?? []);

            $normalizedItems = [];
            foreach ($rawItems as $item) {
                if (is_array($item)) {
                    $normalized = $this->normalize($item);
                    if ($normalized !== null) {
                        $normalizedItems[] = $normalized;
                    }
                }
            }

            return [
                    'total' => $total,
                    'items' => $normalizedItems,
                ];
            } catch (Throwable $e) {
                Log::error('Exceção ao comunicar com a Google Books API: ' . $e->getMessage());

                return [
                    'total' => 0,
                    'items' => [],
                ];
            }
        });
    }

    public function findVolume(string $volumeId): ?array
    {
        $cleanId = trim($volumeId);

        if ($cleanId === '') {
            return null;
        }

        $params = [];
        if ($this->apiKey) {
            $params['key'] = $this->apiKey;
        }

        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/volumes/{$cleanId}", $params);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            return is_array($data) ? $this->normalize($data) : null;
        } catch (Throwable $e) {
            Log::error("Exceção ao procurar volume {$cleanId} na Google Books API: " . $e->getMessage());

            return null;
        }
    }

    public function normalize(array $item): ?array
    {
        $googleId = (string) ($item['id'] ?? '');

        if ($googleId === '') {
            return null;
        }

        $volumeInfo = (array) ($item['volumeInfo'] ?? []);
        $saleInfo = (array) ($item['saleInfo'] ?? []);

        $nome = trim((string) ($volumeInfo['title'] ?? ''));
        if ($nome === '') {
            $nome = 'Título Desconhecido';
        }

        if (!empty($volumeInfo['subtitle'])) {
            $nome .= ' - ' . trim((string) $volumeInfo['subtitle']);
        }

        $autores = [];
        if (!empty($volumeInfo['authors']) && is_array($volumeInfo['authors'])) {
            foreach ($volumeInfo['authors'] as $autor) {
                $autorLimpo = trim((string) $autor);
                if ($autorLimpo !== '') {
                    $autores[] = $autorLimpo;
                }
            }
        }
        if (empty($autores)) {
            $autores = ['Autor Desconhecido'];
        }

        $editora = trim((string) ($volumeInfo['publisher'] ?? ''));
        if ($editora === '') {
            $editora = 'Editora Desconhecida';
        }

        $isbn = null;
        $industryIdentifiers = (array) ($volumeInfo['industryIdentifiers'] ?? []);
        foreach ($industryIdentifiers as $identifier) {
            if (is_array($identifier) && isset($identifier['type'], $identifier['identifier'])) {
                if ($identifier['type'] === 'ISBN_13') {
                    $isbn = trim((string) $identifier['identifier']);
                    break;
                }
            }
        }
        if ($isbn === null) {
            foreach ($industryIdentifiers as $identifier) {
                if (is_array($identifier) && isset($identifier['type'], $identifier['identifier'])) {
                    if ($identifier['type'] === 'ISBN_10') {
                        $isbn = trim((string) $identifier['identifier']);
                        break;
                    }
                }
            }
        }

        $bibliografia = null;
        if (!empty($volumeInfo['description'])) {
            $limpaDesc = html_entity_decode(strip_tags((string) $volumeInfo['description']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $bibliografia = trim($limpaDesc) ?: null;
        }

        $imagemCapa = null;
        $imageLinks = (array) ($volumeInfo['imageLinks'] ?? []);
        $rawImage = $imageLinks['thumbnail'] ?? $imageLinks['smallThumbnail'] ?? null;
        if (!empty($rawImage) && is_string($rawImage)) {
            $imagemCapa = Str::startsWith($rawImage, 'http://')
                ? 'https://' . Str::after($rawImage, 'http://')
                : $rawImage;
        }

        $preco = null;
        $moeda = null;
        if (isset($saleInfo['retailPrice']['amount']) && is_numeric($saleInfo['retailPrice']['amount'])) {
            $preco = (float) $saleInfo['retailPrice']['amount'];
            $moeda = (string) ($saleInfo['retailPrice']['currencyCode'] ?? 'EUR');
        } elseif (isset($saleInfo['listPrice']['amount']) && is_numeric($saleInfo['listPrice']['amount'])) {
            $preco = (float) $saleInfo['listPrice']['amount'];
            $moeda = (string) ($saleInfo['listPrice']['currencyCode'] ?? 'EUR');
        }

        $anoPublicacao = null;
        if (!empty($volumeInfo['publishedDate'])) {
            $anoPublicacao = substr((string) $volumeInfo['publishedDate'], 0, 4);
        }

        return [
            'google_id' => $googleId,
            'nome' => $nome,
            'autores' => $autores,
            'editora' => $editora,
            'isbn' => $isbn,
            'bibliografia' => $bibliografia,
            'imagem_capa' => $imagemCapa,
            'preco' => $preco,
            'moeda' => $moeda,
            'ano_publicacao' => $anoPublicacao,
        ];
    }
}
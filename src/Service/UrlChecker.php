<?php

declare(strict_types=1);

namespace Hexlet\Code\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\DomCrawler\Crawler;
use function optional;

final class UrlChecker
{
    public function __construct(
        private Client $client
    )
    {
    }

    public function check(string $url): array
    {
        try {
            /*
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    //'User-Agent' => 'Mozilla/5.0 (compatible; UrlChecker/1.0)'
                ]]);
            */

            $response = $this->client->request('GET', $url);
            $crawler = new Crawler((string)$response->getBody());

            return [
                'status_code' => $response->getStatusCode(),
                'h1' => $crawler->filter('h1')->count() ? $crawler->filter('h1')->first()->text() : null,
                'title' => $crawler->filter('title')->count() ? $crawler->filter('title')->first()->text() : null,
                'description' => $crawler->filter('meta[name="description"]')->count() ? $crawler->filter('meta[name="description"]')->first()->attr('content') : null,
            ];
        } catch (ConnectException $e) {
            return [];
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Article;
use Illuminate\Support\Str;

class ScrapeArticles extends Command
{
    protected $signature = 'scrape:articles';
    protected $description = 'Scrape articles from KlikDokter and Halodoc';

    public function handle()
    {
        $this->fetchFromApi();

        $this->info("✅ Selesai fetch & seed via API.");
    }

    private function scrapeKlikDokter()
    {
        $client = new Client();
        $this->info("\n🩺 Mulai scrape dari KlikDokter...");

        for ($page = 1; $page <= 3; $page++) {
            $url = "https://www.klikdokter.com/info-sehat?page={$page}";
            $response = $client->get($url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            $crawler->filter('.article-card__title > a')->each(function ($node) use ($client) {
                $articleUrl = $node->attr('href');
                if (!Str::startsWith($articleUrl, 'http')) {
                    $articleUrl = 'https://www.klikdokter.com' . $articleUrl;
                }

                $res = $client->get($articleUrl);
                $html = (string) $res->getBody();
                $detail = new Crawler($html);

                $title = $detail->filter('h1')->text();
                $content = $detail->filter('.content-detail__content')->text();
                $imageUrl = $detail->filter('.content-detail__image img')->attr('src') ?? null;
                $author = $detail->filter('.author')->count() ? $detail->filter('.author')->text() : 'KlikDokter';

                Article::updateOrCreate([
                    'source' => $articleUrl
                ], [
                    'title' => $title,
                    'summary' => Str::limit(strip_tags($content), 200),
                    'content' => $content,
                    'image_url' => $imageUrl,
                    'author' => $author,
                    'source' => $articleUrl,
                    'tag' => 'Kesehatan',
                    'activity_level' => 'light',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info("✔ KlikDokter: $title");
            });
        }
    }

    private function scrapeHalodoc()
    {
        $client = new Client();
        $this->info("\n🧬 Mulai scrape dari Halodoc...");

        for ($page = 1; $page <= 3; $page++) {
            $url = "https://www.halodoc.com/artikel?page={$page}";
            $response = $client->get($url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            $crawler->filter('.css-18n8lnv a')->each(function ($node) use ($client) {
                $articleUrl = $node->attr('href');
                if (!Str::startsWith($articleUrl, 'http')) {
                    $articleUrl = 'https://www.halodoc.com' . $articleUrl;
                }

                $res = $client->get($articleUrl);
                $html = (string) $res->getBody();
                $detail = new Crawler($html);

                $title = $detail->filter('h1')->text();
                $content = $detail->filter('div.css-1v32n3h')->text();
                $imageUrl = $detail->filter('picture img')->attr('src') ?? null;

                Article::updateOrCreate([
                    'source' => $articleUrl
                ], [
                    'title' => $title,
                    'summary' => Str::limit(strip_tags($content), 200),
                    'content' => $content,
                    'image_url' => $imageUrl,
                    'author' => 'Halodoc',
                    'source' => $articleUrl,
                    'tag' => 'Kesehatan',
                    'activity_level' => 'light',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info("✔ Halodoc: $title");
            });
        }
    }
}

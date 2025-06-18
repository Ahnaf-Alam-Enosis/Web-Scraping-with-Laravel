<?php

namespace App\Http\Controllers;

use Cloudstudio\Ollama\Facades\Ollama;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index() {}

    public function scrape(Request $request)
    {
        $client = HttpClient::create();

        $url = $request->input('url');

        // // return response()->json('Hello world');

        // $response = $client->request('GET', $url);
        // $html = $response->getContent();

        // $crawler = new Crawler($html);
        // $title = $crawler->filter('.product-short-info > h1')->text();
        // $element = $crawler->filter('short-description');

        // echo $title . '\n';

        // echo '<pre>';

        $apiKey = "06462c275b9d7357054cb1a993ef3132";
        $response = Http::get("http://api.scraperapi.com", [
            'api_key' => $apiKey,
            'url' => $url,
            'render' => 'false'
        ]);

        $html = $response->body();
        $crawler = new Crawler($html);

        // echo '<pre>';
        // print_r($html);

        // $title = $crawler->filter('h1')->first()->text('');
        // $price = $crawler->filter('.price, .product-price, span.a-price, .price-block > span, .product_main > p')->first()->text('');

        // $image = $crawler->filter('img')->first()->attr('src');
        // $description = $crawler->filter('.product-description, #description, .pdp-product-detail, .product_page > p')->first()->text('');

        $prompt = <<<EOT
                You are a smart AI agent that reads raw HTML from an eCommerce product page.
                Extract the following details as JSON:
                - product_title
                - price
                - image_url
                - description

                Here is the HTML:
                $html
                Respond with only the JSON output.
            EOT;

        $apiKey = "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";

        try {
            $response = Http::timeout(80)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.openai.com/v1/chat/completions',  [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'This is AI agent'
                    ],
                    [
                        'role' => 'user',
                        'content' => "please give a 100 word sample message"
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.2,
            ]);

            $response = $response->json();
            return $response;
        } catch (\Exception $e) {
            return response()->json(['error' => $response['error']['message']]);
        }




        // return view('product-details', compact('title', 'price', 'description', 'image'));
        // return response()->json([
        //     'title' => $title,
        //     'price' => $price,
        //     'image' => $image,
        //     'description' => $description,
        // ]);
    }

    public function scrapeByAI(Request $request)
    {

        $url = $request->input('url');

        $apiKey = "06462c275b9d7357054cb1a993ef3132";
        $scraperResponse = Http::get("http://api.scraperapi.com", [
            'api_key' => $apiKey,
            'url' => $url,
            'render' => 'false'
        ]);

        $html = $scraperResponse->body();


        $baseUrl = 'https://openrouter.ai/api/v1/';
        $client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                'HTTP-Referer' => env('OPENROUTER_REFERRER', 'https://localhost'),
                'X-Title' => env('APP_NAME', 'Laravel'),
                'Content-Type' => 'application/json',
            ],
        ]);

        $prompt = "Extract these fields from the HTML below:
        - productTitle (string)
        - productDescription (string)
        - productPrice (string with currency)
        - productImageUrl (full URL)
        - productRating (number or null if not available)

        Return ONLY a JSON object with these fields. No explanations.

        HTML: " . $html;

        try {
            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => env('DEEPSEEK_MODEL', 'deepseek/deepseek-r1-0528:free'),
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                ],
                'timeout' => 500
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            throw new \Exception("OpenRouter API Error: " . $e->getMessage());
        }


        echo '<pre>';
        var_dump($response->getBody());
    }

    public function ScrapeByOllama(Request $request)
    {
        $url = $request->input('url');

        $html = $this->scraping($url);

        return response($html)->header('Content-Type', 'text/html');

        return;
        $messages = [
            ['role' => 'user', 'content' => $this->getPrompt($html)]
        ];

        $response = Ollama::agent('You are web scraping expert....')
            ->model(config('ollama-laravel.model'))
            ->options(['temperature' => floatval(config('ollama-laravel.temperature'))])
            ->stream(false)
            ->format('json')
            ->chat($messages);

        return $response;
    }

    private function getPrompt($html)
    {
        $prompt = "Extract these fields from the HTML below:
        - productTitle (string)
        - productDescription (string)
        - productPrice (string with currency)
        - productImageUrl (full URL)
        - productRating (number or null if not available)

        Return ONLY a JSON object with these fields. No explanations.

        HTML: " . $html;

        return $prompt;
    }

    private function scraping($url)
    {
        $apiKey = "06462c275b9d7357054cb1a993ef3132";
        $response = Http::get("http://api.scraperapi.com", [
            'api_key' => $apiKey,
            'url' => $url,
            'output_format' => 'markdown',
            'render' => 'true',
        ]);

        $html = $response->body();
        $cleanHtml = $this->cleanUp($html);
        $this->getLeafNode($cleanHtml);

        Storage::disk('local')->put('example.txt', $html);
        Storage::disk('local')->put('cleaned.txt', $cleanHtml);

        // echo $title;
        // echo '\n' . $price;
        // echo '\n' . $description;
        // echo '\n' . $rating;

        return $cleanHtml;
    }

    private function cleanUp($html)
    {
        $crawler = new Crawler($html);

        $crawler->filterXPath('//script|//style|//link|//nav|//svg|//button|//a|//form|//input|//select')->each(function (Crawler $crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $html = $crawler->html();

        $cleanHtml = preg_replace([
            '/(\r?\n\s*){2,}/',         // Multiple newlines
        ], [' ', '><', "\n", '', ''], $html);

        return trim($cleanHtml);

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();

        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($dom);

        $allowedTags = ['html', 'body', 'h1', 'h2', 'p', 'span', 'img', 'ul', 'li', 'div'];

        foreach ($xpath->query('//*') as $node) {
            if (!in_array($node->nodeName, $allowedTags)) {
                $node->parentNode->removeChild($node);
            }
        }

        libxml_clear_errors();

        return $dom->saveHTML();
    }

    private function getLeafNode($html)
    {
        // $htmlContent = Storage::get('htmlContent.txt');

        $crawler = new Crawler($html);
        $leafNodes = [];

        $crawler->filter('*')->each(function (Crawler $node) use (&$leafNodes) {
            if ($node->children()->count() === 0) {
                $text = trim($node->text());
                if (!empty($text)) {
                    if (!empty($text)) {
                        $leafNodes[] = $node->outerHtml();
                    }
                }
            }
        });

        Storage::disk('local')->put('lastTags.txt', implode("\n", $leafNodes));

        foreach ($leafNodes as $leaf) {
            echo $leaf . "\n";
        }
    }
}

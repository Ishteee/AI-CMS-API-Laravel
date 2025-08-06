<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateArticleSlug implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $articleId)
    {
    }

    public function handle(): void
    {
        $article = Article::find($this->articleId); // Find the article using the ID
        if (!$article) {
            return;
        }

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('Gemini API key is not set.');
            return;
        }

        $prompt = "You are an SEO expert. Generate a concise, unique, and URL-friendly slug for an article. The slug should be all lowercase, with words separated by hyphens. Base the slug primarily on the article's title, but use the initial content for context to make it more descriptive if possible. Do not include any explanation, just the slug itself.\n\n"
            . "TITLE: \"{$article->title}\"\n"
            . "CONTENT: \"" . substr($article->content, 0, 250) . "...\"";
        
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($endpoint, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $slug = $response->json('candidates.0.content.parts.0.text');
                $article->slug = Str::slug(trim($slug), '-');
            } else {
                throw new \Exception('API call failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Gemini API call failed for slug generation: ' . $e->getMessage());
            $article->slug = Str::slug($article->title) . '-' . $article->id;
        }

        $article->save();
    }
}
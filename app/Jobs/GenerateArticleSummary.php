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

class GenerateArticleSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $articleId)
    {
    }

    public function handle(): void
    {
        $article = Article::find($this->articleId);
        if (!$article) {
            return;
        }

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('Gemini API key is not set.');
            return;
        }

        // --- FIX IS HERE ---
        // Use the local $article variable, NOT $this->article
        $prompt = "You are a content editor. Summarize the following article content in 2 or 3 short, engaging sentences. Here is the content: \"{$article->content}\"";
        
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($endpoint, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $article->summary = trim($response->json('candidates.0.content.parts.0.text')); // Use local $article
            } else {
                throw new \Exception('API call failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Gemini API call failed for summary generation: ' . $e->getMessage());
            $article->summary = substr($article->content, 0, 250) . '...'; // Use local $article
        }
        
        $article->save(); // Use local $article
    }
}
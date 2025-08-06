<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Validator;
use App\Jobs\GenerateArticleSlug;    
use App\Jobs\GenerateArticleSummary;   
use Carbon\Carbon;  

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start with a base query for articles
        $query = Article::query();

        // Eager load relationships for efficiency to prevent N+1 problems
        $query->with('author:id,name', 'categories:id,name');

        // Filter by status if the 'status' parameter is present
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category if the 'category' parameter is present
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                // Look for categories where the slug or name matches the parameter
                $q->where('slug', $request->category)->orWhere('name', $request->category);
            });
        }

        // Filter by author name if the 'author' parameter is present
        if ($request->has('author')) {
            $query->whereHas('author', function ($q) use ($request) {
                // Search for authors where the name is similar to the provided value
                $q->where('name', 'like', '%' . $request->author . '%');
            });
        }

        // Filter by date range if 'start_date' and 'end_date' are present
        if ($request->has('start_date') && $request->has('end_date')) {
            try {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('published_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                // Silently ignore invalid date formats
            }
        }

        // Order by the latest published articles first, then by creation date
        $articles = $query->orderBy('published_at', 'desc')->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $articles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id', // Check each ID exists
            'status' => 'sometimes|in:Draft,Archived,Published', // Optional, must be one of these
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Determine the status and published_at date
        $status = $request->input('status', 'Draft'); // Default to 'Draft'
        $published_at = ($status === 'Published') ? Carbon::now() : null;

        // Create the article and associate it with the logged-in user
        $article = $request->user()->articles()->create([
            'title' => $request->title,
            'content' => $request->content,
            'status' => $status,
            'published_at' => $published_at,
        ]);

        // Attach the categories to the article
        $article->categories()->attach($request->category_ids);

        // Dispatch the jobs to the queue for background processing
        GenerateArticleSlug::dispatch($article->id);
        GenerateArticleSummary::dispatch($article->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Article created successfully. Slug and summary are being generated.',
            'data' => $article,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        // Eager load the author and categories for the response
        $article->load('author:id,name', 'categories:id,name');

        return response()->json([
            'status' => 'success',
            'data' => $article,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        // 1. Authorize the action using the Gate
        $this->authorize('manage-article', $article);

        // 2. Validate the incoming data
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id',
            'status' => 'sometimes|in:Draft,Published,Archived',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 3. Update the article
        $article->update($request->only('title', 'content', 'status'));

        // If categories are provided, sync them
        if ($request->has('category_ids')) {
            $article->categories()->sync($request->category_ids);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Article updated successfully',
            'data' => $article->load('author:id,name', 'categories:id,name'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        // Authorize the action using the Gate
        $this->authorize('manage-article', $article);

        $article->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Article deleted successfully',
        ], 200);
    }
}

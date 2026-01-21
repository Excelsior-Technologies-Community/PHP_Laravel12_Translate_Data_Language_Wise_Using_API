<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->get('lang', 'en');
        
        $posts = Post::with(['translations' => function($query) use ($locale) {
            $query->where('locale', $locale);
        }])->get();
        
        $formattedPosts = $posts->map(function($post) use ($locale) {
            $translation = $post->getTranslated($locale);
            
            return [
                'id' => $post->id,
                'title' => $translation->title,
                'content' => $translation->content,
                'locale' => $locale,
                'original_post' => $locale !== 'en' ? [
                    'title' => $post->title,
                    'content' => $post->content
                ] : null,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at
            ];
        });
        
        return response()->json([
            'success' => true,
            'locale' => $locale,
            'data' => $formattedPosts
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content
        ]);

        // Auto-translate to all languages
        $translations = $post->translateAndSave();

        return response()->json([
            'success' => true,
            'message' => 'Post created and translated to all languages',
            'data' => [
                'post' => $post,
                'translations' => $translations
            ]
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $locale = $request->get('lang', 'en');
        
        $post = Post::with(['translations' => function($query) use ($locale) {
            $query->where('locale', $locale);
        }])->find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        $translation = $post->getTranslated($locale);
        
        return response()->json([
            'success' => true,
            'locale' => $locale,
            'data' => [
                'id' => $post->id,
                'title' => $translation->title,
                'content' => $translation->content,
                'original_post' => $locale !== 'en' ? [
                    'title' => $post->title,
                    'content' => $post->content
                ] : null,
                'available_translations' => $post->translations->pluck('locale'),
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at
            ]
        ]);
    }

    public function translatePost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'locale' => 'required|string|in:en,hi,gu'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post = Post::find($id);
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        $locale = $request->locale;
        $translation = $post->getTranslated($locale);
        
        return response()->json([
            'success' => true,
            'message' => 'Post translated successfully',
            'data' => [
                'original_id' => $post->id,
                'locale' => $locale,
                'title' => $translation->title,
                'content' => $translation->content
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post->update($request->only(['title', 'content']));
        
        // Re-translate all languages if post updated
        if ($request->hasAny(['title', 'content'])) {
            $post->translateAndSave();
        }

        return response()->json([
            'success' => true,
            'message' => 'Post updated and translations refreshed',
            'data' => $post
        ]);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }
}
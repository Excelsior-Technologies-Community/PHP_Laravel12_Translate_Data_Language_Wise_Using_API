<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use App\Services\GoogleTranslateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GlossaryController extends Controller
{
    public function index(Request $request)
    {
        $query = GlossaryTerm::query();

        if ($request->has('locale') && $request->locale !== 'all') {
            $query->forLocale($request->locale);
        }

        if ($request->filled('search')) {
            $query->where('term', 'like', '%' . $request->search . '%')
                  ->orWhere('replacement', 'like', '%' . $request->search . '%');
        }

        $terms = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $terms
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'term' => 'required|string|max:255',
            'replacement' => 'nullable|string|max:255',
            'target_locale' => 'nullable|string|max:10',
            'do_not_translate' => 'nullable|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $term = GlossaryTerm::create([
            'term' => trim($request->term),
            'replacement' => $request->filled('replacement') ? trim($request->replacement) : null,
            'target_locale' => $request->input('target_locale', 'all') ?: 'all',
            'do_not_translate' => $request->boolean('do_not_translate', true),
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Glossary term registered successfully',
            'data' => $term
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $term = GlossaryTerm::find($id);
        if (!$term) {
            return response()->json(['success' => false, 'message' => 'Term not found'], 404);
        }

        $term->update($request->only([
            'term', 'replacement', 'target_locale', 'do_not_translate', 'description', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Glossary term updated successfully',
            'data' => $term
        ]);
    }

    public function destroy($id)
    {
        $term = GlossaryTerm::find($id);
        if (!$term) {
            return response()->json(['success' => false, 'message' => 'Term not found'], 404);
        }

        $term->delete();

        return response()->json([
            'success' => true,
            'message' => 'Glossary term deleted successfully'
        ]);
    }

    /**
     * Test translation comparison with and without glossary brand lock.
     */
    public function test(Request $request, GoogleTranslateService $translateService)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
            'target_lang' => 'required|string|max:10',
            'source_lang' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $text = $request->text;
        $targetLang = $request->target_lang;
        $sourceLang = $request->input('source_lang', 'en');

        // Translate with glossary
        $withGlossary = $translateService->translate($text, $targetLang, $sourceLang, true);

        // Translate without glossary
        $withoutGlossary = $translateService->translate($text, $targetLang, $sourceLang, false);

        return response()->json([
            'success' => true,
            'data' => [
                'original_text' => $text,
                'target_lang' => $targetLang,
                'source_lang' => $sourceLang,
                'with_glossary' => $withGlossary,
                'without_glossary' => $withoutGlossary,
            ]
        ]);
    }
}

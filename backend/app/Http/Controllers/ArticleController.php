<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Menampilkan daftar artikel.
     * Jika parameter ?activity_level=... ada, filter berdasarkan activity_level.
     */
    public function index(Request $request)
    {
        $activityLevel = $request->query('activity_level');

        $articles = Article::query()
            ->when($activityLevel, function ($query, $activityLevel) {
                $query->where('activity_level', $activityLevel);
            })
            ->get();

        return response()->json($articles);
    }

    /**
     * Endpoint khusus (opsional) jika ingin memisahkan filtering.
     */
    public function byActivityLevel(Request $request)
    {
        $activityLevel = $request->query('activity_level');

        if (!$activityLevel) {
            return response()->json([
                'message' => 'Parameter activity_level wajib diisi.'
            ], 400);
        }

        $articles = Article::where('activity_level', $activityLevel)->get();

        return response()->json($articles);
    }
}

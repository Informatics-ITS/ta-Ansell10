<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    public function index(): JsonResponse
    {
        // kembalikan semua baris exercises
        return response()->json(Exercise::all());
    }
}

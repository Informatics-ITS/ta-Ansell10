<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    // GET /api/activities
    public function index(Request $request)
    {
        // ambil user yg login
        $user = $request->user();

        $profile = $request->query('user_profiles_id');
        // bangun base query
        $q = Activity::where('user_id', $user->id)
            ->when($profile, fn($q) => $q->where('user_profiles_id', $profile))
            ->when($request->filled('date'), fn($q) => $q->where('date', $request->query('date')));

        // optional filter by profile
        if ($request->filled('user_profiles_id')) {
            $q->where('user_profiles_id', $request->query('user_profiles_id'));
        }

        // optional filter by single date
        if ($request->filled('date')) {
            $q->where('date', $request->query('date'));
        }

        // agregasi multi-aktivitas per tanggal
        $activities = $q->select([
            'date',
            DB::raw('SUM(sleep)          AS total_sleep'),
            DB::raw('SUM(duration)       AS total_duration'),
            DB::raw('SUM(calorie_intake) AS total_calories'),
            DB::raw('SUM(steps)          AS total_steps'),
            DB::raw('SUM(water_intake)   AS total_water'),
            // Ambil exercise terakhir per tanggal
            DB::raw("(SELECT a2.exercise_id 
                    FROM activities a2 
                    WHERE a2.user_id = {$user->id}
                        AND a2.user_profiles_id = {$profile}
                        AND a2.date = activities.date
                        AND a2.exercise_id IS NOT NULL
                    ORDER BY a2.created_at DESC
                    LIMIT 1) AS latest_exercise_id"),
            // Ambil MET-nya dengan join
            DB::raw("(SELECT e.met_value 
                    FROM activities a2 
                    JOIN exercises e ON a2.exercise_id = e.id
                    WHERE a2.user_id = {$user->id}
                        AND a2.user_profiles_id = {$profile}
                        AND a2.date = activities.date
                        AND a2.exercise_id IS NOT NULL
                    ORDER BY a2.created_at DESC
                    LIMIT 1) AS latest_exercise_met"),
            // Ambil nama exercise terakhir
            DB::raw("(SELECT e.name 
                    FROM activities a2 
                    JOIN exercises e ON a2.exercise_id = e.id
                        WHERE a2.user_id = {$user->id}
                        AND a2.user_profiles_id = {$profile}
                        AND a2.date = activities.date
                        AND a2.exercise_id IS NOT NULL
                    ORDER BY a2.created_at DESC
                    LIMIT 1) AS latest_exercise_name"),
        ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($activities);
    }

    // POST /api/activities
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_profiles_id' => 'required|exists:user_profiles,id',
            'date'             => 'required|date',

            // Activity hanya wajib jika tidak ada steps, sleep, atau exercise_id
            'activity'         => 'required_without_all:steps,sleep,exercise_id|string',
            'detail'           => 'nullable|string',

            // Validasi nilai numerik wajar
            'sleep'            => 'nullable|integer|min:0|max:24',
            'exercise_id'      => 'nullable|exists:exercises,id',
            'duration'         => 'nullable|integer|min:0|max:1000',       
            'steps'            => 'nullable|integer|min:0|max:50000',
            'water_intake'     => 'nullable|integer|min:0|max:10000',     
            'calorie_intake'   => 'nullable|integer|min:0|max:10000',
        ]);


        $validated['user_id'] = $request->user()->id;

        $act = Activity::create($validated);

        return response()->json($act, 201);
    }


    public function deleteByDate(Request $request, $date)
    {
        $user = $request->user();
        $profileId = $request->query('user_profiles_id');

        $query = Activity::where('user_id', $user->id)
            ->where('date', $date);

        if ($profileId) {
            $query->where('user_profiles_id', $profileId);
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => "Berhasil menghapus seluruh aktivitas pada tanggal $date.",
            'deleted' => $deleted,
        ]);
    }



    // GET /api/activities/history
    public function history(Request $request)
    {
        $user = $request->user();

        if (!$request->filled('user_profiles_id')) {
            return response()->json([
                'message' => 'user_profiles_id wajib disertakan.',
            ], 422);
        }

        $profileId = $request->query('user_profiles_id');

        $activities = Activity::where('user_id', $user->id)
            ->where('user_profiles_id', $profileId)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($activities);
    }
    // GET /api/activities/{id}
    public function show($id)
    {
        return response()->json(Activity::findOrFail($id));
    }
}

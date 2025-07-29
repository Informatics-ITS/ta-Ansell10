<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Facades\DB;


class UserProfileController extends Controller
{
    /* ───────────────────────────────────────────────
        |  UTILITIES
     ─────────────────────────────────────────────── */

    /**
     * Hitung BMR spesifik umur & gender
     * – Mifflin-St Jeor (1990)
     * – Henry 2005 (anak, remaja, lansia)
     */
    private function calculateBmr(float $w, float $h, int $age, string $g): float
    {
        // Henry untuk anak & remaja
        if ($age < 3) {
            return $g === 'male'
                ? 60.9 * $w - 54
                : 61.0 * $w - 51;
        }
        if ($age < 10) {
            return $g === 'male'
                ? 22.7 * $w + 495
                : 22.5 * $w + 499;
        }
        if ($age < 18) {
            return $g === 'male'
                ? 17.5 * $w + 651
                : 12.2 * $w + 746;
        }
        // Mifflin-St Jeor untuk dewasa 18-60
        if ($age <= 60) {
            return $g === 'male'
                ? (10 * $w) + (6.25 * $h) - (5 * $age) + 5
                : (10 * $w) + (6.25 * $h) - (5 * $age) - 161;
        }
        // Henry untuk lansia
        return $g === 'male'
            ? 8.0 * $w + 879
            : 7.18 * $w + 795;
    }

    /** Faktor aktivitas → TDEE */
    private function activityFactor(string $level): float
    {
        $map = [
            'sedentary'   => 1.2,
            'light'       => 1.375,
            'moderate'    => 1.55,
            'active'      => 1.725,
            'very active' => 1.9,
        ];
        return $map[$level] ?? 1.2;
    }

    /** Validasi input profil */
    private function validateInput(Request $r): array
    {
        return $r->validate([
            'name'           => 'required|string|max:255',
            'weight'         => 'required|numeric|min:1',
            'height'         => 'required|numeric|min:1',
            'age'            => 'required|integer|min:1',
            'gender'         => 'required|in:male,female',
            'activity_level' => 'required|in:sedentary,light,moderate,active,very active',
        ]);
    }

    private function detectDelimiter(string $filePath, array $candidates = [';', ',', '.']): string
    {
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $counts = [];

        foreach ($candidates as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        arsort($counts); // Sort dari yang paling banyak
        return array_key_first($counts); // Ambil delimiter terbanyak
    }


    /* ───────────────────────────────────────────────
    |  ENDPOINTS
     ─────────────────────────────────────────────── */

    /** GET /profiles */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json([], 401);
        }

        // Hanya ambil profil yang milik user ini
        $profiles = $user->profiles()->get();
        return response()->json($profiles);
    }

    /** POST /profiles */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Cek apakah pengguna memiliki role 2 (personal)
        if ($user->role == 2) {
            $existingProfile = $user->profiles()->first();
            if ($existingProfile) {
                return response()->json(['message' => 'Pengguna dengan role personal hanya dapat memiliki satu profil.'], 400);
            }
        }

        $data = $this->validateInput($request);
        $bmr  = $this->calculateBmr(
            $data['weight'],
            $data['height'],
            $data['age'],
            $data['gender']
        );
        $tdee = $bmr * $this->activityFactor($data['activity_level']);

        $profile = $user->profiles()->create($data + [
            'bmr'  => $bmr,
            'tdee' => $tdee,
        ]);

        return response()->json([
            'message' => 'Profil baru berhasil disimpan.',
            'data'    => $profile,
        ], 201);
    }

    /** PUT /profiles/{profile} */
    public function update(Request $request, UserProfile $profile)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }


        if ($profile->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $this->validateInput($request);
        $bmr  = $this->calculateBmr(
            $data['weight'],
            $data['height'],
            $data['age'],
            $data['gender']
        );
        $tdee = $bmr * $this->activityFactor($data['activity_level']);

        $profile->update($data + [
            'bmr'  => $bmr,
            'tdee' => $tdee,
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $profile,
        ]);
    }

    /** DELETE /profiles/{profile} */
    public function destroy(UserProfile $profile)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek apakah pengguna memiliki role 2 (personal) dan tidak dapat menghapus profil
        if ($user->role == 2) {
            return response()->json(['message' => 'Pengguna dengan role personal tidak dapat menghapus profil.'], 400);
        }

        if ($profile->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile->delete();

        return response()->json(['message' => 'Profil berhasil dihapus.']);
    }


    public function uploadCsv(Request $request)
    {
        try {
            Log::info('Start uploading CSV file.');

            // Validate file upload
            $request->validate([
                'csv' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            // Retrieve the uploaded file
            $file = $request->file('csv');

            if (!$file) {
                Log::error('No file uploaded.');
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            // Log file details
            Log::info('File uploaded: ' . $file->getClientOriginalName() . ' | Size: ' . $file->getSize() . ' bytes');

            // Store the CSV file
            $path = $file->storeAs('csv', time() . '_' . $file->getClientOriginalName());
            Log::info('File stored at: ' . $path);

            // Read and parse the CSV content
            $data = $this->readCsv($path); // Call readCsv to process the CSV file
            Log::info('CSV file processed with ' . count($data) . ' rows.');

            // Get the authenticated user
            $user = Auth::user();
            if (!$user) {
                Log::error('User not authenticated.');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Start database transaction
            DB::beginTransaction();
            Log::info('Database transaction started.');

            // Process each row in the CSV
            $processedRows = 0;
            $errors = [];

            foreach ($data as $rowIndex => $row) {
                try {
                    // Prepare profile data for store function
                    $profileData = [
                        'name' => $row['name'],
                        'weight' => floatval($row['weight']),
                        'height' => floatval($row['height']),
                        'age' => intval($row['age']),
                        'gender' => $row['gender'],
                        'activity_level' => $row['activity_level'],
                    ];

                    // Temporarily use the store function to handle CSV rows
                    $profileRequest = new Request($profileData);
                    $this->store($profileRequest);  // Call the store method to handle profile creation

                    $processedRows++;
                    Log::info('Profile created for: ' . $row['name']);
                } catch (\Exception $rowError) {
                    $errors[] = [
                        'row' => $rowIndex + 1,
                        'data' => $row,
                        'error' => $rowError->getMessage()
                    ];
                    Log::error('Error processing CSV row', [
                        'row' => $row,
                        'error' => $rowError->getMessage()
                    ]);
                }
            }

            // Commit transaction if all rows processed successfully
            DB::commit();
            Log::info('Database transaction committed.');

            return response()->json([
                'message' => 'CSV file uploaded and processed successfully',
                'path' => $path,
                'processed_rows' => $processedRows,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            // Rollback transaction in case of any error
            DB::rollBack();
            Log::error('Error uploading CSV: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function readCsv(string $path): array
    {
        try {
            Log::info('Reading CSV file: ' . $path);

            // Get the full path to the stored file
            $fullPath = Storage::path($path);
            Log::info('Full path: ' . $fullPath);

            // Auto detect delimiter
            $delimiter = $this->detectDelimiter($fullPath, [';', ',', '.']);
            Log::info('Detected delimiter: ' . $delimiter);

            // Create CSV reader using the League CSV package
            $csv = Reader::createFromPath($fullPath, 'r');
            $csv->setDelimiter($delimiter);
            $csv->setHeaderOffset(0);  // Set the header offset (first row is headers)


            // Use Statement to get records
            $stmt = Statement::create();
            $records = $stmt->process($csv);

            // Convert records to an array
            $recordsArray = iterator_to_array($records);
            Log::info('CSV records processed: ' . count($recordsArray) . ' rows.');

            return $recordsArray;
        } catch (\Exception $e) {
            Log::error('Error reading CSV file: ' . $e->getMessage(), ['path' => $path]);
            throw $e;
        }
    }
}

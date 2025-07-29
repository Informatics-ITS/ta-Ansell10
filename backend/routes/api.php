<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Controllers\ArticleController;

// Auth Controllers
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExerciseController;
// Profil & User Controllers
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodDiaryController;
use App\Http\Controllers\NotificationController;

// ==============================
// Public Routes
// ==============================

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/password/reset', [PasswordResetLinkController::class, 'sendResetLinkEmail']);
Route::post('/password/update', [NewPasswordController::class, 'reset']);
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'resend']);
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify']);

// XSRF token init
Route::get('/sanctum/csrf-cookie', '\Laravel\Sanctum\Http\Controllers\CsrfCookieController@show');

// Optional: test get all users
Route::get('/users', function () {
    return UserResource::collection(User::all());
});

// ==============================
// Protected Routes
// ==============================

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/me', [UserController::class, 'getSignedInUser']);

    Route::get('/dashboard/nutrition-data', [DashboardController::class, 'getNutritionData']);
    Route::get('/dashboard/activity-data', [DashboardController::class, 'getActivityData']);
    Route::get('/dashboard/calories-data', [DashboardController::class, 'getCaloriesData']);
    // Route::get('/dashboard/nutrition-recommendations', [DashboardController::class, 'getNutritionRecommendations']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Personalisasi Pengguna

    /* PROFIL KOLEKSI (hasMany) */
    Route::get('/profiles', [UserProfileController::class, 'index']);  // list semua profil user
    Route::post('/profiles', [UserProfileController::class, 'store']);  // tambah profil

    /* PROFIL INDIVIDU */
    Route::get('/profiles/{profile}', [UserProfileController::class, 'show']);   // detail profil tertentu
    Route::put('/profiles/{profile}', [UserProfileController::class, 'update']); // edit profil
    Route::delete('/profiles/{profile}', [UserProfileController::class, 'destroy']); // hapus profil (opsional)

    /* PROFIL BULK */
    Route::post('/profiles/upload-csv', [UserProfileController::class, 'uploadCsv']);  // Unggah CSV untuk memproses banyak profil

    // activities
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::get('/activities/{id}', [ActivityController::class, 'show']);
    Route::get('/exercises', [ExerciseController::class, 'index']);
    Route::delete('/activities/date/{date}', [ActivityController::class, 'deleteByDate']);
    Route::get('/activities/history', [ActivityController::class, 'history']);

    // food
    Route::prefix('food-diary')->group(function () {
        Route::get('/categories', [FoodDiaryController::class, 'getCategories']);
        Route::get('/food-items/all', [FoodDiaryController::class, 'getAllFoodItems']);
        Route::get('/food-items/{categoryId}', [FoodDiaryController::class, 'getFoodItemsByCategory']);
        Route::post('/entries', [FoodDiaryController::class, 'storeFoodDiaryEntry']);
        Route::get('/entries', [FoodDiaryController::class, 'getFoodDiaryEntries']);
        Route::get('/entries/date', [FoodDiaryController::class, 'getFoodDiaryByDate']);
        Route::get('/entries/{id}', [FoodDiaryController::class, 'getFoodDiaryEntry']);
        Route::put('/entries/{id}', [FoodDiaryController::class, 'updateFoodDiaryEntry']);
        Route::delete('/entries/{id}', [FoodDiaryController::class, 'deleteFoodDiaryEntry']);
    });

    // User Role Route
    Route::get('/user/role', [UserController::class, 'getUserRole']);  // Menambahkan route untuk mendapatkan role pengguna
});

//article
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/filter', [ArticleController::class, 'byActivityLevel']);

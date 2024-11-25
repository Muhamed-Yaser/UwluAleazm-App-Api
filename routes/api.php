<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Rest Password
Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('password/reset-with-otp', [AuthController::class, 'resetPasswordWithOtp']);

Route::middleware('auth:sanctum')->group(function () {
    //profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
});

//Rest Password - not used
Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);


// Send verification email link - not used
Route::middleware(['auth:sanctum'])->get('/email/verify', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.']);
    }

    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Verification link sent!']);
});

// Verify email - not used
Route::middleware(['auth:sanctum'])->get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json(['message' => 'Email verified successfully.']);
})->name('verification.verify');

// Check verification status - not used
Route::middleware(['auth:sanctum'])->get('/email/check-status', function (Request $request) {
    return response()->json(['email_verified' => $request->user()->hasVerifiedEmail()]);
});

//send notification command will do its job
//  Route::get('send-meeting-reminders', [NotificationController::class, 'sendMeetingReminders']);
Route::get('send-test-notification', function () {
    $deviceToken = 'YOUR_TEST_DEVICE_TOKEN';
    $title = 'Test Notification';
    $body = 'This is a test notification message';

    $notificationController = new NotificationController();
    $notificationController->sendNotification($deviceToken, $title, $body);

    return 'Notification sent!';
});

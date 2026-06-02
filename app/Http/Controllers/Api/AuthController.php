<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

         // مهم جدًا لإرسال verification email عند التسجيل اليدوي
        event(new Registered($user));
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
             'email_verified' => !is_null($user->email_verified_at),
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
             'email_verified' => !is_null($user->email_verified_at),
        ]);
    }



    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string',
            'university' => 'nullable|string',
            'major' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'total_credit_hours' => 'nullable|integer',
            'completed_credit_hours' => 'nullable|integer',
            'current_gpa' => 'nullable|numeric',
            'reminder_preferences' => 'nullable|array',
            'theme' => 'nullable|string',
            'language' => 'nullable|string',
            'current_semester' => 'nullable|integer',
            'onboarding_completed' => 'nullable|boolean',
            'avatar_url' => 'nullable|string',
        ]);

        if ($request->has('avatar_url') && str_starts_with($request->avatar_url, 'data:image')) {
            try {
                $imageData = $request->avatar_url;
                $format = explode('/', explode(':', substr($imageData, 0, strpos($imageData, ';')))[1])[1];
                $replace = substr($imageData, 0, strpos($imageData, ',') + 1);
                $image = str_replace($replace, '', $imageData);
                $image = str_replace(' ', '+', $image);
                $imageName = 'avatar_' . $user->id . '_' . time() . '.' . $format;

                // Delete old avatar if it exists and is not a default/external URL
                if ($user->avatar_url && str_contains($user->avatar_url, '/storage/avatars/')) {
                    $oldPath = str_replace('/storage/', 'public/', $user->avatar_url);
                    Storage::delete($oldPath);
                }

                Storage::disk('public')->put('avatars/' . $imageName, base64_decode($image));
                $data['avatar_url'] = Storage::disk('public')->url('avatars/' . $imageName);
            } catch (\Exception $e) {
                // Keep the existing avatar_url if parsing fails
                unset($data['avatar_url']);
            }
        }

        $user->update($data);

        // إضافة إشعار عند تحديث الملف الشخصي
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Profile Updated',
            'message' => 'Your settings and profile information have been saved successfully.',
            'type' => 'success',
            'target_route' => '/settings'
        ]);

        return response()->json([
            'message' => 'Profile Updated',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'email_verified' => !is_null($request->user()->email_verified_at),
        ]);
    }


public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json(['message' => 'Email not found'], 404);
    }

    $token = Str::random(60);

    DB::table('password_resets')->updateOrInsert(
        ['email' => $user->email],
        [
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]
    );

    // بدلاً من إرسال إيميل، نرجع token في JSON
    return response()->json([
        'message' => 'Reset token generated',
        'token' => $token,
        'email' => $user->email
    ]);
}

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // اختياري وأمني ممتاز مع Sanctum:
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }

     public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified.',
            ], 409);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent.',
        ]);
    }

    public function verifyEmail(Request $request, string $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(rtrim(config('app.frontend_url'), '/') . '/email-verified?status=invalid');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect(rtrim(config('app.frontend_url'), '/') . '/email-verified?status=success');
    }



}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max: 50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->character()->create([
                'name' => $user->name,
                'level' => 1,
                'experience' => 0,
                'strength' => 1,
                'intelligence' => 1,
                'discipline' => 1,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Акаунт успішно створено!',
            'user' => $user->load('character'),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Неправильна улектронна пошта фбо пароль.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Ви успішно увійшли!',
            'user' => $request->user()->load('character'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()->load('character'),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'character_name' => ['required', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $character = $user->character;

        $character->name = $validated['character_name'];

        if ($request->hasFile('avatar')) {
            $oldAvatar = $character->avatar;

            $character->avatar = $request->file('avatar')
                ->store('avatars', 'public');

            $character->save();

            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }
        } else {
            $character->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Профіль успішно оновлено!',
            'user' => $user->load('character'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Ви успішно вийшли з акаунта.',
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Пароль успішно змінено!',
        ]);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $character = $request->user()->character;

        if (!$character) {
            return response()->json([
                'success' => false,
                'message' => 'Персонажа не знайдено.',
            ], 404);
        }

        if ($character->avatar) {
            Storage::disk('public')->delete($character->avatar);

            $character->avatar = null;
            $character->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Аватар успішно видалено!',
            'user' => $request->user()->load('character'),
        ]);
    }
}

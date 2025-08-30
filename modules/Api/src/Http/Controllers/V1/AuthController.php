<?php

namespace Modules\Api\Http\Controllers\V1;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use OpenApi\Attributes as OA;
use Laravel\Sanctum\PersonalAccessToken as PAT;

final class AuthController extends BaseController
{
    #[OA\Post(
        path: '/api/v1/auth/register',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
            ])
        ),
        tags: ['Auth'],
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json(['id' => $user->id], 201);
    }

    #[OA\Post(
        path: '/api/v1/auth/login',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password'),
            ])
        ),
        tags: ['Auth'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = $user->currentAccessToken();

        $exists = PAT::whereKey($token->id)->exists();
        if ($exists === false) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($user);
    }

    #[OA\Post(
        path: '/api/v1/auth/logout',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [new OA\Response(response: 204, description: 'No Content')]
    )]
    public function logout(Request $request): Response
    {
        if ($token = $request->user()?->currentAccessToken()) {
            $token->delete();

            if (app()->environment('testing')) {
                cache()->flush();
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->noContent();
        }

        if ($raw = $request->bearerToken()) {
            [$maybeId, $secret] = array_pad(explode('|', $raw, 2), 2, null);
            $secret = $secret ?? $maybeId;

            PAT::where('token', hash('sha256', $secret))->delete();

            if (app()->environment('testing')) {
                cache()->flush();
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        return response()->noContent();
    }
}

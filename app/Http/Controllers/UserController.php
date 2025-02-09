<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(): JsonResponse
    {
        $userId = auth()->id();

        $user = User::findorFail($userId);

        return response()->json([
            'data' => $user,
            'success' => true,
            'message' => 'User retrieved successfully'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'nomor_whatsapp' => 'nullable|string|max:15|unique:users,nomor_whatsapp,' . $userId,
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:MALE,FEMALE',
            'biografi' => 'nullable|string',
        ]);

        $user = User::findorFail($userId);

        $user->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/users",
        summary: "Get all users",
        tags: ["Users"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of users")
        ]
    )]
    public function index()
    {
        $users = User::all();
        return response()->json(['data' => $users]);
    }

    #[OA\Post(
        path: "/api/users",
        summary: "Create a new user",
        tags: ["Users"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "nama", "password"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "johndoe"),
                    new OA\Property(property: "nama", type: "string", example: "John Doe"),
                    new OA\Property(property: "password", type: "string", example: "secret123")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "User created successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'nama' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'nama' => $validated['nama'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
    }

    #[OA\Get(
        path: "/api/users/{uuid}",
        summary: "Get user details",
        tags: ["Users"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User details"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function show($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        return response()->json(['data' => $user]);
    }

    #[OA\Put(
        path: "/api/users/{uuid}",
        summary: "Update a user",
        tags: ["Users"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "username", type: "string", example: "johndoe_updated"),
                    new OA\Property(property: "nama", type: "string", example: "John Doe Updated"),
                    new OA\Property(property: "password", type: "string", example: "newsecret123", description: "Leave empty if not changing password")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "User updated successfully"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function update(Request $request, $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'username' => ['sometimes', 'required', 'string', Rule::unique('users')->ignore($user->id)],
            'nama' => 'sometimes|required|string',
            'password' => 'nullable|string|min:6',
        ]);

        if (isset($validated['username'])) $user->username = $validated['username'];
        if (isset($validated['nama'])) $user->nama = $validated['nama'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully', 'data' => $user]);
    }

    #[OA\Delete(
        path: "/api/users/{uuid}",
        summary: "Delete a user",
        tags: ["Users"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User deleted successfully"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function destroy($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        
        // Prevent deleting the currently logged in user
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Cannot delete currently logged in user'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VehicleController extends Controller
{
    #[OA\Get(
        path: "/api/vehicles",
        summary: "Get all vehicles",
        tags: ["Vehicles"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of vehicles")
        ]
    )]
    public function index()
    {
        $vehicles = Vehicle::with('user')->get();
        return response()->json(['data' => $vehicles]);
    }

    #[OA\Post(
        path: "/api/vehicles",
        summary: "Create a new vehicle",
        tags: ["Vehicles"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["plate_number"],
                    properties: [
                        new OA\Property(property: "plate_number", type: "string", example: "B 1234 CD"),
                        new OA\Property(property: "user_uuid", type: "string", example: "123e4567-e89b-12d3-a456-426614174000", description: "UUID of the assigned user (pengguna)")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Vehicle created successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'user_uuid' => 'nullable|exists:users,uuid',
        ]);

        $vehicle = Vehicle::create([
            'plate_number' => $validated['plate_number'],
            'user_uuid' => $validated['user_uuid'] ?? null,
        ]);

        return response()->json(['message' => 'Vehicle created successfully', 'data' => $vehicle], 201);
    }

    #[OA\Get(
        path: "/api/vehicles/{uuid}",
        summary: "Get vehicle details",
        tags: ["Vehicles"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Vehicle details"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function show($uuid)
    {
        $vehicle = Vehicle::where('uuid', $uuid)->with('user')->firstOrFail();
        return response()->json(['data' => $vehicle]);
    }

    #[OA\Put(
        path: "/api/vehicles/{uuid}",
        summary: "Update a vehicle",
        tags: ["Vehicles"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "plate_number", type: "string", example: "B 9999 XX"),
                        new OA\Property(property: "user_uuid", type: "string", example: "123e4567-e89b-12d3-a456-426614174000")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Vehicle updated successfully"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function update(Request $request, $uuid)
    {
        $vehicle = Vehicle::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'plate_number' => 'sometimes|required|string|unique:vehicles,plate_number,' . $vehicle->id,
            'user_uuid' => 'nullable|exists:users,uuid',
        ]);

        if (isset($validated['plate_number'])) $vehicle->plate_number = $validated['plate_number'];
        if (array_key_exists('user_uuid', $validated)) $vehicle->user_uuid = $validated['user_uuid'];

        $vehicle->save();

        return response()->json(['message' => 'Vehicle updated successfully', 'data' => $vehicle]);
    }

    #[OA\Delete(
        path: "/api/vehicles/{uuid}",
        summary: "Delete a vehicle",
        tags: ["Vehicles"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Vehicle deleted successfully"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function destroy($uuid)
    {
        $vehicle = Vehicle::where('uuid', $uuid)->firstOrFail();
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }
}

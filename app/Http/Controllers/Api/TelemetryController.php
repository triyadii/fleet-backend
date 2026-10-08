<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrackingLog;
use App\Models\Vehicle;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API Documentation for Fleet Live Tracking system",
    title: "Fleet Tracking API"
)]
#[OA\Server(
    url: "https://dicotriyadi.site/fleet",
    description: "Production API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer"
)]
class TelemetryController extends Controller
{
    #[OA\Post(
        path: "/api/telemetry",
        summary: "Submit vehicle telemetry data",
        tags: ["Telemetry"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["vehicle_id", "lat", "lng", "speed", "timestamp"],
                    properties: [
                        new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                        new OA\Property(property: "lat", type: "number", format: "float", example: -6.200000),
                        new OA\Property(property: "lng", type: "number", format: "float", example: 106.816666),
                        new OA\Property(property: "speed", type: "number", format: "float", example: 60.5),
                        new OA\Property(property: "timestamp", type: "string", format: "date-time", example: "2026-10-08T10:30:00Z"),
                        new OA\Property(property: "snapshot", type: "string", format: "binary", description: "Optional image snapshot")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Data received successfully"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'speed' => 'required|numeric',
            'timestamp' => 'required|date',
            'snapshot' => 'nullable|image|max:2048'
        ]);

        $snapshotPath = null;
        if ($request->hasFile('snapshot')) {
            $snapshotPath = $request->file('snapshot')->store('snapshots', 'public');
        }

        $log = TrackingLog::create([
            'vehicle_id' => $validated['vehicle_id'],
            'latitude' => $validated['lat'],
            'longitude' => $validated['lng'],
            'speed' => $validated['speed'],
            'snapshot_path' => $snapshotPath,
            'recorded_at' => $validated['timestamp']
        ]);

        return response()->json([
            'message' => 'Data received successfully',
            'data' => $log
        ], 201);
    }

    #[OA\Get(
        path: "/api/telemetry/latest",
        summary: "Get latest fleet positions",
        tags: ["Telemetry"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "A list of the latest vehicle positions")
        ]
    )]
    public function latest()
    {
        $vehicles = Vehicle::with(['trackingLogs' => function ($query) {
            $query->latest('recorded_at')->take(1);
        }])->get();

        $latestPositions = $vehicles->map(function ($vehicle) {
            $latestLog = $vehicle->trackingLogs->first();
            return [
                'vehicle_id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'lat' => $latestLog ? (float) $latestLog->latitude : null,
                'lng' => $latestLog ? (float) $latestLog->longitude : null,
                'speed' => $latestLog ? (float) $latestLog->speed : null,
                'timestamp' => $latestLog ? $latestLog->recorded_at : null,
                'snapshot_url' => ($latestLog && $latestLog->snapshot_path) ? asset('storage/' . $latestLog->snapshot_path) : null,
            ];
        })->filter(function($item) {
            return $item['lat'] !== null;
        })->values();

        return response()->json([
            'data' => $latestPositions
        ]);
    }
    #[OA\Get(
        path: "/api/telemetry/{vehicle_id}",
        summary: "Get telemetry history for a specific vehicle",
        tags: ["Telemetry"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "vehicle_id",
                in: "path",
                required: true,
                description: "ID of the vehicle",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "A list of tracking logs for the vehicle"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function history($vehicle_id)
    {
        $vehicle = Vehicle::findOrFail($vehicle_id);
        
        $logs = TrackingLog::where('vehicle_id', $vehicle->id)
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'lat' => (float) $log->latitude,
                    'lng' => (float) $log->longitude,
                    'speed' => (float) $log->speed,
                    'timestamp' => $log->recorded_at,
                    'snapshot_url' => $log->snapshot_path ? asset('storage/' . $log->snapshot_path) : null,
                ];
            });
            
        return response()->json([
            'data' => [
                'vehicle_id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'logs' => $logs
            ]
        ]);
    }
}

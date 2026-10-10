<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'latitude', 'longitude', 'speed', 'snapshot_path', 'recorded_at',
        'fokus', 'mengantuk', 'berisik', 'tidak_ditempat'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'fokus' => 'boolean',
        'mengantuk' => 'boolean',
        'berisik' => 'boolean',
        'tidak_ditempat' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

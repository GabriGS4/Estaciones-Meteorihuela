<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrentObservation extends Model
{
     protected $fillable = [
        'station_id',
        'obs_time_utc',
        'obs_time_local',
        'software_type',
        'solar_radiation',
        'uv',
        'winddir',
        'humidity',
        'temp',
        'heat_index',
        'dewpt',
        'wind_chill',
        'wind_speed',
        'wind_gust',
        'pressure',
        'precip_rate',
        'precip_total',
        'qc_status',
    ];

    protected $casts = [
        'obs_time_utc' => 'datetime',
        'obs_time_local' => 'datetime',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}

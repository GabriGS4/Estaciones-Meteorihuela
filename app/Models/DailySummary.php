<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    
    protected $fillable = [
        'station_id',
        'obs_time_utc',
        'obs_time_local',
        'solar_radiation_high',
        'uv_high',
        'winddir_avg',
        'humidity_high',
        'humidity_low',
        'humidity_avg',
        'temp_high',
        'temp_low',
        'temp_avg',
        'windspeed_high',
        'windspeed_low',
        'windspeed_avg',
        'windgust_high',
        'windgust_low',
        'windgust_avg',
        'dewpt_high',
        'dewpt_low',
        'dewpt_avg',
        'windchill_high',
        'windchill_low',
        'windchill_avg',
        'heatindex_high',
        'heatindex_low',
        'heatindex_avg',
        'pressure_max',
        'pressure_min',
        'pressure_trend',
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

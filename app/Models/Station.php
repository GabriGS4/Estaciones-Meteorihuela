<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{

    protected $fillable = [
        'station_id',
        'name',
        'country',
        'lat',
        'lon',
        'elevation',
        'use_ecowitt',
    ];

    protected $hidden = ['api_key'];


    // Relaciones
    public function currentObservations()
    {
        return $this->hasMany(CurrentObservation::class);
    }

    /**
     * Última observación actual (relationship conveniente)
     */
    public function latestCurrentObservation()
    {
        // latestOfMany usa la columna obs_time_utc para elegir la más reciente
        return $this->hasOne(CurrentObservation::class)->latestOfMany('obs_time_utc');
    }

    public function dailySummaries()
    {
        return $this->hasMany(DailySummary::class);
    }

    /**
     * Último resumen diario (relationship conveniente)
     */
    public function latestDailySummary()
    {
        return $this->hasOne(DailySummary::class)->latestOfMany('obs_time_utc');
    }

    public function hourlyObservations()
    {
        return $this->hasMany(HourlyObservation::class);
    }
}

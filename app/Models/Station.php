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
    ];

    protected $hidden = ['api_key'];


    // Relaciones
    public function currentObservations()
    {
        return $this->hasMany(CurrentObservation::class);
    }

    public function dailySummaries()
    {
        return $this->hasMany(DailySummary::class);
    }

    public function hourlyObservations()
    {
        return $this->hasMany(HourlyObservation::class);
    }
}

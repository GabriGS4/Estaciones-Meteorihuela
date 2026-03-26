<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo_url', 'link_url', 'active'];

    public function stories()
    {
        return $this->hasMany(SponsorStory::class);
    }

    public function interactions()
    {
        return $this->hasMany(SponsorInteraction::class);
    }
}

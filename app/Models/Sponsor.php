<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo_url', 'logo_path', 'link_url', 'active', 'order'];

    public function getLogoUrlAttribute($value): ?string
    {
        if ($this->logo_path) {
            return asset(Storage::url($this->logo_path));
        }
        return $value;
    }

    public function stories()
    {
        return $this->hasMany(SponsorStory::class);
    }

    public function interactions()
    {
        return $this->hasMany(SponsorInteraction::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}

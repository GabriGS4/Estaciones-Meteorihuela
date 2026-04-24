<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SponsorStory extends Model
{
    use HasFactory;

    protected $fillable = ['sponsor_id', 'media_url', 'media_path', 'media_type', 'active', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'active'     => 'boolean',
    ];

    public function getMediaUrlAttribute($value): ?string
    {
        if ($this->media_path) {
            return asset(Storage::url($this->media_path));
        }
        return $value;
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function interactions()
    {
        return $this->hasMany(SponsorInteraction::class, 'sponsor_story_id');
    }
}

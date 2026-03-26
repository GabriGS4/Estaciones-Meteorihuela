<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sponsor;
use App\Models\SponsorInteraction;

class SponsorStory extends Model
{
    use HasFactory;

    protected $fillable = ['sponsor_id', 'media_url', 'media_type', 'active', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function interactions()
    {
        return $this->hasMany(SponsorInteraction::class, 'sponsor_story_id');
    }
}

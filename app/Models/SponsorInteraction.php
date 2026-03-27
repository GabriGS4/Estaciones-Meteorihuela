<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorInteraction extends Model
{
    use HasFactory;

    protected $fillable = ['sponsor_id', 'sponsor_story_id', 'interaction_type', 'ip_address', 'user_agent', 'dispositivo_id'];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function story()
    {
        return $this->belongsTo(SponsorStory::class, 'sponsor_story_id');
    }
}

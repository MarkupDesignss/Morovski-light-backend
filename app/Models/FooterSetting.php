<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    protected $fillable = [
        'tagline',
        'description',
        'copyright_text',
        'youtube_url',
        'instagram_url',
        'twitter_url',
        'email',
        'gst_in',
        'contact_phone',
        'contact_address',
    ];

    public static function getSettings()
    {
        return self::first() ?? self::create([
            'tagline' => 'The Future Of Competitive Gaming. Forging Legends, One Tournament At A Time.',
            'copyright_text' => '© 2025 Quantum Esports Collective. All Rights Reserved.',
            'description' => 'We Aim To Cultivate Excellence In Esports By Supporting Athletes, Teams, And Organizations, Providing The Resources And Infrastructure Needed To Achieve Their Fullest Potential.',
            'email' => 'info@qecgg.com',
        ]);
    }
}

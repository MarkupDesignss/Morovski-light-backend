<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    use HasFactory;

    protected $table = 'contact_settings';
    
    protected $guarded =[];

    /**
     * Get settings grouped by type
     */
    public static function getSettings()
    {
        $settings = self::all();
        
        $grouped = [];
        foreach ($settings as $setting) {
            $grouped[$setting->type] = $setting;
        }
        
        // Ensure all types exist
        $types = ['email', 'call_us', 'business_hours', 'visit_us'];
        foreach ($types as $type) {
            if (!isset($grouped[$type])) {
                $grouped[$type] = new self([
                    'type' => $type,
                    'title' => '',
                    'short_description' => '',
                ]);
            }
        }
        
        return (object) $grouped;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationMetric extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider',
        'source_locale',
        'target_locale',
        'characters_count',
        'words_count',
        'from_cache',
        'created_at',
    ];

    protected $casts = [
        'from_cache' => 'boolean',
        'created_at' => 'datetime',
    ];
}

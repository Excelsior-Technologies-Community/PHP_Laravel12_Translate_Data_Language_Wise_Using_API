<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GlossaryTerm extends Model
{
    protected $fillable = [
        'term',
        'replacement',
        'target_locale',
        'do_not_translate',
        'description',
        'is_active',
    ];

    protected $casts = [
        'do_not_translate' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where(function ($q) use ($locale) {
            $q->where('target_locale', 'all')
              ->orWhere('target_locale', $locale);
        });
    }
}

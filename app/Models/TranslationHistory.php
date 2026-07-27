<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationHistory extends Model
{

    protected $fillable = [

        'post_id',
        'source_locale',
        'target_locale',
        'original_text',
        'translated_text'

    ];


    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

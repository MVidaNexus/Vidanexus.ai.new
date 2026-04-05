<?php

namespace Modules\ArticleWriter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ArticleWriter\Database\Factories\ArticleHistoryFactory;

class ArticleHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'topic', 'title', 'meta_description', 'content', 
        'seo_data', 'status', 'provider', 'model', 'language', 'word_count'
    ];

    protected $casts = [
        'seo_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}

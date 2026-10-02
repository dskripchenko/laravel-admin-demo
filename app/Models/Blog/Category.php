<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'blog_categories';

    protected $guarded = ['id'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }
}

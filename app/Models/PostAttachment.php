<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAttachment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const UPDATED_AT = null;
    const CREATED_AT = null;


    protected $table = 'post_attachments';

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'id');
    }
}

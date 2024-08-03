<?php

namespace App\Models;

use App\Models\PostAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    const UPDATED_AT = null;


    protected $guarded = ['id'];



    public function postAttachments(): HasMany
    {
        return $this->hasMany(PostAttachment::class, 'post_id');
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTerm extends Model
{
    protected $fillable = [
        'term',
        'group_id',
        'category',
        'frequency',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

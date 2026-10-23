<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
class Category extends Model
{
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class);
    }
}

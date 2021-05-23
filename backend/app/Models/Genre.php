<?php

namespace App\Models;

use App\ModelFilters\GenreFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use EloquentFilter\Filterable;

class Genre extends Model
{
    use SoftDeletes, \App\Models\Traits\Uuid, Filterable;

    protected $fillable = ['name', 'is_active'];
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function categories() {
        return $this->belongsToMany(Category::class)->withoutTrashed();
    }

    public function modelFilter() {
        return $this->provideFilter(GenreFilter::class);
    }
}

<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store_categories';

    protected $fillable = [
        'enable',
        'pos',
        'code',
        'slug',
        'title',
        'description',
        'image',
        'color',
        'icon',
        'parent_id',
        'meta_title',
        'meta_description',
        'manager',
    ];

    protected $casts = [
        'enable' => 'boolean',
        'pos' => 'integer',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function parent()
    {
        return $this->belongsTo(StoreCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(StoreCategory::class, 'parent_id')
            ->where('enable', true)
            ->orderBy('pos');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function products()
    {
        return $this->hasMany(StoreProduct::class, 'category_id')
            ->where('enable', true);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enable', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubCategories($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('code', 'like', '%' . $keyword . '%')
              ->orWhere('description', 'like', '%' . $keyword . '%');
        });
    }

    public function getPathAttribute()
    {
        $path = collect();
        $category = $this;

        while ($category) {
            $path->prepend($category->title);
            $category = $category->parent;
        }

        return $path->implode(' > ');
    }

    public function getDepthAttribute()
    {
        $depth = 0;
        $category = $this->parent;

        while ($category) {
            $depth++;
            $category = $category->parent;
        }

        return $depth;
    }

    public function getChildrenCountAttribute()
    {
        return $this->children()->count();
    }

    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plug4MarketCategory extends Model
{
    protected $table = 'plug4market_categories';

    protected $fillable = [
        'external_id',
        'name',
        'description',
        'parent_id',
        'external_parent_id',
        'level',
        'path',
        'is_active',
        'sincronizado',
        'ultima_sincronizacao',
        'raw_data'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sincronizado' => 'boolean',
        'ultima_sincronizacao' => 'datetime',
        'raw_data' => 'array'
    ];

    public function parent()
    {
        return $this->belongsTo(Plug4MarketCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Plug4MarketCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Plug4MarketProduct::class, 'categoria_id', 'external_id');
    }

    public function getStatusTextAttribute()
    {
        if (!$this->is_active) {
            return 'Inativa';
        }
        
        if (!$this->sincronizado) {
            return 'Não Sincronizada';
        }
        
        return 'Ativa';
    }

    public function getFullPathAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_path . ' > ' . $this->name;
        }
        
        return $this->name;
    }

    public function scopeAtivas($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSincronizadas($query)
    {
        return $query->where('sincronizado', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChildren($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }
} 
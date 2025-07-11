<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plug4MarketProduct extends Model
{
    protected $table = 'plug4market_products';

    protected $fillable = [
        'external_id',
        'codigo',
        'descricao',
        'nome',
        'ncm',
        'cfop',
        'unidade',
        'valor_unitario',
        'aliquota_icms',
        'aliquota_pis',
        'aliquota_cofins',
        'marca',
        'categoria_id',
        'categoria_nome',
        'largura',
        'altura',
        'comprimento',
        'peso',
        'estoque',
        'origem',
        'ean',
        'modelo',
        'garantia',
        'imagens',
        'metafields',
        'sales_channels',
        'ativo',
        'sincronizado',
        'ultima_sincronizacao'
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'aliquota_icms' => 'decimal:2',
        'aliquota_pis' => 'decimal:2',
        'aliquota_cofins' => 'decimal:2',
        'largura' => 'integer',
        'altura' => 'integer',
        'comprimento' => 'integer',
        'peso' => 'integer',
        'estoque' => 'integer',
        'garantia' => 'integer',
        'imagens' => 'array',
        'metafields' => 'array',
        'sales_channels' => 'array',
        'ativo' => 'boolean',
        'sincronizado' => 'boolean',
        'ultima_sincronizacao' => 'datetime'
    ];

    public function orderItems()
    {
        return $this->hasMany(Plug4MarketOrderItem::class, 'product_id', 'external_id');
    }

    public function category()
    {
        return $this->belongsTo(Plug4MarketCategory::class, 'categoria_id', 'external_id');
    }

    public function getStatusTextAttribute()
    {
        if (!$this->ativo) {
            return 'Inativo';
        }
        
        if (!$this->sincronizado) {
            return 'Não Sincronizado';
        }
        
        return 'Ativo';
    }

    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
    }

    public function getDimensionsAttribute()
    {
        return "{$this->largura} x {$this->altura} x {$this->comprimento} cm";
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeSincronizados($query)
    {
        return $query->where('sincronizado', true);
    }
} 
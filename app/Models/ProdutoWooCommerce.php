<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoWooCommerce extends Model
{

    protected $table = 'produto_woocommerce';

    protected $fillable = [
        'produto_id',
        'woocommerce_id',
        'woocommerce_valor',
        'woocommerce_link',
        'woocommerce_status',
        'empresa_id'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}

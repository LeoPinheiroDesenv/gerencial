<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Produto;

class SkuDup implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $empresa_id = null;
    protected $id = null;
    public function __construct($empresa_id, $id)
    {
        $this->empresa_id = $empresa_id;
        $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($value == '') return true;
        $produto = Produto::where('sku', $value)->where('empresa_id', $this->empresa_id)->first();

        if($produto == null || $produto->id == $this->id){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Já existe um produto com este SKU.';
    }
}

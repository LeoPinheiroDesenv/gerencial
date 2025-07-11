<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;

class TesteController extends Controller
{

    public function index(Request $request){
        $data = Produto::where('unidade_compra', 'LITRO')->get();
        //Produto::where('unidade_compra', 'LITRO')->update(['unidade_compra' => 'LT']);
        dd($data);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\MercadoLivreUtil;
use App\Models\MercadoLivreConfig;

class MercadoLivreController extends Controller
{
    protected $util;
    public function __construct(MercadoLivreUtil $util)
    {
        $this->util = $util;
    }
    
    public function notification(Request $request){
        //webhook mercado livre
        $config = MercadoLivreConfig::where('user_id', $request->user_id)
        ->first();
        $retorno = $this->util->getNotification($config, $request);
        // file_put_contents('ml'.rand(0,123123).'.txt', $retorno);
    }
}

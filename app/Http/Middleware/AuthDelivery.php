<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DeliveryConfig;
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type,      Accept");
// header("Content-Type: application/json");
class AuthDelivery
{
    
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');

        $config = DeliveryConfig::
        where('api_token', $token)
        ->first();
        
        if($config == null){
            return response()->json($token, 403);
        }

        if(!$config->status){
            return response()->json("loja desativada!", 401);
        }

        $request->merge(['empresa_id' => $config->empresa_id]);
        return $next($request);
    }
}

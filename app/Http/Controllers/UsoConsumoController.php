<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsoConsumo;
use App\Models\ItemUsoConsumo;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Helpers\StockMove;
use App\Models\ConfigNota;
use App\Prints\ComprovanteConsumo;

class UsoConsumoController extends Controller
{
    protected $empresa_id = null;

    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if(!$value){
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function index(Request $request){
        $funcionario_id = $request->funcionario_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = UsoConsumo::
        where('empresa_id', $this->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when($funcionario_id, function ($query) use ($funcionario_id) {
            return $query->where('funcionario_id', $funcionario_id);
        })
        ->orderBy('id', 'desc')
        ->paginate(30);

        $funcionarios = Funcionario::
        where('empresa_id', $this->empresa_id)
        ->get();

        return view('uso_consumo.index', compact('data', 'funcionario_id', 'start_date', 'end_date', 'funcionarios'))
        ->with('title', 'Uso e consumo');
    }

    public function create(){

        $funcionarios = Funcionario::
        where('empresa_id', $this->empresa_id)
        ->get();

        return view('uso_consumo.register', compact('funcionarios'))
        ->with('title', 'Uso e consumo');
    }

    public function edit($id){

        $item = UsoConsumo::findOrFail($id);
        $funcionarios = Funcionario::
        where('empresa_id', $this->empresa_id)
        ->get();

        return view('uso_consumo.register', compact('funcionarios', 'item'))
        ->with('title', 'Uso e consumo');
    }

    public function store(Request $request){

        try{
            $soma = (float)__replace($request->soma_produtos);
            $desconto = (float)__replace($request->desconto);
            $acrescimo = (float)__replace($request->acrescimo);
            $item = UsoConsumo::create([
                'empresa_id' => $this->empresa_id,
                'funcionario_id' => $request->funcionario_id,
                'observacao' => $request->observacao ?? '',
                'valor_total' => $soma + $acrescimo - $desconto,
                'desconto' => $desconto,
                'acrescimo' => $acrescimo
            ]);

            $stockMove = new StockMove();
            for($i=0; $i<sizeof($request->produto_id); $i++){
                ItemUsoConsumo::create([
                    'uso_consumo_id' => $item->id,
                    'produto_id' => $request->produto_id[$i],
                    'quantidade' => __replace($request->quantidade[$i]),
                    'valor_unitario' => __replace($request->valor_unitario[$i]),
                    'sub_total' => __replace($request->sub_total[$i])
                ]);

                $produto = Produto::findOrFail($request->produto_id[$i]);
                if($produto->gerenciar_estoque){
                    $stockMove->downStock($produto->id, __replace($request->quantidade[$i]));
                }

            }

            session()->flash('mensagem_sucesso', "Cadastrado com sucesso!");

        }catch(\Exception $e){
            session()->flash('mensagem_erro', "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('uso-consumo.index');
    }

    public function update(Request $request, $id){

        try{
            $soma = (float)__replace($request->soma_produtos);
            $desconto = (float)__replace($request->desconto);
            $acrescimo = (float)__replace($request->acrescimo);
            $item = UsoConsumo::findOrFail($id);
            $item->update([
                'empresa_id' => $this->empresa_id,
                'funcionario_id' => $request->funcionario_id,
                'observacao' => $request->observacao ?? '',
                'valor_total' => $soma + $acrescimo - $desconto,
                'desconto' => $desconto,
                'acrescimo' => $acrescimo
            ]);

            $stockMove = new StockMove();
            foreach($item->itens as $i){
                if($i->produto->gerenciar_estoque){
                    $stockMove->pluStock($i->produto->id, $i->quantidade);
                }
            }
            $item->itens()->delete();
            for($i=0; $i<sizeof($request->produto_id); $i++){
                ItemUsoConsumo::create([
                    'uso_consumo_id' => $item->id,
                    'produto_id' => $request->produto_id[$i],
                    'quantidade' => __replace($request->quantidade[$i]),
                    'valor_unitario' => __replace($request->valor_unitario[$i]),
                    'sub_total' => __replace($request->sub_total[$i])
                ]);

                $produto = Produto::findOrFail($request->produto_id[$i]);
                if($produto->gerenciar_estoque){
                    $stockMove->downStock($produto->id, __replace($request->quantidade[$i]));
                }
            }

            session()->flash('mensagem_sucesso', "Atualizado com sucesso!");

        }catch(\Exception $e){
            session()->flash('mensagem_erro', "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('uso-consumo.index');
    }

    public function destroy($id){
        try{

            $item = UsoConsumo::findOrFail($id);
            $stockMove = new StockMove();
            foreach($item->itens as $i){
                if($i->produto->gerenciar_estoque){
                    $stockMove->pluStock($i->produto->id, $i->quantidade);
                }
            }
            $item->itens()->delete();
            $item->delete();

            session()->flash("mensagem_sucesso", "Registro removido!");

        }catch(\Exception $e){
            session()->flash('mensagem_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('uso-consumo.index');
    }

    public function print($id){
        $item = UsoConsumo::findOrFail($id);
        if(valida_objeto($item)){

            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            if($config->logo){
                $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
            }else{
                $logo = null;
            }
            
            $cupom = new ComprovanteConsumo($item);
            $cupom->monta();
            $pdf = $cupom->render();
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        }else{
            return redirect('/403');
        }
    }

}

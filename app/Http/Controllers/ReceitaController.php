<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receita;
use App\Models\Produto;
use App\Models\ItemReceita;

class ReceitaController extends Controller
{

	public function __construct(){
		$this->middleware(function ($request, $next) {
			$value = session('user_logged');
			if(!$value){
				return redirect("/login");
			}
			return $next($request);
		});
	}

	public function save(Request $request){

		$request->merge(['rendimento' => $request->rendimento > 0 ?
			$request->rendimento : 'a'
		]);

		if(strlen($request->pedacos)){
			$request->merge(['pedacos' => $request->pedacos > 0 ?
				$request->pedacos : 'a'
			]);
		}

		$this->_validate($request);

		
		$result = Receita::create([
			'produto_id' => $request->produto_id,
			'descricao' => $request->descricao,
			'rendimento' => $request->rendimento,
			'tempo_preparo' => $request->tempo_preparo ?? 0,
			'valor_custo' => 0,
			'pizza' => $request->pedacos ? true : false,
			'pedacos' => $request->pedacos ?? 0

		]);

		if($result){
			session()->flash('color', 'blue');
			session()->flash("message", "Cadastrado com sucesso!");
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro ao cadastrar!');
		}

		return redirect("/produtos/receita/$request->produto_id");
	}

	public function update(Request $request){
		$this->_validate($request);
		$receita = Receita::
		where('id', $request->receita_id)
		->first();

		$receita->descricao = $request->descricao;
		$receita->rendimento = $request->rendimento;
		$receita->tempo_preparo = $request->tempo_preparo ?? 0;
		$receita->pizza = $request->pedacos ? true: false;
		$receita->pedacos = $request->pedacos;

		$result = $receita->save();

		if($result){
			session()->flash('color', 'blue');
			session()->flash("message", "Atualizado com sucesso!");
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro ao atualizar!');
		}

		return redirect("/produtos/receita/" . $receita->produto->id);
	}

	public function saveItem(Request $request){
		$this->_validateItem($request);
		$produto = $request->input('produto');

		try{
			$itemReceita = ItemReceita::create([
				'receita_id' => $request->receita_id,
				'produto_id' => $produto,
				'quantidade' => str_replace(",", ".", $request->quantidade),
				'medida' => $request->medida
			]);

			$produto = $itemReceita->produto;
			$produto = $itemReceita->receita->produto;

			$totalCusto = 0;

			foreach($produto->receita->itens as $i){
				$totalCusto += $i->produto->valor_compra*$i->quantidade;
			}
			$produto->valor_compra = $totalCusto;
			$produto->save();

			session()->flash("mensagem_sucesso", "Cadastrado com sucesso!");
		}catch(\Exception $e){
			session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
		}

		return redirect()->back();
	}

	private function _validate(Request $request){
		$rules = [
			'rendimento' => 'required|numeric',
			'tempo_preparo' => '',
			'pedacos' => $request->pedacos ? 'numeric' : ''

		];

		$messages = [
			'rendimento.required' => 'O campo redimento é obrigatório.',
			'rendimento.numeric' => 'Digite um valor maior que 0.',
			'tempo_preparo.required' => 'O campo tempo de preparo é obrigatório.',
			'pedacos.numeric' => 'Informe um valor maior que 0.'

		];

		$this->validate($request, $rules, $messages);
	}

	private function _validateItem(Request $request){
		$rules = [
			'produto' => 'required',
			'quantidade' => 'required',
		];

		$messages = [
			'produto.required' => 'O campo produto é obrigatório.',
			'produto.min' => 'Clique sobre o produto desejado.',
			'quantidade.required' => 'O campo quantidade é obrigatório.',
		];

		$this->validate($request, $rules, $messages);
	}

	public function deleteItem($id){
		$item = ItemReceita
		::where('id', $id)
		->first();
		$produto = $item->receita->produto;

		try{
			$item->delete();

			$totalCusto = 0;

			foreach($produto->receita->itens as $i){
				$totalCusto += $i->produto->valor_compra*$i->quantidade;
			}
			$produto->valor_compra = $totalCusto;
			$produto->save();

			session()->flash('mensagem_sucesso', 'Item removido!');
		}catch(\Exception $e){
			session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
		}
		return redirect()->back();

	}
}

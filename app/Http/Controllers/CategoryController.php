<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\CategoriaProdutoDelivery;

class CategoryController extends Controller
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

    public function index(Request $request){

        $categorias = Categoria::
        where('empresa_id', $request->empresa_id)
        ->get();

        return view('categorias/list')
        ->with('categorias', $categorias)
        ->with('title', 'Categorias');
    }

    public function new(){
        return view('categorias/register')
        ->with('categoriaJs', true)
        ->with('title', 'Cadastrar Categoria');
    }

    public function save(Request $request){

        $category = new Categoria();
        $this->_validate($request);

        $result = $category->create($request->all());

        $atribuir_delivery = $request->atribuir_delivery;
        $msgSucesso = "Categoria cadastrada com sucesso";
        if($atribuir_delivery){

            $this->_validateDelivery($request);
            $file = $request->file('file');

            $extensao = $file->getClientOriginalExtension();
            $nomeImagem = md5($file->getClientOriginalName()).".".$extensao;
            $upload = $file->move(public_path('imagens_categorias'), $nomeImagem);

            if(!$upload){

                session()->flash('mensagem_sucesso', 'Erro ao realizar upload da imagem.');
            }else{

                $result = CategoriaProdutoDelivery::create(
                    [
                        'nome' => $request->nome,
                        'descricao' => $request->descricao,
                        'path' => $nomeImagem,
                        'empresa_id' => $request->empresa_id
                    ]
                );
                if($result){
                    $msgSucesso = "Categoria cadastrada e atribuida ao delivery com sucesso";
                }
            }

        }

        if($result){

            session()->flash("mensagem_sucesso", $msgSucesso);
        }else{
            session()->flash('mensagem_erro', 'Erro ao cadastrar categoria.');
        }

        return redirect('/categorias');
    }

    public function tributacao($id){
        $categoria = Categoria::findOrFail($id);
        if(valida_objeto($categoria)){
            return view('categorias/tributacao')
            ->with('categoria', $categoria)
            ->with('title', 'Tributação por Categoria');
        }else{
            return redirect('/403');
        }

    }

    public function edit($id){
        $categoria = new Categoria(); 

        $resp = $categoria
        ->where('id', $id)->first();  

        if(valida_objeto($resp)){
            return view('categorias/register')
            ->with('categoria', $resp)
            ->with('title', 'Editar Categoria');
        }else{
            return redirect('/403');
        }

    }

    public function update(Request $request){
        $categoria = new Categoria();

        $id = $request->input('id');
        $resp = $categoria
        ->where('id', $id)->first(); 

        $this->_validate($request);

        $resp->nome = $request->input('nome');

        $result = $resp->save();
        if($result){
            session()->flash('mensagem_sucesso', 'Categoria editada com sucesso!');
        }else{
            session()->flash('mensagem_erro', 'Erro ao editar categoria!');
        }

        return redirect('/categorias'); 
    }

    public function delete($id){
        try{
            $categoria = Categoria
            ::where('id', $id)
            ->first();

            // if(sizeof($categoria->produtos) > 0){
            //     session()->flash('mensagem_erro', 'Esta categoria possui produto(s), não é possível remover!!');
            //     return redirect('/categorias');
            // }
            if(valida_objeto($categoria)){
                if($categoria->delete()){
                    session()->flash('mensagem_sucesso', 'Registro removido!');
                }else{

                    session()->flash('mensagem_erro', 'Erro!');
                }
                return redirect('/categorias');
            }else{
                return redirect('403');
            }
        }catch(\Exception $e){
            return view('errors.sql')
            ->with('title', 'Erro ao deletar categoria')
            ->with('motivo', $e->getMessage());
        }
    }

    private function _validate(Request $request){
        $rules = [
            'nome' => 'required|max:50'
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }

    private function _validateDelivery(Request $request){
        $rules = [
            'descricao' => 'required|max:120',
            'file' => 'required'
        ];

        $messages = [
            'descricao.required' => 'O campo descricao é obrigatório.',
            'descricao.max' => '120 caracteres maximos permitidos.',
            'file.required' => 'O campo imagem é obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function quickSave(Request $request){
        try{
            $nome = $request->nome;

            $res = Categoria::create(
                [
                    'nome' => $nome,
                    'empresa_id' => $request->empresa_id
                ]
            );
            return response()->json($res, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function saveTributacao(Request $request){
        $categoria = Categoria::findOrFail($request->id);
        try{
            foreach($categoria->produtos as $p){
                if($request->perc_icms){
                    $p->perc_icms = $request->perc_icms;
                }
                if($request->perc_pis){
                    $p->perc_pis = $request->perc_pis;
                }
                if($request->perc_cofins){
                    $p->perc_cofins = $request->perc_cofins;
                }

                if($request->perc_ipi){
                    $p->perc_ipi = $request->perc_ipi;
                }
                if($request->perc_red_bc){
                    $p->pRedBC = $request->perc_red_bc;
                }
                if($request->CFOP_saida_estadual){
                    $p->CFOP_saida_estadual = $request->CFOP_saida_estadual;
                }
                if($request->CFOP_saida_inter_estadual){
                    $p->CFOP_saida_inter_estadual = $request->CFOP_saida_inter_estadual;
                }
                if($request->CST_CSOSN){
                    $p->CST_CSOSN = $request->CST_CSOSN;
                }
                if($request->CST_PIS){
                    $p->CST_PIS = $request->CST_PIS;
                }
                if($request->CST_COFINS){
                    $p->CST_COFINS = $request->CST_COFINS;
                }
                if($request->CST_IPI){
                    $p->CST_IPI = $request->CST_IPI;
                }

                if($request->cenq_ipi){
                    $p->cenq_ipi = $request->cenq_ipi;
                }

                $p->save();
            }
            session()->flash('mensagem_sucesso', 'Dados alterados!');
        }catch(\Exception $e){
            session()->flash('mensagem_erro', "Algo deu errado: " . $e->getMessage());
        }
        return redirect('/categorias');

    }

}

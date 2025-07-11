<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fornecedor;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Pais;
use App\Rules\ValidaDocumento;

class ProviderController extends Controller
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

    public function index(){
        $fornecedores = Fornecedor::where('empresa_id', $this->empresa_id)
        ->get();
        return view('fornecedores/list')
        ->with('fornecedores', $fornecedores)
        ->with('title', 'Fornecedores');
    }

    public function pesquisa(Request $request){
        $pesquisa = $request->input('pesquisa');

        $fornecedores = Fornecedor::
        where('empresa_id', $this->empresa_id)
        ->where($request->tipo_pesquisa, 'LIKE', "%$pesquisa%")
        ->get();

        return view('fornecedores/list')
        ->with('fornecedores', $fornecedores)
        ->with('tipoPesquisa', $request->tipo_pesquisa)
        ->with('pesquisa', $pesquisa)
        ->with('title', 'Filtro Fornecedor');
    }

    public function new(){
        $cidades = Cidade::all();
        $estados = Cliente::estados();
        $pais = Pais::all();

        return view('fornecedores/register')
        ->with('pessoaFisicaOuJuridica', true)
        ->with('cidadeJs', true)
        ->with('cidades', $cidades)
        ->with('pais', $pais)
        ->with('estados', $estados)
        ->with('title', 'Cadastrar Fornecedor');
    }

    public function save(Request $request){
        $provider = new Fornecedor();
        $this->_validate($request);
        try{

            $cidade = $request->input('cidade');
            $request->merge([ 
                'cidade_id' => $cidade,
                'telefone' => $request->input('telefone') ?? '',
                'celular' => $request->input('celular') ?? '',
                'ie_rg' => $request->input('ie_rg') ?? '',
                'pix' => $request->input('pix') ?? '',
                'complemento' => $request->input('complemento') ?? '',
                'tipo_pix' => $request->input('tipo_pix') ?? 'cpf',
                'email' => $request->email ?? '',
                'id_estrangeiro' => $request->id_estrangeiro ?? '',
            ]);

            $result = $provider->create($request->all());

            $this->criarLog($result);
            session()->flash("mensagem_sucesso", "Fornecedor cadastrado com sucesso!");
        }catch(\Exception $e){
            // echo $e->getMessage();
            // die;
            __saveError($e, $this->empresa_id);
            session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
        }
        
        return redirect('/fornecedores');
    }

    private function criarLog($objeto, $tipo = 'criar'){
        if(isset(session('user_logged')['log_id'])){
            $record = [
                'tipo' => $tipo,
                'usuario_log_id' => session('user_logged')['log_id'],
                'tabela' => 'fornecedores',
                'registro_id' => $objeto->id,
                'empresa_id' => $this->empresa_id
            ];
            __saveLog($record);
        }
    }

    public function edit($id){
        $provider = new Fornecedor(); //Model
        
        $resp = $provider
        ->where('id', $id)->first();  

        $cidades = Cidade::all();
        $estados = Cliente::estados();

        if(valida_objeto($resp)){

            $pais = Pais::all();

            return view('fornecedores/register')
            ->with('cidadeJs', true)
            ->with('pessoaFisicaOuJuridica', true)
            ->with('forn', $resp)
            ->with('pais', $pais)
            ->with('cidades', $cidades)
            ->with('estados', $estados)
            ->with('title', 'Editar Fornecedor');
        }else{
            return redirect('403');
        }

    }

    public function update(Request $request){
        $provider = new Fornecedor();

        $resp = $provider->findOrFail($request->id); 

        $this->_validate($request);

        try{
            $cidade = $request->input('cidade');

            $resp->razao_social = $request->input('razao_social');
            $resp->nome_fantasia = $request->input('nome_fantasia');
            $resp->cpf_cnpj = $request->input('cpf_cnpj');
            $resp->ie_rg = $request->input('ie_rg') ?? '';

            $resp->rua = $request->input('rua');
            $resp->numero = $request->input('numero');
            $resp->bairro = $request->input('bairro');

            $resp->telefone = $request->input('telefone') ?? '';
            $resp->celular = $request->input('celular') ?? '';
            $resp->pix = $request->input('pix') ?? '';
            $resp->complemento = $request->input('complemento') ?? '';
            $resp->tipo_pix = $request->input('tipo_pix') ?? 'cpf';
            $resp->email = $request->input('email');
            $resp->cep = $request->input('cep');
            $resp->contribuinte = $request->input('contribuinte');
            $resp->cidade_id = $cidade;
            $resp->cod_pais = $request->input('cod_pais');
            $resp->id_estrangeiro = $request->input('id_estrangeiro');

            $result = $resp->save();

            $this->criarLog($resp, 'atualizar');

            session()->flash('mensagem_sucesso', 'Fornecedor editado com sucesso!');
        }catch(\Exception $e){
            __saveError($e, $this->empresa_id);
            session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
        }
        
        return redirect('/fornecedores'); 
    }

    public function find($id){
        $fornecedor = Fornecedor::
        where('id', $id)
        ->first();
        
        echo json_encode($this->insertCidade($fornecedor));
    }

    private function insertCidade($fornecedor){
        $cidade = Cidade::getId($fornecedor->cidade_id);
        $fornecedor['nome_cidade'] = $cidade->nome;
        return $fornecedor;
    }

    public function delete($id){
        try{
            $resp = Fornecedor
            ::where('id', $id)
            ->first();
            if(valida_objeto($resp)){
                $this->criarLog($resp, 'deletar');

                if($resp->delete()){
                    session()->flash('mensagem_sucesso', 'Registro removido!');
                }else{
                    session()->flash('mensagem_erro', 'Erro!');
                }
                return redirect('/fornecedores');
            }else{
                return redirect('403');
            }
        }catch(\Exception $e){
            return view('errors.sql')
            ->with('title', 'Erro ao deletar fornecedor')
            ->with('motivo', 'Não é possivel remover fornecedor, presentes em compras!');
        }
    }


    private function _validate(Request $request){
        $rules = [
            'razao_social' => 'required|max:80',
            'nome_fantasia' => 'required|max:80',
            'cpf_cnpj' => ['required', new ValidaDocumento],
            'rua' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'telefone' => 'max:20',
            'celular' => 'max:20',
            'email' => 'max:40',
            'cep' => 'required',
            'cidade' => 'required',
            'ie_rg' => 'max:20',
            'tipo_pix' => strlen($request->pix) > 0 ? 'required' : '',
        ];

        $messages = [
            'razao_social.required' => 'O campo Razão social é obrigatório.',
            'razao_social.max' => '100 caracteres maximos permitidos.',
            'nome_fantasia.required' => 'O campo Nome Fantasia é obrigatório.',
            'nome_fantasia.max' => '80 caracteres maximos permitidos.',
            'cpf_cnpj.required' => 'O campo CPF/CNPJ é obrigatório.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'ie_rg.max' => '20 caracteres maximos permitidos.',
            'rua.max' => '80 caracteres maximos permitidos.',
            'numero.required' => 'O campo Numero é obrigatório.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'cidade.required' => 'O campo Cidade é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'telefone.required' => 'O campo Celular é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',
            'celular.required' => 'O campo Celular 2 é obrigatório.',
            'celular.max' => '20 caracteres maximos permitidos.',

            'email.required' => 'O campo Email é obrigatório.',
            'email.max' => '40 caracteres maximos permitidos.',
            'email.email' => 'Email inválido.',
            'tipo_pix.required' => 'Campo obrigatório.',


        ];
        $this->validate($request, $rules, $messages);
    }

    public function all(){
        $providers = Fornecedor::
        where('empresa_id', $this->empresa_id)
        ->get();
        $arr = array();
        foreach($providers as $c){
            $arr[$c->id. ' - ' .$c->razao_social] = null;
                //array_push($arr, $temp);
        }
        echo json_encode($arr);
    }

    public function consultaCadastrado($doc){
        $doc = str_replace("_", "/", $doc);
        $cliente = Fornecedor::
        where('cpf_cnpj', $doc)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        return response()->json($cliente, 200);
    }

    public function quickSave(Request $request){
        try{
            $data = $request->data;

            $cli = [
                'razao_social' => $data['razao_social'],
                'nome_fantasia' => $data['razao_social'],
                'bairro' => $data['bairro'] ?? '',
                'numero' => $data['numero'] ?? '',
                'rua' => $data['rua'] ?? '',
                'cpf_cnpj' => $data['cpf_cnpj'] ?? '',
                'telefone' => $data['telefone'] ?? '',
                'celular' => $data['celular'] ?? '',
                'email' => $data['email'] ?? '',
                'cep' => $data['cep'] ?? '',
                'ie_rg' => $data['ie_rg'] ?? '',
                'pix' => $data['pix'] ?? '',
                'complemento' => $data['complemento'] ?? '',
                'tipo_pix' => $data['tipo_pix'] ?? 'cpf',
                'cidade_id' => $data['cidade_id'] ?? 1, 
                'contribuinte' => $data['contribuinte'] ?? 1,
                'empresa_id' => $this->empresa_id, 
            ];

            $res = Fornecedor::create($cli);
            return response()->json($res, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }
}

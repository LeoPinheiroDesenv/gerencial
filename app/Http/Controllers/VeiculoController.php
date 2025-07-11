<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veiculo;
class VeiculoController extends Controller
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
        $veiculos = Veiculo::
        where('empresa_id', $this->empresa_id)
        ->get();
        return view('veiculos/list')
        ->with('veiculos', $veiculos)
        ->with('title', 'Veiculos');
    }

    public function filtro(Request $request){
        $placa = $request->placa;
        $modelo = $request->modelo;
        $marca = $request->marca;

        $veiculos = Veiculo::
        where('empresa_id', $this->empresa_id)
        ->when($placa, function ($q) use ($placa) {
            return $q->where('placa', 'like', "%$placa%");
        })
        ->when($modelo, function ($q) use ($modelo) {
            return $q->where('modelo', 'like', "%$modelo%");
        })
        ->when($marca, function ($q) use ($marca) {
            return $q->where('marca', 'like', "%$marca%");
        })
        ->get();

        return view('veiculos/list')
        ->with('placa', $placa)
        ->with('modelo', $modelo)
        ->with('marca', $marca)
        ->with('veiculos', $veiculos)
        ->with('title', 'Veiculos');
    }

    public function new(){
        $tipos = Veiculo::tipos();
        $tiposRodado = Veiculo::tiposRodado();
        $tiposCarroceria = Veiculo::tiposCarroceria();
        $tiposProprietario = Veiculo::tiposProprietario();
        $ufs = Veiculo::cUF();

        return view('veiculos/register')
        ->with('tipos', $tipos)
        ->with('tiposRodado', $tiposRodado)
        ->with('tiposCarroceria', $tiposCarroceria)
        ->with('tiposProprietario', $tiposProprietario)
        ->with('ufs', $ufs)
        ->with('veiculoJs', true)
        ->with('title', 'Cadastrar Veiculo');
    }

    public function save(Request $request){
        $veiculo = new Veiculo();
        $this->_validate($request);
        try{
            $request->merge([
                'rntrc' => $request->rntrc ?? '',
                'taf' => $request->taf ?? '',
                'numero_registro_estadual' => $request->numero_registro_estadual ?? '',
                'renavam' => $request->renavam ?? ''
            ]);
            $result = $veiculo->create($request->all());

            session()->flash("mensagem_sucesso", "Veículo cadastrado com sucesso.");
        }catch(\Exception $e){
            __saveError($e, $this->empresa_id);
            session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
        }

        return redirect('/veiculos');
    }

    public function edit($id){
      $tipos = Veiculo::tipos();
        $veiculo = new Veiculo(); //Model

        $tiposRodado = Veiculo::tiposRodado();
        $tiposCarroceria = Veiculo::tiposCarroceria();
        $tiposProprietario = Veiculo::tiposProprietario();
        $ufs = Veiculo::cUF();

        $resp = $veiculo
        ->where('id', $id)
        ->first();  
        if(valida_objeto($resp)){

            return view('veiculos/register')
            ->with('veiculo', $resp)
            ->with('tipos', $tipos)
            ->with('tiposRodado', $tiposRodado)
            ->with('tiposCarroceria', $tiposCarroceria)
            ->with('tiposProprietario', $tiposProprietario)
            ->with('ufs', $ufs)
            ->with('veiculoJs', true)
            ->with('title', 'Editar Veiculo');
        }else{
            return redirect('/403');
        }

    }

    public function update(Request $request){
    	$veiculo = new Veiculo();
    	$resp = $veiculo->findOrFail($request->id);
    	$this->_validate($request);

    	try{
            $resp->cor = $request->input('cor');
            $resp->marca = $request->input('marca');
            $resp->modelo = $request->input('modelo');
            $resp->placa = $request->input('placa');
            $resp->tipo = $request->input('tipo');
            $resp->uf = $request->input('uf');
            $resp->rntrc = $request->input('rntrc');
            $resp->taf = $request->input('taf');
            $resp->numero_registro_estadual = $request->input('numero_registro_estadual');
            $resp->renavam = $request->input('renavam');
            $resp->tipo = $request->input('tipo');
            $resp->tipo_carroceira = $request->input('tipo_carroceira');
            $resp->tipo_rodado = $request->input('tipo_rodado');
            $resp->tara = $request->input('tara');
            $resp->capacidade = $request->input('capacidade');
            $resp->proprietario_nome = $request->input('proprietario_nome');
            $resp->proprietario_ie = $request->input('proprietario_ie');
            $resp->proprietario_uf = $request->input('proprietario_uf');
            $resp->proprietario_tp = $request->input('proprietario_tp');
            $resp->proprietario_documento = $request->input('proprietario_documento');

            $result = $resp->save();
            session()->flash('mensagem_sucesso', 'Veículo editado com sucesso!');

        }catch(\Exception $e){
            __saveError($e, $this->empresa_id);
            session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
        }

        return redirect('/veiculos'); 
    }

    public function delete($id){
        try{
            $resp = Veiculo::
            where('id', $id)
            ->first();
            if(valida_objeto($resp)){
                if($resp->delete()){
                    session()->flash('mensagem_sucesso', 'Registro removido!');
                }else{
                    session()->flash('mensagem_erro', 'Erro!');
                }
                return redirect('/veiculos');
            }else{
                return redirect('/403');
            }
        }catch(\Exception $e){
            return view('errors.sql')
            ->with('title', 'Erro ao deletar veiculo')
            ->with('motivo', 'Não é possivel remover veiculos presentes em transportes!');
        }
    }


    private function _validate(Request $request){
        $rules = [
            'placa' => 'required|max:8',
            'cor' => 'required|max:10',
            'marca' => 'required|max:20',
            'modelo' => 'required|max:20',
            'tara' => 'required|max:10',
            'rntrc' => 'max:9',
            'capacidade' => 'required|max:10',
            'proprietario_nome' => 'required|max:40',
            'proprietario_ie' => 'required|max:13',
            'proprietario_documento' => 'required|max:20',
        ];

        $messages = [
            'placa.required' => 'O campo placa é obrigatório.',
            'nome.max' => '8 caracteres maximos permitidos.',
            'cor.required' => 'O campo cor é obrigatório.',
            'cor.max' => '10 caracteres maximos permitidos.',
            'marca.required' => 'O campo marca é obrigatório.',
            'marca.max' => '20 caracteres maximos permitidos.',
            'modelo.required' => 'O campo modelo é obrigatório.',
            'modelo.max' => '20 caracteres maximos permitidos.',
            'capacidade.required' => 'O campo capacidade é obrigatório.',
            'capacidade.max' => '10 caracteres maximos permitidos.',
            'tara.required' => 'O campo tara é obrigatório.',
            'tara.max' => '10 caracteres maximos permitidos.',

            'rntrc.required' => 'O campo RNTRC é obrigatório.',
            'rntrc.min' => '8 caracteres permitidos.',
            'rntrc.max' => '9 caracteres permitidos.',

            'proprietario_nome.required' => 'O campo Nome proprietário é obrigatório.',
            'proprietario_nome.max' => '40 caracteres maximos permitidos.',
            'proprietario_ie.required' => 'O campo I.E proprietário é obrigatório.',
            'proprietario_ie.max' => '13 caracteres maximos permitidos.',
            'proprietario_documento.required' => 'O campo CPF/CNPJ proprietário é obrigatório.',
            'proprietario_documento.max' => '20 caracteres maximos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }
}

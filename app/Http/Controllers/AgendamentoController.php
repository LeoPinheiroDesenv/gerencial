<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agendamento;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\ConfigNota;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\ConfigCaixa;
use App\Models\ContaEmpresa;
use App\Models\Cidade;
use App\Models\Servico;
use App\Models\ItemAgendamento;
use App\Models\CategoriaServico;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use App\Models\Certificado;
use App\Models\AberturaCaixa;
use App\Models\Acessor;
use App\Models\Pais;
use App\Models\GrupoCliente;
use App\Models\ItemVendaCaixa;

class AgendamentoController extends Controller
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

		$funcionarios = Funcionario::
		where('funcionarios.empresa_id', $this->empresa_id)
		->select('funcionarios.*')
		->join('usuarios', 'usuarios.id', '=', 'funcionarios.usuario_id')
		->get();

		$clientes = Cliente::
		where('empresa_id', $this->empresa_id)
		->where('inativo', false)
		->get();

		$servicos = Servico::
		where('empresa_id', $this->empresa_id)
		->get();
		
		$categorias = CategoriaServico::
		where('empresa_id', $this->empresa_id)
		->get();

		return view('agendamentos/view')
		->with('fullcalendar', true)
		->with('funcionarios', $funcionarios)
		->with('clientes', $clientes)
		->with('servicos', $servicos)
		->with('categorias', $categorias)
		->with('title', 'Agendamentos');
	}

	public function saveCliente(Request $request){
		$cliente = $request->cliente;

		$arr = [
			'razao_social' => $cliente['nome'],
			'nome_fantasia' => $cliente['nome'],
			'bairro' => '',
			'numero' => '',
			'rua' => '',
			'cpf_cnpj' => '',
			'telefone' => $cliente['telefone'],
			'celular' => $cliente['telefone'],
			'email' => '',
			'cep' => '',
			'ie_rg' => '',
			'consumidor_final' => 1,
			'limite_venda' => 0,
			'cidade_id' => 1,
			'contribuinte' => 1,
			'rua_cobranca' => '',
			'numero_cobranca' => '',
			'bairro_cobranca' => '',
			'cep_cobranca' => '',
			'cidade_cobranca_id' => null,
			'empresa_id' => $this->empresa_id
		];
		$res = Cliente::create($arr);

		return response()->json($res, 200);
	}

	public function save(Request $request){
		$agendamento = $request->agendamento;

		$arr = [
			'funcionario_id' => $agendamento['funcionario_id'], 
			'cliente_id' => $agendamento['cliente_id'],
			'data' => $this->parseDate($agendamento['data']),
			'inicio' => $agendamento['inicio'],
			'termino' => $agendamento['termino'],
			'observacao' => $agendamento['observacao'] ?? '',
			'total' => __replace($agendamento['total']) - __replace($agendamento['desconto']) + __replace($agendamento['acrescimo']),
			'desconto' => __replace($agendamento['desconto']),
			'acrescimo' => __replace($agendamento['acrescimo']),
			'status' => false,
			'empresa_id' => $this->empresa_id
		];

		$result = Agendamento::create($arr);

		foreach($agendamento['itens'] as $i){
			$arr = [
				'agendamento_id' => $result->id,
				'servico_id' => $i['id'],
				'quantidade' => 1
			];
			$item = ItemAgendamento::create($arr);

		}

		return response()->json($arr, 200);

	}

	private function parseDate($date){
		return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
	}

	private function menos10Dias(){
		return date('Y-m-d', strtotime("-10 days",strtotime(str_replace("/", "-", 
			date('Y-m-d')))));
	}

	private function mais20Dias(){
		return date('Y-m-d', strtotime("+20 days",strtotime(str_replace("/", "-", 
			date('Y-m-d')))));
	}

	public function all(){
		$mais20 = $this->mais20Dias();
		$menos10 = $this->menos10Dias();
		
		$agendamentos = Agendamento::
		whereBetween('data', [$menos10, 
			$mais20])
		->where('empresa_id', $this->empresa_id)
		->get();
		$temp = [];

		foreach($agendamentos as $a){
			$titulo = $a->cliente->razao_social . " - ";
			foreach($a->itens as $key => $i){
				$titulo .= $i->servico->nome . ($key < sizeof($a->itens)-1 ? "|" : "");
			}

			$arr = [
				'title' => $titulo,
				'start' => $a->data.'T'.$a->inicio,
				'end' => $a->data.'T'.$a->termino,
				'url' => "/agendamentos/detalhes/".$a->id,
				'backgroundColor' => $a->status ? '#4db6ac' : '#ef5350'
			];

			array_push($temp, $arr);
		}
		return response()->json($temp, 200);

	}

	public function filtro(Request $request){
		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;
		$funcionario = $request->funcionario;
		$cliente = $request->cliente;
		$status = $request->status;

		$agendamentos = Agendamento::select('*')
		->where('empresa_id', $this->empresa_id);

		if($dataInicial && $dataFinal){
			$data1 = $this->parseDate($dataInicial);
			$data2 = $this->parseDate($dataFinal);
			$agendamentos->whereBetween('data', [$data1, 
				$data2]);
		}

		if($funcionario != 'null'){
			$agendamentos->where('funcionario_id', $funcionario);
		}
		if($cliente != 'null'){
			$agendamentos->where('cliente_id', $cliente);
		}
		if($status != 'todos'){
			$agendamentos->where('status', $status);
		}

		$agendamentos = $agendamentos->get();

		$temp = [];

		foreach($agendamentos as $a){
			$titulo = $a->cliente->razao_social . " - ";
			foreach($a->itens as $key => $i){
				$titulo .= $i->servico->nome . ($key < sizeof($a->itens)-1 ? "|" : "");
			}

			$arr = [
				'title' => $titulo,
				'start' => $a->data.'T'.$a->inicio,
				'end' => $a->data.'T'.$a->termino,
				'url' => "/agendamentos/detalhes/".$a->id,
				'backgroundColor' => $a->status ? '#4db6ac' : '#ef5350'
			];

			array_push($temp, $arr);
		}
		return response()->json($temp, 200);

	}

	public function detalhes($id){
		$agendamento = Agendamento::find($id);
		if(valida_objeto($agendamento)){
			return view('agendamentos/detalhes')
			->with('agendamento', $agendamento)
			->with('title', 'Detalhe agendamento');
		}else{
			return redirect('/403');
		}
	}

	public function update(Request $request, $id){
		$item = Agendamento::findOrFail($id);
		try{
			$item->cliente_id = $request->cliente_id;
			$item->funcionario_id = $request->funcionario_id;
			$item->data = $request->data;
			$item->inicio = $request->inicio;
			$item->termino = $request->termino;
			$item->observacao = $request->observacao ?? '';
			$item->save();
			session()->flash("mensagem_sucesso", "Agendamento atualizado!");

		}catch(\Exception $e){
			session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
		}
		return redirect('/agendamentos');
	}

	public function edit($id){
		$agendamento = Agendamento::find($id);
		if(valida_objeto($agendamento)){

			$clientes = Cliente::
			where('empresa_id', $this->empresa_id)
			->where('inativo', false)
			->get();

			$funcionarios = Funcionario::
			where('funcionarios.empresa_id', $this->empresa_id)
			->select('funcionarios.*')
			->join('usuarios', 'usuarios.id', '=', 'funcionarios.usuario_id')
			->get();

			return view('agendamentos.edit')
			->with('agendamento', $agendamento)
			->with('clientes', $clientes)
			->with('funcionarios', $funcionarios)
			->with('title', 'Editar agendamento');
		}else{
			return redirect('/403');
		}
	}

	public function delete($id){
		$agendamento = Agendamento::find($id);
		if(valida_objeto($agendamento)){
			ItemAgendamento::where('agendamento_id', $id)->delete();

			$agendamento->delete();
			session()->flash("mensagem_sucesso", "Agendamento removido!");

			return redirect('agendamentos');
		}else{
			return redirect('/403');
		}
	}

	public function alterarStatus($id){

		$agendamento = Agendamento::find($id);
		if(valida_objeto($agendamento)){

			$agendamento->status = 1;

			$valorComissao = $this->calculaComissao($agendamento);

			$agendamento->valor_comissao = $valorComissao;
			$agendamento->save();
			session()->flash("mensagem_sucesso", "Agendamento alterado para finalizado!");

			return redirect('agendamentos');
		}else{
			return redirect('/403');
		}
	}

	private function calculaComissao($agendamento){
		$soma = 0;
		$somaDesconto = 0;
		$total = $agendamento->total + $agendamento->acrescimo - $agendamento->desconto;

		foreach($agendamento->itens as $key => $i){
			$tempDesc = 0;
			$valorServico = $i->servico->valor;

			if($key < sizeof($agendamento->itens)-1){
				$media = (((($valorServico - $total)/$total))*100);
				
				$media = 100 - ($media * -1);
				$tempDesc = ($agendamento->desconto*$media)/100;

				$somaDesconto += $tempDesc;

			}else{
				$tempDesc = $agendamento->desconto - $somaDesconto;
			}

			$comissao = $i->servico->comissao;

			$valorComissao = ($valorServico - $tempDesc) * ($comissao/100);
			$soma += $valorComissao;
		}

		return number_format($soma,2);
	}

	public function irParaFrenteCaixa($id){
		$agendamento = Agendamento::find($id);
		if(valida_objeto($agendamento)){

			$produto_agendamento_id = $this->verificaProdutoServicoCadastrado();

			$atributes = $this->addAtributes($agendamento, $produto_agendamento_id);

			$usuario = Usuario::find(get_id_user());
			$tiposPagamento = VendaCaixa::tiposPagamento();
			$config = ConfigNota::
			where('empresa_id', $this->empresa_id)
			->first();

			$certificado = Certificado::
			where('empresa_id', $this->empresa_id)
			->first();

			$tiposPagamentoMulti = VendaCaixa::tiposPagamentoMulti();
			$produtos = Produto::
			where('empresa_id', $this->empresa_id)
			->get();

			$categorias = Categoria::
			where('empresa_id', $this->empresa_id)
			->get();

			$clientes = Cliente::
			where('empresa_id', $this->empresa_id)
			->where('inativo', false)
			->orderBy('razao_social')->get();

			$abertura = AberturaCaixa::
			where('status', 0)
			->where('empresa_id', $this->empresa_id)
			->orderBy('id', 'desc')
			->first();

			if($abertura != null){

				$produtosGroup = Produto::
				where('empresa_id', $this->empresa_id)
				->where('inativo', false)
				->where('valor_venda', '>', 0)
				->groupBy('referencia_grade')
				->get();

				$atalhos = ConfigCaixa::
				where('usuario_id', get_id_user())
				->first();

				$funcionarios = Funcionario::
				where('funcionarios.empresa_id', $this->empresa_id)
				->select('funcionarios.*')
				->join('usuarios', 'usuarios.id', '=', 'funcionarios.usuario_id')
				->get();

				$view = 'main3';
				// if($atalhos != null && $atalhos->modelo_pdv == 1){
				// 	$view = 'main2';
				// }

				$consignadas = $this->getConsignadas();
				$acessores = Acessor::where('empresa_id', $this->empresa_id)->get();

				$usuarios = Usuario::where('empresa_id', $this->empresa_id)
				->where('ativo', 1)
				->orderBy('nome', 'asc')
				->get();
				$vendedores = [];
				foreach($usuarios as $u){
					if($u->funcionario){
						array_push($vendedores, $u);
					}
				}

				$estados = Cliente::estados();
				$cidades = Cidade::all();
				$pais = Pais::all();
				$grupos = GrupoCliente::get();

				$rascunhos = $this->getRascunhos();
				$funcionarios = Funcionario::where('empresa_id', $this->empresa_id)->get(); 
				$produtosMaisVendidos = $this->produtosMaisVendidos();
				$filial = $abertura != null ? $abertura->filial : null;

				$contasEmpresa = ContaEmpresa::where('empresa_id', $this->empresa_id)
				->where('status', 1)->get();

				return view('frontBox/'.$view)
				->with('atalhos', $atalhos)
				->with('rascunhos', $rascunhos)
				->with('contasEmpresa', $contasEmpresa)
				->with('consignadas', $consignadas)
				->with('itens', $atributes)
				->with('filial', $filial)
				->with('funcionarios', $funcionarios)
				->with('frenteCaixa', true)
				->with('tiposPagamento', $tiposPagamento)
				->with('tiposPagamentoMulti', $tiposPagamentoMulti)
				->with('config', $config)
				->with('usuario', $usuario)
				->with('clientes', $clientes)
				->with('acessores', $acessores)
				->with('produtos', $produtos)
				->with('produtosMaisVendidos', $produtosMaisVendidos)
				->with('vendedores', $vendedores)
				->with('produtosGroup', $produtosGroup)
				->with('pais', $pais)
				->with('grupos', $grupos)
				->with('cidades', $cidades)
				->with('estados', $estados)
				->with('agendamento_id', $agendamento->id)
				->with('categorias', $categorias)
				->with('certificado', $certificado)
				->with('title', 'Finalizar Agendamento '.$id);

			}else{
				echo "É necessário abrir o caixa no PDV primeiramente";
				echo " <a href='/frenteCaixa'>ir para PDV</a>";
			}
		}else{
			return redirect('/403');
		}
	}

	private function produtosMaisVendidos(){

		$abertura = AberturaCaixa::where('empresa_id', $this->empresa_id)
		->where('usuario_id', get_id_user())
		->where('status', 0)
		->orderBy('id', 'desc')
		->first();
		$filial = -1;

		if($abertura){
			$filial = $abertura->filial_id;
			if($filial == null){
				$filial = -1;
			}
		}
		$itens = ItemVendaCaixa::
		selectRaw('item_venda_caixas.*, count(quantidade) as qtd')
		->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
		->join('produtos', 'produtos.id', '=', 'item_venda_caixas.produto_id')
		->where('venda_caixas.empresa_id', $this->empresa_id)
		->groupBy('item_venda_caixas.produto_id')
		->orderBy('qtd')
		->when(empresaComFilial(), function ($q) use ($filial) {
			return $q->where(function($query) use ($filial){
				$query->where('produtos.locais', 'like', "%{$filial}%");
			});
		})
		->limit(21)
		->get();

		$produtos = [];
		foreach($itens as $i){
			$p = Produto::find($i->produto_id);
			if(!$p->inativo){
				array_push($produtos, $p);
			}
		}
		return $produtos;
	}

	private function getRascunhos(){
		return VendaCaixa::
		where('rascunho', 1)
		->where('empresa_id', $this->empresa_id)
		->limit(20)
		->orderBy('id', 'desc')
		->get();
	}

	private function getConsignadas(){
		return VendaCaixa::
		where('consignado', 1)
		->where('empresa_id', $this->empresa_id)
		->limit(20)
		->orderBy('id', 'desc')
		->get();
	}

	private function verificaProdutoServicoCadastrado(){
		$categoria_id = $this->verificaCategoriaServicoCadastrado();
		$produto = Produto::where('nome', 'Agendamento de serviço')
		->where('empresa_id', $this->empresa_id)->first();
		if($produto != null) return $produto->id;

		$produtoFirst = Produto::where('empresa_id', $this->empresa_id)->first();
		$arr = [
			'nome' => 'Agendamento de serviço',
			'categoria_id' => $categoria_id,
			'cor' => '',
			'valor_venda' => 0,
			'NCM' => $produtoFirst != null ? $produtoFirst->NCM : '4407.11.00',
			'CST_CSOSN' => $produtoFirst != null ? $produtoFirst->CST_CSOSN : '102',
			'CST_PIS' => $produtoFirst != null ? $produtoFirst->CST_PIS : '49',
			'CST_COFINS' => $produtoFirst != null ? $produtoFirst->CST_COFINS : '49',
			'CST_IPI' => $produtoFirst != null ? $produtoFirst->CST_IPI : '99',
			'unidade_compra' => $produtoFirst != null ? $produtoFirst->unidade_compra : 'UNID',
			'unidade_venda' => $produtoFirst != null ? $produtoFirst->unidade_venda : 'UNID',
			'composto' => 0,
			'codBarras' => 'SEM GTIN',
			'conversao_unitaria' => 1,
			'valor_livre' => 0,
			'perc_icms' => 0,
			'perc_pis' => 0,
			'perc_cofins' => 0,
			'perc_ipi' => 0,
			'CFOP_saida_estadual' => $produtoFirst != null ? $produtoFirst->CFOP_saida_estadual : '5101',
			'CFOP_saida_inter_estadual' => $produtoFirst != null ? $produtoFirst->CFOP_saida_inter_estadual : '6101',
			'codigo_anp' => '',
			'descricao_anp' => '',
			'perc_iss' => 0,
			'cListServ' => '',
			'imagem' => '',
			'alerta_vencimento' => 0,
			'valor_compra' => 0,
			'gerenciar_estoque' => 0,
			'estoque_minimo' => 0,
			'referencia' => '',
			'tela_id' => NULL,
			'largura' => 0,
			'comprimento' => 0,
			'altura' => 0,
			'peso_liquido' => 0,
			'peso_bruto' => 0,
			'empresa_id' => $this->empresa_id
		];
		$result = Produto::create($arr);
		return $result->id;
	}

	private function verificaCategoriaServicoCadastrado(){
		$categoria = Categoria::where('nome', 'serviços')
		->where('empresa_id', $this->empresa_id)->first();
		if($categoria != null) return $categoria->id;

		$arr = [
			'nome' => 'serviços',
			'empresa_id' => $this->empresa_id
		];
		$result = Categoria::create($arr);
		return $result->id;
	}

	private function addAtributes($agendamento, $produto_agendamento_id){
		$temp = [];

		$produto = Produto::find($produto_agendamento_id);


		$produto->valor_venda = $agendamento->total;

		array_push($temp, $produto);


		return $temp;
	}

	public function comissao(){
		$funcionarios = Funcionario::
		where('empresa_id', $this->empresa_id)
		->get();
		
		return view('agendamentos/comissao')
		->with('funcionarios', $funcionarios)
		->with('title', 'Comissão %');
	}

	public function filtrarComissao(Request $request){
		
		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;
		$funcionario = $request->funcionario;

		$agendamentos = Agendamento::select('*');

		if($dataInicial && $dataFinal){
			$data1 = $this->parseDate($dataInicial);
			$data2 = $this->parseDate($dataFinal);
			$agendamentos->whereBetween('data', [$data1, 
				$data2]);
		}

		if($funcionario != 'null'){
			$agendamentos->where('funcionario_id', $funcionario);
		}

		$agendamentos->where('valor_comissao', '>', 0);
		$agendamentos->where('empresa_id', $this->empresa_id);
		$agendamentos->where('status', 1);

		$agendamentos = $agendamentos->get();


		$arrAgrupado = null;
		if($funcionario == 'null'){
			$arrAgrupado = $this->agrupa($agendamentos);
		}

		$funcionarios = Funcionario::
		where('empresa_id', $this->empresa_id)
		->get();

		return view('agendamentos/comissao')
		->with('funcionarios', $funcionarios)
		->with('agendamentos', $agendamentos)
		->with('arrAgrupado', $arrAgrupado)
		->with('dataFinal', $request->data_final)
		->with('dataInicial', $request->data_inicial)
		->with('funcionario', $request->funcionario)

		->with('title', 'Comissão %');
	}

	private function agrupa($agendamentos){
		$arr = $this->criarArrayFuncionarios();
		$len = sizeof($arr);
		foreach($agendamentos as $a){
			for($i=0; $i<$len; $i++){
				if($a->funcionario->id == $arr[$i]['id']){
					$arr[$i]['valor_agendamento'] += $a->total;
					$arr[$i]['valor_comissao'] += $a->valor_comissao;
					$arr[$i]['total_de_servicos'] += sizeof($a->itens);
				}
			}
		}
		return $arr;
	}

	private function criarArrayFuncionarios(){
		$funcionaios = Funcionario::
		where('empresa_id', $this->empresa_id)
		->get();

		$temp = [];
		foreach($funcionaios as $f){
			$arr = [
				'id' => $f->id,
				'nome' => $f->nome,
				'valor_agendamento' => 0,
				'valor_comissao' => 0,
				'total_de_servicos' => 0
			];
			array_push($temp, $arr);
		}
		return $temp;
	}

	public function servicos(){
		$funcionarios = Funcionario::
		where('empresa_id', $this->empresa_id)
		->get();
		return view('agendamentos/servicos')
		->with('funcionarios', $funcionarios)
		->with('title', 'Serviços do agendamento');
	}

	public function filtrarServicos(Request $request){
		
		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;
		$funcionario = $request->funcionario;

		if($funcionario == 'null'){
			session()->flash("mensagem_erro", "Selecione o atendente!");
			return redirect('/agendamentos/servicos');
		}

		$agendamentos = Agendamento::select('*');

		if($dataInicial && $dataFinal){
			$data1 = $this->parseDate($dataInicial);
			$data2 = $this->parseDate($dataFinal);
			$agendamentos->whereBetween('data', [$data1, 
				$data2]);
		}

		if($funcionario != 'null'){
			$agendamentos->where('funcionario_id', $funcionario);
		}

		$agendamentos->where('valor_comissao', '>', 0);
		$agendamentos->where('status', 1);
		$agendamentos->where('empresa_id', $this->empresa_id);

		$agendamentos = $agendamentos->get();


		$arrServicos = $this->criaArrayDeServicos();
		$len = sizeof($arrServicos);
		$servicos = [];

		foreach($agendamentos as $a){
			foreach($a->itens as $item){
				array_push($servicos, $item);
				for($i=0; $i < $len; $i++){
					if($item->servico->id == $arrServicos[$i]['id']){

						$arrServicos[$i]['valor'] += $item->servico->valor;
						$arrServicos[$i]['quantidade'] += 1;

					}
				}
			}
		}

		$funcionarios = Funcionario::
		where('empresa_id', $this->empresa_id)
		->get();
		return view('agendamentos/servicos')
		->with('funcionarios', $funcionarios)
		->with('grupo', $arrServicos)
		->with('servicos', $servicos)
		->with('dataInicial', $request->data_inicial)
		->with('dataFinal', $request->data_final)
		->with('funcionario', $request->funcionario)
		->with('title', 'Serviços do agendamento');
	}

	private function criaArrayDeServicos(){
		$servicos = Servico::
		where('empresa_id', $this->empresa_id)
		->get();
		$temp = [];
		foreach($servicos as $s){
			$arr = [
				'id' => $s->id,
				'servico' => $s->nome,
				'valor' => 0,
				'quantidade' => 0,
			];
			array_push($temp, $arr);
		}
		return $temp;
	}
}

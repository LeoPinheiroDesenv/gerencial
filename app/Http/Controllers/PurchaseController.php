<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filial;
use App\Models\Compra;
use App\Models\ItemPurchase;
use App\Helpers\StockMove;
use App\Services\NFeEntradaService;
use App\Models\ConfigNota;
use App\Models\NaturezaOperacao;
use NFePHP\DA\NFe\Danfe;
use App\Models\ItemCompra;
use App\Models\Produto;
use App\Models\Cidade;
use App\Models\Etiqueta;
use App\Models\CompraReferencia;
use NFePHP\DA\NFe\Daevento;
use Mail;
use App\Models\EscritorioContabil;
use Dompdf\Dompdf;
use App\Prints\CompraPrint80;

class PurchaseController extends Controller
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

    public function numeroSequencial(){
        $verify = Compra::where('empresa_id', $this->empresa_id)
        ->where('numero_sequencial', 0)
        ->first();
        if($verify){
            $vendas = Compra::where('empresa_id', $this->empresa_id)
            ->get();

            $n = 1;
            foreach($vendas as $v){
                $v->numero_sequencial = $n;
                $n++;
                $v->save();
            }
        }
    }

    public function index(){

        $this->numeroSequencial();
        $totalRegistros = count(Compra::where('empresa_id', $this->empresa_id)->get());
        $compras = Compra::
        orderBy('id', 'desc')
        ->where('empresa_id', $this->empresa_id)
        ->paginate(15);

        // $somaCompraMensal = $this->somaCompraMensal();
        return view('compraManual/listAll')
        ->with('compras', $compras)
        // ->with('somaCompraMensal', $somaCompraMensal)
        ->with('links', true)
        ->with('graficoJs', true)
        ->with('title', 'Compras');
        
    }

    public function pesquisa(Request $request){
        $compras = Compra::pesquisaProduto($request->pesquisa);
        $totalRegistros = count($compras);

        // $somaCompraMensal = $this->somaCompraMensal();
        return view('compraManual/listAll')
        ->with('compras', $compras)
        // ->with('somaCompraMensal', $somaCompraMensal)
        ->with('graficoJs', true)
        ->with('title', 'Pequisa de Produto em Compras');
        
    }

    private function somaCompraMensal(){
        $compras = Compra::
        where('empresa_id', $this->empresa_id)
        ->get();
        $temp = [];
        $soma = 0;
        $mesAnterior = null;
        $anoAnterior = null;

        foreach($compras as $key => $c){
            $date = $c->created_at;
            $mes = substr($date, 5, 2);
            $ano = substr($date, 0, 4);


            if($mesAnterior != $mes){
                $temp["Mes: ".$mes."/$ano"] = $c->valor;
            }else{
                $temp["Mes: ".$mesAnterior."/$anoAnterior"] += $c->valor;

            }
            $mesAnterior = $mes;
            $anoAnterior = $ano;
        }

        return $temp;
    }

    private function somaCompraMensalFiltro($compras){
        $temp = [];
        $soma = 0;
        $mesAnterior = null;
        $anoAnterior = null;


        foreach($compras as $c){
            $date = $c->created_at;
            $mes = substr($date, 5, 2);
            $ano = substr($date, 0, 4);

            if($mesAnterior != $mes){
                $temp["Mes: ".$mes."/$ano"] = $c->valor;
            }else{
                $temp["Mes: ".$mesAnterior."/$anoAnterior"] += $c->valor;
            }
            $mesAnterior = $mes;
            $anoAnterior = $ano;
        }

        return $temp;
    }

    private function somaCompraDiarioFiltro($compras){
        $temp = [];
        $soma = 0;
        $diaAnterior = null;
        $mesAnterior = null;
        $s = 0;

        foreach($compras as $c){
            $date = $c->created_at;
            $dia = substr($date, 8, 2);
            $mes = substr($date, 5, 2);
            if($diaAnterior != $dia){
                $temp["Dia: ".$dia."/$mes"] = $c->valor;
            }else{
                $temp["Dia: ".$diaAnterior."/$mesAnterior"] += $c->valor;
                $s += $c->valor;
            }
            $mesAnterior = $mes;
            $diaAnterior = $dia;
        }

        return $temp;
    }
    private function diferencaEntreDatas($data1, $data2){
        $dif = strtotime($data2) - strtotime($data1);
        return floor($dif / (60 * 60 * 24));
    }

    public function filtro(Request $request){
        $dataInicial = $request->data_inicial;
        $dataFinal = $request->data_final;
        $fornecedor = $request->fornecedor;
        $numero_nfe = $request->numero_nfe;
        $filial_id = $request->filial_id;
        $compras = null;
        $diferencaDatas = null;

        // if($dataInicial == null || $dataFinal == null || $fornecedor == null){
        //     session()->flash('mensagem_erro', 'Informe o fornecedor, data inicial e data final!');
        //     return redirect('/compras');
        // }
        $compras = Compra::
        select('compras.*')
        ->orderBy('compras.created_at', 'desc')
        ->where('compras.empresa_id', $this->empresa_id);

        if(($fornecedor)){
            $compras->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id')
            ->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%");
        }
        if(($dataInicial) && isset($dataFinal)){
            $compras->whereBetween('compras.created_at', [
                $this->parseDate($dataInicial), 
                $this->parseDate($dataFinal, true)
            ]);
        }
        if(($numero_nfe)){
            $compras->where('nf', 'LIKE', "%$numero_nfe%");
        }

        if($filial_id){
            if($filial_id == -1){
                $compras->where('filial_id', null);
            }else{
                $compras->where('filial_id', $filial_id);
            }
        }

        $compras = $compras->get();

        if(isset($dataInicial) && isset($dataFinal)){
            $diferencaDatas = $this->diferencaEntreDatas($this->parseDate($dataInicial), $this->parseDate($dataFinal));
        }

        if($diferencaDatas > 31 || $diferencaDatas == null){
            $somaCompraMensal = $this->somaCompraMensalFiltro($compras);
        }else{
            $somaCompraMensal = $this->somaCompraDiarioFiltro($compras);
        }

        return view('compraManual/listAll')
        ->with('compras', $compras)
        ->with('fornecedor', $fornecedor)
        ->with('dataInicial', $dataInicial)
        ->with('numero_nfe', $numero_nfe)
        ->with('dataFinal', $dataFinal)
        ->with('filial_id', $filial_id)
        // ->with('somaCompraMensal', $somaCompraMensal)
        ->with('graficoJs', true)
        ->with('infoDados', "Contas filtradas")
        ->with('title', 'Filtro Compras');

    }

    private function parseDate($date, $plusDay = false){

        if($plusDay == false)
            return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
        else
            return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
    }

    public function downloadXml($id){
        $compra = Compra::
        where('id', $id)
        ->first();
        if(valida_objeto($compra)){
            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if($compra->nf > 0) return response()->download(public_path('xml_entrada/').$compra->chave. '.xml');
            else return response()->download(public_path('xml_entrada_emitida/').$compra->chave. '.xml');
        }else{
            return redirect('/403');
        }
    }

    public function downloadXmlCancela($id){
        $compra = Compra::
        where('id', $id)
        ->first();
        if(valida_objeto($compra)){
            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            return response()->download(public_path('xml_nfe_entrada_cancelada/').$compra->chave. '.xml');
        }else{
            return redirect('/403');
        }
    }

    public function detalhes($id){
        $compra = Compra::
        where('id', $id)
        ->first();
        if(valida_objeto($compra)){
            $value = session('user_logged');

            return view('compraManual/detail')
            ->with('compra', $compra)
            ->with('adm', $value['adm'])
            ->with('title', 'Detalhes da compra');
        }else{
            return redirect('/403');
        }
    }

    public function delete($id){

        $compra = Compra::
        where('id', $id)
        ->first();
        if(valida_objeto($compra)){
            $stockMove = new StockMove();
            $public = env('SERVIDOR_WEB') ? 'public/' : '';

            if($compra->xml_path != "" && file_exists(public_path("xml_entrada/") . $compra->xml_path)){
                unlink(public_path("xml_entrada/") .$compra->xml_path);
            }
            foreach($compra->itens as $i){
        // baixa de estoque
                $stockMove->downStock($i->produto->id, $i->quantidade*$i->produto->conversao_unitaria, $compra->filial_id);
                $i->delete();
            } 

            if($compra->delete()){
                session()->flash('mensagem_sucesso', 'Registro removido!');
            }else{
                session()->flash('mensagem_erro', 'Erro!');
            }
            return redirect('/compras');
        }else{
            return redirect('/403');
        }
    }

    public function itemCompra(Request $request){
        $item = ItemCompra::with('produto')->findOrFail($request->id);
        return response()->json($item, 200);
    }

    public function emitirEntrada($id){
        $compra = Compra::find($id);
        if(valida_objeto($compra)){
            $naturezas = NaturezaOperacao::
            where('empresa_id', $this->empresa_id)
            ->get();

            $cidades = Cidade::all();

            $dadosEntrada = true;
            $produtosInvalidos = [];
            foreach($compra->itens as $i){
                if(!$i->produto->CST_CSOSN_entrada || !$i->produto->CST_PIS_entrada || !$i->produto->CST_COFINS_entrada || !$i->produto->CST_IPI_entrada || !$i->produto->CFOP_entrada_estadual || !$i->produto->CFOP_entrada_inter_estadual){
                    $dadosEntrada = false;
                    array_push($produtosInvalidos, $i->produto_id);
                }
            }

            $tiposPagamento = Compra::tiposPagamento();
            return view('compraManual/emitirEntrada')
            ->with('compra', $compra)
            ->with('cidades', $cidades)
            ->with('naturezas', $naturezas)
            ->with('tiposPagamento', $tiposPagamento)
            ->with('dadosEntrada', $dadosEntrada)
            ->with('produtosInvalidos', $produtosInvalidos)
            ->with('NFeEntradaJS', true)
            ->with('title', 'Emitir NFe Entrada');
        }else{
            return redirect('/403');
        }
    }

    public function gerarEntrada(Request $request){
        $compra = Compra::find($request->compra_id);
        if(valida_objeto($compra)){

            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            if($compra->filial_id != null){
                $config = Filial::findOrFail($compra->filial_id);
                if($config->arquivo_certificado == null){
                    echo "Necessário o certificado para realizar esta ação!";
                    die;
                }
            }
            $isFilial = $compra->filial_id;

            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

            $nfe_service = new NFeEntradaService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id,
                "is_filial" => $isFilial
            ], 55);

            header('Content-type: text/html; charset=UTF-8');
            $natureza = NaturezaOperacao::find($request->natureza);
            
            $nfe = $nfe_service->gerarNFe($compra, $natureza, $request->tipo_pagamento);

            $signed = $nfe_service->sign($nfe['xml']);
            $resultado = $nfe_service->transmitir($signed, $nfe['chave']);
            if(substr($resultado, 0, 4) != 'Erro'){
                $compra->chave = $nfe['chave'];
            // $venda->path_xml = $nfe['chave'] . '.xml';
                $compra->estado = 'APROVADO';
                $compra->numero_emissao = $nfe['nNf'];
                $compra->data_emissao = date('Y-m-d H:i:s');
                
                $compra->save();
                $config->ultimo_numero_nfe = $nfe['nNf'];
                $config->save();
                $this->enviarEmailAutomatico($compra);

                $file = file_get_contents(public_path('xml_entrada_emitida/'.$nfe['chave'].'.xml'));
                importaXmlSieg($file, $this->empresa_id);

                return response()->json($resultado, 200);

            }else{
                $compra->estado = 'REJEITADO';
                $compra->save();
                return response()->json($resultado, 401);

            }
        }else{
            return response()->json("Não permitido!!", 403);

        }
    }

    public function gerarEntradaWithXml(Request $request){
        $compra = Compra::find($request->id);
        if(valida_objeto($compra)){

            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            if($compra->filial_id != null){
                $config = Filial::findOrFail($compra->filial_id);
                if($config->arquivo_certificado == null){
                    echo "Necessário o certificado para realizar esta ação!";
                    die;
                }
            }
            $isFilial = $compra->filial_id;

            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

            $nfe_service = new NFeEntradaService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id,
                "is_filial" => $isFilial
            ], 55);

            header('Content-type: text/html; charset=UTF-8');
            $natureza = NaturezaOperacao::find($compra->natureza_id);
            $xml = $request->xml;
            $exp = simplexml_load_string($xml);
            $array = json_decode(json_encode((array) $exp), true);

            $chave = (string)substr($array['infNFe']['@attributes']['Id'], 3, 47);
            $nNF = (string)$array['infNFe']['ide']['nNF'];

            $signed = $nfe_service->sign($xml);
            // return response()->json($signed, 401);
            $resultado = $nfe_service->transmitir($signed, $chave);
            if(substr($resultado, 0, 4) != 'Erro'){
                $compra->chave = $chave;
            // $venda->path_xml = $nfe['chave'] . '.xml';
                $compra->estado = 'APROVADO';
                $compra->numero_emissao = $nNF;
                $compra->data_emissao = date('Y-m-d H:i:s');
                
                $compra->save();
                $config->ultimo_numero_nfe = $nNF;
                $config->save();
                $this->enviarEmailAutomatico($compra);

                $file = file_get_contents(public_path('xml_entrada_emitida/'.$chave.'.xml'));
                importaXmlSieg($file, $this->empresa_id);

                return response()->json($resultado, 200);

            }else{
                $compra->estado = 'REJEITADO';
                $compra->save();
                return response()->json($resultado, 401);

            }
        }else{
            return response()->json("Não permitido!!", 403);

        }
    }

    public function imprimir($id){
        $compra = Compra::find($id);
        if(valida_objeto($compra)){

            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            $xml = null;
            if(file_exists(public_path('xml_entrada_emitida/').$compra->chave.'.xml')){
                $xml = file_get_contents(public_path('xml_entrada_emitida/').$compra->chave.'.xml');
            }else if(file_exists(public_path('xml_entrada/').$compra->chave.'.xml')){
                $xml = file_get_contents($public.'xml_entrada/'.$compra->chave.'.xml');
            }else{
                session()->flash('mensagem_erro', 'Xml não encontrado!');
                return redirect('/compras');
            }
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();
            
            if($config->logo){
                $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
            }else{
                $logo = null;
            }
        // $docxml = FilesFolders::readFile($xml);

            try {
                $danfe = new Danfe($xml);
                // $id = $danfe->monta($logo);
                $pdf = $danfe->render($logo);
                header('Content-Type: application/pdf');
                // echo $pdf;

                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }  
        }else{
            return redirect('/403');
        }
    }

    public function imprimirCce($id){
        $compra = Compra::
        where('id', $id)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        if($compra->sequencia_cce > 0){

            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if(file_exists($public.'xml_nfe_entrada_correcao/'.$compra->chave.'.xml')){
                $xml = file_get_contents($public.'xml_nfe_entrada_correcao/'.$compra->chave.'.xml');

                $config = ConfigNota::
                where('empresa_id', $this->empresa_id)
                ->first();

                if($config->logo){
                    $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'logos/' . $config->logo));
                }else{
                    $logo = null;
                }

                $dadosEmitente = $this->getEmitente($compra->filial);

                try {
                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);

                    return response($pdf)
                    ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }  
            }else{
                echo "Arquivo XML não encontrado!!";
            }
        }else{
            echo "<center><h1>Este documento não possui evento de correção!<h1></center>";
        }
    }

    private function getEmitente($config = null){
        if($config == null){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();
        }
        return [
            'razao' => $config->razao_social,
            'logradouro' => $config->logradouro,
            'numero' => $config->numero,
            'complemento' => '',
            'bairro' => $config->bairro,
            'CEP' => $config->cep,
            'municipio' => $config->municipio,
            'UF' => $config->UF,
            'telefone' => $config->telefone,
            'email' => ''
        ];
    }

    public function cancelarEntrada(Request $request){

        $compra = Compra::
        where('id', $request->compra_id)
        ->first();

        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $isFilial = $compra->filial_id;
        if($compra->filial_id != null){
            $config = Filial::findOrFail($compra->filial_id);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new NFeEntradaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ], 55);

        $stockMove = new StockMove();
        
        $compra = Compra::find($request->compra_id);
        $nfe = $nfe_service->cancelar($compra, $request->justificativa);
        if($this->isJson($nfe)){

            foreach($compra->itens as $i){
                $stockMove->downStock($i->produto->id, $i->quantidade*$i->produto->conversao_unitaria, $compra->filial_id);
            } 
            $compra->estado = 'CANCELADO';
            $compra->save();

            $file = file_get_contents(public_path('xml_nfe_entrada_cancelada/'.$compra->chave.'.xml'));
            importaXmlSieg($file, $this->empresa_id);
        }
        echo json_encode($nfe);

    }

    public function cartaCorrecao(Request $request){

        $compra = Compra::
        where('id', $request->id)
        ->first();

        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $isFilial = $compra->filial_id;
        if($compra->filial_id != null){
            $config = Filial::findOrFail($compra->filial_id);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);


        $nfe_service = new NFeEntradaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ], 55);

        $nfe = $nfe_service->cartaCorrecao($request->id, $request->correcao);
        echo json_encode($nfe);
    }

    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function produtosSemValidade(){
        $produtos = Produto::select('id')
        ->where('alerta_vencimento', '>', 0)
        ->where('empresa_id', $this->empresa_id)
        ->get();

        $estoque = ItemCompra::where('validade', NULL)
        ->limit(100)->get();

        $itensSemEstoque = [];
        foreach($estoque as $e){
            foreach($produtos as $p){
                if($p->id == $e->produto_id && $e->produto->empresa_id == $this->empresa_id){
                    array_push($itensSemEstoque, $e);
                }
            }
        }

        return view('compraManual/itens_sem_estoque')
        ->with('itens', $itensSemEstoque)
        ->with('title', 'Itens sem Estoque');
    }

    public function salvarValidade(Request $request){
        $tamanhoArray = $request->tamanho_array;
        $contErro = 0;

        for($aux = 0; $aux < $tamanhoArray; $aux++){

            $validade = str_replace("/", "-", $request->input('validade_'.$aux));
            $id = $request->input('id_'.$aux);

            if(strlen($validade) == 10){ // tamanho data ok
                $item = ItemCompra::find($id);
                $dataHoje = strtotime(date('Y-m-d'));
                $validadeForm = strtotime(\Carbon\Carbon::parse($validade)->format('Y-m-d'));
                if($validadeForm > $dataHoje){ // confirma data futura
                    $item->validade = \Carbon\Carbon::parse($validade)->format('Y-m-d');
                    $item->save();
                }else{
                    $contErro++;
                }
            }else{
                $contErro++;
            }
        }
        if($contErro == 0){
            session()->flash('mensagem_sucesso', 'Validades inseridas para os itens!');
        }else{
            session()->flash('mensagem_erro', 'Erro no formulário para os itens abaixo!');
        }
        return redirect('/compras/produtosSemValidade');
        
    }

    public function validadeAlerta(){
        $dataHoje = date('Y-m-d', strtotime("-30 days",strtotime(date('Y-m-d'))));
        $dataFutura = date('Y-m-d', strtotime("+30 days",strtotime(date('Y-m-d'))));
        // $produtos = Produto::select('id')->where('alerta_vencimento', '>', 0)->get();
        $itensCompra = ItemCompra::
        whereBetween('validade', [$dataHoje, $dataFutura])
        ->limit(300)->get();
        $itens = [];
        foreach($itensCompra as $i){
            $strValidade = strtotime($i->validade);
            $strHoje = strtotime(date('Y-m-d'));
            $dif = $strValidade - $strHoje;
            $dif = $dif/24/60/60;

            if($dif <= $i->produto->alerta_vencimento && $i->produto->empresa_id == $this->empresa_id){
                array_push($itens, $i);
            }
        }

        return view('compraManual/validade_alerta')
        ->with('itens', $itens)
        ->with('title', 'Produtos com validade próxima');
    }

    public function xmlTemporaria(Request $request){
        $compra = Compra::find($request->id);

        if(valida_objeto($compra)){

            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            if($compra->filial_id != null){
                $config = Filial::findOrFail($compra->filial_id);
                if($config->arquivo_certificado == null){
                    echo "Necessário o certificado para realizar esta ação!";
                    die;
                }
            }
            $isFilial = $compra->filial_id;

            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

            $nfe_service = new NFeEntradaService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id,
                "is_filial" => $isFilial
            ], 55);

            header('Content-type: text/html; charset=UTF-8');
            $natureza = NaturezaOperacao::find($request->natureza);
            
            $nfe = $nfe_service->gerarNFe($compra, $natureza, $request->tipo_pagamento);
            if(!isset($nfe['erros_xml'])){

                $config = ConfigNota::
                where('empresa_id', $this->empresa_id)
                ->first();
                $public = env('SERVIDOR_WEB') ? 'public/' : '';
                
                if($config->logo){
                    $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'logos/' . $config->logo));
                }else{
                    $logo = null;
                }
                
                $danfe = new Danfe($nfe['xml']);
            // $id = $danfe->monta($logo);
                $pdf = $danfe->render($logo);

                return response($nfe['xml'])
                ->header('Content-Type', 'application/xml');
            }else{
                print_r($nfe['erros_xml']);
            }
        }
    }

    public function danfeTemporaria(Request $request){
        $compra = Compra::find($request->id);

        if(valida_objeto($compra)){

            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            if($compra->filial_id != null){
                $config = Filial::findOrFail($compra->filial_id);
                if($config->arquivo_certificado == null){
                    echo "Necessário o certificado para realizar esta ação!";
                    die;
                }
            }
            $isFilial = $compra->filial_id;

            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

            $nfe_service = new NFeEntradaService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id,
                "is_filial" => $isFilial
            ], 55);

            header('Content-type: text/html; charset=UTF-8');
            $natureza = NaturezaOperacao::find($request->natureza);
            
            $nfe = $nfe_service->gerarNFe($compra, $natureza, $request->tipo_pagamento);
            if(!isset($nfe['erros_xml'])){

                $config = ConfigNota::
                where('empresa_id', $this->empresa_id)
                ->first();
                $public = env('SERVIDOR_WEB') ? 'public/' : '';

                if($config->logo){
                    $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
                }else{
                    $logo = null;
                }

                $danfe = new Danfe($nfe['xml']);
            // $id = $danfe->monta($logo);
                $pdf = $danfe->render($logo);

                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            }else{
                print_r($nfe['erros_xml']);
            }

            // return response($nfe['xml'])
            // ->header('Content-Type', 'application/xml');
        }
    }

    public function consultar(Request $request){

        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $compra = Compra::find($request->compra_id);

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new NFeEntradaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], 55);

        $c = $nfe_service->consultar($compra);
        echo json_encode($c);

    }

    public function salvarChaveRef(Request $request){
        try{

            CompraReferencia::create([
                'compra_id' => $request->compra_id,
                'chave' => $request->chave
            ]);
            session()->flash('mensagem_sucesso', "Chave referenciada!");
        }catch(\Exception $e){
            session()->flash('mensagem_erro', "Erro: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function deleteChave($id){
        try{

            CompraReferencia::find($id)->delete();
            session()->flash('mensagem_sucesso', "Chave removida!");
        }catch(\Exception $e){
            session()->flash('mensagem_erro', "Erro: " . $e->getMessage());
        }
        return redirect()->back();
    }

    private function enviarEmailAutomatico($compra){
        $escritorio = EscritorioContabil::
        where('empresa_id', $this->empresa_id)
        ->first();

        if($escritorio != null && $escritorio->envio_automatico_xml_contador){
            $email = $escritorio->email;
            Mail::send('mail.xml_automatico', ['descricao' => 'Envio de NFe Entrada'], function($m) use ($email, $compra){
                $nomeEmpresa = env('MAIL_NAME');
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $emailEnvio = env('MAIL_USERNAME');

                $m->from($emailEnvio, $nomeEmpresa);
                $m->subject('Envio de XML Automático');

                $m->attach(public_path('xml_entrada_emitida/'.$compra->chave.'.xml'));
                $m->to($email);
            });
        }
    }

    public function estadoFiscal($id){
        $compra = Compra::
        where('id', $id)
        ->first();
        $value = session('user_logged');
        if($value['adm'] == 0) return redirect()->back();
        if(valida_objeto($compra)){

            return view("compraManual/alterar_estado_fiscal")
            ->with('compra', $compra)
            ->with('title', "Alterar estado compra $id");
        }else{
            return redirect('/403');
        }
    }

    public function estadoFiscalStore(Request $request){
        try{
            $compra = Compra::find($request->compra_id);
            $estado = $request->estado;
            
            $compra->estado = $estado;
            if ($request->hasFile('file')){
                $public = env('SERVIDOR_WEB') ? 'public/' : '';

                $xml = simplexml_load_file($request->file);
                $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
                $file = $request->file;
                $file->move(public_path('xml_entrada_emitida'), $chave.'.xml');
                $compra->chave = $chave;
                $compra->numero_emissao = (int)$xml->NFe->infNFe->ide->nNF;
                
            }

            $compra->save();
            session()->flash("mensagem_sucesso", "Estado alterado");

        }catch(\Exception $e){
            session()->flash("mensagem_erro", "Erro: " . $e->getMessage());

        }
        return redirect()->back();
    }

    public function setNaturezaPagamento(Request $request){
        $compra = Compra::find($request->id);

        $compra->tipo_pagamento = $request->tipo_pagamento;
        $compra->natureza_id = $request->natureza_id;
        $compra->save();
        return "sucesso";
    }

    public function print($id){
        $compra = Compra::find($id);
        if(valida_objeto($compra)){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();
            $p = view('compraManual/print')
            ->with('config', $config)
            ->with('compra', $compra);
            // return $p;

            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($p);

            $pdf = ob_get_clean();

            $domPdf->setPaper("A4");
            $domPdf->render();
            $domPdf->stream("Pedido de Compra $id.pdf", array("Attachment" => false));
        }else{
            return redirect('/403');
        }
    }

    public function print80($id){
        $compra = Compra::findOrFail($id);
        if(valida_objeto($compra)){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            $cupom = new CompraPrint80($compra);
            $cupom->monta();
            $pdf = $cupom->render();
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        }else{
            return redirect('/403');
        }
    }

    public function etiqueta($id){
        $compra = Compra::findOrFail($id);
        if(valida_objeto($compra)){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            $padrosEtiqueta = Etiqueta::
            where('empresa_id', null)
            ->orWhere('empresa_id', $this->empresa_id)
            ->get();
            
            return view('compraManual/etiqueta')
            ->with('compra', $compra)
            ->with('padrosEtiqueta', $padrosEtiqueta)
            ->with('title', 'Gerar Etiqueta');
        }else{
            return redirect('/403');
        }
    }

    public function etiquetaStore(Request $request){

        $this->_validateEtiqueta($request);

        // print_r($request->all());
        // die;
        try{

            $files = glob(public_path("barcode/*")); 

            foreach($files as $file){ 
                if(is_file($file)) {
                    unlink($file); 
                }
            }

            $compra = Compra::findOrFail($request->compra_id);
            $data = [];
            $cont = 0;
            foreach($compra->itens as $it){
                if($request['prod_select_'.$it->id]){
                    $cont++;
                    $produto = $it->produto;
                    $nome = $produto->nome . " " . $produto->str_grade;
                    $codigo = $produto->codBarras;
                    $valor = $produto->valor_venda;
                    $unidade = $produto->unidade_venda;

                    if($codigo == "" || $codigo == "SEM GTIN" || $codigo == "sem gtin"){
                        session()->flash('mensagem_erro', "Produto $nome sem código de barras definido");
                        return redirect()->back();
                    }

                    $rand = rand(1000, 9999);
                    $item = [
                        'nome_empresa' => $request->nome_empresa ? true : false,
                        'nome_produto' => $request->nome_produto ? true : false,
                        'valor_produto' => $request->valor_produto ? true : false,
                        'cod_produto' => $request->cod_produto ? true : false,
                        'codigo_barras_numerico' => $request->codigo_barras_numerico ? true : false,
                        'nome' => $nome,
                        'codigo_barras' => $codigo,
                        'codigo' => $produto->id . ($produto->referencia != '' ? ' | REF'.$produto->referencia : ''),
                        'valor' => $valor,
                        'unidade' => $unidade,
                        'empresa' => $produto->empresa->nome,
                        'rand' => $rand
                    ];

                    $generatorPNG = new \Picqer\Barcode\BarcodeGeneratorPNG();

                    $bar_code = $generatorPNG->getBarcode($codigo, $generatorPNG::TYPE_EAN_13);

                    file_put_contents(public_path("barcode")."/$rand.png", $bar_code);

                    for($i=0; $i<$it->quantidade; $i++){
                        array_push($data, $item);
                    }
                }
            }

            if($cont == 0){
                session()->flash('mensagem_erro', 'Seleceione ao menos um produto para imprimir');
                return redirect()->back();
            }

            $qtdLinhas = $request->qtd_linhas;
            $qtdTotal = $request->qtd_etiquetas;

            return view('compraManual/print_etiqueta')
            ->with('altura', $request->altura)
            ->with('largura', $request->largura)
            ->with('codigo', $codigo)
            ->with('quantidade', $qtdTotal)
            ->with('distancia_topo', $request->dist_topo)
            ->with('distancia_lateral', $request->dist_lateral)
            ->with('quantidade_por_linhas', $qtdLinhas)
            ->with('tamanho_fonte', $request->tamanho_fonte)
            ->with('tamanho_codigo', $request->tamanho_codigo)
            ->with('data', $data);
        }catch(\Exception $e){
            session()->flash('mensagem_erro', 'Erro: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    private function _validateEtiqueta(Request $request){
        $rules = [
            'largura' => 'required',
            'altura' => 'required',
            'qtd_linhas' => 'required',
            'dist_lateral' => 'required',
            'dist_topo' => 'required',
            'tamanho_fonte' => 'required',
            'tamanho_codigo' => 'required',
        ];

        $messages = [
            'largura.required' => 'Campo obrigatório.',
            'altura.required' => 'Campo obrigatório.',
            'qtd_linhas.required' => 'Campo obrigatório.',
            'dist_lateral.required' => 'Campo obrigatório.',
            'dist_topo.required' => 'Campo obrigatório.',
            'qtd_etiquetas.required' => 'Campo obrigatório.',
            'tamanho_fonte.required' => 'Campo obrigatório.',
            'tamanho_codigo.required' => 'Campo obrigatório.',

        ];
        $this->validate($request, $rules, $messages);
    }

    public function editXml(Request $request){
        $id = $request->id;
        $natureza = $request->natureza;
        $tipo_pagamento = $request->tipo_pagamento;


        $item = Compra::findOrFail($id);

        $item->tipo_pagamento = $request->tipo_pagamento;
        $item->natureza_id = $request->natureza;
        $item->save();

        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        if($item->filial_id != null){
            $config = Filial::findOrFail($item->filial_id);
            if($config->arquivo_certificado == null){
                echo "Necessário o certificado para realizar esta ação!";
                die;
            }
        }
        $isFilial = $item->filial_id;

        $nfe_service = new NFeEntradaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ], 55);

        $natureza = NaturezaOperacao::find($request->natureza);
        $nfe = $nfe_service->gerarNFe($item, $natureza, $request->tipo_pagamento);
        
        if(!isset($nfe['erros_xml'])){
            $xml = $nfe['xml'];

            return view('compraManual.edit_xml', compact('item', 'xml'))
            ->with('title', 'Editando XML');
        }else{
            print_r($nfe['erros_xml']);
        }
    }

    public function setarValidade($id){
        $item = Compra::findOrFail($id);

        return view('compraManual/setar_validade')
        ->with('item', $item)
        ->with('title', 'Setar validade dos itens');
    }

    public function setarValidadeStore(Request $request){
        try{
            for($i=0; $i<sizeof($request->validade); $i++){
                $item = ItemCompra::findOrFail($request->item_id[$i]);
                $item->validade = $request->validade[$i];
                $item->save();
            }
            session()->flash('mensagem_sucesso', 'Validade setada!');
            return redirect('/compras');
        }catch(\Exception $e){
            session()->flash('mensagem_erro', 'Algo deu errado ' . $e->getMessage());
            return redirect()->back();
        }

    }

    public function comprasSemValidade(){
        $data = Compra::where('empresa_id', $this->empresa_id)
        ->orderBy('compras.id', 'desc')
        ->get();

        $compras = [];
        foreach($data as $item){
            if($item->verificaValidade()){
                array_push($compras, $item);
            }
        }
        if(sizeof($compras) == 0){
            return response()->json("err", 401);
        }
        return view('compraManual.sem_validade', compact('compras'));
    }

    public function alertaValidade(Request $request){
        $produtos = Produto::where('empresa_id', $this->empresa_id)
        ->where('alerta_vencimento', '>', 0)
        ->get();

        $data = [];
        foreach($produtos as $p){
            $item = ItemCompra::where('produto_id', $p->id)
            ->where('validade', '!=', null)
            ->first();

            $strValidade = strtotime($item->validade);
            $strHoje = strtotime(date('Y-m-d'));
            $dif = $strValidade - $strHoje;
            $dif = $dif/24/60/60;
            $item->dif = (int)$dif;

            if($dif <= $p->alerta_vencimento){
                array_push($data, $item);
            }
        }

        if(sizeof($data) == 0){
            return response()->json("err", 401);
        }
        return view('compraManual.produtos_alerta_validade', compact('data'));

        // return response()->json($data, 200);
    }

    public function alertaEstoque(Request $request){
        $produtos = Produto::where('empresa_id', $this->empresa_id)
        ->where('estoque_minimo', '>', 0)
        ->get();

        $data = [];
        foreach($produtos as $p){
            if($p->estoque){
                if($p->estoque->quantidade <= $p->estoque_minimo){
                    array_push($data, $p);
                }
            }else{
                array_push($data, $p);
            }
        }

        if(sizeof($data) == 0){
            return response()->json("err", 401);
        }
        return view('compraManual.produtos_alerta_estoque', compact('data'));

        return response()->json($data, 200);
    }

    public function imprimirAlertaEstoque(){
        $produtos = Produto::where('empresa_id', $this->empresa_id)
        ->where('estoque_minimo', '>', 0)
        ->get();

        $data = [];
        foreach($produtos as $p){
            if($p->estoque){
                if($p->estoque->quantidade <= $p->estoque_minimo){
                    array_push($data, $p);
                }
            }else{
                array_push($data, $p);
            }
        }

        if(sizeof($data) == 0){
            return response()->json("err", 401);
        }
        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();
        $p = view('compraManual/print_estoque')
        ->with('config', $config)
        ->with('data', $data);

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Alerta de estoque.pdf", array("Attachment" => false));

    }

    public function setDadosImportacaoItem(Request $request){

        $item = ItemCompra::findOrFail($request->item_id);
        try{

            $item->nDI = $request->nDI;
            $item->dDI = $request->dDI;
            $item->cidade_desembarque_id = $request->cidade_desembarque_id;
            $item->dDesemb = $request->dDesemb;
            $item->tpViaTransp = $request->tpViaTransp;
            $item->vAFRMM = __replace($request->vAFRMM);
            $item->tpIntermedio = $request->tpIntermedio;
            $item->documento = $request->documento;
            $item->UFTerceiro = $request->UFTerceiro;
            $item->cExportador = $request->cExportador;
            $item->nAdicao = $request->nAdicao;
            $item->cFabricante = $request->cFabricante;
            
            $item->save();
            session()->flash('mensagem_sucesso', 'Dados definidos para o item!');
        }catch(\Exception $e){
            session()->flash('mensagem_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->back();
    }
}

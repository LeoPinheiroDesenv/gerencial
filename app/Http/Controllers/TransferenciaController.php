<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filial;
use App\Models\Estoque;
use App\Models\ConfigNota;
use App\Models\Usuario;
use App\Models\NaturezaOperacao;
use App\Models\Transportadora;
use App\Models\Produto;
use App\Models\Transferencia;
use App\Models\ItemTransferencia;
use App\Helpers\StockMove;
use DB;
use App\Prints\ComprovanteTransferencia;
use Dompdf\Dompdf;
use App\Services\TransferenciaService;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\Daevento;

class TransferenciaController extends Controller
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

        $filiais = Filial::where('empresa_id', $this->empresa_id)
        ->where('status',1)->get();

        if(sizeof($filiais) == 0){
            session()->flash('mensagem_erro', 'É necessário ter ao menos uma filial ativa!');
            return redirect()->back();
        }
        $usuario = Usuario::findOrFail(get_id_user());
        $locaisUsuario = __locaisAtivosUsuario($usuario);

        if(sizeof($locaisUsuario) < 2){
            session()->flash('mensagem_erro', 'É necessário estar vinculado a mais de um local para transferência');
            return redirect()->back();
        }

        return view('transferencias.index', compact('filiais'))
        ->with('title', 'Transferência de estoque');
    }

    public function store(Request $request){
        $entrada = $request->entrada;
        $saida = $request->saida;

        if($entrada == $saida){
            session()->flash('mensagem_erro', 'Selecione corretamente os locais!');
            return redirect()->back();
        }
        // dd($request->all());

        for($i=0; $i<sizeof($request->produto); $i++){

            $produto = $request->produto[$i];
            $quantidade = $request->quantidade[$i];

            $prod = Produto::findOrFail($produto);

            $estoqueAtual = Estoque
            ::where('produto_id', $produto)
            ->when($saida != -1, function ($query) use ($saida) {
                return $query->where('filial_id', $saida);
            })
            ->first();

            if($estoqueAtual == null || $estoqueAtual->quantidade < $quantidade){
                session()->flash('mensagem_erro', 'Estoque insuficiente do produto ' . $prod->nome);
                return redirect()->back();
            }
        }


        try{
            DB::transaction(function () use ($request) {
                $entrada = $request->entrada;
                $saida = $request->saida;

                $entrada = $request->entrada > 0 ? $request->entrada : null;
                $saida = $request->saida > 0 ? $request->saida : null;
                $item = Transferencia::create([
                    'empresa_id' => $this->empresa_id,
                    'filial_saida_id' => $saida,
                    'filial_entrada_id' => $entrada,
                    'observacao' => $request->observacao ?? '',
                    'usuario_id' => get_id_user()
                ]);

                $stockMove = new StockMove();

                for($i=0; $i<sizeof($request->produto); $i++){

                    $produto = $request->produto[$i];
                    $quantidade = __replace($request->quantidade[$i]);

                    $estoqueAtual = Estoque
                    ::where('produto_id', $produto)
                    ->when($saida != -1, function ($query) use ($saida) {
                        return $query->where('filial_id', $saida);
                    })
                    ->first();

                    $p = $estoqueAtual->produto;
                    $locais = json_decode($p->locais);
                    array_push($locais, $entrada);

                    if(!in_array($entrada, $locais)){
                        array_push($locais, $entrada);
                    }
                    $p->locais = $locais;
                    $p->save();

                    $stockMove->downStock($produto, $quantidade, $saida);
                    $stockMove->pluStock($produto, $quantidade, -1, $entrada);

                    ItemTransferencia::create([
                        'transferencia_id' => $item->id,
                        'produto_id' => $produto,
                        'quantidade' => $quantidade,
                    ]);

                }
            });
            session()->flash("mensagem_sucesso", "Transferência realizada!");

        }catch(\Exception $e){
            // echo $e->getMessage();
            // die;
            __saveError($e, $this->empresa_id);
            session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->back();

    }

    public function list(){
        $data = Transferencia::where('empresa_id', $this->empresa_id)
        ->orderBy('id', 'desc')
        ->paginate(50);

        return view('transferencias.list', compact('data'))
        ->with('links', 1)
        ->with('title', 'Lista de transferências');
    }

    public function search(Request $request){
        $pesquisa = $request->pesquisa;
        $data = Transferencia::where('transferencias.empresa_id', $this->empresa_id)
        ->select('transferencias.*')
        ->orderBy('id', 'desc')
        ->join('produtos', 'produtos.id', '=', 'transferencias.produto_id')
        ->when($pesquisa, function ($query) use ($pesquisa) {
            return $query->where('produtos.nome', 'like', "%$pesquisa%");
        })
        ->get();

        return view('transferencias.list', compact('data'))
        ->with('pesquisa', $pesquisa)
        ->with('title', 'Lista de transferências');
    }

    public function view($id){
        $item = Transferencia::findOrFail($id);

        $naturezas = NaturezaOperacao::
        where('empresa_id', $this->empresa_id)
        ->get();

        $transportadoras = Transportadora::
        where('empresa_id', $this->empresa_id)
        ->get();

        return view('transferencias.view', compact('item', 'naturezas', 'transportadoras'))
        ->with('title', 'Transferência');
    }

    public function print($id){
        $item = Transferencia::findOrFail($id);

        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $p = view('transferencias/print', compact('item', 'config'));
        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Comprovante de Transferencia $id.pdf", array("Attachment" => false));

        // $cupom = new ComprovanteTransferencia($item);
        // $cupom->monta();
        // $pdf = $cupom->render();

        // return response($pdf)
        // ->header('Content-Type', 'application/pdf');

    }

    public function updateFiscal(Request $request, $id){
        $item = Transferencia::findOrFail($id);
        try{

            $item->natureza_id = $request->natureza_id;
            $item->finNFe = $request->finNFe;
            $item->tpNF = $request->tpNF;
            $item->transportadora_id = $request->transportadora_id;
            $item->save();
            session()->flash("mensagem_sucesso", "Dados salvos!");
        }catch(\Exception $e){

            session()->flash("mensagem_erro", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function xmlTemp($id){
        $item = Transferencia::findOrFail($id);
        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $isFilial = $item->filial_id;
        if($item->filial_id == null){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();
        }else{
            $config = Filial::findOrFail($item->filial_id);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new TransferenciaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ]);
        $nfe = $nfe_service->gerarNFe($item);
        if(!isset($nfe['erros_xml'])){
            $xml = $nfe['xml'];

            
            return response($xml)
            ->header('Content-Type', 'application/xml');
            
        }else{
            print_r($nfe['erros_xml']);
        }
    }

    public function transmitirNfe(Request $request){
        $item = Transferencia::findOrFail($request->transferencia_id);
        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $isFilial = $item->filial_id;
        if($item->filial_id == null){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();
        }else{
            $config = Filial::findOrFail($item->filial_id);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new TransferenciaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ]);
        if($item->estado == 'rejeitado' || $item->estado == 'novo'){

            $nfe = $nfe_service->gerarNFe($item);
            if(!isset($nfe['erros_xml'])){
            // file_put_contents('xml/teste2.xml', $nfe['xml']);
            // return response()->json($nfe, 200);
                $signed = $nfe_service->sign($nfe['xml']);
                $resultado = $nfe_service->transmitir($signed, $nfe['chave'], $item->id);

                if(substr($resultado, 0, 4) != 'Erro'){
                    $item->chave = $nfe['chave'];
                    $item->estado = 'aprovado';
                    $item->data_emissao = date('Y-m-d H:i:s');

                    $item->numero_nfe = $nfe['nNf'];
                    $item->save();

                    $config->ultimo_numero_nfe = $nfe['nNf'];
                    $config->save();

                    $file = file_get_contents(public_path('xml_nfe/'.$nfe['chave'].'.xml'));
                    importaXmlSieg($file, $this->empresa_id);

                }else{
                    if($item->signed_xml == null){
                        $item->signed_xml = $signed;
                    }
                    $item->estado = 'rejeitado';
                    $item->chave = $nfe['chave'];
                    $item->save();
                }
                echo json_encode($resultado);
            }else{
                return response()->json($nfe['erros_xml'], 401);
            }

        }else{
            echo json_encode("Apro");
        }
    }

    public function danfeTemp($id){
        $item = Transferencia::findOrFail($id);
        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $isFilial = $item->filial_id;
        if($item->filial_id == null){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();
        }else{
            $config = Filial::findOrFail($item->filial_id);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new TransferenciaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => $isFilial
        ]);
        $nfe = $nfe_service->gerarNFe($item);
        if(!isset($nfe['erros_xml'])){
            $xml = $nfe['xml'];

            if($config->logo){
                $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
            }else{
                $logo = null;
            }

            try {
                $danfe = new Danfe($xml);
                    // $id = $danfe->monta();
                $danfe->setVUnComCasasDec($config->casas_decimais);

                $pdf = $danfe->render();
                header("Content-Disposition: ; filename=DANFE Temporária.pdf");
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            } 
        }else{
            print_r($nfe['erros_xml']);
        }
    }

    public function imprimirNfe($id){

        $item = Transferencia::
        where('id', $id)
        ->where('empresa_id', $this->empresa_id)
        ->first();
        if(valida_objeto($item)){

            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            if(file_exists(public_path('xml_nfe/').$item->chave.'.xml')){
                $xml = file_get_contents(public_path('xml_nfe/').$item->chave.'.xml');
                if($config->logo){
                    $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
                }else{
                    $logo = null;
                }

                try {
                    $danfe = new Danfe($xml);
                    $danfe->setVUnComCasasDec($config->casas_decimais);
                    
                    // $id = $danfe->monta($logo);
                    $pdf = $danfe->render($logo);
                    header("Content-Disposition: ; filename=DANFE $item->numero_nfe.pdf");
                    return response($pdf)
                    ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }  
            }else{
                echo "Arquivo XML não encontrado!!";
            }
        }else{
            return redirect('/403');
        }
    }

    public function corrigirNfe(Request $request){

        $item = Transferencia::
        where('id', $request->transferencia_id)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $isFilial = $item->filial_saida;
        if($item->filial_saida != null){
            $config = Filial::findOrFail($item->filial_saida);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new TransferenciaService([
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
        ]);

        $nfe = $nfe_service->cartaCorrecao($item, $request->correcao);
        echo json_encode($nfe);
    }

    public function cancelarNfe(Request $request){
        $item = Transferencia::
        where('id', $request->transferencia_id)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        $config = ConfigNota::
        where('empresa_id', $this->empresa_id)
        ->first();

        $isFilial = $item->filial_saida;
        if($item->filial_saida != null){
            $config = Filial::findOrFail($item->filial_saida);
        }

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfe_service = new TransferenciaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ]);

        $nfe = $nfe_service->cancelar($item, $request->justificativa);

        if(!isset($nfe['erro'])){

            $item->estado = 'cancelado';
            $item->save();

            $file = file_get_contents(public_path('xml_nfe_cancelada/'.$item->chave.'.xml'));
            importaXmlSieg($file, $this->empresa_id);

            return response()->json($nfe, 200);

        }else{
            return response()->json($nfe['data'], $nfe['status']);
        }
    }

    public function imprimirCorrecao($id){
        $item = Transferencia::
        where('id', $id)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        if($item->sequencia_cce > 0){

            if(file_exists(public_path('xml_nfe_correcao/').$item->chave.'.xml')){
                $xml = file_get_contents(public_path('xml_nfe_correcao/').$item->chave.'.xml');

                $config = ConfigNota::
                where('empresa_id', $this->empresa_id)
                ->first();

                if($config->logo){
                    $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
                }else{
                    $logo = null;
                }

                $dadosEmitente = $this->getEmitente($item->filial);

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

    public function imprimirCancela($id){
        $item = Transferencia::
        where('id', $id)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        if($item->estado == 'cancelado'){
            try {
                if(file_exists(public_path('xml_nfe_cancelada/').$item->chave.'.xml')){
                    $xml = file_get_contents(public_path('xml_nfe_cancelada/').$item->chave.'.xml');

                    $config = ConfigNota::
                    where('empresa_id', $this->empresa_id)
                    ->first();

                    if($config->logo){
                        $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('logos/') . $config->logo));
                    }else{
                        $logo = null;
                    }

                    $dadosEmitente = $this->getEmitente($item->filial);

                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);

                    return response($pdf)
                    ->header('Content-Type', 'application/pdf');
                }else{
                    echo "Arquivo XML não encontrado!!";
                }
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }  
        }else{
            echo "<center><h1>Este documento não possui evento de cancelamento!<h1></center>";
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

}

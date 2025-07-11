<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\MercadoLivreUtil;
use App\Models\MercadoLivreConfig;
use App\Models\PedidoMercadoLivre;
use App\Models\ItemPedidoMercadoLivre;
use App\Models\Produto;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\Transportadora;
use App\Models\NaturezaOperacao;
use App\Models\Empresa;
use App\Models\Frete;
use App\Models\Venda;
use App\Models\ItemVenda;
use NFePHP\DA\NFe\Danfe;
use App\Helpers\StockMove;

class PedidoMercadoLivreController extends Controller
{
    protected $util;
    public function __construct(MercadoLivreUtil $util)
    {
        $this->util = $util;
    }

    private function __validaToken(){
        $retorno = $this->util->refreshToken(request()->empresa_id);
        if($retorno != 'token valido!'){
            if(!isset($retorno->access_token)){
                dd($retorno);
            }
        }
    }

    public function index(Request $request){
        $this->getPedidos($request);

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_nome = $request->get('cliente_nome');
        $data = PedidoMercadoLivre::where('empresa_id', $request->empresa_id)
        ->orderBy('id', 'desc')
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data_pedido', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('data_pedido', '<=', $end_date);
        })
        ->when(!empty($cliente_nome), function ($query) use ($cliente_nome) {
            return $query->where('cliente_nome', 'LIKE', "%$cliente_nome%");
        })
        ->paginate(30);

        return view('mercado_livre_pedidos.index', compact('data', 'end_date', 'start_date', 'cliente_nome'));
    }

    private function getPedidos($request){
        $this->__validaToken();
        $curl = curl_init();
        $config = MercadoLivreConfig::where('empresa_id', $request->empresa_id)
        ->first();

        curl_setopt($curl, CURLOPT_URL, 
            "https://api.mercadolibre.com/orders/search?seller=$config->user_id");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
            'Content-Type: application/json'
        ]);

        $res = curl_exec($curl);
        $retorno = json_decode($res);
        // dd($retorno);
        if(isset($retorno->results)){
            foreach($retorno->results as $pedido){
                $this->util->criaPedido($request->empresa_id, $pedido);
            }
        }
    }

    public function show($id){
        $item = PedidoMercadoLivre::findOrFail($id);
        return view('mercado_livre_pedidos.show', compact('item'));
    }

    public function chat($id){
        $this->__validaToken();

        $item = PedidoMercadoLivre::findOrFail($id);
        $chat = $this->getChat($item);

        $notaEmitida = false;
        if($item->nfe){
            if($item->nfe->chave != null && $item->nfe->estado == 'aprovado'){
                $notaEmitida = true;
            }
        }
        $messages = null;
        if(isset($chat->messages)){
            $messages = $chat->messages;
            foreach($messages as $m){
                $str = strtotime($m->message_date->created);
                $m->_date = date('d/m/Y H:i', $str);
            }
        }
        // dd($messages);

        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)
        ->first();
        return view('mercado_livre_pedidos.chat', compact('item', 'messages', 'notaEmitida', 'config'));
    }

    public function chatSend(Request $request, $id){
        $this->__validaToken();
        $item = PedidoMercadoLivre::findOrFail($id);
        $retorno = $this->enviarMensagem($item, $request->mensagem);
        try{
            if($retorno->status  == 'available'){
                session()->flash("mensagem_sucesso", "Mensagem enviada");
            }else{
                session()->flash("mensagem_erro", "Algo deu errado!");
            }
        }catch(\Exception $e){
            session()->flash("mensagem_erro", "Algo deu errado!");
        }
        return redirect()->back();
    }

    public function chatSendNfe($id){
        $item = PedidoMercadoLivre::findOrFail($id);
        $chave = $item->nfe->chave;
        if (file_exists(public_path('xml_nfe/') . $chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $chave . '.xml');

            $config = MercadoLivreConfig::where('empresa_id', request()->empresa_id)
            ->first();

            $danfe = new Danfe($xml);
            $pdf = $danfe->render();
            
            file_put_contents(public_path('danfe_temp/') . $chave . '.pdf', $pdf);
            $pathFileDanfe = public_path("/danfe_temp/")."$chave.pdf";
            $retorno = $this->uploadFileDanfe($pathFileDanfe, $item);

            try{
                if($retorno->status  == 'available'){
                    session()->flash("mensagem_sucesso", "Mensagem enviada");
                }else{
                    session()->flash("mensagem_erro", "Algo deu errado!");
                }
            }catch(\Exception $e){
                session()->flash("mensagem_erro", "Algo deu errado!");
            }
            return redirect()->back();

        } else {
            session()->flash("mensagem_erro", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    private function uploadFileDanfe($pathFile, $item){
        $curl = curl_init();
        $config = MercadoLivreConfig::where('empresa_id', request()->empresa_id)
        ->first();

        $cfile = curl_file_create($pathFile);
        $postData = array('file'=> $cfile);
        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/messages/attachments?tag=post_sale&site_id=MLB");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, ($postData));

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
            'content-type: multipart/form-data;',
        ]);
        $res = curl_exec($curl);
        $retorno = json_decode($res);
        if(isset($retorno->id)){
            $retorno = $this->enviarMensagem($item, 'DANFE', $retorno->id);
        }
        return $retorno;

    }

    private function getChat($item){
        $curl = curl_init();
        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)
        ->first();

        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/messages/packs/$item->_id/sellers/".
            "$config->user_id?tag=post_sale&site_id=MLB");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
            'Content-Type: application/json'
        ]);
        $res = curl_exec($curl);
        $retorno = json_decode($res);
        return $retorno;
    }

    private function enviarMensagem($item, $mensagem, $pathFile = null){

        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)
        ->first();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/orders/$item->_id");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
            'Content-Type: application/json'
        ]);
        $res = curl_exec($curl);
        $retorno = json_decode($res);

        if(!isset($retorno->buyer)){
            session()->flash("mensagem_erro", "Não foi possível buscar os dados do cliente");
            return redirect()->back();
        }
        $client_id = $retorno->buyer->id;

        $curl = curl_init();
        $dataMercadoLivre = [
            'text' => $mensagem
        ];

        $dataMercadoLivre['from'] = [
            'user_id' => $config->user_id
        ];
        $dataMercadoLivre['to'] = [
            'user_id' => $client_id
        ];

        if($pathFile){
            $dataMercadoLivre['attachments'] = [$pathFile];
        }
        // echo json_encode($dataMercadoLivre);
        // die;
        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/messages/packs/$item->_id/sellers/".
            "$config->user_id?tag=post_sale");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataMercadoLivre));

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
            'Content-Type: application/json',
            'cache-control: no-cache'
        ]);
        $res = curl_exec($curl);
        $retorno = json_decode($res);
        return $retorno;
        // dd($retorno);
    }

    public function gerarNfe($id)
    {
        $pedido = PedidoMercadoLivre::findOrFail($id);

        if(!$pedido->cliente){
            session()->flash("mensagem_erro", "Cliente não cadastrado no sistema");
            return redirect()->back();
        }
        $cliente = $pedido->cliente;

        $config = ConfigNota::
        where('empresa_id', request()->empresa_id)
        ->first();

        $naturezas = NaturezaOperacao::
        where('empresa_id', request()->empresa_id)
        ->get();

        $transportadoras = Transportadora::
        where('empresa_id', request()->empresa_id)
        ->get();

        $cidades = Cidade::all();

        $erros = [];

        $doc = $pedido->cliente->cpf_cnpj;

        if(strlen($doc) == 14){
            if(!$this->validaCPF($doc)){
                array_push($erros, "CPF cliente inválido");
            }
        }

        if(strlen($doc) == 18){
            if(!$this->validaCNPJ($doc)){
                array_push($erros, "CNPJ cliente inválido");
            }
        }

        if($pedido->cliente->cidade_id == 1){
            array_push($erros, "Cidade cliente inválida");
        }

        return view('mercado_livre_pedidos/emitir_nfe')
        ->with('pedido', $pedido)
        ->with('erros', $erros)
        ->with('cidades', $cidades)
        ->with('naturezas', $naturezas)
        ->with('transportadoras', $transportadoras)
        ->with('title', 'Emitir NFe');

    }

    public function salvarVenda(Request $request){
        $pedido = PedidoMercadoLivre::find($request->id);

        $transportadora = $request->transportadora ?? NULL;
        $natureza = $request->natureza;

        $tipoPagamento = $request->forma_pagamento;

        $frete = null;
        if($request->frete != '9'){
            $frete = Frete::create([
                'placa' => $request->placa ?? '',
                'valor' => __replace($request->valor_frete),
                'tipo' => $request->frete,
                'qtdVolumes' => $request->qtd_volumes ?? 1,
                'uf' => $request->uf_placa ? $request->uf_placa : '',
                'numeracaoVolumes' => $request->numeracao_volumes ?? 1,
                'especie' => $request->especie ?? '',
                'peso_liquido' => $request->peso_liquido ? __replace($request->peso_liquido) : 0,
                'peso_bruto' => $request->peso_bruto ? __replace($request->peso_bruto) : 0
            ]);
        }

        $dataVenda = [
            'cliente_id' => $pedido->cliente->id,
            'usuario_id' => get_id_user(),
            'frete_id' => $frete != null ? $frete->id : null,
            'valor_total' => $pedido->total,
            'forma_pagamento' => 'a_vista',
            'NfNumero' => 0,
            'natureza_id' => $natureza,
            'chave' => '',
            'path_xml' => '',
            'estado' => 'DISPONIVEL',
            'observacao' => '',
            'desconto' => 0,
            'transportadora_id' => $transportadora,
            'sequencia_cce' => 0,
            'tipo_pagamento' => $tipoPagamento,
            'empresa_id' => $request->empresa_id,
            'pedido_mercado_livre_id' => $pedido->id,
        ];

        $venda = Venda::create($dataVenda);

        $pedido->venda_id = $venda->id;
        $pedido->save();

        $stockMove = new StockMove();
        foreach($pedido->itens as $i){
            $dataItem = [
                'produto_id' => $i->produto->id,
                'venda_id' => $venda->id,
                'quantidade' => $i->quantidade,
                'valor' => $i->valor_unitario,
                'valor_custo' => $i->produto->valor_compra
            ];

            $stockMove->downStock($i->produto->id, $i->quantidade,);

            $item = ItemVenda::create($dataItem);
        }

        session()->flash("mensagem_sucesso", "Venda de pedido gerada com sucesso!");
        return redirect('/vendas');
    }

    public function setCliente(Request $request, $id){
        $item = PedidoMercadoLivre::findOrFail($id);
        $cliente = Cliente::findOrFail($request->cliente_id);

        if($cliente){
            $item->cliente_nome = $cliente->razao_social;
            $item->cliente_documento = $cliente->cpf_cnpj;
            $item->cliente_id = $cliente->id;
            $item->save();
            session()->flash("mensagem_sucesso", "Cliente alterado!");
        }
        return redirect()->back();
    }

    public function downloadChat($id){
        $this->__validaToken();
        $curl = curl_init();
        $config = MercadoLivreConfig::where('empresa_id', request()->empresa_id)
        ->first();

        curl_setopt($curl, CURLOPT_URL, 
            "https://api.mercadolibre.com/messages/attachments/$id?tag=post_sale&site_id=MLA");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
        ]);

        $res = curl_exec($curl);
        $retorno = json_decode($res);
        dd($retorno);

    }
}

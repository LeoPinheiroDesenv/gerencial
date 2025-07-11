<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\ClienteDelivery;
use App\Models\Produto;
use App\Models\ItemVendaCaixa;
use App\Models\ItemVenda;
use App\Models\PedidoDelivery;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Usuario;
use App\Models\Aviso;
use App\Models\AvisoAcesso;
use App\Models\Orcamento;
use App\Models\PlanoEmpresa;
use App\Models\RemessaNfe;
use App\Models\ConfigNota;

class HomeController extends Controller
{
    protected $empresa_id = null;
    protected $acesso_financeiro = false;

    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if(session('user_contador')){
                return redirect('/contador');
            }

            if(!$value){
                return redirect("/login");
            }else{
                $usuario = Usuario::find($value['id']);
                $permissao = json_decode($usuario->permissao);
                // print_r($permissao);
                if(in_array("/contasPagar", $permissao) || in_array("/contasReceber", $permissao)){
                    $this->acesso_financeiro = true;
                }
            }
            return $next($request);
        });
    }

    private function setMaskDoc($doc){
        if(strlen($doc) == 14){
            $str = substr($doc, 0,2). ".";
            $str .= substr($doc, 2,3). ".";
            $str .= substr($doc, 5,3). "/";
            $str .= substr($doc, 8,4). "-";
            $str .= substr($doc, 12,2);

            return $str;
        }else{
            $str = substr($doc, 0,3). ".";
            $str .= substr($doc, 3,3). ".";
            $str .= substr($doc, 6,3). "-";
            $str .= substr($doc, 9,2);
            return $str;
        }
    }
    
    public function contasPagar(Request $request){
        $data_final = date('Y-m-d', strtotime('+7 day'));
        $data_inicial = date('Y-m-d', strtotime('-7 day'));
        $filial_id = $request->filial_id;

        if($filial_id == -1){
            $filial_id = null;
        }
        $retorno = [];

        $dateAux = $data_inicial;
        for($aux = 0; $aux < 15; $aux++){
            $total = ContaPagar::
            whereDate('data_vencimento', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->where('status', 0)
            ->sum('valor_integral');

            $d = date($data_inicial, strtotime(($aux).' day'));
            $d = \Carbon\Carbon::parse($d)->format('d/m');
            $temp = [
                'data' => date('d/m', strtotime("+".($aux)." days",strtotime($data_inicial))),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
        }
        return response()->json($retorno, 200);
    }

    public function contasReceber(Request $request){
        $data_final = date('Y-m-d', strtotime('+7 day'));
        $data_inicial = date('Y-m-d', strtotime('-7 day'));
        $filial_id = $request->filial_id;
        if($filial_id == -1){
            $filial_id = null;
        }
        $retorno = [];

        $dateAux = $data_inicial;
        for($aux = 0; $aux < 15; $aux++){
            $total = ContaReceber::
            whereDate('data_vencimento', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->where('status', 0)
            ->sum('valor_integral');

            $d = date($data_inicial, strtotime(($aux).' day'));
            $d = \Carbon\Carbon::parse($d)->format('d/m');
            $temp = [
                'data' => date('d/m', strtotime("+".($aux)." days",strtotime($data_inicial))),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
        }
        return response()->json($retorno, 200);
    }

    public function vendasPdv(Request $request){
        $data_final = date('Y-m-d');
        $data_inicial = date('Y-m-d', strtotime('-15 day'));
        $filial_id = $request->filial_id;
        if($filial_id == -1){
            $filial_id = null;
        }
        $retorno = [];

        $dateAux = $data_inicial;
        for($aux = 0; $aux <= 15; $aux++){
            $total = VendaCaixa::
            whereDate('created_at', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->sum('valor_total');

            $d = date($data_inicial, strtotime(($aux).' day'));
            $d = \Carbon\Carbon::parse($d)->format('d/m');
            $temp = [
                'data' => date('d/m', strtotime("+".($aux)." days",strtotime($data_inicial))),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
        }
        return response()->json($retorno, 200);
    }

    public function vendasPedido(Request $request){
        $data_final = date('Y-m-d');
        $data_inicial = date('Y-m-d', strtotime('-15 day'));
        $filial_id = $request->filial_id;
        if($filial_id == -1){
            $filial_id = null;
        }
        $retorno = [];

        $dateAux = $data_inicial;
        for($aux = 0; $aux <= 15; $aux++){
            $total = Venda::
            whereDate('created_at', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->sum('valor_total');

            $d = date($data_inicial, strtotime(($aux).' day'));
            $d = \Carbon\Carbon::parse($d)->format('d/m');
            $temp = [
                'data' => date('d/m', strtotime("+".($aux)." days",strtotime($data_inicial))),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
        }
        return response()->json($retorno, 200);
    }

    public function orcamentos(Request $request){
        $data_final = date('Y-m-d');
        $data_inicial = date('Y-m-d', strtotime('-15 day'));
        $filial_id = $request->filial_id;
        if($filial_id == -1){
            $filial_id = null;
        }
        $retorno = [];

        $dateAux = $data_inicial;
        for($aux = 0; $aux <= 15; $aux++){
            $total = Orcamento::
            whereDate('created_at', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->sum('valor_total');

            $d = date($data_inicial, strtotime(($aux).' day'));
            $d = \Carbon\Carbon::parse($d)->format('d/m');
            $temp = [
                'data' => date('d/m', strtotime("+".($aux)." days",strtotime($data_inicial))),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
        }
        return response()->json($retorno, 200);
    }

    public function produtos(Request $request){
        $mes_atual = date('m');
        $mes_inicial = date('m', strtotime('-6 months'));
        $filial_id = $request->filial_id;
        // if($filial_id == -1){
        //     $filial_id = null;
        // }
        $retorno = [];
        $mes = (int)$mes_inicial;

        for($aux = 0; $aux <= 6; $aux++){
            if($mes > 12){
                $mes = 1;
            }
            $data = Produto::
            whereMonth('created_at', ($mes < 10) ? "0".$mes : $mes)
            ->where('empresa_id', $this->empresa_id)
            ->get();

            $total = 0;
            foreach($data as $p){
                $l = json_decode($p->locais);
                if(is_array($l)){
                    if(in_array($filial_id, $l)){
                        $total++;
                    }
                }
            }

            $temp = [
                'data' => $this->getMes($mes),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
            $mes++;
        }
        return response()->json($retorno, 200);
    }

    private function getMes($p){
        $meses = [
            'Jan',
            'Fev',
            'Mar',
            'Abr',
            'Mai',
            'Jun',
            'Jul',
            'Ago',
            'Set',
            'Out',
            'Nov',
            'Dez',
        ];
        return $meses[$p-1];
    }

    public function emissaoNfe(Request $request){
        $data_final = date('Y-m-d');
        $data_inicial = date('Y-m-d', strtotime('-15 day'));
        $filial_id = $request->filial_id;
        if($filial_id == -1){
            $filial_id = null;
        }
        $retorno = [];

        $dateAux = $data_inicial;
        for($aux = 0; $aux <= 15; $aux++){
            $totalVenda = Venda::
            whereDate('data_emissao', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->where('NfNumero', '>', 0)
            ->sum('valor_total');

            $totalRemessa = RemessaNfe::
            whereDate('data_emissao', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->where('numero_nfe', '>', 0)
            ->sum('valor_total');

            $total = $totalVenda + $totalRemessa;

            $d = date($data_inicial, strtotime(($aux).' day'));
            $d = \Carbon\Carbon::parse($d)->format('d/m');
            $temp = [
                'data' => date('d/m', strtotime("+".($aux)." days",strtotime($data_inicial))),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
        }
        return response()->json($retorno, 200);
    }

    public function emissaoNfce(Request $request){
        $data_final = date('Y-m-d');
        $data_inicial = date('Y-m-d', strtotime('-15 day'));
        $filial_id = $request->filial_id;
        if($filial_id == -1){
            $filial_id = null;
        }
        $retorno = [];

        $dateAux = $data_inicial;
        for($aux = 0; $aux <= 15; $aux++){
            $total = VendaCaixa::
            whereDate('created_at', date('Y-m-d', strtotime("+".($aux)." days",strtotime($data_inicial))))
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->where('NFcNumero', '>', 0)
            ->sum('valor_total');

            $d = date($data_inicial, strtotime(($aux).' day'));
            $d = \Carbon\Carbon::parse($d)->format('d/m');
            $temp = [
                'data' => date('d/m', strtotime("+".($aux)." days",strtotime($data_inicial))),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($retorno, $temp);
        }
        return response()->json($retorno, 200);
    }

    public function index()
    {
        // $this->setMaskDoc('09520985980');

        $dataFinal2 = $dataFinal = date('d/m/Y');
        $dataInicial2 = $dataInicial = date('d/m/Y', strtotime('-6 day'));
        $totalDeClientes = Cliente::where('empresa_id', $this->empresa_id)->count();
        $config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

        $graficosDash = [];

        if($config != null){
            $graficosDash = $config->graficos_dash ? json_decode($config->graficos_dash) : []; 
        }
        return view('default/grafico')
        ->with('graficoHomeJs', true)
        ->with('totalDeClientes', $totalDeClientes)
        ->with('graficosDash', $graficosDash)
        ->with('dataInicial', $dataInicial)
        ->with('dataFinal', $dataFinal)
        ->with('dataInicial2', $dataInicial2)
        ->with('dataFinal2', $dataFinal2)
        ->with('title', 'Bem Vindo');
    }

    public function countProdutos(Request $request){
        $filial_id = $request->filial_id;

        $totalDeProdutos = Produto::
        where('empresa_id', $this->empresa_id)
        ->where('locais', 'like', "%{$filial_id}%")
        ->count();

        return response()->json($totalDeProdutos, 200);
    }

    private function totalizacao(){
        $totalDeProdutos = Produto::
        where('empresa_id', $this->empresa_id)
        // ->groupBy('referencia_grade')
        ->count();

        $totalDeClientes = Cliente::where('empresa_id', $this->empresa_id)->count();

        return [
            'totalDeClientes' => $totalDeClientes,
            'totalDeProdutos' => $totalDeProdutos,
            'totalDeVendas' => $this->totalDeVendasHoje(),
            'totalDePedidos' => $this->totalDePedidosDeDliveryHoje(),
            'totalDeContaReceber' => $this->totalDeContaReceberHoje(),
            'totalDeContaPagar' => $this->totalDeContaPagarHoje(),
        ];
    }

    private function totalDeVendasHoje(){
        $vendas = Venda::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d') . " 00:00:00",
            date('Y-m-d') . " 23:59:59"
        ])
        ->where('empresa_id', $this->empresa_id)
        ->first();

        $vendaCaixas = VendaCaixa::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d') . " 00:00:00",
            date('Y-m-d') . " 23:59:59"
        ])
        ->where('empresa_id', $this->empresa_id)
        ->first();

        return $vendas->total + $vendaCaixas->total;

    }

    private function totalDePedidosDeDliveryHoje(){
        $pedidos = PedidoDelivery::
        select(\DB::raw('count(*) as linhas'))
        ->whereBetween('data_registro', [date("Y-m-d"), 
            date('Y-m-d', strtotime('+1 day'))])
        ->first();
        return $pedidos->linhas;
    }

    private function totalDeContaReceberHoje(){
        $contas = ContaReceber::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [date("Y-m-d"), 
            date('Y-m-d', strtotime('+1 day'))])
        ->where('status', false)
        ->where('empresa_id', $this->empresa_id)
        ->first(); 
        if($this->acesso_financeiro == 0) return 0;
        return $contas->total ?? 0;
    }

    private function totalDeContaPagarHoje(){
        $contas = ContaPagar::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [date("Y-m-d"), 
            date('Y-m-d', strtotime('+1 day'))])
        ->where('status', false)
        ->where('empresa_id', $this->empresa_id)
        ->first(); 
        if($this->acesso_financeiro == 0) return 0;
        return $contas->total ?? 0;
    }



    public function faturamentoDosUltimosSeteDias(Request $request){

        $arrayVendas = [];
        $filial_id = $request->filial_id;
        $filial_id = $filial_id == -1 ? null : $filial_id;
        for($aux = 0; $aux > -7; $aux--){
            $vendas = Venda::
            select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('data_registro', 
                [
                    date('Y-m-d', strtotime($aux.' day')), 
                    date('Y-m-d', strtotime(($aux+1).' day'))
                ]
            )
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->first();


            $vendaCaixas = VendaCaixa::
            select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('data_registro', 
                [
                    date('Y-m-d', strtotime($aux.' day')), 
                    date('Y-m-d', strtotime(($aux+1).' day'))
                ]
            )
            ->where('empresa_id', $this->empresa_id)
            ->where('filial_id', $filial_id)
            ->first();

            $total = (float)str_replace(",", ".", $vendas->total) + (float)str_replace(",", ".", $vendaCaixas->total);
            $temp = [
                'data' => date('d/m', strtotime(($aux).' day')),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($arrayVendas, $temp);
        }
        if($this->acesso_financeiro == 0){
            return response()->json(array_reverse([]));
        }
        return response()->json(array_reverse($arrayVendas));

    }

    public function produtosFiltrado(Request $request){

        $dataInicial = \Carbon\Carbon::parse(str_replace("/", "-", $request->data_inicial))->format('Y-m-d');
        $dataFinal = \Carbon\Carbon::parse(str_replace("/", "-", $request->data_final))->format('Y-m-d');
        $data = [];

        $filial_id = ($request->filial_id && $request->filial_id >= 1) ? $request->filial_id : null;

        $itensVendaCaixa = ItemVendaCaixa::
        selectRaw('produtos.nome as nome, sum(quantidade) as qtd, item_venda_caixas.produto_id as produto_id')
        ->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
        ->join('produtos', 'produtos.id', '=', 'item_venda_caixas.produto_id')
        ->where('venda_caixas.empresa_id', $this->empresa_id)
        ->groupBy('produto_id')
        ->orderBy('qtd')
        ->whereDate('item_venda_caixas.created_at', '>=', $dataInicial)
        ->whereDate('item_venda_caixas.created_at', '<=', $dataFinal)
        ->when($filial_id, function ($query) use ($filial_id) {
            return $query->where('venda_caixas.filial_id', $filial_id);
        })
        ->limit(15)
        ->get();

        $itensVenda = ItemVenda::
        selectRaw('produtos.nome as nome, sum(quantidade) as qtd, item_vendas.produto_id as produto_id')
        ->join('vendas', 'vendas.id', '=', 'item_vendas.venda_id')
        ->join('produtos', 'produtos.id', '=', 'item_vendas.produto_id')
        ->where('vendas.empresa_id', $this->empresa_id)
        ->groupBy('produto_id')
        ->orderBy('qtd')
        ->whereDate('item_vendas.created_at', '>=', $dataInicial)
        ->whereDate('item_vendas.created_at', '<=', $dataFinal)
        ->when($filial_id, function ($query) use ($filial_id) {
            return $query->where('vendas.filial_id', $filial_id);
        })
        ->limit(15)
        ->get();

        $ids = [];
        foreach($itensVenda as $i){
            if(!in_array($i->produto_id, $ids)){
                array_push($ids, $i->produto_id);
                array_push($data, [
                    'data' => $i->nome,
                    'total' => $i->qtd
                ]);
            }
        }

        foreach($itensVendaCaixa as $i){
            if(!in_array($i->produto_id, $ids)){
                array_push($ids, $i->produto_id);
                array_push($data, [
                    'data' => $i->nome,
                    'total' => $i->qtd
                ]);
            }
        }

        return response()->json(array_reverse($data));

    }

    public function faturamentoFiltrado(Request $request){

        $dataInicial = strtotime(str_replace("/", "-", $request->data_inicial));
        $dataFinal = strtotime(str_replace("/", "-", $request->data_final));

        $diferenca = ($dataFinal - $dataInicial)/86400; //86400 segundos do dia

        $arrayVendas = [];
        $filial_id = $request->filial_id;
        $filial_id = $filial_id == -1 ? null : $filial_id;
        
        if($diferenca+1 > 30){ //filtrar por mes

            $total = 0;
            for($aux = 0; $aux > (($diferenca+1)*-1); $aux--){
                $vendas = Venda::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->where('filial_id', $filial_id)
                ->where('empresa_id', $this->empresa_id)
                ->first();


                $vendaCaixas = VendaCaixa::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->where('filial_id', $filial_id)
                ->where('empresa_id', $this->empresa_id)
                ->first();

                if($this->confereMesNoArray($arrayVendas, date('m/Y', strtotime(($aux).' day')))){
                    $cont = 0;
                    foreach($arrayVendas as $arr){
                        if($arr['data'] == date('m/Y', strtotime(($aux).' day'))){
                            $arrayVendas[$cont]['total'] += $vendas->total + $vendaCaixas->total;

                        }
                        $cont++;
                    }
                }else{
                    $temp = [
                        'data' => date('m/Y', strtotime(($aux).' day')),
                        'total' => number_format($vendas->total + $vendaCaixas->total, 2, '.', '')
                    ];
                    array_push($arrayVendas, $temp);
                }
                
            }
            
        }else{ //filtro por dia
            for($aux = 0; $aux > (($diferenca+1)*-1); $aux--){
                $vendas = Venda::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->where('filial_id', $filial_id)
                ->where('empresa_id', $this->empresa_id)
                ->first();


                $vendaCaixas = VendaCaixa::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->where('filial_id', $filial_id)
                ->where('empresa_id', $this->empresa_id)
                ->first();
                $temp = [
                    'data' => date('d/m', strtotime(($aux).' day')),
                    'total' => number_format(($vendas->total + $vendaCaixas->total), 2, '.', '')
                ];
                array_push($arrayVendas, $temp);
            }
        }
        if($this->acesso_financeiro == 0){
            return response()->json(array_reverse([]));
        }
        return response()->json(array_reverse($arrayVendas));
        
    }

    private function confereMesNoArray($arr, $mes){
        foreach($arr as $a){
            if($a['data'] == $mes) return true;
        }
        return false;
    }


    private function totalDeVendasDias($dias, $filial_id){
        $vendas = Venda::
        select(\DB::raw('sum(valor_total-desconto+acrescimo) as total'))
        // ->whereBetween('created_at', [
        //     date('Y-m-d', strtotime("-$dias days")), 
        //     date('Y-m-d', strtotime('+1 day'))
        // ])
        ->when($dias == 1, function ($query) {
            return $query->whereDate('created_at', date('Y-m-d'));
        })
        ->when($dias == 7, function ($query) {
            return $query->whereRaw('WEEK(created_at) = ' . (date('W')-1));
        })
        ->when($dias == 30, function ($query) {
            return $query->whereMonth('created_at', date('m'));
        })
        ->where('filial_id', $filial_id)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        $vendaCaixas = VendaCaixa::
        select(\DB::raw('sum(valor_total) as total'))
        // ->whereBetween('created_at', [
        //     date('Y-m-d', strtotime("-$dias days")), 
        //     date('Y-m-d', strtotime('+1 day'))
        // ])
        ->when($dias == 1, function ($query) {
            return $query->whereDate('created_at', date('Y-m-d'));
        })
        ->when($dias == 7, function ($query) {
            return $query->whereRaw('WEEK(created_at) = ' . (date('W')-1));
        })
        ->when($dias == 30, function ($query) {
            return $query->whereMonth('created_at', date('m'));
        })
        ->where('filial_id', $filial_id)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        return number_format($vendas->total + $vendaCaixas->total, 2, ',', '.');

    }

    private function totalDePedidosDeDliveryDias($dias, $filial_id){
        $pedidos = PedidoDelivery::
        select(\DB::raw('count(*) as linhas'))
        ->whereBetween('data_registro', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d', strtotime('+1 day'))]
        )
        ->first();
        return $pedidos->linhas;
    }

    private function totalDeContaReceberDias($dias, $filial_id){
        $contas = ContaReceber::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [
            date('Y-m-d'),
            date('Y-m-d', strtotime("+$dias days"))
        ])
        ->where('filial_id', $filial_id)
        ->where('status', false)
        ->where('empresa_id', $this->empresa_id)
        ->first(); 
        if($this->acesso_financeiro == 0) return 0;
        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    private function totalDeContaPagarDias($dias, $filial_id){
        if($filial_id == -1){
            $filial_id = null;
        }
        $contas = ContaPagar::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [
            date('Y-m-d') . " 00:00:00",
            date('Y-m-d', strtotime("+$dias days")). " 23:59:00"
        ])
        ->where('filial_id', $filial_id)
        ->where('status', false)
        ->where('empresa_id', $this->empresa_id)
        ->first();

        if($this->acesso_financeiro == 0){ 
            return 0;
        }
        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    public function boxConsulta(Request $request){
        $dias = $request->dias;
        $filial_id = $request->filial_id;
        $filial_id = $filial_id == -1 ? null : $filial_id;
        $data = [
            'totalDeVendas' => $this->totalDeVendasDias($dias, $filial_id),
            'totalDePedidos' => $this->totalDePedidosDeDliveryDias($dias, $filial_id),
            'totalDeContaReceber' => $this->totalDeContaReceberDias($dias, $filial_id),
            'totalDeContaPagar' => $this->totalDeContaPagarDias($dias, $filial_id)
        ];

        return response()->json($data, 200);
    }


    public function all(){
        $avisos = Aviso::limit(10)
        ->where('status', 1)
        ->get();

        $temp = [];
        foreach($avisos as $a){
            $avisoAcesso = AvisoAcesso
            ::where('empresa_id', $this->empresa_id)
            ->where('aviso_id', $a->id)
            ->exists();

            if(!$avisoAcesso){
                array_push($temp, $a);
            }
        }
        $avisos = $temp;

        $view = view('alertas/linhas', compact('avisos'))->render();

        $data = [
            'view' => $view,
            'size' => sizeof($avisos)
        ];
        return response()->json($data, 200);
    }

    public function avisoView($id){

        $temp = AvisoAcesso::
        where('empresa_id', $this->empresa_id)
        ->where('aviso_id', $id)
        ->exists();

        if(!$temp){
            AvisoAcesso::create([
                'empresa_id' => $this->empresa_id,
                'aviso_id' => $id
            ]); 
        }
        $item = Aviso::findOrFail($id);
        return view('alertas/view')
        ->with('title', 'Alerta')
        ->with('item', $item);
    }

    public function getPlan(){
        $plan = PlanoEmpresa::
        where('empresa_id', $this->empresa_id)
        ->with('plano')
        ->first();

        $plan->expira = \Carbon\Carbon::parse($plan->expiracao)->format('d/m/Y');
        return response()->json($plan, 200);
    }

}

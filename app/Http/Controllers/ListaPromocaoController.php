<?php

namespace App\Http\Controllers;

use App\Models\ListaPromocao;
use App\Models\Produto; // Importando o modelo Produto
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\ProdutoListaPromocao;
use Illuminate\Http\Request;

class ListaPromocaoController extends Controller
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

        $listapromocao = Listapromocao::
        where('empresa_id', $request->empresa_id)
        ->get();

        return view('listaPromocao/list')
        ->with('listapromocao', $listapromocao)
        ->with('title', 'Lista de promoções');
    }

    public function new(){
        return view('listaPromocao/register')
        ->with('title', 'Cadastrar Promoção');
    }

    public function save(Request $request)
    {
        $listapromocao = new ListaPromocao(); // Corrigido para usar a classe correta
        $this->_validate($request);
    
        // Adicionando os campos de data ao array de dados
        $data = $request->all();
        $data['data_inicio'] = $request->input('data_inicio');
        $data['data_termino'] = $request->input('data_termino');
    
        $result = $listapromocao->create($data);
    
        $msgSucesso = "Promoção cadastrada com sucesso";
    
        if ($result) {
            session()->flash("mensagem_sucesso", $msgSucesso);
        } else {
            session()->flash('mensagem_erro', 'Erro ao cadastrar Promoção.');
        }
    
        return redirect('/listapromocao');
    }

    public function edit($id){
        $listapromocao = new Listapromocao(); 

        $resp = $listapromocao
        ->where('id', $id)->first();  

        if(valida_objeto($resp)){
            return view('listaPromocao/register')
            ->with('listapromocao', $resp)
            ->with('title', 'Editar Promoção');
        }else{
            return redirect('/403');
        }

    }

    public function update(Request $request)
    {
        $listapromocao = new ListaPromocao();
    
        $id = $request->input('id');
        $resp = $listapromocao->where('id', $id)->first();
    
        $this->_validate($request);
    
        // Atualizando os campos de data
        $resp->nome = $request->input('nome');
        $resp->data_inicio = $request->input('data_inicio'); // Adicionando o campo de data de início
        $resp->data_termino = $request->input('data_termino'); // Adicionando o campo de data de término
    
        $result = $resp->save();
        if ($result) {
            session()->flash('mensagem_sucesso', 'Promoção editada com sucesso!');
        } else {
            session()->flash('mensagem_erro', 'Erro ao editar promoção!');
        }
    
        return redirect('/listapromocao');
    }

    public function delete($id)
    {
        try {
            // Busca a promoção pelo ID
            $listapromocao = ListaPromocao::find($id);
    
            // Verifica se a promoção existe
            if (!$listapromocao) {
                session()->flash('mensagem_erro', 'Promoção não encontrada.');
                return redirect('/listapromocao');
            }
    
            // Remove a promoção
            if ($listapromocao->delete()) {
                session()->flash('mensagem_sucesso', 'Registro removido!');
            } else {
                session()->flash('mensagem_erro', 'Erro ao remover o registro!');
            }
    
            return redirect('/listapromocao');
        } catch (\Exception $e) {
            return view('errors.sql')
                ->with('title', 'Erro ao deletar listapromocao')
                ->with('motivo', $e->getMessage());
        }
    }


    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:50',
            'data_inicio' => 'required|date', // Validação para data de início
            'data_termino' => 'required|date|after_or_equal:data_inicio', // Validação para data de término
        ];
    
        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres máximos permitidos.',
            'data_inicio.required' => 'O campo data de início é obrigatório.',
            'data_inicio.date' => 'O campo data de início deve ser uma data válida.',
            'data_termino.required' => 'O campo data de término é obrigatório.',
            'data_termino.date' => 'O campo data de término deve ser uma data válida.',
            'data_termino.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function quickSave(Request $request){
        try{
            $nome = $request->nome;

            $res = Listapromocao::create(
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

    public function produtos($id)
    {
        $listapromocao = ListaPromocao::find($id);
    
        if (!$listapromocao) {
            return redirect('/listapromocao')->with('mensagem_erro', 'Promoção não encontrada.');
        }
    
        $produtos = $listapromocao->produtos; // Supondo que você tenha o relacionamento definido
    
        return view('listapromocao.produtos', compact('listapromocao', 'produtos'))
              ->with('title', 'Lista de produtos da promoção');
    }

    public function showAddProdutoForm($id)
    {
        $listapromocao = ListaPromocao::find($id);
        
        if (!$listapromocao) {
            return redirect('/listapromocao')->with('mensagem_erro', 'Promoção não encontrada.');
        }
    
        // Supondo que você tenha a empresa_id armazenada na sessão
        $empresa_id = session('user_logged')['empresa']; // Ajuste conforme sua lógica de autenticação
    
        // Filtrando categorias e marcas pela empresa logada
        $categorias = Categoria::where('empresa_id', $empresa_id)->get(); // Carregando as categorias da empresa
        $marcas = Marca::where('empresa_id', $empresa_id)->get(); // Carregando as marcas da empresa
    
        return view('listaPromocao.add_produto', compact('listapromocao', 'categorias', 'marcas'))
            ->with('title', 'Adicionar produto à promoção');
    }

public function searchProdutos(Request $request, $id)
{
    $listapromocao = ListaPromocao::find($id);
    
    if (!$listapromocao) {
        return redirect('/listapromocao')->with('mensagem_erro', 'Promoção não encontrada.');
    }

    $query = Produto::with(['categoria', 'marca']); // Carregando as relações

    // Supondo que você tenha a empresa_id armazenada na sessão
    $empresa_id = session('user_logged')['empresa']; // Ajuste conforme sua lógica de autenticação

    // Filtrando produtos pela empresa logada
    $query->where('empresa_id', $empresa_id);

    // Filtros de busca
    if ($request->filled('nome')) {
        $query->where('nome', 'like', '%' . $request->nome . '%');
    }
    if ($request->filled('referencia')) {
        $query->where('referencia', 'like', '%' . $request->referencia . '%');
    }
    if ($request->filled('codigo_barras')) {
        $query->where('codBarras', 'like', '%' . $request->codigo_barras . '%');
    }
    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->categoria_id); // Filtrando pela categoria
    }
    if ($request->filled('marca_id')) {
        $query->where('marca_id', $request->marca_id); // Filtrando pela marca
    }

    $produtos = $query->get();

    // Carregar categorias e marcas para a view
    $categorias = Categoria::where('empresa_id', $empresa_id)->get();
    $marcas = Marca::where('empresa_id', $empresa_id)->get();

    return view('listaPromocao.add_produto', compact('listapromocao', 'produtos', 'categorias', 'marcas'))
    ->with('title', 'Adicionar produto à promoção');
}

public function addMultipleProdutos(Request $request, $id)
{
    $listapromocao = ListaPromocao::find($id);
    
    if (!$listapromocao) {
        return redirect()->back()->with('mensagem_erro', 'Promoção não encontrada.');
    }

    // Verifica se produtos foram selecionados
    if ($request->has('produtos')) {
        $produtosIds = $request->input('produtos');
        $produtosAdicionados = []; // Para armazenar IDs de produtos que foram adicionados
        $produtosJaExistem = []; // Para armazenar IDs de produtos que já existem na lista

        // Adiciona cada produto à promoção
        foreach ($produtosIds as $produtoId) {
            // Verifica se o produto já está na lista de produtos da promoção
            $exists = $listapromocao->produtos()->where('produto_id', $produtoId)->exists();

            if (!$exists) {
                // Encontre o produto pelo ID
                $produto = Produto::find($produtoId);
                if ($produto) {
                    // Cria uma nova entrada na tabela ProdutoListaPromocao
                    $listapromocao->produtos()->create([
                        'produto_id' => $produtoId,
                        'preco_compra' => $produto->valor_compra,
                        'preco_venda' => $produto->valor_venda,
                        'porcentagem_desconto' => 0, // Defina conforme necessário
                        'valor_desconto' => 0, // Defina conforme necessário
                        'valor_final' => $produto->valor_venda, // Definindo valor_final como o preço de venda
                    ]);
                    $produtosAdicionados[] = $produtoId; // Adiciona o ID à lista de produtos adicionados
                }
            } else {
                // Se o produto já existe, armazena o ID para exibir a mensagem depois
                $produtosJaExistem[] = $produtoId;
            }
        }

        // Mensagem de sucesso se algum produto foi adicionado
        if (!empty($produtosAdicionados)) {
            return redirect()->route('listapromocao.produtos', $id)->with('mensagem_sucesso', 'Produtos adicionados à promoção com sucesso.');
        }

        // Mensagem de erro se nenhum produto foi adicionado
        if (!empty($produtosJaExistem)) {
            $produtosLista = implode(', ', $produtosJaExistem);
            return redirect()->back()->with('mensagem_erro', "Os produtos com IDs $produtosLista já estão na lista da promoção. Por favor, desmarque-os antes de tentar adicionar novamente.");
        }
    }

    return redirect()->back()->with('mensagem_erro', 'Nenhum produto selecionado.');
}

public function showProdutos($id)
{
    $listapromocao = ListaPromocao::find($id);
    
    if (!$listapromocao) {
        return redirect('/listapromocao')->with('mensagem_erro', 'Promoção não encontrada.');
    }

    // Carregar produtos associados à promoção
    $produtos = $listapromocao->produtos()->with('produto')->get(); // Carregando os produtos relacionados

    return view('listaPromocao.produtos', compact('listapromocao', 'produtos'))
    ->with('title', 'Lista de produtos da promoção');;
}

public function updateMultipleProdutos(Request $request, $id)
{
    $listapromocao = ListaPromocao::find($id);
    
    if (!$listapromocao) {
        return redirect()->back()->with('mensagem_erro', 'Promoção não encontrada.');
    }

    // Verifica se produtos foram enviados para atualização
    if ($request->has('produtos')) {
        $produtos = $request->input('produtos');
        $produtosAtualizados = []; // Para armazenar IDs de produtos que foram atualizados
        $produtosNaoEncontrados = []; // Para armazenar IDs de produtos que não foram encontrados

        // Atualiza cada produto na promoção
        foreach ($produtos as $produto) {
            $produtoLista = $listapromocao->produtos()->where('produto_id', $produto['id'])->first();

            if ($produtoLista) {
                // Verifica se houve alteração nos dados
                $alterou = false;

                if ($produtoLista->porcentagem_desconto != $produto['porcentagem_desconto']) {
                    $produtoLista->porcentagem_desconto = $produto['porcentagem_desconto'];
                    $alterou = true;
                }

                $valorDesconto = str_replace(',', '.', str_replace('.', '', $produto['valor_desconto']));
                if ($produtoLista->valor_desconto != $valorDesconto) {
                    $produtoLista->valor_desconto = $valorDesconto;
                    $alterou = true;
                }

                $valorFinal = str_replace(',', '.', str_replace('.', '', $produto['valor_final']));
                if ($produtoLista->valor_final != $valorFinal) {
                    $produtoLista->valor_final = $valorFinal;
                    $alterou = true;
                }

                // Salva apenas se houve alteração
                if ($alterou) {
                    $produtoLista->save();
                    $produtosAtualizados[] = $produto['id']; // Adiciona o ID à lista de produtos atualizados
                }
            } else {
                // Se o produto não for encontrado, armazena o ID para exibir a mensagem depois
                $produtosNaoEncontrados[] = $produto['id'];
            }
        }

        // Mensagem de sucesso se algum produto foi atualizado
        if (!empty($produtosAtualizados)) {
            return redirect()->route('listapromocao.produtos', $id)->with('mensagem_sucesso', 'Produtos atualizados com sucesso.');
        }

        // Mensagem de erro se nenhum produto foi encontrado
        if (!empty($produtosNaoEncontrados)) {
            $produtosLista = implode(', ', $produtosNaoEncontrados);
            return redirect()->back()->with('mensagem_erro', "Os produtos com IDs $produtosLista não foram encontrados na lista da promoção.");
        }
    }

    return redirect()->back()->with('mensagem_erro', 'Nenhum produto enviado para atualização.');
}

public function destroy($id)
{
    try {
        $produtoLista = ProdutoListaPromocao::find($id);
        if ($produtoLista) {
            $produtoLista->delete();
            return response()->json(['success' => true, 'message' => 'Produto removido com sucesso!'], 200);
        }
        return response()->json(['error' => 'Produto não encontrado'], 404);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao remover o produto', 'message' => $e->getMessage()], 500);
    }
}
}

@extends('default.layout')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="card card-custom gutter-b">
    <div class="card-body">
        <h3 class="card-title">Produtos da Promoção: {{ $listapromocao->nome }}</h3>

        <!-- Botão de Voltar -->
        <div><a href="/listapromocao" class="btn btn-secondary mb-3">Voltar</a></div>

        <a href="/listapromocao/produtos/{{ $listapromocao->id }}/add" class="btn btn-success mb-3">Adicionar Produto</a>

        <hr> <!-- Linha separadora -->

        <h4>Aplicar Desconto a Todos os Produtos</h4>
        <div style="height: 10px;"></div> <!-- Espaço vazio -->
        <div class="mb-3">
            <label for="desconto_global" class="d-block">Desconto (%):</label> <!-- Rótulo acima do input -->
            <div class="d-flex align-items-center">
                <input type="text" id="desconto_global" placeholder="0,00" class="form-control money" style="width: 100px;">
                <button class="btn btn-primary ml-2" onclick="aplicarDescontoGlobal()">Aplicar Desconto</button>
            </div>
        </div>
        <hr> <!-- Linha separadora -->
        <h4>Lista de Produtos</h4>
        <form method="POST" action="{{ route('listapromocao.updateProdutos', $listapromocao->id) }}">
            @csrf
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome do Produto</th>
                        <th>Preço de Compra</th>
                        <th>Preço de Venda</th>
                        <th>% Desconto</th>
                        <th>Valor de Desconto</th>
                        <th>Valor Final</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @if($produtos && $produtos->count() > 0)
                        @foreach($produtos as $produtoLista)
                            <tr>
                                <td>{{ $produtoLista->produto->nome }}</td>
                                <td>{{ number_format($produtoLista->preco_compra, 2, ',', '.') }}</td>
                                <td>
                                    <input type="hidden" name="produtos[{{ $produtoLista->produto_id }}][preco_venda]" value="{{ $produtoLista->preco_venda }}">
                                    {{ number_format($produtoLista->preco_venda, 2, ',', '.') }}
                                </td>
                                <td>
                                    <input type="hidden" name="produtos[{{ $produtoLista->produto_id }}][id]" value="{{ $produtoLista->produto_id }}">
                                    <input type="text" name="produtos[{{ $produtoLista->produto_id }}][porcentagem_desconto]" value="{{ $produtoLista->porcentagem_desconto }}" class="form-control money" onchange="calcularDesconto({{ $produtoLista->produto_id }})">
                                </td>
                                <td>
                                    <input type="text" name="produtos[{{ $produtoLista->produto_id }}][valor_desconto]" value="{{ number_format($produtoLista->valor_desconto, 2, ',', '.') }}" class="form-control money" id="valor_desconto_{{ $produtoLista->produto_id }}" onchange="calcularValorFinal({{ $produtoLista->produto_id }})">
                                </td>
                                <td>
                                    <input type="text" name="produtos[{{ $produtoLista->produto_id }}][valor_final]" value="{{ number_format($produtoLista->valor_final, 2, ',', '.') }}" class="form-control money" id="valor_final_{{ $produtoLista->produto_id }}" onchange="calcularValorDesconto({{ $produtoLista->produto_id }})">
                                </td>
                                <td>
                                    <!-- Botão de Exclusão fora do Formulário -->
                                    <button type="button" class="btn btn-danger" onclick="removerProduto({{ $produtoLista->id }})">Remover</button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">Nenhum produto encontrado para esta promoção.</td>
                        </tr>
                    @endif
                </tbody>
            </table> 
            <button type="submit" class="btn btn-primary">Salvar Todas as Alterações</button>
        </form>
    </div>
</div>
<script>
function calcularDesconto(produtoId) {
    const porcentagemDesconto = parseFloat(document.querySelector(`input[name="produtos[${produtoId}][porcentagem_desconto]"]`).value.replace(',', '.'));
    const precoVenda = parseFloat(document.querySelector(`input[name="produtos[${produtoId}][preco_venda]"]`).value);
    if (!isNaN(porcentagemDesconto) && !isNaN(precoVenda)) {
        const valorDesconto = (precoVenda * porcentagemDesconto) / 100;
        const valorFinal = precoVenda - valorDesconto;

        document.getElementById(`valor_desconto_${produtoId}`).value = valorDesconto.toFixed(2).replace('.', ',');
        document.getElementById(`valor_final_${produtoId}`).value = valorFinal.toFixed(2).replace('.', ',');
    }
}

function calcularValorFinal(produtoId) {
    const valorDesconto = parseFloat(document.getElementById(`valor_desconto_${produtoId}`).value.replace(',', '.'));
    const precoVenda = parseFloat(document.querySelector(`input[name="produtos[${produtoId}][preco_venda]"]`).value);
    if (!isNaN(valorDesconto) && !isNaN(precoVenda)) {
        const valorFinal = precoVenda - valorDesconto;
        const porcentagemDesconto = (valorDesconto / precoVenda) * 100;
        
        document.querySelector(`input[name="produtos[${produtoId}][porcentagem_desconto]"]`).value = porcentagemDesconto.toFixed(2);
        document.getElementById(`valor_final_${produtoId}`).value = valorFinal.toFixed(2).replace('.', ',');
        calcularDesconto(produtoId); // Atualiza a porcentagem de desconto
    } else {
        document.getElementById(`valor_final_${produtoId}`).value = '';
    }
}

function calcularValorDesconto(produtoId) {
    const valorFinal = parseFloat(document.getElementById(`valor_final_${produtoId}`).value.replace(',', '.'));
    const precoVenda = parseFloat(document.querySelector(`input[name="produtos[${produtoId}][preco_venda]"]`).value);
    if (!isNaN(valorFinal) && !isNaN(precoVenda)) {
        const valorDesconto = precoVenda - valorFinal;
        const porcentagemDesconto = (valorDesconto / precoVenda) * 100;

        document.querySelector(`input[name="produtos[${produtoId}][porcentagem_desconto]"]`).value = porcentagemDesconto.toFixed(2);
        document.getElementById(`valor_desconto_${produtoId}`).value = valorDesconto.toFixed(2).replace('.', ',');
        calcularValorFinal(produtoId); // Atualiza o valor final
    } else {
        document.getElementById(`valor_desconto_${produtoId}`).value = '';
    }
}

function aplicarDescontoGlobal() {
    const descontoGlobal = parseFloat(document.getElementById('desconto_global').value);
    if (!isNaN(descontoGlobal) && descontoGlobal >= 0 && descontoGlobal <= 100) {
        const produtos = document.querySelectorAll('input[name^="produtos["][name$="[porcentagem_desconto]"]');
        produtos.forEach(produto => {
            const produtoId = produto.closest('tr').querySelector('input[name$="[id]"]').value;
            produto.value = descontoGlobal.toFixed(2);
            calcularDesconto(produtoId);
        });
    }
}

function removerProduto(produtoId) {
    if (confirm('Tem certeza que deseja remover este produto da promoção?')) {
        fetch(`/listapromocao/produtos/${produtoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (response.ok) {
                alert('Produto removido com sucesso!');
                location.reload(); // Recarrega a página para refletir as mudanças
            } else {
                alert('Erro ao remover o produto.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao remover o produto.');
        });
    }
}
</script>
@endsection
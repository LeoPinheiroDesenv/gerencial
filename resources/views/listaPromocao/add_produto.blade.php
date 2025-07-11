@extends('default.layout')
@section('content')
<div class="card card-custom gutter-b">
    <div class="card-body">
        <h3 class="card-title">Adicionar Produto à Promoção: {{ $listapromocao->nome }}</h3>

        <!-- Botão de Voltar -->
        <a href="/listapromocao/produtos/{{ $listapromocao->id }}" class="btn btn-secondary mb-3">Voltar</a>

        <form method="GET" action="/listapromocao/produtos/{{ $listapromocao->id }}/search">
            @csrf
            <div class="form-row"> <!-- Usando form-row para criar uma linha -->
                <div class="form-group col-md-2">
                    <label>Nome do Produto</label>
                    <input type="text" name="nome" class="form-control" placeholder="Digite o nome do produto" value="{{ request('nome') }}">
                </div>
                <div class="form-group col-md-2">
                    <label>Referência</label>
                    <input type="text" name="referencia" class="form-control" placeholder="Digite a referência do produto" value="{{ request('referencia') }}">
                </div>
                <div class="form-group col-md-2">
                    <label>Código de Barras</label>
                    <input type="text" name="codigo_barras" class="form-control" placeholder="Digite o código de barras" value="{{ request('codigo_barras') }}">
                </div>
                <div class="form-group col-md-2">
                    <label>Categoria</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Selecione uma categoria</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Marca</label>
                    <select name="marca_id" class="form-control">
                        <option value="">Selecione uma marca</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}" {{ request('marca_id') == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Buscar Produtos</button>
        </form>

        <hr>

        <h4>Resultados da Busca</h4>
        <form method="POST" action="/listapromocao/produtos/{{ $listapromocao->id }}/add-multiple">
            @csrf
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="checkAll" onclick="toggleCheckboxes(this)"> <!-- Checkbox para marcar/desmarcar todos -->
                        </th>
                        <th>ID</th>
                        <th>Nome do Produto</th>
                        <th>Referência</th>
                        <th>Código de Barras</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>Preço de Compra</th>
                        <th>Preço de Venda</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($produtos) && $produtos->count() > 0)
                        @foreach($produtos as $produto)
                        <tr>
                            <td>
                                <input type="checkbox" name="produtos[]" value="{{ $produto->id }}"> <!-- Checkbox para seleção -->
                            </td>
                            <td>{{ $produto->id }}</td>
                            <td>{{ $produto->nome }}</td>
                            <td>{{ $produto->referencia }}</td>
                            <td>{{ $produto->codBarras }}</td>
                            <td>{{ $produto->categoria->nome ?? '' }}</td>
                            <td>{{ $produto->marca->nome ?? '' }}</td>
                            <td>{{ number_format($produto->valor_compra, 2, ',', '.') ?? 'N/A' }}</td>
                            <td>{{ number_format($produto->valor_venda, 2, ',', '.') ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8">Nenhum produto encontrado.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <button type="submit" class="btn btn-success mt-4">Adicionar Produtos Marcados</button>
        </form>
    </div>
</div>

<script>
    function toggleCheckboxes(source) {
        const checkboxes = document.querySelectorAll('input[name="produtos[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
    }
</script>
@endsection
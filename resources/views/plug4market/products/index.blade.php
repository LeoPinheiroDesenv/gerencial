@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Produtos Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.products.create') }}" class="btn btn-primary btn-lg">
                <i class="fa fa-plus"></i> Novo Produto
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('error') }}
            </div>
        @endif

        @if(isset($error))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ $error }}
            </div>
        @endif

        <!-- Botões de Ação -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('plug4market.products.sync-all') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-sync"></i> Sincronizar Todos
                </a>
                <a href="{{ route('plug4market.settings.index') }}" class="btn btn-info btn-lg">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </div>
        </div>

        <!-- Tabela de Produtos -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>Valor Unitário</th>
                        <th>Status</th>
                        <th width="200">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                <strong>{{ $product->codigo }}</strong>
                                @if($product->external_id)
                                    <br><small class="text-muted">API ID: {{ $product->external_id }}</small>
                                @endif
                            </td>
                            <td>{{ $product->descricao }}</td>
                            <td>
                                @if($product->category)
                                    <span class="badge badge-info">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($product->marca)
                                    <span class="badge badge-light">{{ $product->marca }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $product->formatted_price }}</strong>
                            </td>
                            <td>
                                @if($product->sincronizado)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Sincronizado
                                    </span>
                                    @if($product->ultima_sincronizacao)
                                        <br><small class="text-muted">{{ $product->ultima_sincronizacao->format('d/m/Y H:i') }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Não Sincronizado
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('plug4market.products.show', $product->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('plug4market.products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$product->sincronizado)
                                    <a href="{{ route('plug4market.products.sync', $product->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-sync"></i>
                                    </a>
                                @endif
                                <form action="{{ route('plug4market.products.destroy', $product->id) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Nenhum produto encontrado
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($products->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Mostrando {{ $products->firstItem() ?? 0 }} a {{ $products->lastItem() ?? 0 }} 
                    de {{ $products->total() ?? 0 }} registros
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

@endsection 
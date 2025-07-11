@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Detalhes do Produto Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.products.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
            <a href="{{ route('plug4market.products.edit', $product->id) }}" class="btn btn-warning btn-lg">
                <i class="fa fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <!-- Informações Básicas -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Informações do Produto</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Código/SKU:</label>
                                    <p class="form-control-static">{{ $product->codigo }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Categoria:</label>
                                    <p class="form-control-static">
                                        @if($product->category)
                                            <span class="badge badge-info">{{ $product->category->name }}</span>
                                        @else
                                            <span class="text-muted">Não definida</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label font-weight-bold">Descrição:</label>
                            <p class="form-control-static">{{ $product->descricao }}</p>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Marca:</label>
                                    <p class="form-control-static">
                                        @if($product->marca)
                                            <span class="badge badge-light">{{ $product->marca }}</span>
                                        @else
                                            <span class="text-muted">Não definida</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Valor Unitário:</label>
                                    <p class="form-control-static">
                                        <strong>{{ $product->formatted_price }}</strong>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Unidade:</label>
                                    <p class="form-control-static">{{ $product->unidade ?: 'Não definida' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Fiscais -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Informações Fiscais</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">NCM:</label>
                                    <p class="form-control-static">{{ $product->ncm ?: 'Não definido' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">CFOP:</label>
                                    <p class="form-control-static">{{ $product->cfop ?: 'Não definido' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Origem:</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-{{ $product->origem == 'nacional' ? 'success' : 'warning' }}">
                                            {{ ucfirst($product->origem ?: 'nacional') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Alíquota ICMS:</label>
                                    <p class="form-control-static">{{ $product->aliquota_icms ? $product->aliquota_icms . '%' : 'Não definida' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Alíquota PIS:</label>
                                    <p class="form-control-static">{{ $product->aliquota_pis ? $product->aliquota_pis . '%' : 'Não definida' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Alíquota COFINS:</label>
                                    <p class="form-control-static">{{ $product->aliquota_cofins ? $product->aliquota_cofins . '%' : 'Não definida' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status de Sincronização -->
                <div class="card card-custom gutter-b">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Sincronização com API</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($product->sincronizado)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Sincronizado</strong>
                                @if($product->external_id)
                                    <br><small>ID na API: {{ $product->external_id }}</small>
                                @endif
                                @if($product->ultima_sincronizacao)
                                    <br><small>Última sincronização: {{ $product->ultima_sincronizacao->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Não Sincronizado</strong>
                                <br><small>Este produto não foi sincronizado com a API Plug4Market</small>
                            </div>
                            <a href="{{ route('plug4market.products.sync', $product->id) }}" class="btn btn-success btn-block">
                                <i class="fas fa-sync"></i> Sincronizar Agora
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Dados da API -->
                @if($apiProduct)
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h4 class="card-label">Dados da API</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label font-weight-bold">ID da API:</label>
                                <p class="form-control-static">{{ $apiProduct['id'] ?? 'N/A' }}</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Nome na API:</label>
                                <p class="form-control-static">{{ $apiProduct['name'] ?? 'N/A' }}</p>
                            </div>
                            @if(isset($apiProduct['categoryId']))
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Categoria na API:</label>
                                    <p class="form-control-static">{{ $apiProduct['categoryId'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Ações Rápidas -->
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="card-label">Ações</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('plug4market.products.edit', $product->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Produto
                            </a>
                            @if(!$product->sincronizado)
                                <a href="{{ route('plug4market.products.sync', $product->id) }}" class="btn btn-success">
                                    <i class="fas fa-sync"></i> Sincronizar
                                </a>
                            @endif
                            <form action="{{ route('plug4market.products.destroy', $product->id) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                    <i class="fas fa-trash"></i> Excluir Produto
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 
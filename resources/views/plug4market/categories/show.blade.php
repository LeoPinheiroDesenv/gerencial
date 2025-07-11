@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Detalhes da Categoria Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.categories.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
            <a href="{{ route('plug4market.categories.edit', $category->id) }}" class="btn btn-warning btn-lg">
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
                            <h4 class="card-label">Informações da Categoria</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Nome:</label>
                                    <p class="form-control-static">{{ $category->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Status:</label>
                                    <p class="form-control-static">
                                        @if($category->is_active)
                                            <span class="badge badge-success">Ativa</span>
                                        @else
                                            <span class="badge badge-danger">Inativa</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label font-weight-bold">Descrição:</label>
                            <p class="form-control-static">
                                {{ $category->description ?: 'Nenhuma descrição fornecida' }}
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Nível:</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-light">{{ $category->level }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Categoria Pai:</label>
                                    <p class="form-control-static">
                                        @if($category->parent)
                                            <span class="badge badge-info">{{ $category->parent->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">Raiz</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Produtos:</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-info">{{ $category->products()->count() }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($category->path)
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Caminho Completo:</label>
                                <p class="form-control-static">{{ $category->path }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Categorias Filhas -->
                @if($category->children->count() > 0)
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h4 class="card-label">Subcategorias ({{ $category->children->count() }})</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Status</th>
                                            <th>Produtos</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category->children as $child)
                                            <tr>
                                                <td>{{ $child->name }}</td>
                                                <td>
                                                    @if($child->is_active)
                                                        <span class="badge badge-success">Ativa</span>
                                                    @else
                                                        <span class="badge badge-danger">Inativa</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $child->products()->count() }}</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('plug4market.categories.show', $child->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('plug4market.categories.edit', $child->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
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
                        @if($category->sincronizado)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Sincronizada</strong>
                                @if($category->external_id)
                                    <br><small>ID na API: {{ $category->external_id }}</small>
                                @endif
                                @if($category->ultima_sincronizacao)
                                    <br><small>Última sincronização: {{ $category->ultima_sincronizacao->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Não Sincronizada</strong>
                                <br><small>Esta categoria não foi sincronizada com a API Plug4Market</small>
                            </div>
                            <a href="{{ route('plug4market.categories.sync', $category->id) }}" class="btn btn-success btn-block">
                                <i class="fas fa-sync"></i> Sincronizar Agora
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Dados da API -->
                @if($apiCategory)
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h4 class="card-label">Dados da API</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label font-weight-bold">ID da API:</label>
                                <p class="form-control-static">{{ $apiCategory['id'] ?? 'N/A' }}</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Nome na API:</label>
                                <p class="form-control-static">{{ $apiCategory['name'] ?? 'N/A' }}</p>
                            </div>
                            @if(isset($apiCategory['parentId']))
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Categoria Pai na API:</label>
                                    <p class="form-control-static">{{ $apiCategory['parentId'] }}</p>
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
                            <a href="{{ route('plug4market.categories.edit', $category->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Categoria
                            </a>
                            @if(!$category->sincronizado)
                                <a href="{{ route('plug4market.categories.sync', $category->id) }}" class="btn btn-success">
                                    <i class="fas fa-sync"></i> Sincronizar
                                </a>
                            @endif
                            <form action="{{ route('plug4market.categories.destroy', $category->id) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                                    <i class="fas fa-trash"></i> Excluir Categoria
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
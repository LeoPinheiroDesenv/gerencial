@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Categorias Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.categories.create') }}" class="btn btn-primary btn-lg">
                <i class="fa fa-plus"></i> Nova Categoria
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

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('warning') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('info') }}
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
                <a href="{{ route('plug4market.categories.sync-all') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-sync"></i> Sincronizar Todas
                </a>
                <a href="{{ route('plug4market.settings.index') }}" class="btn btn-info btn-lg">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </div>
        </div>

        <!-- Tabela de Categorias -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Categoria Pai</th>
                        <th>Nível</th>
                        <th>Status</th>
                        <th>Sincronização</th>
                        <th>Produtos</th>
                        <th width="200">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                @if($category->external_id)
                                    <br><small class="text-muted">API ID: {{ $category->external_id }}</small>
                                @endif
                            </td>
                            <td>
                                @if($category->description)
                                    {{ Str::limit($category->description, 50) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($category->parent)
                                    <span class="badge badge-info">{{ $category->parent->name }}</span>
                                @else
                                    <span class="badge badge-secondary">Raiz</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light">{{ $category->level }}</span>
                            </td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-danger">Inativa</span>
                                @endif
                            </td>
                            <td>
                                @if($category->sincronizado)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Sincronizada
                                    </span>
                                    @if($category->ultima_sincronizacao)
                                        <br><small class="text-muted">{{ $category->ultima_sincronizacao->format('d/m/Y H:i') }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Não Sincronizada
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $category->products()->count() }}</span>
                            </td>
                            <td>
                                <a href="{{ route('plug4market.categories.show', $category->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('plug4market.categories.edit', $category->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$category->sincronizado)
                                    <a href="{{ route('plug4market.categories.sync', $category->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-sync"></i>
                                    </a>
                                @endif
                                <form action="{{ route('plug4market.categories.destroy', $category->id) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Nenhuma categoria encontrada.
                                    <a href="{{ route('plug4market.categories.create') }}" class="alert-link">Criar primeira categoria</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($categories->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Mostrando {{ $categories->firstItem() ?? 0 }} a {{ $categories->lastItem() ?? 0 }} 
                    de {{ $categories->total() ?? 0 }} registros
                </div>
                <div>
                    {{ $categories->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

@endsection 
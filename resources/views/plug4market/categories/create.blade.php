@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Nova Categoria Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.categories.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        <form action="{{ route('plug4market.categories.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name" class="form-label">Nome da Categoria *</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               maxlength="255"
                               placeholder="Ex: Eletrônicos">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="parent_id" class="form-label">Categoria Pai</label>
                        <select class="form-control @error('parent_id') is-invalid @enderror" 
                                id="parent_id" 
                                name="parent_id">
                            <option value="">Selecione uma categoria pai (opcional)</option>
                            @foreach($parentCategories as $parentCategory)
                                <option value="{{ $parentCategory->id }}" 
                                        {{ old('parent_id') == $parentCategory->id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $parentCategory->level) }}{{ $parentCategory->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Deixe em branco para criar uma categoria raiz
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" 
                          name="description" 
                          rows="3" 
                          placeholder="Descrição detalhada da categoria (opcional)">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" 
                           class="custom-control-input" 
                           id="is_active" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">
                        Categoria Ativa
                    </label>
                </div>
                <small class="form-text text-muted">
                    Categorias inativas não aparecerão na listagem de produtos
                </small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Informação:</strong> A categoria será criada localmente e automaticamente sincronizada com a API Plug4Market.
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> Criar Categoria
                </button>
                <a href="{{ route('plug4market.categories.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fa fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-resize textarea
    $('#description').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});
</script>
@endpush 
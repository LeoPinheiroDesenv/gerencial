@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Editar Produto Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.products.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        <form action="{{ route('plug4market.products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="codigo" class="form-label">Código/SKU *</label>
                        <input type="text" 
                               name="codigo" 
                               id="codigo"
                               class="form-control @error('codigo') is-invalid @enderror" 
                               value="{{ old('codigo', $product->codigo) }}"
                               required 
                               maxlength="255"
                               placeholder="Ex: PROD001">
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="categoria_id" class="form-label">Categoria</label>
                        <select name="categoria_id" 
                                id="categoria_id"
                                class="form-control @error('categoria_id') is-invalid @enderror">
                            <option value="">Selecione uma categoria (opcional)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->external_id }}" 
                                        {{ old('categoria_id', $product->categoria_id) == $category->external_id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $category->level) }}{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Categoria do produto na API Plug4Market
                        </small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="descricao" class="form-label">Descrição *</label>
                        <input type="text" 
                               name="descricao" 
                               id="descricao"
                               class="form-control @error('descricao') is-invalid @enderror" 
                               value="{{ old('descricao', $product->descricao) }}"
                               required 
                               maxlength="255"
                               placeholder="Descrição completa do produto">
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" 
                               name="marca" 
                               id="marca"
                               class="form-control @error('marca') is-invalid @enderror" 
                               value="{{ old('marca', $product->marca) }}"
                               maxlength="255"
                               placeholder="Ex: Samsung">
                        @error('marca')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="ncm" class="form-label">NCM</label>
                        <input type="text" 
                               name="ncm" 
                               id="ncm"
                               class="form-control @error('ncm') is-invalid @enderror" 
                               value="{{ old('ncm', $product->ncm) }}"
                               maxlength="20"
                               placeholder="Ex: 85171200">
                        @error('ncm')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="cfop" class="form-label">CFOP</label>
                        <input type="text" 
                               name="cfop" 
                               id="cfop"
                               class="form-control @error('cfop') is-invalid @enderror" 
                               value="{{ old('cfop', $product->cfop) }}"
                               maxlength="10"
                               placeholder="Ex: 5102">
                        @error('cfop')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="unidade" class="form-label">Unidade</label>
                        <input type="text" 
                               name="unidade" 
                               id="unidade"
                               class="form-control @error('unidade') is-invalid @enderror" 
                               value="{{ old('unidade', $product->unidade) }}"
                               maxlength="10"
                               placeholder="Ex: UN">
                        @error('unidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="valor_unitario" class="form-label">Valor Unitário *</label>
                        <input type="number" 
                               name="valor_unitario" 
                               id="valor_unitario"
                               class="form-control @error('valor_unitario') is-invalid @enderror" 
                               value="{{ old('valor_unitario', $product->valor_unitario) }}"
                               step="0.01" 
                               min="0"
                               required
                               placeholder="0,00">
                        @error('valor_unitario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="aliquota_icms" class="form-label">Alíquota ICMS (%)</label>
                        <input type="number" 
                               name="aliquota_icms" 
                               id="aliquota_icms"
                               class="form-control @error('aliquota_icms') is-invalid @enderror" 
                               value="{{ old('aliquota_icms', $product->aliquota_icms) }}"
                               step="0.01" 
                               min="0" 
                               max="100"
                               placeholder="0,00">
                        @error('aliquota_icms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="aliquota_pis" class="form-label">Alíquota PIS (%)</label>
                        <input type="number" 
                               name="aliquota_pis" 
                               id="aliquota_pis"
                               class="form-control @error('aliquota_pis') is-invalid @enderror" 
                               value="{{ old('aliquota_pis', $product->aliquota_pis) }}"
                               step="0.01" 
                               min="0" 
                               max="100"
                               placeholder="0,00">
                        @error('aliquota_pis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="aliquota_cofins" class="form-label">Alíquota COFINS (%)</label>
                        <input type="number" 
                               name="aliquota_cofins" 
                               id="aliquota_cofins"
                               class="form-control @error('aliquota_cofins') is-invalid @enderror" 
                               value="{{ old('aliquota_cofins', $product->aliquota_cofins) }}"
                               step="0.01" 
                               min="0" 
                               max="100"
                               placeholder="0,00">
                        @error('aliquota_cofins')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if($product->external_id)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informação:</strong> Este produto está sincronizado com a API Plug4Market (ID: {{ $product->external_id }}).
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atenção:</strong> Este produto não está sincronizado com a API Plug4Market.
                </div>
            @endif

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> Atualizar Produto
                </button>
                <a href="{{ route('plug4market.products.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fa fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection 
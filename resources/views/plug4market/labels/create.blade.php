@extends('default.layout')

@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ $title ?? 'Nova Etiqueta de Pedido Plug4Market' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('plug4market.labels.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        <form action="{{ route('plug4market.labels.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="orderId" class="form-label">Pedido *</label>
                        <select name="orderId" 
                                id="orderId"
                                class="form-control @error('orderId') is-invalid @enderror" 
                                required>
                            <option value="">Selecione um pedido</option>
                            @foreach($orders as $order)
                                <option value="{{ $order['id'] }}" 
                                        {{ old('orderId') == $order['id'] ? 'selected' : '' }}>
                                    #{{ $order['id'] }} - {{ $order['customerName'] ?? 'Cliente não informado' }}
                                    @if(isset($order['totalAmount']))
                                        (R$ {{ number_format($order['totalAmount'], 2, ',', '.') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('orderId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Selecione o pedido para o qual será criada a etiqueta
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shippingCompany" class="form-label">Transportadora *</label>
                        <select name="shippingCompany" 
                                id="shippingCompany"
                                class="form-control @error('shippingCompany') is-invalid @enderror" 
                                required>
                            <option value="">Selecione a transportadora</option>
                            <option value="correios" {{ old('shippingCompany') == 'correios' ? 'selected' : '' }}>Correios</option>
                            <option value="jadlog" {{ old('shippingCompany') == 'jadlog' ? 'selected' : '' }}>Jadlog</option>
                            <option value="total" {{ old('shippingCompany') == 'total' ? 'selected' : '' }}>Total</option>
                            <option value="sequoia" {{ old('shippingCompany') == 'sequoia' ? 'selected' : '' }}>Sequoia</option>
                            <option value="latam" {{ old('shippingCompany') == 'latam' ? 'selected' : '' }}>LATAM</option>
                            <option value="gol" {{ old('shippingCompany') == 'gol' ? 'selected' : '' }}>GOL</option>
                            <option value="azul" {{ old('shippingCompany') == 'azul' ? 'selected' : '' }}>Azul</option>
                            <option value="outros" {{ old('shippingCompany') == 'outros' ? 'selected' : '' }}>Outros</option>
                        </select>
                        @error('shippingCompany')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Empresa responsável pelo transporte
                        </small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shippingService" class="form-label">Serviço de Envio *</label>
                        <input type="text" 
                               name="shippingService" 
                               id="shippingService"
                               class="form-control @error('shippingService') is-invalid @enderror" 
                               value="{{ old('shippingService') }}"
                               required 
                               maxlength="255"
                               placeholder="Ex: PAC, SEDEX, Expresso">
                        @error('shippingService')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Tipo de serviço oferecido pela transportadora
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trackingCode" class="form-label">Código de Rastreio</label>
                        <input type="text" 
                               name="trackingCode" 
                               id="trackingCode"
                               class="form-control @error('trackingCode') is-invalid @enderror" 
                               value="{{ old('trackingCode') }}"
                               maxlength="255"
                               placeholder="Ex: BR123456789BR">
                        @error('trackingCode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Código de rastreamento do envio (opcional)
                        </small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="shippingDate" class="form-label">Data de Envio</label>
                        <input type="datetime-local" 
                               name="shippingDate" 
                               id="shippingDate"
                               class="form-control @error('shippingDate') is-invalid @enderror" 
                               value="{{ old('shippingDate') }}">
                        @error('shippingDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Data e hora do envio
                        </small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estimatedDelivery" class="form-label">Previsão de Entrega</label>
                        <input type="date" 
                               name="estimatedDelivery" 
                               id="estimatedDelivery"
                               class="form-control @error('estimatedDelivery') is-invalid @enderror" 
                               value="{{ old('estimatedDelivery') }}">
                        @error('estimatedDelivery')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Data prevista para entrega
                        </small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="shippingCost" class="form-label">Custo do Envio</label>
                        <input type="number" 
                               name="shippingCost" 
                               id="shippingCost"
                               class="form-control @error('shippingCost') is-invalid @enderror" 
                               value="{{ old('shippingCost') }}"
                               step="0.01" 
                               min="0"
                               placeholder="0,00">
                        @error('shippingCost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Valor do frete cobrado
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Observações</label>
                <textarea name="notes" 
                          id="notes"
                          class="form-control @error('notes') is-invalid @enderror" 
                          rows="3"
                          maxlength="1000"
                          placeholder="Observações adicionais sobre o envio...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    Informações adicionais sobre o envio (máximo 1000 caracteres)
                </small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Informação:</strong> Os campos marcados com * são obrigatórios. A etiqueta será criada na API Plug4Market.
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> Criar Etiqueta
                </button>
                <a href="{{ route('plug4market.labels.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fa fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection 
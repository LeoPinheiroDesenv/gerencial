@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
    <div class="card-header">
        <div class="col-12">
            <h3 class="card-title">Configurações - WooCommerce</h3>
        </div>
    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                @if(session()->has('success'))
                                <div class="alert alert-success">
                    {{ session()->get('success') }}
                                </div>
                                @endif
                                @if(session()->has('error'))
                                <div class="alert alert-danger">
                                    {{ session()->get('error') }}
                                </div>
                                @endif
                            </div>
                        </div>

        <form action="{{ route('woocommerce-config.store') }}" method="POST">
            @csrf
                        <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>URL da Loja</label>
                        <input type="text" name="store_url" class="form-control" value="{{ $config->store_url ?? '' }}" required>
                    </div>
                                        </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Consumer Key</label>
                        <input type="text" name="consumer_key" class="form-control" value="{{ $config->consumer_key ?? '' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Consumer Secret</label>
                        <input type="text" name="consumer_secret" class="form-control" value="{{ $config->consumer_secret ?? '' }}" required>
                    </div>
                                        </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Markup de Preço (%)</label>
                        <input type="number" step="0.01" name="markup_preco" class="form-control" value="{{ $config->price_markup ?? 0 }}" required>
                                    </div>
                                </div>
                            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Intervalo de Sincronização (minutos)</label>
                        <input type="number" name="intervalo_sincronizacao" class="form-control" value="{{ $config->sync_interval ?? 60 }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                                            <label>
                            <input type="checkbox" name="ativar_integracao" value="1" {{ isset($config) && $config->is_active ? 'checked' : '' }}>
                            Ativar Integração
                                            </label>
                                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="sincronizar_produtos" value="1" {{ isset($config) && $config->sync_products ? 'checked' : '' }}>
                            Sincronizar Produtos
                        </label>
                                </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="sincronizar_pedidos" value="1" {{ isset($config) && $config->sync_orders ? 'checked' : '' }}>
                            Sincronizar Pedidos
                        </label>
                            </div>
                        </div>
                    </div>

                        <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="sincronizar_estoque" value="1" {{ isset($config) && $config->sync_stock ? 'checked' : '' }}>
                            Sincronizar Estoque
                        </label>
                    </div>
                            </div>
                        </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="sincronizacao_automatica" value="1" {{ isset($config) && $config->auto_sync ? 'checked' : '' }}>
                            Sincronização Automática
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
            </div>
        </div>
        </form>
    </div>
</div>

@endsection

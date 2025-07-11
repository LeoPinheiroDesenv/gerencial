@extends('default.layout', ['title' => 'SPED Configuração'])
@section('content')
<div class="card card-custom gutter-b">
    <div class="card-body">
        <br>
        <div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">

            <h6 class="mb-0">Configuração SPED</h6>
            <form method="post" action="{{ route('sped-config.store') }}" class="mt-3">
                @csrf
                <div class="row">

                    <div class="col-lg-3 col-6">
                        <label>Código conta analítica</label>
                        <input type="text" name="codigo_conta_analitica" class="form-control" value="{{ $item != null ? $item->codigo_conta_analitica : '' }}">
                    </div>

                    <div class="col-lg-3 col-6">
                        <label>Código da receita</label>
                        <input type="text" name="codigo_receita" class="form-control" value="{{ $item != null ? $item->codigo_receita : '' }}">
                    </div>

                    <div class="col-lg-2 col-6">
                        <label>Gerar bloco K</label>
                        <select class="custom-select form-control" name="gerar_bloco_k">
                            <option @if($item && $item->gerar_bloco_k == '0') selected @endif value="0">Não</option>
                            <option @if($item && $item->gerar_bloco_k == '1') selected @endif value="1">Sim</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-6">
                        <label>Gerar bloco K</label>
                        <select class="custom-select form-control" name="layout_bloco_k">
                            <option @if($item && $item->layout_bloco_k == '0') selected @endif value="0">
                                Leiaute simplificado
                            </option>
                            <option @if($item && $item->layout_bloco_k == '1') selected @endif value="1">
                                Leiaute completo
                            </option>
                            <option @if($item && $item->layout_bloco_k == '2') selected @endif value="2">
                                Leiaute restrito aos saldos de estoque
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-6 col-12">
                        <label>Código de obrigação E116</label>
                        <select class="custom-select form-control" name="codigo_obrigacao">
                            @foreach(App\Models\SpedConfig::codigosDeObrigacao() as $key => $c)
                            <option @if($item && $item->codigo_obrigacao == $key) selected @endif value="{{ $key }}">{{ $key }} - {{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-6">
                        <label>Data de vencimento E116</label>
                        <input required type="text" name="data_vencimento" class="form-control" value="{{ $item != null ? $item->data_vencimento : '' }}" data-mask="00">
                    </div>

                    <div class="col-12">
                        <hr>
                        <button class="btn btn-success mt-2" type="submit"> 
                            Salvar Configuração
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

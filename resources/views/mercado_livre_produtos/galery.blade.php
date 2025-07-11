@extends('default.layout', ['title' => 'Galeria do Produto'])
@section('css')
<style type="text/css">
    .img-ml{
        height: 200px;
        margin-left: auto;
        margin-right: auto;
        width: 50%;
    }
</style>
@endsection
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Galeria <strong>{{ $item->nome }}</strong></h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('mercado-livre-produtos.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <form class="row" action="{{ route('mercado-livre-produtos-galery-store') }}" enctype="multipart/form-data" method="post">
            @csrf
            <input type="hidden" name="produto_id" value="{{ $item->id }}">
            @foreach($retorno->pictures as $i)
            <input type="hidden" name="picture[]" value="{{ $i->url }}">
            @endforeach
            <div class="col-md-12">
                <div class="image-input image-input-outline" id="kt_image_1">
                    <div class="image-input-wrapper"
                    @if(!isset($produto) || $produto->imagem == '') style="background-image: url(/imgs/no_image.png)" @else
                    style="background-image: url(/imgs_produtos/{{$produto->imagem}})"
                    @endif></div>
                    <label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
                        <i class="fa fa-pencil icon-sm text-muted"></i>
                        <input type="file" name="image" accept="image/*">
                        <input type="hidden" name="profile_avatar_remove">
                    </label>
                    <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="cancel" data-toggle="tooltip" title="" data-original-title="Cancel avatar">
                        <i class="fa fa-close icon-xs text-muted"></i>
                    </span>
                </div>
            </div>
            <div class="col-md-12">

                <button class="btn btn-success mt-2">
                    <i class="ri-send-plane-fill"></i>
                    Enviar para plataforma
                </button>
            </div>
        </form>
        <hr>
        <div class="row mt-2">

            <h4 class="col-12">Imagens</h4><br>
            <h5 class="text-danger col-12">Atenção a plataforma pode demorar para processar as imagens e aparecerá em branco, aguarde o processamento!</h5>

            <div class="row"> 
                @foreach($retorno->pictures as $i)
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <img class="img-ml" src="{{ $i->url }}">
                        </div>
                        <div class="card-footer">
                            <form action="{{ route('mercado-livre-produtos.galery-delete') }}">
                                <input type="hidden" name="produto_id" value="{{ $item->id }}">

                                @foreach($retorno->pictures as $pic)
                                @if($i->url != $pic->url)
                                <input type="hidden" name="picture[]" value="{{ $pic->url }}">
                                @endif
                                @endforeach
                                <button class="btn btn-danger w-100">
                                    Remover
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
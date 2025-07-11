@extends('default.layout', ['title' => 'Respondendo Pergunta #'.$item->_id])

@section('content')

<div class="card mt-1">
    
    <div class="card-body">

        <h5>Data: <strong>{{ __date($item->data) }}</strong></h5>
        <h5>Anúncio: <strong>{{ $item->anuncio ? $item->anuncio->nome : '#'.$item->item_id }}</strong></h5>

        <p>Pergunta: <strong>{{ $item->texto }}</strong></p>
        
        <form method="post" action="{{ route('mercado-livre-perguntas.update', [$item->id]) }}">
            @csrf
            @method('put')
            <div class="pl-lg-4">
                <div class="row g-2">
                    <div class="col-md-12">
                        @if($item->status != 'ANSWERED')
                        
                        <label>Resposta</label>
                        <textarea required name="resposta" class="form-control"></textarea>
                        @else

                        <label>Resposta</label>
                        <textarea readonly required name="resposta" class="form-control">{{$item->resposta}}</textarea>
                        @endif

                    </div>
                    <hr class="mt-4">
                    @if($item->status != 'ANSWERED')
                    <div class="col-12" style="text-align: right;">
                        <br>
                        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

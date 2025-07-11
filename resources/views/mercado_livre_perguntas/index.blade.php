@extends('default.layout', ['title' => 'Perguntas Mercado Livre'])
@section('content')
<div class="card card-custom gutter-b">
    <div class="card-body">

        <hr class="mt-3">

        <form class="col-lg-12">

            <div class="row mt-3">
                <div class="col-md-2">

                    <label>Status</label>
                    <select name="status" class="form-control form-select">
                        <option @if($status == 'UNANSWERED') selected @endif value="UNANSWERED">AGUARDANDO RESPOSTA</option>
                        <option @if($status == 'ANSWERED') selected @endif value="ANSWERED">RESPONDIDA</option>
                    </select>
                </div>
                <div class="col-md-3 text-left ">
                    <br>
                    <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                    <a id="clear-filter" class="btn btn-danger" href="{{ route('mercado-livre-perguntas.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                </div>
            </div>

        </form>

        <div class="col-md-12 mt-3">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Anúncio</th>
                            <th>Pergunta</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th width="10%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>

                            <td>{{ $item->anuncio ? $item->anuncio->nome : '#'.$item->item_id }}</td>
                            <td>{{ $item->texto }}</td>
                            <td>{{ __date($item->data) }}</td>
                            <td>
                                @if($item->status == 'UNANSWERED')
                                AGUARDANDO RESPOSTA
                                @elseif($item->status == 'ANSWERED')
                                RESPONDIDA
                                @else
                                --
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('mercado-livre-perguntas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                    @method('delete')
                                    @csrf

                                    <a title="Responder pergunta" class="btn btn-dark btn-sm text-white" href="{{ route('mercado-livre-perguntas.show', [$item->id]) }}">
                                        <i class="la la-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                        <i class="la la-trash"></i> 
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <br>

            </div>
        </div>
        {!! $data->appends(request()->all())->links() !!}

    </div>
</div>
@endsection


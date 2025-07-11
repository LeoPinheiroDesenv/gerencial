@extends('default.layout')
@section('content')
<div class="d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="card card-custom gutter-b example example-compact">
        <div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
            <div class="col-lg-12">
                <br>
                <form method="post" action="{{{ isset($listapromocao) ? '/listapromocao/update': '/listapromocao/save' }}}" enctype="multipart/form-data">

                    <input type="hidden" name="id" value="{{{ isset($listapromocao) ? $listapromocao->id : 0 }}}">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h3 class="card-title">{{isset($listapromocao) ? 'Editar' : 'Nova'}} Promoções</h3>
                        </div>
                    </div>
                    @csrf

                    <div class="row">
                        <div class="col-xl-2"></div>
                        <div class="col-xl-8">
                            <div class="kt-section kt-section--first">
                                <div class="kt-section__body">

                                    <div class="row">
                                        <div class="form-group validated col-sm-4 col-lg-4">
                                            <label class="col-form-label">Nome</label>
                                            <div class="">
                                                <input type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" name="nome" value="{{{ isset($listapromocao) ? $listapromocao->nome : old('nome') }}}">
                                                @if($errors->has('nome'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('nome') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group validated col-sm-4 col-lg-4">
                                            <label class="col-form-label">Data de Início</label>
                                            <div class="">
                                                <input type="date" class="form-control @if($errors->has('data_inicio')) is-invalid @endif" name="data_inicio" value="{{{ isset($listapromocao) ? $listapromocao->data_inicio : old('data_inicio') }}}">
                                                @if($errors->has('data_inicio'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('data_inicio') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group validated col-sm-4 col-lg-4">
                                            <label class="col-form-label">Data de Término</label>
                                            <div class="">
                                                <input type="date" class="form-control @if($errors->has('data_termino')) is-invalid @endif" name="data_termino" value="{{{ isset($listapromocao) ? $listapromocao->data_termino : old('data_termino') }}}">
                                                @if($errors->has('data_termino'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('data_termino') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">

                        <div class="row">
                            <div class="col-xl-2"></div>
                            <div class="col-lg-3 col-sm-6 col-md-4">
                                <a style="width: 100%" class="btn btn-danger" href="/listapromocao">
                                    <i class="la la-close"></i>
                                    <span class="">Cancelar</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-md-4">
                                <button style="width: 100%" type="submit" class="btn btn-success">
                                    <i class="la la-check"></i>
                                    <span class ="">Salvar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
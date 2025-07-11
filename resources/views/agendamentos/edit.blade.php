@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b @if(env('ANIMACAO')) animate__animated @endif animate__bounce">
	<div class="card-body">
		<form class="row" id="kt_user_profile_aside" method="post" action="/agendamentos/update/{{ $agendamento->id }}" style="margin-left: 10px; margin-right: 10px;">
			@csrf
			<div class="form-group validated col-md-4">
				<label class="col-form-label" id="">Cliente</label><br>
				<select required class="form-control select2" style="width: 100%" id="kt_select2_3" name="cliente_id">
					<option value="">Selecione o cliente</option>
					@foreach($clientes as $c)
					<option @if($agendamento->cliente_id == $c->id) selected @endif value="{{$c->id}}">{{$c->id}} - {{$c->razao_social}}</option>
					@endforeach
				</select>
			</div>

			<div class="form-group validated col-md-4">
				<label class="col-form-label" id="">Atendente</label><br>
				<select required class="form-control select2" style="width: 100%" id="kt_select2_4" name="funcionario_id">
					<option value="">Selecione o atendente</option>
					@foreach($funcionarios as $f)
					<option @if($agendamento->funcionario_id == $f->id) selected @endif value="{{$f->id}}">{{$f->id}} - {{$f->nome}}</option>
					@endforeach
				</select>
			</div>

			<div class="form-group validated col-md-2">
				<label class="col-form-label">Data</label>
				<input type="date" name="data" value="{{ $agendamento->data }}" class="form-control data_inicio_servico"/>
			</div>

			<div class="form-group col-md-2">
				<label class="col-form-label">Horário de início</label><br>

				<div class="input-group timepicker">

					<input value="{{ $agendamento->inicio }}" name="inicio" class="form-control" id="kt_timepicker_2" readonly="readonly" placeholder="Selecione o início" type="text">
					<div class="input-group-append">
						<span class="input-group-text">
							<i class="la la-clock-o"></i>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group col-md-2">
				<label class="col-form-label" id="">Horário de término</label><br>

				<div class="input-group timepicker">

					<input value="{{ $agendamento->termino }}" name="termino" class="form-control" id="kt_timepicker_2" readonly="readonly" placeholder="Selecione o início" type="text">
					<div class="input-group-append">
						<span class="input-group-text">
							<i class="la la-clock-o"></i>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group validated col-md-10">
				<label class="col-form-label" id="">Observação</label><br>
				<input class="form-control" value="{{ $agendamento->observacao }}" type="text" name="observacao">
			</div>

			<div class="form-group validated col-md-12">
				<button class="btn btn-light-success font-weight-bold spinner-white spinner-right float-right">Atualizar</button>
			</div>

		</form>
	</div>
</div>

@endsection	
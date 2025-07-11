@extends('default.layout', ['title' => 'Editar Produto Mercado Livre'])
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<form action="{{ route('mercado-livre-produtos.update', [$item->id]) }}" method="post">
			@csrf
			@method('put')
			<h4>Editar produtos do Mercado Livre</h4>
			<div style="text-align: right; margin-top: -35px;">
				<a href="{{ route('mercado-livre-produtos.index') }}" class="btn btn-danger btn-sm px-3">
					<i class="ri-arrow-left-double-fill"></i>Voltar
				</a>
			</div>

			<div class="row">
				<div class="form-group col-md-4">
					<label class="col-form-label">Nome</label>
					<input type="text" class="form-control" name="nome" value="{{ $item->nome }}">
				</div>

				<div class="form-group col-md-2">
					<label class="col-form-label">Categoria do anúncio</label>
					<select id="inp-mercado_livre_categoria" class="form-control input-ml" name="mercado_livre_categoria" value="{{ $item->mercado_livre_categoria }}">
						<option selected value="{{ $item->mercado_livre_categoria }}">{{ $item->categoriaMercadoLivre->nome }}</option>
					</select>
				</div>

				@if(sizeof($prodML->variations) == 0)
				<div class="form-group col-md-2">
					<label class="col-form-label">Valor do anúncio</label>
					<input type="text" class="form-control money input-ml" name="mercado_livre_valor" value="{{ moeda($item->mercado_livre_valor) }}">
				</div>
				@endif
				<input type="hidden" id="tipo_publicacao_hidden" value="{{ isset($item) ? $item->mercado_livre_tipo_publicacao : '' }}">

				<div class="form-group col-md-4">
					<label class="col-form-label">Link do youtube</label>
					<input class="form-control" name="mercado_livre_youtube" value="{{ $item->mercado_livre_youtube }}">
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label">Descrição</label>
					<textarea class="form-control" rows="12" name="mercado_livre_descricao">{{ isset($descricaoML->plain_text) ? $descricaoML->plain_text : ''}}</textarea>
				</div>

				@if(sizeof($prodML->variations) > 0)
				<h4>Variações do produto</h4>
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>Tipo</th>
								<th>Variação</th>
								<th>Valor</th>
								<th>Quantidade</th>
							</tr>
						</thead>
						<tbody>
							@foreach($prodML->variations as $v)
							<tr>
								<td>
									<input readonly class="form-control" type="" value="{{ $v->attribute_combinations[0]->name }}" name="variacao_nome[]">
								</td>
								<td>
									<input readonly class="form-control" type="" value="{{ $v->attribute_combinations[0]->value_name }}" name="variacao_valor_nome[]">
								</td>
								<td>
									<input class="form-control moeda" type="tel" value="{{ __moeda($v->price) }}" name="variacao_valor[]">
								</td>

								<td>
									<input class="form-control " type="tel" value="{{ ($v->available_quantity) }}" name="variacao_quantidade[]">
								</td>

								<input type="hidden" value="{{ $v->id }}" name="variacao_id[]">

							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				@endif
			</div>

			<div class="col-12 mt-2" style="text-align: right;">
				<button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
			</div>
		</form>

	</div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">
	$("#inp-mercado_livre_categoria").select2({
		minimumInputLength: 2,
		language: "pt-BR",
		placeholder: "Digite para buscar a categoria do anúncio",
		width: "100%",
		ajax: {
			cache: true,
			url: path + "mercado-livre-get-categorias",
			dataType: "json",
			data: function (params) {
				console.clear();
				var query = {
					pesquisa: params.term,
				};
				return query;
			},
			processResults: function (response) {
				var results = [];

				$.each(response, function (i, v) {
					var o = {};
					o.id = v._id;

					o.text = v.nome;
					o.value = v._id;
					results.push(o);
				});
				return {
					results: results,
				};
			},
		},
	});
</script>
@endsection

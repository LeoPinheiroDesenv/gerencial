@extends('default.layout', ['title' => isset($item) ? 'Editar Uso e Consumo' : 'Novo Uso e Consumo'])
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				<form method="post" action="{{ isset($item) ? route('uso-consumo.update', [$item->id]) : route('uso-consumo.store') }}">
					@csrf
					@isset($item)
					@method('put')
					@endif

					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">
							<h3 class="card-title">{{isset($item) ? 'Editar' : 'Nova'}} Uso e Consumo</h3>
						</div>
					</div>

					<div class="row">
						<div class="col-xl-12">

							<div class="row">
								<div class="form-group validated col-md-6 col-12">
									<label class="col-form-label">Funcionário</label>
									<select class="form-control select2" id="kt_select2_3" name="funcionario_id">
										<option value="">Selecione o funcionário</option>
										@foreach($funcionarios as $f)
										<option @isset($item) @if($item->funcionario_id == $f->id) selected @endif @endif value="{{$f->id}}">{{$f->id}} - {{$f->nome}} ({{$f->cpf}})</option>
										@endforeach
									</select>
								</div>
							</div>

							<div class="row line-row">
								<div class="col-12 appends">
									<div class="dynamic-form row mt-4">
										<h4 class="col-12">Produtos</h4>

										@isset($item)
										@foreach($item->itens as $i)
										<div class="col-md-6 col-12">
											<label class="col-form-label">Produto</label>
											<select required class="form-control produto_id" name="produto_id[]">
												<option value="{{ $i->produto_id }}">
													{{ $i->produto->nome }}
												</option>
											</select>
										</div>
										<div class="col-md-2 col-12">
											<label class="col-form-label">Quantidade</label>
											<input required class="form-control quantidade" value="{{ $i->quantidade }}" name="quantidade[]"/>
										</div>
										<div class="col-md-2 col-12">
											<label class="col-form-label">Valor Unit.</label>
											<input required class="form-control valor_unitario money" value="{{ moeda($i->valor_unitario) }}" name="valor_unitario[]"/>
										</div>
										<div class="col-md-2 col-12">
											<label class="col-form-label">Subtotal</label>
											<input readonly required class="form-control sub_total money" name="sub_total[]" value="{{ moeda($i->sub_total) }}"/>
										</div>
										@endforeach
										@else
										<div class="col-md-6 col-12">
											<label class="col-form-label">Produto</label>
											<select required class="form-control produto_id" name="produto_id[]">
											</select>
										</div>
										<div class="col-md-2 col-12">
											<label class="col-form-label">Quantidade</label>
											<input required class="form-control quantidade" value="1" name="quantidade[]"/>
										</div>
										<div class="col-md-2 col-12">
											<label class="col-form-label">Valor Unit.</label>
											<input required class="form-control valor_unitario money" name="valor_unitario[]"/>
										</div>
										<div class="col-md-2 col-12">
											<label class="col-form-label">Subtotal</label>
											<input readonly required class="form-control sub_total money" name="sub_total[]"/>
										</div>
										@endif
									</div>
								</div>
							</div>
							<div class="row col-12 mt-4">
								<button type="button" class="btn btn-info btn-clone">
									<i class="la la-plus"></i> Adicionar linha
								</button>
							</div>
						</div>

						<h4 class="col-12 mt-2 text-right">Subtotal: <strong class="sub_total_text">R$ {{ isset($item) ? moeda($item->valor_total+$item->desconto-$item->acrescimo) : '0,00' }}</strong></h4>
					</div>

					<div class="row">
						<div class="col-xl-12">

							<div class="row">
								<div class="form-group validated col-12">
									<label class="col-form-label">Observação</label>
									<input type="text" class="form-control" name="observacao" value="{{ isset($item) ? $item->observacao : '' }}">
								</div>

								<div class="form-group validated col-6 col-md-2">
									<label class="col-form-label">Desconto</label>
									<input type="tel" class="form-control money" name="desconto" value="{{ isset($item) ? moeda($item->desconto) : '' }}">
								</div>

								<div class="form-group validated col-6 col-md-2">
									<label class="col-form-label">Acréscimo</label>
									<input type="tel" class="form-control money" name="acrescimo" value="{{ isset($item) ? moeda($item->acrescimo) : '' }}">
									<input type="hidden" class="form-control" id="soma_produtos" name="soma_produtos" value="{{ isset($item) ? $item->valor_total+$item->desconto-$item->acrescimo : '' }}">
								</div>
							</div>
						</div>
					</div>
					<br>
					<div class="card-footer">

						<div class="row">
							<div class="col-xl-2">
							</div>
							<div class="col-lg-3 col-sm-6 col-md-4">
								<a style="width: 100%" class="btn btn-danger" href="{{ route('uso-consumo.index') }}">
									<i class="la la-close"></i>
									<span class="">Cancelar</span>
								</a>
							</div>
							<div class="col-lg-3 col-sm-6 col-md-4">
								<button style="width: 100%" type="submit" class="btn btn-success">
									<i class="la la-check"></i>
									<span class="">Salvar</span>
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

@section('javascript')
<script type="text/javascript">

	$(document).on("change", ".produto_id", function() {
		let id = $(this).val()
		$elem = $(this).closest(".dynamic-form");
		$qtd = $elem.find('.quantidade')
		$valor_unitario = $elem.find('.valor_unitario')
		$sub_total = $elem.find('.sub_total')

		$.get(path + 'produtos/autocompleteProduto', {id: id})
		.done((res) => {

			console.log(res)
			$qtd.val('1')
			$valor_unitario.val(convertFloatToMoeda(res.valor_venda))
			$sub_total.val(convertFloatToMoeda(res.valor_venda))
			somaTotal()

		})
		.fail((err) => {
			console.log(err)
			swal("Erro", "Erro ao encontrar produto", "error")
		})
	})

	$(document).on("change", ".valor_unitario", function() {
		$elem = $(this).closest(".dynamic-form");
		$qtd = $elem.find('.quantidade')

		let qtd = $qtd.val()
		$sub_total = $elem.find('.sub_total')

		let valorUnit = convertMoedaToFloat($(this).val())

		$sub_total.val(convertFloatToMoeda(qtd * valorUnit))
		somaTotal()

	})

	$(document).on("change", ".quantidade", function() {

		$elem = $(this).closest(".dynamic-form");
		let qtd = $(this).val()
		if(qtd <= 0){
			$(this).val('1')
			qtd = 1
			swal("Erro", "A quantidade deve ser maior que zero", "error")
		}
		$valor_unitario = $elem.find('.valor_unitario')
		$sub_total = $elem.find('.sub_total')

		let valorUnit = convertMoedaToFloat($valor_unitario.val())

		$sub_total.val(convertFloatToMoeda(qtd * valorUnit))
		somaTotal()
	})

	$(".produto_id").select2({
		minimumInputLength: 2,
		language: "pt-BR",
		placeholder: "Digite para buscar o produto",
		width: "100%",
		ajax: {
			cache: true,
			url: path + 'produtos/autocomplete',
			dataType: "json",
			data: function(params) {
				console.clear()
				let filial = $('#filial').val()
				let lista_id = $('#lista_id').val()

				var query = {
					pesquisa: params.term,
					filial_id: filial,
					lista_id: lista_id
				};
				return query;
			},
			processResults: function(response) {

				var results = [];

				$.each(response, function(i, v) {
					var o = {};
					o.id = v.id;

					o.text = v.nome + (v.grade ? " "+v.str_grade : "") + " | R$ " + parseFloat(v.valor_venda).toFixed(2).replace(".", ",")
					+ (v.referencia != "" ? " - Ref: " + v.referencia: "") + (parseFloat(v.estoqueAtual) > 0 ? " | Estoque: " + v.estoqueAtual : "");
					o.value = v.id;
					o.valor_venda = v.valor_venda;
					results.push(o);
				});
				return {
					results: results
				};
			}
		}
	});

	$('.btn-clone').on("click", function() {
		console.clear()
		var $elem = $(this)
		.closest(".row")
		.prev();

		var hasEmpty = false;

		$elem.find("input, select").each(function() {
			if (($(this).val() == "" || $(this).val() == null) && $(this).attr("type") != "hidden" && $(this).attr("type") != "file" && !$(this).hasClass("ignore")) {
				hasEmpty = true;
			}
		});

		if (hasEmpty) {
			swal(
				"Atenção",
				"Preencha todos os campos antes de adicionar novos.",
				"warning"
				);
			return;
		}

		$(".produto_id").select2("destroy");
		var $tr = $elem.find(".dynamic-form").first();
		var $clone = $tr.clone();

		$clone.show();

		$clone.find(".produto_id, .quantidade, .valor_unitario, .sub_total").val("");
		$elem.find('.appends').append($clone);

		setTimeout(() => {

			$(".produto_id").select2({
				minimumInputLength: 2,
				language: "pt-BR",
				placeholder: "Digite para buscar o produto",
				width: "100%",
				ajax: {
					cache: true,
					url: path + 'produtos/autocomplete',
					dataType: "json",
					data: function(params) {
						console.clear()
						let filial = $('#filial').val()
						let lista_id = $('#lista_id').val()

						var query = {
							pesquisa: params.term,
							filial_id: filial,
							lista_id: lista_id
						};
						return query;
					},
					processResults: function(response) {

						var results = [];

						$.each(response, function(i, v) {
							var o = {};
							o.id = v.id;

							o.text = v.nome + (v.grade ? " "+v.str_grade : "") + " | R$ " + parseFloat(v.valor_venda).toFixed(2).replace(".", ",")
							+ (v.referencia != "" ? " - Ref: " + v.referencia: "") + (parseFloat(v.estoqueAtual) > 0 ? " | Estoque: " + v.estoqueAtual : "");
							o.value = v.id;
							o.valor_venda = v.valor_venda;
							results.push(o);
						});
						return {
							results: results
						};
					}
				}
			});
			somaTotal()
		}, 200);

	})

	function somaTotal(){
		let soma = 0
		$('.sub_total').each(function(e){
			let v = convertMoedaToFloat($(this).val())
			soma += v
		})

		setTimeout(() => {
			$('.sub_total_text').text('R$ ' + convertFloatToMoeda(soma))
			$('#soma_produtos').val(soma)
		}, 10)
	}
</script>
@endsection
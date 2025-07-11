@extends('default.layout', ['title' => 'Cadastrar Produto Mercado Livre'])
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<h4>Cadastrando produtos do Mercado Livre</h4>

		{!! __view_locais('Disponibilidade') !!}

		<form method="post" action="{{ route('mercado-livre-produtos.store') }}">
			@csrf
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<td>Nome</td>
							<td>Status</td>
							<td>ID plataforma</td>
							<td>Valor de venda</td>
							<td>Valor de venda plataforma</td>
							<td>Estoque</td>
							<td>Valor de compra</td>
							<td>Código de barras</td>
							<td>Categoria</td>
							<td>Unidade</td>
							<td>Gerencia estoque</td>
							<td>NCM</td>
							<td>CEST</td>
							<td>CFOP sáida estadual</td>
							<td>CFOP sáida outro estado</td>

							<td>CFOP entrada estadual</td>
							<td>CFOP entrada outro estado</td>
							<td>%ICMS</td>
							<td>%PIS</td>
							<td>%COFINS</td>
							<td>%IPI</td>
							<td>%RED. BC</td>
							<td>CST/CSOSN</td>
							<td>CST/PIS</td>
							<td>CST/COFINS</td>
							<td>CST/IPI</td>
							<td>Código de enquandramento de IPI</td>
						</tr>
					</thead>
					<tbody>
						@foreach($produtosIsert as $p)
						<tr>
							<td>
								<input required style="width: 400px" type="" class="form-control" name="nome[]" value="{{ $p['nome'] }}">
								<input required type="hidden" name="mercado_livre_categoria[]" value="{{ $p['mercado_livre_categoria'] }}">
							</td>
							<td>
								<input style="width: 150px" readonly type="" class="form-control" name="mercado_livre_status[]" value="{{ $p['status'] }}">
							</td>
							<td>
								<input style="width: 250px" readonly type="" class="form-control" name="mercado_livre_id[]" value="{{ $p['mercado_livre_id'] }}">
							</td>
							<td>
								<input required style="width: 200px" type="tel" class="form-control moeda" name="valor_venda[]" value="{{ moeda($p['valor_venda']) }}">
							</td>
							<td>
								<input required style="width: 200px" type="tel" class="form-control moeda" name="mercado_livre_valor[]" value="{{ moeda($p['mercado_livre_valor']) }}">
							</td>

							<td>
								<input required style="width: 120px" type="tel" class="form-control" name="estoque[]" value="{{ moeda($p['estoque']) }}" data-mask='00000.00' data-mask-reverse="true">
							</td>
							<td>
								<input style="width: 200px" type="tel" class="form-control moeda" name="valor_compra[]" value="">
							</td>
							<td>
								<input style="width: 200px" type="tel" class="form-control" name="codigo_barras[]" value="">
							</td>
							<td>
								<select required style="width: 250px" class="form-control custom-select select_categoria" name="categoria_id[]">
									<option value=""></option>
									@foreach($categorias as $c)
									<option value="{{ $c->id }}">{{ $c->nome }}</option>
									@endforeach
								</select>
								@if($loop->first)
								<a onclick="setCategoria()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>

							<td>
								<select required style="width: 130px" class="form-control custom-select" required name="unidade[]">
									@foreach(App\Models\Produto::unidadesMedida() as $un)
									<option @if($un == 'UN') selected @endif value="{{ $un }}">{{ $un }}</option>
									@endforeach
								</select>
							</td>


							<td>
								<select style="width: 130px" class="form-control custom-select" required name="gerenciar_estoque[]">
									<option value="1">Sim</option>
									<option value="0">Não</option>
								</select>
							</td>
							<td>
								<input required style="width: 150px" type="tel" class="form-control ncm" name="ncm[]" value="">
								@if($loop->first)
								<a onclick="setNcm()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<input style="width: 150px" type="tel" class="form-control cest" name="cest[]" value="">
							</td>
							<td>
								<input required style="width: 150px" type="tel" class="form-control cfop cfop_estadual" name="cfop_estadual[]" value="">
								@if($loop->first)
								<a onclick="setCfopEstadual()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<input required style="width: 150px" type="tel" class="form-control cfop cfop_outro_estado" name="cfop_outro_estado[]" value="">
								@if($loop->first)
								<a onclick="setCfopOutroEstado()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>

							<td>
								<input required style="width: 150px" type="tel" class="form-control cfop cfop_entrada_estadual" name="cfop_entrada_estadual[]" value="">
								@if($loop->first)
								<a onclick="setCfopEntradaEstado()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<input required style="width: 150px" type="tel" class="form-control cfop cfop_entrada_outro_estado" name="cfop_entrada_outro_estado[]" value="">
								@if($loop->first)
								<a onclick="setCfopEntradaOutroEstado()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>

							<td>
								<input required style="width: 120px" type="tel" class="form-control perc perc_icms" name="perc_icms[]" value="">
								@if($loop->first)
								<a onclick="setPercIcms()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<input required style="width: 120px" type="tel" class="form-control perc perc_pis" name="perc_pis[]" value="">
								@if($loop->first)
								<a onclick="setPercPis()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<input required style="width: 120px" type="tel" class="form-control perc perc_cofins" name="perc_cofins[]" value="">
								@if($loop->first)
								<a onclick="setPercCofins()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<input required style="width: 120px" type="tel" class="form-control perc perc_ipi" name="perc_ipi[]" value="">
								@if($loop->first)
								<a onclick="setPercIpi()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<input style="width: 120px" type="tel" class="form-control perc perc_red_bc" name="perc_red_bc[]" value="">
							</td>
							<td>
								<select required style="width: 350px" class="form-control custom-select cst_csosn" required name="cst_csosn[]">
									<option value=""></option>
									@foreach($listaCTSCSOSN as $key => $l)
									<option value="{{ $key }}">{{ $key }} - {{ $l }}</option>
									@endforeach
								</select>
								@if($loop->first)
								<a onclick="setCstCsosn()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>

							<td>
								<select required style="width: 300px" class="form-control custom-select cst_pis" required name="cst_pis[]">
									<option value=""></option>
									@foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $l)
									<option value="{{ $key }}">{{ $key }} - {{ $l }}</option>
									@endforeach
								</select>
								@if($loop->first)
								<a onclick="setCstPis()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>

							<td>
								<select required style="width: 300px" class="form-control custom-select cst_cofins" required name="cst_cofins[]">
									<option value=""></option>
									@foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $l)
									<option value="{{ $key }}">{{ $key }} - {{ $l }}</option>
									@endforeach
								</select>
								@if($loop->first)
								<a onclick="setCstCofins()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<select required style="width: 300px" class="form-control custom-select cst_ipi" required name="cst_ipi[]">
									<option value=""></option>
									@foreach(App\Models\Produto::listaCST_IPI() as $key => $l)
									<option value="{{ $key }}">{{ $key }} - {{ $l }}</option>
									@endforeach
								</select>
								@if($loop->first)
								<a onclick="setCstIpi()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
							<td>
								<select required style="width: 500px" class="form-control custom-select cEnq" required name="cEnq[]">
									<option value=""></option>
									@foreach(App\Models\Produto::listaCenqIPI() as $key => $l)
									<option value="{{ $key }}">{{ $key }} - {{ $l }}</option>
									@endforeach
								</select>
								@if($loop->first)
								<a onclick="setCeqn()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
								@endif
							</td>
						</tr>

						@if(isset($p['variacoes']) && sizeof($p['variacoes']) > 0)
						<tr>
							<td colspan="4">
								<table class="table">
									<thead>
										<tr>
											<th>Tipo</th>
											<th>Variação</th>
											<th>Valor</th>
											<th>Quantidade</th>
											<th>ID variação</th>
										</tr>
									</thead>
									<tbody>
										@foreach($p['variacoes'] as $v)
										<tr>
											<td>
												<input readonly class="form-control" type="" value="{{ $v['nome'] }}" name="variacao_nome[]">
											</td>
											<td>
												<input readonly class="form-control" type="" value="{{ $v['valor_nome'] }}" name="variacao_valor_nome[]">
											</td>
											<td>
												<input readonly class="form-control moeda" type="tel" value="{{ moeda($v['valor']) }}" name="variacao_valor[]">
											</td>

											<td>
												<input readonly class="form-control " type="tel" value="{{ ($v['quantidade']) }}" name="variacao_quantidade[]">
											</td>

											<td>
												<input readonly class="form-control" type="" value="{{ $v['_id'] }}" name="variacao_id[]">
											</td>

											<input type="hidden" name="mercado_livre_id_row[]" value="{{ $p['mercado_livre_id'] }}">

										</tr>
										@endforeach
									</tbody>
								</table>
							</td>
						</tr>

						@endif
						@endforeach
					</tbody>
				</table>
			</div>
			<div class="col-12 mt-2" style="text-align: right;">
				<button type="submit" class="btn btn-success px-5" id="btn-store">Salvar Produtos</button>
			</div>
		</form>
	</div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">
	function setCategoria(){
		let v = $('.select_categoria').first().val()
		$('.select_categoria').val(v).change()
	}
	function setCfopEstadual(){
		let v = $('.cfop_estadual').first().val()
		$('.cfop_estadual').val(v).change()
	}
	function setCfopOutroEstado(){
		let v = $('.cfop_outro_estado').first().val()
		$('.cfop_outro_estado').val(v).change()
	}
	function setCfopEntradaEstado(){
		let v = $('.cfop_entrada_estadual').first().val()
		$('.cfop_entrada_estadual').val(v).change()
	}
	function setCfopEntradaOutroEstado(){
		let v = $('.cfop_entrada_outro_estado').first().val()
		$('.cfop_entrada_outro_estado').val(v).change()
	}
	function setNcm(){
		let v = $('.ncm').first().val()
		$('.ncm').val(v)
	}
	function setCstCsosn(){
		let v = $('.cst_csosn').first().val()
		$('.cst_csosn').val(v).change()
	}
	function setCstPis(){
		let v = $('.cst_pis').first().val()
		$('.cst_pis').val(v).change()
	}
	function setCstCofins(){
		let v = $('.cst_cofins').first().val()
		$('.cst_cofins').val(v).change()
	}
	function setCstIpi(){
		let v = $('.cst_ipi').first().val()
		$('.cst_ipi').val(v).change()
	}
	function setCeqn(){
		let v = $('.cEnq').first().val()
		$('.cEnq').val(v).change()
	}
	function setPercIcms(){
		let v = $('.perc_icms').first().val()
		$('.perc_icms').val(v)
	}
	function setPercPis(){
		let v = $('.perc_pis').first().val()
		$('.perc_pis').val(v)
	}
	function setPercCofins(){
		let v = $('.perc_cofins').first().val()
		$('.perc_cofins').val(v)
	}
	function setPercIpi(){
		let v = $('.perc_ipi').first().val()
		$('.perc_ipi').val(v)
	}
</script>
@endsection
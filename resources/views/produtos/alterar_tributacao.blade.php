@extends('default.layout', ['title' => 'Alterar tributação'])
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">

		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-sm-12 col-md-6 col-xl-3">
				<h5>Alterar tributação</h5>

			</div>
		</div>
		<br>
		<form method="get">
			<div class="row align-items-center">

				<div class="form-group col-md-3">
					<label class="col-form-label">Produto</label>
					<input type="text" name="nome" class="form-control" value="{{ isset($nome) ? $nome : '' }}">
				</div>

				<div class="form-group col-md-2">
					<label class="col-form-label">Categoria</label>
					<select name="categoria_id" class="custom-select">
						<option value="">Selecione</option>
						@foreach($categorias as $c)
						<option @if(isset($categoria_id) && $c->id == $categoria_id) selected @endif value="{{ $c->id }}">
							{{ $c->nome }}
						</option>
						@endforeach
					</select>
				</div>

				<div class="form-group col-md-5">
					<label class="col-form-label">CST/CSOSN</label>
					<select class="custom-select form-control" name="cst_csosn">
						<option value="">Selecione</option>
						@foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
						<option value="{{$key}}" @if(isset($cst_csosn) && $key == $cst_csosn) selected @endif>
							{{$key}} - {{$c}}
						</option>
						@endforeach
					</select>
				</div>

				<div class="col-lg-2 col-xl-2 mt-4">
					<button type="submit" class="btn btn-light-primary font-weight-bold">Filtrar</button>
				</div>
			</div>
		</form>

		@if(sizeof($data) > 0)
		<h4>Total de registros <strong class="text-info">{{ sizeof($data) }}</strong></h4>
		@endif
		<form method="post" action="/produtos/alterar-tributacao-save">
			@csrf
			<div class="m-4" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
				<br>

				<div class="row @if(env('ANIMACAO')) animate__animated @endif animate__backInRight table-responsive">

					<table class="table m-4">
						<thead>
							<tr>
								<th>Produto</th>
								<th>Valor de venda</th>
								<th>NCM</th>
								<th>CST/CSOSN</th>
								<th>CST PIS</th>
								<th>CST COFINS</th>
								<th>CST IPI</th>
								<th>CST/CSOSN Exportação</th>

								<th>% ICMS</th>
								<th>% PIS</th>
								<th>% COFINS</th>
								<th>% IPI</th>
								<th>% RED. BC</th>
								<th>% DIFERIMENTO</th>
								<th>CFOP saída estadual</th>
								<th>CFOP saída outro estado</th>
								<th>CFOP entrada estadual</th>
								<th>CFOP entrada outro estado</th>
								<th>Código Beneficio Fiscal</th>
							</tr>
						</thead>
						<tbody>
							@foreach($data as $item)
							<tr>
								<input type="hidden" name="produto_id[]" value="{{ $item->id }}">
								<td>
									<label style="width: 450px">
										{{$item->nome}}
									</label>
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control money" name="valor_venda[]" value="{{ moeda($item->valor_venda) }}">
								</td>
								<td>
									<input style="width: 150px" type="text" class="form-control ncm" name="ncm[]" value="{{ $item->NCM }}">
									@if($loop->first)
									<a onclick="setNcm()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>

								<td>
									<select required class="custom-select form-control cst_csosn" name="cst_csosn[]" style="width: 450px">
										@foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
										<option value="{{$key}}" @if($key == $item->CST_CSOSN) selected @endif>
											{{$key}} - {{$c}}
										</option>
										@endforeach
									</select>
									@if($loop->first)
									<a onclick="setCsosn()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>

								<td>
									<select required class="custom-select form-control pis" name="cst_pis[]" style="width: 350px">
										@foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
										<option value="{{$key}}" @if($key == $item->CST_PIS) selected @endif>
											{{$key}} - {{$c}}
										</option>
										@endforeach
									</select>
									@if($loop->first)
									<a onclick="setPis()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<select required class="custom-select form-control cofins" name="cst_cofins[]" style="width: 350px">
										@foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
										<option value="{{$key}}" @if($key == $item->CST_COFINS) selected @endif>
											{{$key}} - {{$c}}
										</option>
										@endforeach
									</select>
									@if($loop->first)
									<a onclick="setCofins()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<select required class="custom-select form-control ipi" name="cst_ipi[]" style="width: 350px">
										@foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
										<option value="{{$key}}" @if($key == $item->CST_IPI) selected @endif>
											{{$key}} - {{$c}}
										</option>
										@endforeach
									</select>
									@if($loop->first)
									<a onclick="setIpi()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>

								<td>
									<select required class="custom-select form-control CST_CSOSN_EXP" name="CST_CSOSN_EXP[]" style="width: 450px">
										@foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
										<option value="{{$key}}" @if($key == $item->CST_CSOSN_EXP) selected @endif>
											{{$key}} - {{$c}}
										</option>
										@endforeach
									</select>
									@if($loop->first)
									<a onclick="setCsosnExp()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>

								<td>
									<input required style="width: 150px" type="tel" class="form-control perc perc_icms" name="perc_icms[]" value="{{ $item->perc_icms }}">

									@if($loop->first)
									<a onclick="setPercIcms()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control perc perc_pis" name="perc_pis[]" value="{{ $item->perc_pis }}">
									@if($loop->first)
									<a onclick="setPercPis()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control perc perc_cofins" name="perc_cofins[]" value="{{ $item->perc_cofins }}">
									@if($loop->first)
									<a onclick="setPercCofins()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control perc perc_ipi" name="perc_ipi[]" value="{{ $item->perc_ipi }}">
									@if($loop->first)
									<a onclick="setPercIpi()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control perc perc_red_bc" name="pRedBC[]" value="{{ $item->pRedBC }}">
									@if($loop->first)
									<a onclick="setPercRedBc()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control perc perc_red_bc" name="pDif[]" value="{{ $item->pDif }}">
									@if($loop->first)
									<a onclick="setPercDif()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control cfop CFOP_saida_estadual" name="CFOP_saida_estadual[]" value="{{ $item->CFOP_saida_estadual }}">
									@if($loop->first)
									<a onclick="setCfopSaidaEstado()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control cfop CFOP_saida_inter_estadual" name="CFOP_saida_inter_estadual[]" value="{{ $item->CFOP_saida_inter_estadual }}">
									@if($loop->first)
									<a onclick="setCfopSaidaOutroEstado()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>

								<td>
									<input required style="width: 150px" type="tel" class="form-control cfop CFOP_entrada_estadual" name="CFOP_entrada_estadual[]" value="{{ $item->CFOP_entrada_estadual }}">
									@if($loop->first)
									<a onclick="setCfopEntradaEstado()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input required style="width: 150px" type="tel" class="form-control cfop CFOP_entrada_inter_estadual" name="CFOP_entrada_inter_estadual[]" value="{{ $item->CFOP_entrada_inter_estadual }}">
									@if($loop->first)
									<a onclick="setCfopEntradaOutroEstado()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								<td>
									<input style="width: 150px" type="tel" class="form-control cBenef" name="cBenef[]" value="{{ $item->cBenef }}">
									@if($loop->first)
									<a onclick="setcBenef()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
									@endif
								</td>
								
							</tr>
							@endforeach

							@if(sizeof($data) == 0)
							<tr>
								<td colspan="10">Filtre para buscar os produtos</td>
							</tr>
							@endif
						</tbody>
					</table>

				</div>
				<button class="btn btn-success float-right m-3">Salvar</button>
			</div>
		</form>
	</div>
</div>

@endsection
@section('javascript')
<script type="text/javascript">
	function setNcm(){
		let v = $('.ncm').first().val()
		$('.ncm').val(v)
	}
	function setCsosn(){
		let v = $('.cst_csosn').first().val()
		$('.cst_csosn').val(v).change()
	}
	function setCsosnExp(){
		let v = $('.CST_CSOSN_EXP').first().val()
		$('.CST_CSOSN_EXP').val(v).change()
	}
	function setPis(){
		let v = $('.pis').first().val()
		$('.pis').val(v).change()
	}
	function setCofins(){
		let v = $('.cofins').first().val()
		$('.cofins').val(v).change()
	}
	function setIpi(){
		let v = $('.ipi').first().val()
		$('.ipi').val(v).change()
	}
	function setPercIcms(){
		let v = $('.perc_icms').first().val()
		$('.perc_icms').val(v).change()
	}
	function setPercPis(){
		let v = $('.perc_pis').first().val()
		$('.perc_pis').val(v).change()
	}
	function setPercCofins(){
		let v = $('.perc_cofins').first().val()
		$('.perc_cofins').val(v).change()
	}
	function setPercIpi(){
		let v = $('.perc_ipi').first().val()
		$('.perc_ipi').val(v).change()
	}
	function setPercRedBc(){
		let v = $('.perc_red_bc').first().val()
		$('.perc_red_bc').val(v).change()
	}
		function setPercDif(){
		let v = $('.perc_dif').first().val()
		$('.perc_dif').val(v).change()
	}
	function setCfopSaidaEstado(){
		let v = $('.CFOP_saida_estadual').first().val()
		$('.CFOP_saida_estadual').val(v)
	}
	function setCfopSaidaOutroEstado(){
		let v = $('.CFOP_saida_inter_estadual').first().val()
		$('.CFOP_saida_inter_estadual').val(v)
	}
	function setCfopEntradaEstado(){
		let v = $('.CFOP_entrada_estadual').first().val()
		$('.CFOP_entrada_estadual').val(v)
	}
	function setCfopEntradaOutroEstado(){
		let v = $('.CFOP_entrada_inter_estadual').first().val()
		$('.CFOP_entrada_inter_estadual').val(v)
	}
	function setcBenef(){
		let v = $('.cBenef').first().val()
		$('.cBenef').val(v)
	}
</script>
@endsection


@extends('default.layout', ['title' => 'Produtos no Mercado Livre'])
@section('content')
<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-12">
				<div class="row">
					<a style="margin-left: 5px; margin-top: 5px;" href="/produtos/new?mercadolivre=1" class="btn btn-lg btn-success">
						<i class="fa fa-plus"></i>Novo Produto
					</a>
				</div>
			</div>

			<form method="get" action="/mercado-livre-produtos">
				<div class="row align-items-center">

					<div class="form-group col-md-4 col-12">
						<label class="col-form-label">Nome</label>
						<div>
							<div class="input-group">
								<input type="text" name="nome" class="form-control" value="{{{isset($nome) ? $nome : ''}}}"
								placeholder="Pesquisar por nome do produto">
							</div>
						</div>
					</div>

					<!-- <div class="form-group col-md-2 col-12">
						<label class="col-form-label">Código de barras</label>
						<div>
							<div class="input-group">
								<input type="text" name="codBarras" class="form-control" value="{{{isset($codBarras) ? $codBarras : ''}}}"
								placeholder="Pesquisar por código de barras">
							</div>
						</div>
					</div> -->
					<div class="col-lg-2 col-xl-2 mt-4">
						<button type="submit" class="btn btn-light-primary font-weight-bold">Filtrar</button>
					</div>
				</div>

			</form>

			<br>
			<h4>Lista de Produtos</h4>

			<div class="row">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>Produto</th>
								<th>Código</th>
								<th>Data de cadastro</th>
								<th>Valor de venda</th>
								<th>Estoque</th>
								<th>Categoria</th>
								<th>Código de barras</th>
								<th>Ações</th>
							</tr>
						</thead>
						<tbody>
							@forelse($data as $item)
							<tr class="{{ $item->statusMercadoLivre() }}">

								<td width="300">{{ $item->nome }}</td>
								<td width="150">{{ $item->mercado_livre_id }}</td>
								<td width="150">{{ __date($item->created_at) }}</td>
								<td width="150">{{ moeda($item->mercado_livre_valor) }}</td>
								<td width="150">
									{{ $item->estoquePorLocal($filial_id ?? null) }}
								</td>
								<td width="150">
									{{ $item->categoriaMercadoLivre ? $item->categoriaMercadoLivre->nome : '' }}
								</td>
								<td width="150">
									{{ $item->codBarras }}
								</td>
								<td>
									<form style="width: 250px">

										<a class="btn btn-warning btn-sm" href="{{ route('mercado-livre-produtos.edit', [$item->id, 'mercadolivre=1']) }}">
											<i class="la la-edit"></i>
										</a>

										<a title="Galeria" href="{{ route('mercado-livre-produtos.galery', [$item->id]) }}" class="btn btn-dark btn-sm"><i class="la la-image"></i></a>

										<a title="Duplicar" class="btn btn-sm btn-primary" onclick='swal("Atenção!", "Deseja duplicar este registro?", "warning").then((sim) => {if(sim){ location.href="/produtos/duplicar/{{ $item->id }}" }else{return false} })' href="#!">
											<i class="la la-copy"></i>	
										</a>

										<a title="Remover" class="btn btn-sm btn-danger" onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/produtos/delete/{{ $item->id }}" }else{return false} })' href="#!">
											<i class="la la-trash"></i>	
										</a>
									</form>
								</td>
							</tr>

							@empty
							<tr>
								<td colspan="18" class="text-center">Nada encontrado</td>
							</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@extends('default.layout')
@section('content')
<style type="text/css">
	#focus-codigo:hover{
		cursor: pointer
	}

	.search-prod{
		position: absolute;
		top: 0;
		margin-top: 80px;
		left: 10;
		width: 100%;
		max-height: 200px;
		overflow: auto;
		z-index: 9999;
		border: 1px solid #eeeeee;
		border-radius: 4px;
		background-color: #fff;
		box-shadow: 0px 1px 6px 1px rgba(0, 0, 0, 0.4);
	}

	.search-prod label:hover{
		cursor: pointer;
	}

	.search-prod label{
		margin-left: 10px;
		width: 100%;
		margin-top: 7px;
		font-size: 14px;
	}

	.delete-parcela:hover{
		cursor: pointer;
	}

	.modal.in .modal-dialog {
    transform: none;
    margin: 0 auto;
    top: 10%;
    position: absolute;
    }
</style>
<div class="row" id="anime" style="display: none">
	<div class="col s8 offset-s2">
		<lottie-player src="/anime/{{\App\Models\Venda::randSuccess()}}" background="transparent" speed="0.8" style="width: 100%; height: 300px;" autoplay>
		</lottie-player>
	</div>
</div>
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">

	<div id="content" style="display: block">

		<div class="card card-custom gutter-b example example-compact">
			<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__bounce">

				<div class="col-lg-12">

					<input type="hidden" name="id" value="{{{ isset($cliente) ? $cliente->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">Importando XML</h3>
						</div>
					</div>
					@csrf

					<div class="row">
						<div class="col-xl-2"></div>
						<div class="col-xl-8">

							<h4 class="center-align">Nota Fiscal: <strong class="text-primary">{{$dadosNf['nNf']}}</strong></h4>
							<h4 class="center-align">Data de emissão: <strong class="text-primary">{{ \Carbon\Carbon::parse($dadosNf['data_emissao'])->format('d/m/Y H:i')}}</strong></h4>
							<h4 class="center-align">Chave: <strong class="text-primary">{{$dadosNf['chave']}}</strong></h4>
							@if(count($dadosAtualizados) > 0)
							<div class="row">
								<div class="col-xl-12">
									<h5 class="text-success">Dados atualizados do fornecedor</h5>
									@foreach($dadosAtualizados as $d)
									<p class="red-text">{{$d}}</p>
									@endforeach
								</div>
							</div>
							@endif

							<div class="row">
								<div class="col s8">
									<h5>Fornecedor: <strong>{{$dadosEmitente['razaoSocial']}}</strong></h5>
									<h5>Nome Fantasia: <strong>{{$dadosEmitente['nomeFantasia']}}</strong></h5>
								</div>
								<div class="col s4">
									<h5>CNPJ: <strong>{{$dadosEmitente['cnpj']}}</strong></h5>
									<h5>IE: <strong>{{$dadosEmitente['ie']}}</strong></h5>
								</div>
							</div>
							<div class="row">
								<div class="col s8">
									<h5>Logradouro: <strong>{{$dadosEmitente['logradouro']}}</strong></h5>
									<h5>Numero: <strong>{{$dadosEmitente['numero']}}</strong></h5>
									<h5>Bairro: <strong>{{$dadosEmitente['bairro']}}</strong></h5>
								</div>
								<div class="col s4">
									<h5>CEP: <strong>{{$dadosEmitente['cep']}}</strong></h5>
									<h5>Fone: <strong>{{$dadosEmitente['fone']}}</strong></h5>
								</div>
							</div>

							<input type="hidden" id="pathXml" value="{{$pathXml}}">
							<input type="hidden" id="idFornecedor" value="{{$idFornecedor}}">
							<input type="hidden" id="nNf" value="{{$dadosNf['nNf']}}">
							<input type="hidden" id="data_emissao" value="{{$dadosNf['data_emissao']}}">
							<input type="hidden" id="vDesc" value="{{$dadosNf['vDesc']}}">
							<input type="hidden" id="prodSemRegistro" value="{{$dadosNf['contSemRegistro']}}">
							<input type="hidden" id="chave" value="{{$dadosNf['chave']}}">
							<input type="hidden" id="total_ipi" value="{{$dadosNf['total_ipi']}}">
							<input type="hidden" id="total_outras_despesas" value="{{$dadosNf['total_outras_despesas']}}">

						</div>
						<div class="col-xl-12">
							<div class="row">
								{!! __view_locais_select() !!}

								<div class="col-xl-12">

									@if(!empty($dadosNf['contSemRegistro']))
										<!--<p>*Produtos em <b class="text-danger">vermelho</b> ainda não foram cadastrados no sistema! Total: <strong class="prodSemRegistro text-danger">{{$dadosNf['contSemRegistro']}}</strong></p>
										<p>*Clique <a href="#" class="text-danger" data-toggle="modal" data-target="#registerModal"><b>aqui</b></a> para cadastrar todos produtos ainda não cadastrados.</p>-->
									@endif
									<!-- Modal de registro dos botões -->
									<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
										<div class="modal-dialog" role="document">
										<div class="modal-content">
											<div class="modal-header">
											<h5 class="modal-title" id="registerModalLabel">Registrar Produtos</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
											</div>
											<div class="modal-body">
												Ao confirmar, todos os produtos que ainda não foram cadastrados serão registrados automaticamente.
												<br><br>
												Por favor, não feche o navegador até que o processo seja concluído.
											</div>
											<div class="modal-footer">
											<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
											{{-- <form action="{{ route('registrar-produtos') }}" method="POST"> --}}
												{{-- @csrf --}}
												{{-- <input type="hidden" name="itens" value="{{ json_encode($itens) }}"> --}}
												{{-- <button id="salvar" type="submit" class="btn btn-success">Registrar Produtos</button> --}}
												<button onclick="registrarTodosProdutos()" class="btn btn-success">Registrar Todos os Produtos</button>
											{{-- </form> --}}
											</div>
										</div>
										</div>
									</div>

									<div class="modal fade" id="registrandoProdutos" registrados="0" nregistrados="0" tabindex="-1" role="dialog" aria-labelledby="registrandoProdutosLabel" aria-hidden="true">
										<div class="modal-dialog" role="document">
										<div class="modal-content">
											<div class="modal-header">
											<h5 class="modal-title" id="registrandoProdutosLabel">Registrando produtos</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
											</div>
											<div class="modal-body">
												Registrando Produtos
												<span id="qtdImportados">0</span> de <span id="total"></span>
												<br/>
												Por favor, não feche o navegador até que o processo seja concluído.
											</div>
											<div class="modal-footer">
												<button type="button" style="z-index:99999 !important" class="btn btn-cancelar btn-danger" data-dismiss="modal">Parar</button>
											</div>
										</div>
										</div>
									</div>
									<h4>Itens da NFe: <strong class="text-info">{{sizeof($itens)}}</strong></h4>
									<div class="row" style="margin-bottom: 15px;">
                                        <div class="col-xl-4">
                                            <div class="form-group">
                                                <label>Base de Cálculo</label>
                                                <select id="update_base_calculo" class="form-control" @if($dadosNf['contSemRegistro'] > 0) disabled @endif>
                                                    <option value="bruto">C. Bruto</option>
                                                    <option value="liquido" selected>C. Líquido</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="form-group">
                                                <label>% de Venda</label>
                                                <!-- Se quiser usar o valor padrão do cadastro, ex.: $config->percentual_lucro_padrao -->
												<input type="text" id="update_percentual_venda" class="form-control" value="{{ $config->percentual_lucro_padrao ?? '0' }}" 
                                                @if($dadosNf['contSemRegistro'] > 0) disabled @endif>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
												<button type="button" class="btn btn-success btn-block" onclick="atualizarTodosProdutos()"
                                                    @if($dadosNf['contSemRegistro'] > 0) disabled @endif>
                                                    Atualizar Todos
                                                </button>
                                            </div>
                                        </div>
                                    </div>
									<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">
										<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">
											<table class="datatable-table" style="max-width: 100%; overflow: scroll;">
												<thead class="datatable-head">
													<tr class="datatable-row" style="left: 0px;">
														<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">#</span></th>
														<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 180px;">Produto</span></th>
														<th data-field="Status" class="datatable-cell datatable-cell-sort"><span style="width: 90px;">Cod Barra</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Un. Compra</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Vlr. Compra XML</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Custo Bruto</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Custo Liquido</span></th>
														<th data-field="preco_venda_atual" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Valor de Venda Atual</span></th>
														<th data-field="base_calculo" class="datatable-cell datatable-cell-sort"><span style="width: 110px;">Base Cálculo</span></th>
                                                        <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">% de Venda</span></th>
                                                        <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Preço de Venda</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Qtd_XML</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Qtd</span></th>
														<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">NCM</span></th>
														<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CEST</span></th>
														<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CFOP</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CFOP Ent.</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Subtotal Des.</span></th>
														<th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Subtotal</span></th>
														<th data-field="Actions" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Ações</span></th>
													</tr>
												</thead>
										
												<tbody class="datatable-body">
													@foreach($itens as $index=>$i)
													<tr class="datatable-row produto-linha" id="tr_{{$i['codigo']}}_{{$i['codBarras']}}" data-id_xml="{{$i['codigo']}}" data-codBarras_xml="{{$i['codBarras']}}" style="left: 0px;">
													    <td class="datatable-cell"><span class="codigo" style="width: 70px;" id="codigo_{{$i['codigo']}}_{{$i['codBarras']}}"data-id_xml="{{$i['codigo']}}"data-codBarras_xml="{{$i['codBarras']}}">{{$i['codigo']}}</span></td>
														<td class="datatable-cell"><span style="width: 180px;" id="n_{{$i['codigo']}}_{{$i['codBarras']}}" class="{{$i['produtoNovo'] ? 'text-danger' : ''}} nome">{{$i['xProd']}}</span></td>
														<td class="datatable-cell"><span class="codBarras" style="width: 90px;">{{$i['codBarras']}}</span></td>
														<td class="datatable-cell"><span class="unidade" style="width: 80px;">{{$i['uCom']}}</span></td>
														<td class="datatable-cell"><span class="valor_xml" style="width: 80px;">{{ number_format((float)$i['custo_bruto'], 6, ',', '') }}</span></td>
														<td class="datatable-cell"><span class="valor_bruto" style="width: 80px;">
														    @php
                                                                // Converte o custo unitário informado no XML
                                                                $custoUnitario = (float) str_replace(',', '.', $i['custo_bruto']);

                                                                // Converte as quantidades
                                                                $qComXml   = (float) $i['qCom_xml'];
                                                                $qComFinal = (float) $i['qCom_final'];

                                                                // Se houver conversão (quantidade final diferente da quantidade xml),
                                                                // então calcula o custo ajustado: (custo unitário * qCom_xml) / qCom_final.
                                                                // Caso contrário (não houver conversão), usa o custo unitário direto.
                                                                if ($qComFinal > 0 && $qComFinal !== $qComXml) {
                                                                    $resultado = ($custoUnitario * $qComXml) / $qComFinal;
                                                                } else {
                                                                    $resultado = $custoUnitario;
                                                                }
                                                            @endphp
                                                            {{ number_format($resultado, 6, ',', '.') }}
                                                            </span>
                                                        </td>
														<td class="datatable-cell" style="display:none;"><span class="desconto_unit" style="width: 80px;">{{number_format((float)( ($i['desconto_item'] ?? 0) / ((float)$i['qCom'] * ((float)$i['conversao_unitaria'] ?: 1))),6, ',', '')}}</span></td>
														<td class="datatable-cell"><span class="valor" style="width: 80px;">{{number_format((float) str_replace(',', '.', $i['vUnCom']), 6, ',', '')}}</span></td>
                                                        <td class="datatable-cell">
                                                            <span style="width: 80px;" id="preco_venda_atual_{{$i['codigo']}}_{{$i['codBarras']}}">
                                                                <input class="preco_venda_atual form-control" style="width: 80px;" type="text" readonly value="{{ isset($i['valor_venda']) ? $i['valor_venda'] : '0' }}">
                                                            </span>
                                                        </td>
														<td class="datatable-cell">
                                                            <span style="width: 110px;" id="base_calculo_{{$i['codigo']}}_{{$i['codBarras']}}">
                                                                <select class="base_calculo form-control" style="width: 110px;" 
                                                                    @if($dadosNf['contSemRegistro'] > 0) disabled @endif>
                                                                    <option value="bruto">C. Bruto</option>
                                                                    <option value="liquido" selected>C. Líquido</option>
                                                                </select>
                                                            </span>
                                                        </td>
														<td class="datatable-cell">
                                                            <span style="width: 80px;" id="porcentagem_venda_{{$i['codigo']}}_{{$i['codBarras']}}">
															    <input id="porcentagem_venda_input" class="porcentagem_venda form-control" style="width: 80px;" type="text" 
                                                                value="{{ $i['percentual_lucro'] ?? '0' }}" @if($dadosNf['contSemRegistro'] > 0) disabled @endif>
                                                            </span>
                                                        </td>
                                                        <td class="datatable-cell">
                                                            <span style="width: 80px;" id="preco_venda_{{$i['codigo']}}_{{$i['codBarras']}}">
															    <input id="preco_venda_input" class="preco_venda form-control money" style="width: 80px;" type="text" value="{{ isset($i['preco_venda']) ? $i['preco_venda'] : '' }}"
                                                                    @if($dadosNf['contSemRegistro'] > 0) disabled @endif>
                                                            </span>
                                                        </td>
														<td class="datatable-cell" style="display:none;"><span class="valor_ipi" style="width: 80px;">{{number_format((float) str_replace(',', '.', ($i['valor_ipi'] ?? '0')), 6, ',', '')}}</span></td>
														<td class="datatable-cell" style="display:none;"><span class="outras_despesas" style="width: 80px;">{{number_format((float) str_replace(',', '.', ($i['outras_despesas'] ?? '0')),6,',','')}}</span></td>
														<td class="datatable-cell" style="display:none;"><span class="substituicao_tributaria" style="width: 80px;">{{number_format((float) str_replace(',', '.', ($i['substituicao_tributaria'] ?? '0')),6,',','')}}</span></td>
														<td class="datatable-cell" style="display:none;"><span class="valor_Seguro" style="width: 80px;">{{number_format((float) str_replace(',', '.', ($i['valor_Seguro'] ?? '0')),6,',','')}}</span></td>
														<!-- QTD_XML -->
                                                        <td class="datatable-cell"><span id="qtd_xml_{{$i['codigo']}}_{{$i['codBarras']}}" class="quantidade_xml" style="width: 80px;">{{$i['qCom']}}</span></td>
                                                        <!-- QTD Final (convertida) -->
                                                        <td class="datatable-cell"><span id="qtd_aux_{{$i['codigo']}}_{{$i['codBarras']}}" class="quantidade" style="width: 80px;">{{$i['qCom']}}</span></td>
														<td class="datatable-cell"><span class="ncm" style="width: 80px;">{{$i['NCM']}}</span></td>
														<td class="datatable-cell"><span style="width: 80px;">{{$i['CEST']}}</span></td>
														<td class="datatable-cell"><span class="cfop" style="width: 80px;">{{$i['CFOP']}}</span></td>
														<td class="datatable-cell">
															<span style="width: 80px;" id="cfop_entrada_{{$i['codigo']}}_{{$i['codBarras']}}">
																<input id="cfop_entrada_input" class="cfop form-control" style="width: 60px;" type="text" value="{{$i['CFOP_entrada']}}">
															</span>
														</td>
														<td class="datatable-cell"><span class="subtotal_desconto" style="width: 80px;">{{ number_format((float)($i['desconto_item'] ?? 0), $casasDecimais, ',', '') }}</span></td>
														<td class="datatable-cell quantidade"><span style="width: 80px;">{{ number_format(((((float) str_replace(',', '.', $i['vProd_item']) / (float)$i['qCom_final'])
                                                                                                                                                                           - ((float) str_replace(',', '.', ($i['desconto_item'] ?? 0)) / (float)$i['qCom_xml'])
                                                                                                                                                                           + (float) str_replace(',', '.', ($i['valor_ipi'] ?? 0))
                                                                                                                                                                           + (float) str_replace(',', '.', ($i['outras_despesas'] ?? 0))
                                                                                                                                                                           + (float) str_replace(',', '.', ($i['substituicao_tributaria'] ?? 0))
                                                                                                                                                                           + (float) str_replace(',', '.', ($i['valor_Seguro'] ?? 0))
                                                                                                                                                                           ) * (float)$i['qCom_final']),
                                                                                                                                                                           $casasDecimais, ',', '.') }}</span></td>
																																										   <td class="datatable-cell">
															<span style="width: 80px;">
															    @if($i['produtoNovo'])
                                                                <a id="th_acao1_{{$i['codigo']}}_{{$i['codBarras']}}" style="display: block" onclick="cadProd(this, '{{$i['codigo']}}', '{{$i['codBarras']}}')"
                                                                   href="javascript:;" class="btn btn-add btn-sm btn-clean btn-icon mr-2">  
                                                                    <span class="svg-icon svg-icon-success">
                                                                       <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                                <rect fill="#000000" x="4" y="11" width="16" height="2" rx="1" />
                                                                                <rect fill="#000000" opacity="0.3" transform="translate(12.000000, 12.000000) rotate(-270.000000) translate(-12.000000, -12.000000)" x="4" y="11" width="16" height="2" rx="1" />
                                                                            </g>
                                                                        </svg>
                                                                    </span>
                                                                </a>
                                                                @endif

																<a id="th_acao2_{{$i['produtoId']}}" @if(!$i['produtoNovo']) style="display: block" @else style="display: none" @endif data-id_xml="{{$i['codigo']}}" data-codbarras_xml="{{$i['codBarras']}}"  onclick="editProd({{$i['produtoId']}})" href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2">
																	<span class="svg-icon svg-icon-danger">
																		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
																			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
																				<rect x="0" y="0" width="24" height="24" />
																				<path d="M8,17.9148182 L8,5.96685884 C8,5.56391781 8,5.17792052 8.44982609,4.89581508 L10.965708,2.42895648 C11.5426798,1.86322723 12.4640974,1.85620921 13.0496196,2.41308426 L15.5337377,4.77566479 C15.8314604,5.0588212 16,5.45170806 16,5.86258077 L16,17.9148182 C16,18.7432453 15.3284271,19.4148182 14.5,19.4148182 L9.5,19.4148182 C8.67157288,19.4148182 8,18.7432453 8,17.9148182 Z" fill="#000000" fill-rule="nonzero" transform="translate(12.000000, 10.707409) rotate(-135.000000) translate(-12.000000, -10.707409)" />
																				<rect fill="#000000" opacity="0.3" x="5" y="20" width="15" height="2" rx="1" />
																			</g>
																		</svg>
																	</span>
																</a>
															    <!-- Novo botão para detalhamento de custo, sempre visível -->
                                                                <a href="javascript:;" onclick="detalharCusto(this)" class="btn btn-info btn-sm" title="Detalhamento de custo">
                                                                    <i class="la la-info-circle"></i>
                                                                </a>
															</span>
														</td>																												   
														<td class="cod" id="th_prod_id_{{$i['produtoId']}}" style="visibility: hidden">{{$i['produtoId']}}</td>
														<td class="valor_venda" id="th_prod_valor_venda_{{$i['produtoId']}}" style="display: none">-1</td>
														<td style="visibility: hidden" class="conv_estoque" id="th_prod_conv_unit_{{$i['produtoId']}}">{{$i['conversao_unitaria']}}</td>
														<td style="display: none" class="valor_compra" id="th_prod_valor_compra_{{$i['produtoId']}}">
                                                            {{ number_format((float)$i['vUnCom'], 2, ',', '.') }}
                                                        </td>
														<td style="display: none" class="link_prod" id="link_prod_{{$i['produtoId']}}">

													</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									   <br><br>

									   @if($dadosNf['contSemRegistro'] > 0)
									<div class="row sem-registro">
										<div class="col-xl-12">
											<p class="text-danger">*Esta nota possui produto(s) sem cadastro inclua antes de continuar</p>
										</div>
									</div>
									@endif
								</div>
								<span id="subtotal" data-original="0" style="display:none;"></span>
							</div>
						</div>
					</div>

<!-- Modal com largura extra (modal-xl) -->
<div class="modal fade" id="modalDetalhamento" tabindex="-1" role="dialog" aria-labelledby="modalDetalhamentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document"><!-- modal-xl deixa o modal bem largo -->
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalDetalhamentoLabel">Detalhamento de Custo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        
        <!-- Nome do Produto -->
        <div class="form-group">
          <label for="dc_produto_nome">Produto</label>
          <input type="text" class="form-control" id="dc_produto_nome" readonly />
        </div>

        <!-- Linha única de cálculo: valor_bruto + valor_ipi + ... - desconto = custo_liquido -->
        <div class="row no-gutters align-items-end justify-content-center"><!-- no-gutters: remove espaçamento horizontal -->
          
          <!-- Valor Bruto -->
          <div class="col-auto text-center mr-3">
            <label for="dc_valor_bruto" class="mb-0"><strong>Valor Bruto</strong></label>
            <input type="text" class="form-control form-control-sm text-center" 
                   style="width:100px;" id="dc_valor_bruto" readonly />
          </div>

          <!-- + -->
          <div class="col-auto" style="font-size: 20px; margin-bottom: 0.5rem;">+</div>

          <!-- Valor IPI -->
          <div class="col-auto text-center mr-3">
            <label for="dc_valor_ipi" class="mb-0"><strong>Valor IPI</strong></label>
            <input type="text" class="form-control form-control-sm text-center" 
                   style="width:100px;" id="dc_valor_ipi" readonly />
          </div>

          <!-- + -->
          <div class="col-auto" style="font-size: 20px; margin-bottom: 0.5rem;">+</div>

          <!-- Outras Despesas -->
          <div class="col-auto text-center mr-3">
            <label for="dc_outras_despesas" class="mb-0"><strong>Outras Despesas</strong></label>
            <input type="text" class="form-control form-control-sm text-center"
                   style="width:150px;" id="dc_outras_despesas" readonly />
          </div>

          <!-- + -->
          <div class="col-auto" style="font-size: 20px; margin-bottom: 0.5rem;">+</div>

          <!-- Subst. Trib. -->
          <div class="col-auto text-center mr-3">
            <label for="dc_substituicao_tributaria" class="mb-0"><strong>Subst. Trib.</strong></label>
            <input type="text" class="form-control form-control-sm text-center"
                   style="width:100px;" id="dc_substituicao_tributaria" readonly />
          </div>

          <!-- + -->
          <div class="col-auto" style="font-size: 20px; margin-bottom: 0.5rem;">+</div>

          <!-- Valor Seguro -->
          <div class="col-auto text-center mr-3">
            <label for="dc_valor_seguro" class="mb-0"><strong>Valor Seguro</strong></label>
            <input type="text" class="form-control form-control-sm text-center" 
                   style="width:100px;" id="dc_valor_seguro" readonly />
          </div>

          <!-- - -->
          <div class="col-auto" style="font-size: 20px; margin-bottom: 0.5rem;">-</div>

          <!-- Desconto Unit. -->
          <div class="col-auto text-center mr-3">
            <label for="dc_desconto_unit" class="mb-0"><strong>Desconto Unit.</strong></label>
            <input type="text" class="form-control form-control-sm text-center"
                   style="width:100px;" id="dc_desconto_unit" readonly />
          </div>

          <!-- = -->
          <div class="col-auto" style="font-size: 20px; margin-bottom: 0.5rem;">=</div>

          <!-- Custo Líquido -->
          <div class="col-auto text-center mr-3">
            <label for="dc_custo_liquido" class="mb-0"><strong>Custo Líquido</strong></label>
            <input type="text" class="form-control form-control-sm text-center"
                   style="width:100px;" id="dc_custo_liquido" readonly />
          </div>

        </div><!-- /row no-gutters -->

      </div><!-- /.modal-body -->

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

					<div class="col-xl-12">
						<div class="card card-custom gutter-b example example-compact">

							<div class="card-body">
								<div class="row">
									<div class="form-group validated col-sm-2 col-lg-2">
										<label class="col-form-label">Data de Vencimento</label>
										<div class="">
											<div class="input-group date">
												<input type="text" class="form-control data-input" id="kt_datepicker_3">
												<div class="input-group-append">
													<span class="input-group-text">
														<i class="la la-calendar"></i>
													</span>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group validated col-sm-2 col-lg-2">
										<label class="col-form-label">Valor da parcela</label>
										<div class="">
											<input type="text" class="form-control" id="valor_parcela">

										</div>
									</div>

									<div class="form-group validated col-sm-4 col-lg-4">
										<br>
										<a style="margin-top: 13px;" id="add-pag" class="btn btn-primary font-weight-bold text-uppercase px-9 py-4">
											Adicionar Pag.
										</a>
									</div>
								</div>
								<div class="">
									<h2 style="margin-left: 10px;">Fatura</h2>
									<input type="hidden" id="fatura" value="{{json_encode($fatura)}}">
									<div class="row" id="fatura-html">

									</div>
								</div>
							</div>
						</div>
					</div>

					<input type="hidden" id="total" value="{{$dadosNf['vProd']}}" name="">
					<div class="col-xl-12">
						<div class="row">
							<div class="col-xl-6">
							    <h5>Total Produtos: <strong id="valorProdutos" class="blue-text">R$ {{ number_format((float)$dadosNf['vBrut'], 2, ',', '') }}</strong></h5>
							    <h5>Total Desconto: R$ <strong id="total_desconto" class="blue-text">{{ number_format((float)$dadosNf['total_desconto'], 2, ',', '') }}</strong></h5>
								<h5>Valor Total IPI: R$ <strong id="valor_total_ipi" name="valor_total_ipi" class="blue-text">{{ $dadosNf['total_ipi'] }}</strong></h5>
								<h5>Valor Total Outras Despesas: R$<strong id="total_outras_despesas" name="total_outras_despesas" class="blue-text"> {{ $dadosNf['total_outras_despesas'] }}</strong></h5>
								<h5>Valor Total Subs. Trib.: R$<strong id="total_substituicao_tributaria" name="total_substituicao_tributaria" class="blue-text"> {{ $dadosNf['total_substituicao_tributaria'] }}</strong></h5>
								<h5>Valor Total Seguro: R$<strong id="total_seguro" name="total_seguro" class="blue-text"> {{ number_format((float)$dadosNf['total_seguro'], 2, ',', '') }}</strong></h5>
								<h5>Total Nota: <strong id="valorDaNF" class="blue-text">R$ {{ number_format((float)$dadosNf['vProd'], 2, ',', '') }}</strong></h5>
							</div>
							<div class="col-xl-3">

								<input type="text" class="form-control" id="lote" placeholder="Lote">

							</div>
							<div class="col-xl-3">
								<button id="salvarNF" disabled style="width: 100%" type="submit" class="btn btn-success spinner-white spinner-right">
									<i class="la la-check"></i>
									<span class="">Salvar</span>
								</button>
							</div>
						</div>
					</div>

				</div>
				<br>
			</div>
		</div>
	</div>
</div>
</div>
<input type="hidden" id="subs" value="{{json_encode($subs)}}">


<div class="modal fade" id="modal1" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Adicionar produto</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">

				<div class="wizard wizard-3" id="kt_wizard_v4" data-wizard-state="between" data-wizard-clickable="true">
					<!--begin: Wizard Nav-->

					<div class="wizard-nav">

						<div class="wizard-steps px-8 py-8 px-lg-15 py-lg-3">
							<!--begin::Wizard Step 1 Nav-->
							<div class="wizard-step" data-wizard-type="step" data-wizard-state="done">
								<div class="wizard-label">
									<h3 class="wizard-title">
										<span>
											IDENTIFICAÇÃO
										</span>
									</h3>
									<div class="wizard-bar"></div>
								</div>
							</div>
							<!--end::Wizard Step 1 Nav-->
							<!--begin::Wizard Step 2 Nav-->
							<div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
								<div class="wizard-label">
									<h3 class="wizard-title">
										<span>
											ALÍQUOTAS
										</span>
									</h3>
									<div class="wizard-bar"></div>
								</div>
							</div>
						</div>
					</div>

					<div class="card-body">
						<div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">

							<!--begin: Wizard Form-->
							<form class="form fv-plugins-bootstrap fv-plugins-framework form-prod" id="kt_form">
								<!--begin: Wizard Step 1-->
								<input type="hidden" id="id_xml" name="id_xml" value="">
								<input type="hidden" id="codBarras_xml" name="codBarras_xml" value="">

								<div class="pb-5" data-wizard-type="step-content">
									<div class="row">
										<div class="form-group validated col-sm-10 col-lg-10">
											<label class="col-form-label">Nome do Produto <strong class="text-danger">*</strong></label>
											<div class="input-group">
												<input id="nome" type="text" class="form-control" name="nome" value="">
                                                <button onclick="linkProduto(this)" class="btn btn-info" type="button">
                                                    <i class="la la-search"></i>
                                                </button>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-3 col-lg-2">
											<label class="col-form-label">NCM <strong class="text-danger">*</strong></label>
											<div class="">
												<input id="ncm" type="text" class="form-control" name="ncm" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-2 col-lg-3">
											<label class="col-form-label">CEST</label>
											<div class="">
												<input type="text" id="CEST" class="form-control @if($errors->has('CEST')) is-invalid @endif">
											</div>
										</div>
										<div class="form-group validated col-sm-3 col-lg-2">
											<label class="col-form-label">CFOP <strong class="text-danger">*</strong></label>
											<div class="">
												<input id="cfop" type="text" class="form-control" name="cfop" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-2">
											<label class="col-form-label">Referência</label>
											<div class="">
												<input id="referencia" type="text" class="form-control" name="referencia" value="">
											</div>
										</div>
										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Conversão unitária para estoque</label>
											<div class="">
												<input id="conv_estoque" type="text" class="form-control" name="conv_estoque" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Quantidade <strong class="text-danger">*</strong></label>
											<div class="">
												<input id="quantidade" type="text" class="form-control" name="quantidade" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
											<label class="col-form-label">Valor de compra <strong class="text-danger">*</strong></label>
											<div class="">
												<input id="valor" type="text" class="form-control" name="valor" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
											<label class="col-form-label">Valor IPI </label>
											<div class="">
												<input id="valor_ipi" type="text" class="form-control" name="valor_ipi" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
											<label class="col-form-label">Outras Despesas </label>
											<div class="">
												<input id="outras_despesas" type="text" class="form-control" name="outras_despesas" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
											<label class="col-form-label">% lucro</label>
											<div class="">
												<input type="text" id="percentual_lucro" class="form-control money" name="percentual_lucro" value="{{$config->percentual_lucro_padrao }}">
											</div>
										</div>


										<input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}">

										<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
											<label class="col-form-label">Valor de Venda <strong class="text-danger">*</strong></label>
											<div class="">
												<input id="valor_venda" type="text" class="form-control" name="valor_venda" value="">
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Unidade de Compra <strong class="text-danger">*</strong></label>
											<div class="">
												<select class="custom-select form-control" name="un_compra" id="un_compra">
													@foreach($unidadesDeMedida as $u)
													<option value="{{$u}}">{{$u}}</option>
													@endforeach
												</select>
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Unidade de Venda <strong class="text-danger">*</strong></label>
											<select class="custom-select form-control" id="unidade_venda">
												@foreach($unidadesDeMedida as $u)
												<option value="{{$u}}">{{$u}}</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Categoria</label>
											<select class="custom-select form-control" id="categoria_id">
												@foreach($categorias as $cat)
												<option value="{{$cat->id}}">{{$cat->nome}}</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Sub Categoria</label>
											<select class="custom-select form-control" id="sub_categoria_id">
												<option value="">selecione</option>
											</select>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Marca</label>
											<select class="custom-select form-control" id="marca_id">
												<option value="">selecione</option>
												@foreach($marcas as $m)
												<option value="{{$m->id}}">{{$m->nome}}</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Estoque minimo</label>
											<div class="">
												<input type="text" id="estoque_minimo" class="form-control @if($errors->has('∂')) is-invalid @endif">
											</div>
										</div>

										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="col-form-label">Gerenciar estoque</label>
											<div class="col-6">
												<span class="switch switch-outline switch-primary">
													<label>
														<input value="true" @if($config->gerenciar_estoque_produto == 1) checked @endif type="checkbox" id="gerenciar_estoque">
														<span></span>
													</label>
												</span>
											</div>
										</div>

										<div class="form-group validated col-sm-6 col-lg-2">
											<label class="col-form-label">Inativo</label>
											<div class="col-6">
												<span class="switch switch-outline switch-danger">
													<label>
														<input value="true" type="checkbox" id="inativo">
														<span></span>
													</label>
												</span>
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Código de barras</label>
											<div class="input-group">
												<!-- Campo visível para o usuário alterar o código de barras -->
                                                <input id="codBarras" type="text" class="form-control" name="codBarras" value="">

                                                <!-- Campo oculto que mantém o código de barras original vindo do XML -->
                                                <input id="codBarras_xml" type="hidden" name="codBarras_xml" value="">
												<div class="input-group-prepend">
													<span class="input-group-text btn-info btn" onclick="gerarCode()">
														<i class="la la-barcode"></i>
													</span>
												</div>
											</div>
										</div>

										<hr>
										<div class="form-group validated col-12">
											<h3>Derivado Petróleo</h3>
										</div>

										<div class="form-group validated col-lg-6 col-md-10 col-sm-10">
											<label class="col-form-label">ANP</label>

											<select class="custom-select form-control" id="anp">
												<option value="">--</option>
												@foreach($anps as $key => $a)
												<option value="{{$key}}">[{{$key}}] - {{$a}}
												</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
											<label class="col-form-label">%GLP</label>

											<input type="text" id="perc_glp" class="form-control @if($errors->has('perc_glp')) is-invalid @endif trib">
										</div>

										<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
											<label class="col-form-label">%GNn</label>

											<input type="text" id="perc_gnn" class="form-control @if($errors->has('perc_gnn')) is-invalid @endif trib">
										</div>

										<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
											<label class="col-form-label">%GNi</label>

											<input type="text" id="perc_gni" class="form-control @if($errors->has('perc_gni')) is-invalid @endif trib">
										</div>

										<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
											<label class="col-form-label">Valor de partida</label>

											<input type="text" id="valor_partida" class="form-control @if($errors->has('valor_partida')) is-invalid @endif money">
										</div>

										<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
											<label class="col-form-label">Un. tributável</label>

											<input type="text" id="unidade_tributavel" class="form-control @if($errors->has('unidade_tributavel')) is-invalid @endif" data-mask="AAAA">
										</div>

										<div class="form-group validated col-lg-3 col-md-4 col-sm-4">
											<label class="col-form-label">Qtd. tributável</label>

											<input type="text" id="quantidade_tributavel" class="form-control @if($errors->has('quantidade_tributavel')) is-invalid @endif" data-mask="00000,00" data-mask-reverse="true">
										</div>


										<hr>
										<div class="form-group validated col-12">
											<h3>Dados de dimensão e peso do produto (Opcional)</h3>
										</div>


										<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
											<label class="col-form-label">Largura (cm)</label>

											<input type="text" id="largura" class="form-control @if($errors->has('largura')) is-invalid @endif">

										</div>

										<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
											<label class="col-form-label">Altura (cm)</label>

											<input type="text" id="altura" class="form-control @if($errors->has('altura')) is-invalid @endif">
										</div>

										<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
											<label class="col-form-label">Comprimento (cm)</label>

											<input type="text" id="comprimento" class="form-control @if($errors->has('comprimento')) is-invalid @endif">
										</div>

										<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
											<label class="col-form-label">Peso liquido</label>

											<input type="text" id="peso_liquido" class="form-control @if($errors->has('peso_liquido')) is-invalid @endif">
										</div>

										<div class="form-group validated col-lg-2 col-md-4 col-sm-4">
											<label class="col-form-label">Peso bruto</label>

											<input type="text" id="peso_bruto" class="form-control @if($errors->has('peso_bruto')) is-invalid @endif">
										</div>

										<div class="col-lg-12 col-xl-12">
											<p class="text-danger">*Se atente a preencher todos os dados para utilizar a Api dos correios.</p>
										</div>

									</div>

								</div>
								<div class="pb-5" data-wizard-type="step-content">
									<div class="row">

										<div class="form-group validated col-sm-6 col-lg-12">
											<label class="col-form-label">CST/CSOSN</label>
											<select class="custom-select form-control" id="CST_CSOSN">
												@foreach($listaCSTCSOSN as $key => $c)
												<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_CSOSN)
													selected
													@endif
													@else
													@if($key == $config->CST_CSOSN_padrao)
													selected
													@endif
													@endif

													@endif
													>{{$key}} - {{$c}}
												</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-sm-4 col-lg-6">
											<label class="col-form-label">CST PIS</label>
											<select class="custom-select form-control" id="CST_PIS">
												@foreach($listaCST_PIS_COFINS as $key => $c)
												<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_PIS)
													selected
													@endif
													@else
													@if($key == $config->CST_PIS_padrao)
													selected
													@endif
													@endif

													@endif
													>{{$key}} - {{$c}}
												</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-sm-3 col-lg-6">
											<label class="col-form-label">CST COFINS</label>
											<select class="custom-select form-control" id="CST_COFINS">
												@foreach($listaCST_PIS_COFINS as $key => $c)
												<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_COFINS)
													selected
													@endif
													@else
													@if($key == $config->CST_COFINS_padrao)
													selected
													@endif
													@endif

													@endif
													>{{$key}} - {{$c}}
												</option>
												@endforeach
											</select>
										</div>

										<div class="form-group validated col-sm-3 col-lg-6">
											<label class="col-form-label">CST IPI</label>
											<select class="custom-select form-control" id="CST_IPI">
												@foreach($listaCST_IPI as $key => $c)
												<option value="{{$key}}" @if($config !=null) @if(isset($produto)) @if($key==$produto->CST_IPI)
													selected
													@endif
													@else
													@if($key == $config->CST_IPI_padrao)
													selected
													@endif
													@endif

													@endif
													>{{$key}} - {{$c}}
												</option>
												@endforeach
											</select>
										</div>
										<div class="form-group validated col-sm-2 col-lg-2">
											<label class="col-form-label">%ICMS</label>
											<div class="">
												<input id="perc_icms" type="text" class="form-control trib" name="perc_icms" value="0">
											</div>
										</div>
										<div class="form-group validated col-sm-2 col-lg-2">
											<label class="col-form-label">%PIS</label>
											<div class="">
												<input id="perc_pis" type="text" class="form-control trib" name="perc_pis" value="0">
											</div>
										</div>
										<div class="form-group validated col-sm-2 col-lg-2">
											<label class="col-form-label">%COFINS</label>
											<div class="">
												<input id="perc_cofins" type="text" class="form-control trib" name="perc_cofins" value="0">
											</div>
										</div>
										<div class="form-group validated col-sm-2 col-lg-2">
											<label class="col-form-label">%IPI</label>
											<div class="">
												<input id="perc_ipi" type="text" class="form-control trib" name="perc_ipi" value="0">
											</div>
										</div>

									</div>
								</div>
							</form>
						</div>
					</div>
				</div>


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="salvar" class="btn btn-success font-weight-bold spinner-white spinner-right">Salvar</button>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="modal2" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Editar produto</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">

				<input id="idEdit" type="hidden" class="form-control" name="idEdit" value="">

				<div class="row">
					<div class="form-group validated col-sm-12 col-lg-12">
						<label class="col-form-label">Nome do Produto</label>
						<div class="">
							<input id="nomeEdit" type="text" class="form-control" name="nomeEdit" value="">

						</div>
					</div>
				</div>

				<div class="row">

					<div class="form-group validated col-sm-3 col-lg-3">
						<label class="col-form-label">Conv. unit. estoque</label>
						<div class="">
							<input id="conv_estoqueEdit" type="text" class="form-control" name="conv_estoqueEdit" value="">
						</div>
					</div>

					<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
						<label class="col-form-label">Valor de compra</label>
						<div class="">
							<input id="valorCompraEdit" type="text" class="form-control money" name="valorCompraEdit" value="" disabled>
						</div>
					</div>

					<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
                        <label class="col-form-label">% Margem de Venda</label>
                        <div class="">
						    <input id="percentualLucroEdit" type="text" class="form-control money" name="percentualLucroEdit" value="" onkeyup="atualizarPrecoVenda()">
                        </div>
                    </div>

					<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
                        <label class="col-form-label">Valor de Venda Atual</label>
                        <div class="">
						<input id="valorVendaAtual" type="text" class="form-control money" name="valorVendaAtual" value="" disabled>
                        </div>
                    </div>

					<div class="form-group validated col-sm-3 col-lg-3" style="display: none">
						<label class="col-form-label">Novo Valor de venda</label>
						<div class="">
							<input id="valorVendaEdit" type="text" class="form-control money" name="valorVendaEdit" value="">
						</div>
					</div>

				</div>


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
				<button type="button" id="salvarEdit" class="btn btn-success font-weight-bold spinner-white spinner-right">Salvar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-link" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Atribuir ao produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    x
                </button>
            </div>
            <div class="modal-body">

                <!-- Campos ocultos para armazenar ID XML e Código de Barras XML -->
                <input type="hidden" id="id_xml_atribuir" value="">
                <input type="hidden" id="codBarras_xml_atribuir" value="">
				<input type="hidden" id="codBarras_atribuir" value="">

                <div class="row">
                    <div class="form-group validated col-sm-12 col-lg-12 col-12">
                        <label class="col-form-label" id="">Produto</label><br>
                        <input placeholder="Digite para buscar o produto por nome ou referência" type="search" id="produto-search" class="form-control">
                        <div class="search-prod" style="display: none"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group validated col-sm-3 col-lg-3 col-12">
                        <label class="col-form-label">Quantidade</label>
                        <div class="">
                            <input id="estoque" type="text" class="form-control" name="estoque" value="">
                        </div>
                    </div>

                    <div class="form-group validated col-sm-3 col-lg-3 col-12" style="display: none">
                        <label class="col-form-label">Valor de venda</label>
                        <div class="">
                            <input id="valor_venda2" type="text" class="form-control money" name="valor_venda2" value="">
                        </div>
                    </div>

                    <div class="form-group validated col-sm-3 col-lg-3 col-12" style="display: none">
                        <label class="col-form-label">Valor de compra</label>
                        <div class="">
                            <input id="valor_compra2" type="text" class="form-control money" name="valor_compra2" value="">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
                <button type="button" id="salvarLink" class="btn btn-success font-weight-bold spinner-white spinner-right">Salvar</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/dfe.js') }}"></script>
<script>
    window.baseUrl = "{{ url('/') }}";
</script>
<script>
	var itens = '<?php echo json_encode($itens); ?>'
	// console.log(itens)
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip();
	});
  </script>

@endsection
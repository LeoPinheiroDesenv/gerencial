<div class="modal fade" id="modal-composicao-preco" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Composição de preço</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					x
				</button>
			</div>
			<div class="modal-body">
				<div class="row custo">
					<h4 class="col-12">Custo do Produto</h4>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Valor de Compra</label>
						<input type="tel" class="form-control valor_compra money">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">%ICMS</label>
						<input type="tel" name="perc_icms_compra" class="form-control perc_icms_compra perc input-calc" data-op="soma" data-tipo="perc">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">% DIF. ICMS</label>
						<input type="tel" name="perc_diferenca_icms" class="form-control perc_diferenca_icms perc input-calc" data-op="soma" data-tipo="perc">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">% IPI</label>
						<input type="tel" name="perc_ipi_compra" class="form-control perc_ipi_compra perc input-calc" data-op="soma" data-tipo="perc">
					</div>

					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Frete</label>
						<input type="tel" name="frete_compra" class="form-control frete_compra money input-calc" data-op="soma" data-tipo="valor">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Custo financeiro</label>
						<input type="tel" name="custo_financeiro_compra" class="form-control custo_financeiro_compra money input-calc" data-tipo="valor" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Embalagem</label>
						<input type="tel" name="embalagem_compra" class="form-control embalagem_compra money input-calc" data-op="soma" data-tipo="valor">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Desconto</label>
						<input type="tel" name="desconto_compra" class="form-control desconto_compra money input-calc" data-op="desconto" data-tipo="valor">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Custo adicional</label>
						<input type="tel" name="custo_adicional_compra" class="form-control custo_adicional_compra money input-calc" data-tipo="valor" data-op="soma">
					</div>

					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Custo do produto</label>
						<input style="background: #FFC0CB" name="custo_produto" type="tel" readonly id="custo_produto" class="form-control money">
					</div>
				</div>
				<div class="row venda">
					<h4 class="col-12">Preço de venda</h4>

					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Margem de lucro</label>
						<input type="tel" class="form-control margem_lucro perc input-calc-venda" data-op="soma" data-tipo="perc">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">% Imposto federal</label>
						<input type="tel" name="perc_imposto_federal" class="form-control perc_imposto_federal perc input-calc-venda" data-tipo="perc" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">% Imposto estadual</label>
						<input type="tel" name="perc_imposto_estadual" class="form-control perc_imposto_estadual perc input-calc-venda" data-tipo="perc" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Custo financeiro</label>
						<input type="tel" name="custo_financeiro" class="form-control custo_financeiro money input-calc-venda" data-tipo="valor" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">% Comissão</label>
						<input type="tel" name="comissao" class="form-control comissao perc input-calc-venda" data-tipo="perc" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Capital de giro</label>
						<input type="tel" name="capital_giro" class="form-control capital_giro money input-calc-venda" data-tipo="valor" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Custo operacional</label>
						<input type="tel" name="custo_operacional" class="form-control custo_operacional money input-calc-venda" data-tipo="valor" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Rentabilidade</label>
						<input type="tel" name="rentabilidade" class="form-control rentabilidade perc input-calc-venda" data-tipo="perc" data-op="soma">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Lucro liquído</label>
						<input type="tel" name="lucro_liquido" data-tipo="valor" data-op="soma" class="form-control lucro_liquido input-calc-venda money">
					</div>
					<div class="form-group validated col-lg-2 col-6">
						<label class="col-form-label">Preço de venda</label>
						<input style="background: #40E0D0" type="tel" readonly id="preco_venda" class="form-control money">
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" id="btn-frete" class="btn btn-danger font-weight-bold spinner-white spinner-right" data-dismiss="modal" aria-label="Close">Fechar</button>
				<button type="button" onclick="setaComposicaoPreco()" class="btn btn-success font-weight-bold spinner-white spinner-right">Salvar</button>
			</div>
		</div>
	</div>
</div>
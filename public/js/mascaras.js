var casas_decimais = 2;
var casas_decimais_qtd = 2;
var mask = "00";
var maskQtd = "000";
$(function () {

	if(casas_decimais == 2)
		mask = "00"
	else if(casas_decimais == 3)
		mask = "000"
	else if(casas_decimais == 4)
		mask = "0000"
	else if(casas_decimais == 5)
		mask = "00000"
	else if(casas_decimais == 6)
		mask = "000000"
	else if(casas_decimais == 7)
		mask = "0000000"

	if(casas_decimais_qtd == 2)
		maskQtd = "00"
	else if(casas_decimais_qtd == 3)
		maskQtd = "000"
	else if(casas_decimais_qtd == 4)
		maskQtd = "0000"
	else if(casas_decimais_qtd == 5)
		maskQtd = "00000"
	else if(casas_decimais_qtd == 6)
		maskQtd = "000000"
	else if(casas_decimais_qtd == 7)
		maskQtd = "0000000"

	setTimeout(() => {
		$('#cpf').mask('000.000.000-00');
		$('.lat_lng').mask('-00.000000');
		$('.cpf').mask('00000000000', {reverse: true});
		$('.cpfp').mask('000.000.000-00', {reverse: true});
		$('#cardNumber').mask('0000 0000 0000 0000', {reverse: true});
		$('#cardExpirationMonth').mask('00', {reverse: true});
		$('#cardExpirationYear').mask('00', {reverse: true});
		$('#securityCode').mask('0000', {reverse: true});

		var SPMaskBehavior = function (val) {
			return val.replace(/\D/g, '').length === 11 ? '00 00000-0000' : '00 0000-00009';
		},
		spOptions = {
			onKeyPress: function(val, e, field, options) {
				field.mask(SPMaskBehavior.apply({}, arguments), options);
			}
		};
		$('#telefone').mask(SPMaskBehavior, spOptions);

		$('.telefone').mask('00 00000-0000', {reverse: true});
		$('#celular').mask('00 00000-0000', {reverse: true});
		$('.celular').mask('00 000000000');
		$('#cnpj').mask('00.000.000/0000-00', {reverse: true});
		$('#valor').mask('000000000000000,'+mask, {reverse: true});
		$('#valor_parcela').mask('000000000000000,'+mask, {reverse: true});
		$('#vale_valor').mask('000000,'+mask, {reverse: true});
		$('#taxa_entrega').mask('000000,00', {reverse: true});
		$('#troco_para').mask('000000,00', {reverse: true});
		$('#ncm_padrao').mask('0000.00.00', {reverse: true});
		$('.ncm').mask('0000.00.00', {reverse: true});
		$('#limite_maximo_desconto').mask('00,00', {reverse: true});
		$('#pRedBC').mask('000000,0000', {reverse: true});
		$('#pDif').mask('000000,0000', {reverse: true});
		$('#perc_imposto').mask('00,00', {reverse: true});
		$('.perc').mask('000.00', {reverse: true});

		$('.trib').mask('000,00', {reverse: true});

		$('#percentual_alteracao').mask('000,00', {reverse: true});

		$('#valor_frete').mask('000000000000000,00', {reverse: true});
		$('#valor_sangria').mask('000000000000000,00', {reverse: true});
		$('#valor_entrega').mask('000000000000000,00', {reverse: true});
		$('#valor_carga').mask('000000000000000,00', {reverse: true});
		$('#desconto').mask('000000000000000,00', {reverse: true});
		$('#valor_recebido').mask('000000000000000,00', {reverse: true});
		$('#valor_componente').mask('000000000000000,00', {reverse: true});
		$('#valor_venda').mask('000000000000000,00', {reverse: true});
		$('#valor_item').mask('000000,'+mask, {reverse: true});
		// $('.money').mask('000000,'+mask, {reverse: true});

		$(document).on("focus", ".money", function() {
			$(this).mask('000000000000000,'+mask, { reverse: true })
		})
		$('#valor_transporte').mask('000000,00', {reverse: true});
		$('#valor_receber').mask('000000,00', {reverse: true});
		$('#ncm').mask('0000.00.00', {reverse: true});
		$('#cest').mask('00.000.00', {reverse: true});
		$('#CFOP_saida_estadual').mask('0000', {reverse: true});
		$('#CFOP_entrada_estadual').mask('0000', {reverse: true});
		$('#CFOP_saida_inter_estadual').mask('0000', {reverse: true});
		$('#CFOP_entrada_inter_estadual').mask('0000', {reverse: true});
		$('.cfop').mask('0000', {reverse: true});
		$('#qtdVol').mask('000000', {reverse: true});
		$('#nInicio').mask('0000000', {reverse: true});
		$('#nFinal').mask('0000000', {reverse: true});
		$('#cep').mask('00000-000', {reverse: true});
		$('.cep').mask('00000-000', {reverse: true});
		$('.cepFrete').mask('00000000', {reverse: true});
		$('#numero_sms').mask('00 00000-0000', {reverse: true});
		$('.numero_serie').mask('000', {reverse: true});
		$('.picker').mask('00:00', {reverse: true});
		$('#alterta_vencimento').mask('000', {reverse: true});

		$('.date-input').mask('00/00/0000', {reverse: true});

		$('#quantidade').mask('0000000000,'+maskQtd, {reverse: true});
		$('#quantidade_carga').mask('0000000000,0000', {reverse: true});


		$('#limite_diario').mask('000', {reverse: true});
		$('#pesoL').mask('0000000000,000', {reverse: true});
		$('#pesoB').mask('0000000000,000', {reverse: true});
		$('.qCom').mask('0000000000,000', {reverse: true});
		$('.qCom2').mask('0000000000,0000', {reverse: true});
		$('#tempo_preparo').mask('00000', {reverse: true});
		$('#qtdParcelas').mask('00', {reverse: true});
		$('#recorrencia').mask('00/00', {reverse: true});
		$('#placa').mask('AAA-AAAA');
		$('#uf').mask('SS', {reverse: true});
		$('#chave_nfe').mask('00000000000000000000000000000000000000000000', {reverse: true});

		$(document).on("focus", ".chave_nfe", function() {
			$(this).mask("00000000000000000000000000000000000000000000", { reverse: true })
		});

		$(document).on("focus", ".quantidade", function() {
			$(this).mask("0000000.000", { reverse: true })
		});
		$('#icms').mask('00,00', {reverse: true});
		$('#pis').mask('00,00', {reverse: true});
		$('#cofins').mask('00,00', {reverse: true});
		$('#ipi').mask('00,00', {reverse: true});
		$('#codigoPromocional').mask('000000', {reverse: true});

		$('#valor_anterior').mask('000000,00', {reverse: true});

		$('#valor_receita').mask('000000,00', {reverse: true});
		$('.valor_pizza').mask('000000,00', {reverse: true});
		$('#pedacos').mask('00', {reverse: true});
		$('.rendimento').mask('00', {reverse: true});
		$('.tempo_preparo').mask('000', {reverse: true});
		$('.number').mask('000', {reverse: true});


		$('#capacidade').mask('000000', {reverse: true});
		$('#tara').mask('000000', {reverse: true});
		$('#proprietario_documento').mask('00.000.000/0000-00', {reverse: true});
		$('#cnpj_contratante').mask('00.000.000/0000-00', {reverse: true});
		$('#ciot_cpf_cnpj').mask('00000000000000', {reverse: true});
		$('#vale_cnpj_fornecedor').mask('00.000.000/0000-00', {reverse: true});
		$('#vale_cpf_cnpj_pagador').mask('00000000000000', {reverse: true});

		$('.qtd_rateio').mask('000,00', {reverse: true});

		$('.chave').mask('00000000000000000000000000000000000000000000', {reverse: true});

		$('.v-lacre').mask('00000000000000000000', {reverse: true});

		$('#nDoc').mask('000000000000000000000000', {reverse: true});
		$('.imposto').mask('00,00', {reverse: true});
		$('#vDocFisc').mask('000000,00', {reverse: true});
		$('#cListServ').mask('00,00', {reverse: true});

		$('#largura').mask('000000,00', {reverse: true});
		$('#comprimento').mask('000000,00', {reverse: true});
		$('#altura').mask('000000,00', {reverse: true});
		$('#peso_liquido').mask('000000,000', {reverse: true});
		$('#peso_bruto').mask('000000,000', {reverse: true});
		$('.peso').mask('000000,000', {reverse: true});
		$('.dim').mask('000000,00', {reverse: true});

		$(document).on("focus", ".qtd-p", function() {
			$(this).mask('00000000.'+maskQtd, {reverse: true});
		});
		$('.money-p').mask('0000000.'+mask, {reverse: true});

		var cpfMascara = function(val) {
			return val.replace(/\D/g, "").length > 11
			? "00.000.000/0000-00"
			: "000.000.000-009";
		},
		cpfOptions = {
			onKeyPress: function(val, e, field, options) {
				field.mask(cpfMascara.apply({}, arguments), options);
			}
		};

		$(".cpf_cnpj").mask(cpfMascara, cpfOptions);
	}, 200);
});


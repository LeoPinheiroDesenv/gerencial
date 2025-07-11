var TOTAL = 0;
var ITENS = [];
var caixaAberto = false;
var PRODUTO = null;
var CLIENTE = null;
var LIMITEDESCONTO = 0;
var VALORDOPRODUTO = 0;
var TOTALEMABERTOCLIENTE = null;
var COMANDA = 0;
var PEDIDOIFOOD = 0;
var PRODUTOS = [];
var VALORBAIRRO = 0;
var VALORACRESCIMO = 0;
var OBSERVACAO = "";
var OBSERVACAOITEM = "";
var DESCONTO = 0;
var LISTAID = 0;
var PDV_VALOR_RECEBIDO = 0;
var PRODUTOGRADE = null;
var VALORPAG1 = 0
var VALORPAG2 = 0
var VALORPAG3 = 0
var TIPOPAG1 = ''
var TIPOPAG2 = ''
var TIPOPAG3 = ''
var OBSPAG1 = ''
var OBSPAG2 = ''
var OBSPAG3 = ''
var VENCPAG1 = ''
var VENCPAG2 = ''
var VENCPAG3 = ''
var PAGMULTI = [];
var CATEGORIAS = [];
var CLIENTES = [];
var ACESSORES = [];
var ATALHOS = null;
var DIGITOBALANCA = 5;
var TIPOUNIDADEBALANCA = 1;
var QUANTIDADE = 1;
var VENDA = null;
var VENDEDORES = null;
var REMOVIDO_NA_TROCA = [];
var ADICIONADO_NA_TROCA = [];
var PREVENDA_NIVEL = 0;
var VENDEDOR_ID = '';
var SENHA_ALTERAR_PRECO = 0;
var sangriaRequest = false
var PERMITEDESCONTO = false;
var PERCENTUALMAXDESCONTO = false;
var SENHADESBLOQUEADA = false
var exibe_modal_cartoes = 0
var TOTALTROCAANTERIOR = 0

document.addEventListener("DOMContentLoaded", function(event) {
	// 
	$('#prods').css('visibility', 'visible')
});

function montaVenda(){

	let cont = 0;
	VENDA.itens.map((x) => {
		
		let item = {
			// cont: cont++,
			cont: Math.floor(Math.random() * 10000),
			obs: "",
			id: x.produto_id,
			nome: x.produto.nome,
			quantidade: parseFloat(x.quantidade).toFixed(casas_decimais),
			valor: parseFloat(x.valor).toFixed(casas_decimais),
			is_troca,
			imagem: x.produto.imagem
		}
		ITENS.push(item)
		PRODUTOS.push(x.produto)

		TOTAL += parseFloat(x.valor)*(x.quantidade);
	})
	console.log(VENDA)

	if(VENDA.consignado){
		// is_troca = 1
	}

	if(VENDA.desconto){
		DESCONTO = parseFloat(VENDA.desconto.replace(",", "."));
		$('#valor_desconto').html(formatReal(DESCONTO));
	}
	if(VENDA.cliente_id){
		$('#kt_select2_3').val(VENDA.cliente_id);
		selecionarCliente();
	}

	$('#valor_recebido').val(parseFloat(VENDA.prevenda_nivel == 2 ? VENDA.valor_total : VENDA.dinheiro_recebido).toFixed(casas_decimais)
		.replace('.', ','))
	setTimeout(() => {
		let t = montaTabela();
		$('#body').html(t);
		atualizaTotal();
	}, 300);

	if(VENDA.cliente_id){
		 $('#kt_select2_3').val(VENDA.cliente_id);
		 selecionarCliente();

		CLIENTE = VENDA.cliente;
	}
	if(VENDA.observacao){
		$('#obs').val(VENDA.observacao).change();
	}
}

$('.btn-consulta-status').click(() => {

	$('.btn-consulta-status').addClass('spinner')
	let token = $('#_token').val();
	$.post(path + 'nfce/consultaStatusSefaz',{_token: token})
	.done((res) => {
		$('.btn-consulta-status').removeClass('spinner')
		console.log(res)
		let msg = "cStat: " + res.cStat
		msg += "\nMotivo: " + res.xMotivo
		msg += "\nAmbiente: " + (res.tpAmb == 2 ? "Homologação" : "Produção")
		msg += "\nverAplic: " + res.verAplic
		
		swal("Sucesso", msg, "success")
	})
	.fail((err) => {
		$('.btn-consulta-status').removeClass('spinner')
		console.log(err)
		try{
			swal("Erro", err.responseText, "error")
		}catch{
			swal("Erro", "Algo deu errado", "error")
		}
	})
})

function selectVendedor(){
	VENDEDOR_ID = $('#select-vendedor').val()
	if(VENDEDOR_ID){

		let nome = VENDEDORES.find((i) => i.id == VENDEDOR_ID);
		nome = nome.funcionario.nome;
		$('#btn_informar_vendedor').text(nome);
	}
}

$(function () {

	setTimeout(() => {
		// console.clear()
	}, 1000)
	exibe_modal_cartoes = $('#exibe_modal_cartoes').val()
	$('#select-assessor').val('').change()
	$('#codBarras').focus(() => {
		$('#mousetrapTitle').css('display', 'none');
	});
	$('#codBarras').focusout(() => {
		$('#mousetrapTitle').css('display', 'flex');
	});

	try{
		setTimeout(() => {
			try{
				VENDA = JSON.parse($('#venda').val())
				montaVenda()
			}catch{}
		}, 10)
		
	}catch(e){
	}

	try{
		VENDEDORES = JSON.parse($('#vendedores').val())
		ACESSORES = JSON.parse($('#acessores').val())
	}catch(e){
		
	}

	// if(is_preVenda){
	// 	// if(localStorage.getItem('VENDEDOR_ID') > 0){
	// 	// 	VENDEDOR_ID = localStorage.getItem('VENDEDOR_ID');
	// 	// 	$('#select-vendedor').val(VENDEDOR_ID)
	// 	// }else{
	// 	// 	localStorage.setItem('VENDEDOR_ID', '');
	// 	// }
	// 	if($('#select-vendedor').val() !== ''){
	// 		VENDEDOR_ID = $('#select-vendedor').val();
	// 	}

	// 	$('#select-vendedor').change(e => {
	// 		VENDEDOR_ID = e.target.value;
	// 		localStorage.setItem('VENDEDOR_ID', VENDEDOR_ID);
	// 		$('#codBarras').focus();
	// 	})
	// }
	if(!VENDA){
		// if($('#select-vendedor').val() !== ''){
		// 	VENDEDOR_ID = $('#select-vendedor').val();

		// 	let nome = VENDEDORES.find((i) => i.id == VENDEDOR_ID);
		// 	nome = nome.funcionario.nome;
		// 	$('#btn_informar_vendedor').text(nome);
		// }

		// $('#select-vendedor').change(e => {
		// 	VENDEDOR_ID = e.target.value;
		// 	localStorage.setItem('VENDEDOR_ID', VENDEDOR_ID);
		// 	$('#codBarras').focus();

		// 	let nome = VENDEDORES.find((i) => i.id == VENDEDOR_ID);
		// 	nome = nome.funcionario.nome;
		// 	$('#btn_informar_vendedor').text(nome);
		// })
	}else{
		VENDEDOR_ID = $('#select-vendedor').val();
	}

	if($('#vendedor_pre_venda').val()){
		VENDEDOR_ID = $('#vendedor_pre_venda').val()
	}
	ATALHOS = JSON.parse($('#ATALHOS').val())

	CATEGORIAS = JSON.parse($('#categorias').val())
	CLIENTES = JSON.parse($('#clientes').val())

	montaAtalhos()

	$('#salvar-venda').attr('disabled', true)
	$('#finalizar-venda').attr('disabled', true)
	$('#finalizar-rascunho').attr('disabled', true)
	$('#finalizar-consignado').attr('disabled', true)

	var w = window.innerWidth
	if(w < 900){
		$('#grade').trigger('click')
	}

	novaHora();
	novaData();
	$('#codBarras').val('')

	let semCertificado = $('#semCertificado').val()
	if(semCertificado){
		swal("Aviso", "Para habilitar o cupom fiscal, realize o upload do certificado digital!!", "warning")
	}

	PDV_VALOR_RECEBIDO = $('#PDV_VALOR_RECEBIDO').val()

	let valor_entrega = $('#valor_entrega').val();

	VALORACRESCIMO = parseFloat(valor_entrega);
	let obs = $('#obs').val();
	if(obs) OBSERVACAO = obs;

	verificaCaixa((v) => {
		// 
		caixaAberto = v >= 0 ? true : false;
		if(v < 0){
			$('#modal1').modal('show');
		}
	})

	let itensPedido = $('#itens_pedido').val();

	//Verifica se os dados estao vindo da comanda
	//Controller Pedido
	if(itensPedido){

		itensPedido = JSON.parse(itensPedido);
		console.clear()
		console.log("itensPedido", itensPedido)
		if($('#bairro').val() != 0){
			// 
			let bairro = JSON.parse($('#bairro').val());

			VALORBAIRRO = parseFloat(bairro.valor_entrega);
		}
		let cont = 1;
		itensPedido.map((v) => {

			let nome = '';
			let valorUnit = 0;
			if(v.sabores && v.sabores.length > 0){

				let cont = 0;
				v.sabores.map((sb) => {
					cont++;
					valorUnit = v.valor;
					nome += sb.produto.produto.nome +
					(cont == v.sabores.length ? '' : ' | ')
				})
				valorUnit = v.maiorValor

			}else{

				if (typeof v.produto !== 'undefined') {
					
					nome = v.produto.nome;
					valorUnit = v.valor
				}else{

					nome = v.nome;
					valorUnit = v.valor
				}
			}

			let item = null
			if (typeof v.produto !== 'undefined') {
				item = {
					// cont: cont++,
					cont: Math.floor(Math.random() * 10000),
					id: v.produto_id,
					nome: nome,
					quantidade: v.quantidade,
					valor: parseFloat(valorUnit) + parseFloat(v.valorAdicional),
					pizza: v.maiorValor ? true : false,
					itemPedido: v.item_pedido,
					imagem: v.produto.imagem
				}
				if(!item.valor){
					item.valor = parseFloat(valorUnit)
				}
			}else{

				item = {
					// cont: cont++,
					cont: Math.floor(Math.random() * 10000),
					id: v.id,
					nome: nome,
					quantidade: 1 + "",
					valor: (valorUnit),
					pizza: false,
					itemPedido: null
				}
			}
			

			ITENS.push(item)

			TOTAL += parseFloat((item.valor * item.quantidade));

		});
		let t = montaTabela();

		let valor_total = $('#valor_total').val();
		if(valor_total > TOTAL){
			TOTAL = valor_total
			VALORACRESCIMO = 0;
		}

		atualizaTotal();
		$('#body').html(t);
		let codigo_comanda = $('#codigo_comanda_hidden').val();
		let pedido_ifood = $('#pedido_ifood').val();

		COMANDA = codigo_comanda;
		PEDIDOIFOOD = pedido_ifood;

	}

	// ******* Bootstrap datepicker tradução pt-BR
	!function(a){a.fn.datepicker.dates["pt-BR"]={days:["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],daysShort:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],daysMin:["Do","Se","Te","Qu","Qu","Se","Sa"],months:["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],monthsShort:["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],today:"Hoje",monthsTitle:"Meses",clear:"Limpar",format:"dd/mm/yyyy"}}(jQuery);
	// *******
	// transformado #vencimento_pagamento em datepicker
	$('#vencimento_pagamento').datepicker({
		todayBtn: "linked",
		clearBtn: false,
		format: 'dd/mm/yyyy',
		todayHighlight: false,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		},
		language: "pt-BR"
	})

	$("#kt_select2_1").select2({
		minimumInputLength: 1,
		language: "pt-BR",
		placeholder: "Digite para buscar o produto",
		width: "90%",
		ajax: {
			cache: true,
			url: path + 'produtos/autocomplete',
			dataType: "json",
			data: function(params) {
				console.clear();
				let filial = $('#filial').val();
				let lista_id = $('#lista_id').val();
	
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
	
					let precoNormal = parseFloat(v.valor_venda).toFixed(2).replace(".", ",");
					let precoPromo = v.preco_promocional ? parseFloat(v.preco_promocional).toFixed(2).replace(".", ",") : null;
					
					let precoTexto = precoPromo 
						? `<span style="text-decoration: line-through; color: gray;">R$ ${precoNormal}</span> 
							<span style="color:red; font-weight:bold;">R$ ${precoPromo} (Promoção!)</span>` 
						: `R$ ${precoNormal}`;
	
					o.text = v.nome + (v.grade ? " "+v.str_grade : "") + " | " + precoTexto
						+ (v.referencia != "" ? " - Ref: " + v.referencia : "") 
						+ (parseFloat(v.estoqueAtual) > 0 ? " | Estoque: " + v.estoqueAtual : "");
	
					o.value = v.id;
					results.push(o);
				});
	
				return {
					results: results
				};
			}
		},
		escapeMarkup: function(markup) {
			return markup; // Permite renderizar HTML no Select2
		}
	});
	

	setTimeout(() => {
		if(VENDA){
			TOTALTROCAANTERIOR = VENDA.valor_total
		}
	}, 100)

	$('.select2-selection__arrow').addClass('select2-selection__arroww')
	$('.select2-selection__arrow').removeClass('select2-selection__arrow')
	PERMITEDESCONTO = $('#permite_desconto').val()
	PERCENTUALMAXDESCONTO = $('#percentual_max_desconto').val()
});

$("#kt_select2_1").change(() => {
    let lista_id = $('#lista_id').val();
    let id = $("#kt_select2_1").val();
    let filial_id = $("#filial").val();

    if(id){
        console.log("🔍 Buscando produto no servidor... ID:", id);

        $.get(path + 'produtos/autocompleteProduto', {id: id, lista_id: lista_id, filial_id: filial_id})
        .done((res) => {
            // Armazena o produto recebido globalmente para uso posterior
            PTEMP = PRODUTO = res;
            console.clear();
            console.log("✅ Produto recebido do servidor:", res);
            console.log("Lotes:", res.lotes);

            // Se existir lote, mostra o botão e abre automaticamente o modal
            if (res.lotes && res.lotes.length > 0) {
                $("#btn-selecionar-lote").show();
                montarTabelaLotes(res.lotes);
                $('#modal-lotes').modal('show');
            } else {
                // Se não tiver lotes, oculta o botão
                $("#btn-selecionar-lote").hide();
            }

            LIMITEDESCONTO = parseFloat(PRODUTO.limite_maximo_desconto);
            VALORDOPRODUTO = parseFloat(PRODUTO.valor_venda);

            let precoNormal = parseFloat(PRODUTO.valor_venda).toFixed(2);
            let precoPromo = PRODUTO.preco_promocional ? parseFloat(PRODUTO.preco_promocional).toFixed(2) : null;

            console.log("💰 Preço normal:", precoNormal, " | Preço promocional:", precoPromo);

            let precoFinal = precoPromo ? precoPromo : precoNormal;

            // Se o usuário não editou manualmente o valor, seta o valor final
            if (!$('#valor_item').data('editado')) {
                $('#valor_item').val(precoFinal);
            }
            $('#quantidade').val(1);
        })
        .fail((err) => {
            console.log("❌ Erro ao buscar produto:", err);
            swal("Erro", "Erro ao encontrar produto", "error");
        });
    }
});

$(document).ready(function() {
    var acaoPos = $('#PDV_ACAO_POS_PRODUTO').val();
    if (!acaoPos) {
        // Se estiver vazio, define o valor padrão desejado, por exemplo:
        acaoPos = 'quantidade';
    }
    window.acaoPosProduto = acaoPos;
    console.log("Ação pós seleção definida:", window.acaoPosProduto);
    
    $('#acao_pos_produto').on('change', function() {
        var novoValor = $(this).val();
        $('#PDV_ACAO_POS_PRODUTO').val(novoValor);
        window.acaoPosProduto = novoValor;
        console.log("Nova ação (select e hidden):", window.acaoPosProduto);
    });

	$('#kt_select2_1').on('select2:select', function(e) {
		var acao = window.acaoPosProduto;
		console.log("Ação pós seleção no select2: ", acao);
		if (acao === 'quantidade') {
			$("#quantidade").focus().select();
		} else if (acao === 'valor_item') {
			$("#valor_item").focus().select();
		} else if (acao === 'adicionar-item') {
			waitAndCallAddItem();
		} else {
			$("#quantidade").focus().select();
		}
	});	
});

/**
 * Função que aguarda até que o objeto do produto esteja disponível.
 * Se estiver pronto, chama addItem(); caso contrário, aguarda um pouco e tenta novamente.
 */
function waitAndCallAddItem() {
    // Se o alerta ainda não existir, cria um aviso na tela
    if ($('#prodStatusMessage').length === 0) {
        $('body').append(
            '<div id="prodStatusMessage" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%);' +
            'background: #f8f9fa; color: #333; border: 1px solid #ccc; padding: 10px 20px; z-index: 9999; border-radius: 5px;">' +
            'Produto sendo incluído na venda, aguarde...' +
            '</div>'
        );
    } else {
        $('#prodStatusMessage').text("Produto sendo incluído na venda, aguarde...");
    }
    
    // Verifica se o objeto do produto está disponível e possui a propriedade "valor_venda"
    if (window.PRODUTO && window.PRODUTO.valor_venda != null) {
        // Remove a mensagem, adiciona o item e logo em seguida reabre o select2
        $('#prodStatusMessage').remove();
        addItem();
        // Aguarda um breve momento e foca no select2 novamente
        setTimeout(function() {
            $("#kt_select2_1").select2("open");
        }, 200);
    } else {
        // Se ainda não estiver pronto, tenta novamente após 300ms
        setTimeout(waitAndCallAddItem, 300);
    }
}

// Função que monta a tabela de lotes dentro do modal
function montarTabelaLotes(lotes) {
    let html = `<table class="table table-bordered">
        <thead>
            <tr>
                <th>Lote</th>
                <th>Data de Fabricação</th>
                <th>Data de Validade</th>
                <th>Qtd. Inicial</th>
                <th>Saldo</th>
                <th>Fabricante</th>
                <th>Cód. de Barras</th>
                <th>Preço Compra</th>
                <th>Preço Venda</th>
            </tr>
        </thead>
        <tbody>`;
    lotes.forEach(function(lote) {
        // Se o objeto lote não tiver o fabricante já carregado, você pode exibir o id ou tratar conforme necessário.
        let fabricante = lote.fabricante && lote.fabricante.razao_social ? lote.fabricante.razao_social : lote.fabricante_id;
        html += `<tr class="lote-item" data-lote-id="${lote.id}" data-numero-lote="${lote.numero_lote}" style="cursor:pointer;">
                    <td>${lote.numero_lote}</td>
                    <td>${lote.data_fabricacao}</td>
                    <td>${lote.data_validade}</td>
                    <td>${lote.quantidade_inicial}</td>
                    <td>${lote.saldo}</td>
                    <td>${fabricante}</td>
                    <td>${lote.codigo_barras}</td>
                    <td>${lote.preco_compra}</td>
                    <td>${lote.preco_venda}</td>
                 </tr>`;
    });
    html += `</tbody></table>`;
    $("#modal-lotes .modal-body").html(html);
}

// Quando o usuário clica em uma linha do modal, seleciona o lote
$(document).on("click", ".lote-item", function() {
    let selectedLoteId = $(this).data("lote-id");
    let numeroLote = $(this).data("numero-lote");

    // Salva o ID do lote selecionado em um campo oculto para envio
    $("#input-lote-selecionado").val(selectedLoteId);
    // Atualiza o botão ou área de exibição com o número do lote selecionado
    $("#btn-selecionar-lote").text("Lote Selecionado: " + numeroLote);
    // Fecha o modal
    $("#modal-lotes").modal("hide");
});

$('#desconto').keyup(() => {
	$('#acrescimo').val('0')
	let desconto = $('#desconto').val();
	// if(!desconto){ $('#desconto').val('0'); desconto = 0}

	if(desconto){
		desconto = parseFloat(desconto.replace(",", "."))
		DESCONTO = 0;
		if(desconto > TOTAL && $('#desconto').val().length > 2){
			// Materialize.toast('ERRO, Valor desconto maior que o valor total', 4000)
			$('#desconto').val("");
		}else{
			DESCONTO = desconto;

			atualizaTotal();
		}
	}
})

function pad(s) {
	return (s < 10) ? '0' + s : s;
}

var CATEGORIASELECIONADA = null
function filtroCategoria(cat){

	$('#pesquisa-produto-lateral').val('')
	CATEGORIASELECIONADA = cat
	desmarcarCategorias(() => {
		$('#cat_' + cat).addClass('ativo')
	})

	produtosDaCategoria(cat, (res) => {
		if(res){
			montaProdutosPorCategoria(res, (html) => {
				$('#prods').html(html)
			})
		}
	})
}

var ITEMATACADO = 0
$(document).on("blur", "#quantidade", function () {
	let quantidade = $(this).val()
	$.get(path + 'produtos/valida-atacado', {
		quantidade: quantidade,
		produto_id: PRODUTO.id
	})
	.done((res) => {
		console.log(res)
		if(res){
			let v = parseFloat(res)
			if(v > 0){
				ITEMATACADO = 1
				$('#valor_item').val(convertFloatToMoeda(v))
			}
		}
	})
	.fail((err) => {
		console.log(err)
	})
});

function desmarcarCategorias(call){
	CATEGORIAS.map((v) => {
		$('#cat_' + v.id).removeClass('ativo')
		$('#cat_' + v.id).removeClass('desativo')
	})
	$('#cat_todos').removeClass('desativo')
	$('#cat_todos').removeClass('ativo')

	call(true)
}

function produtosDaCategoria(categoria, call){
	let lista_id = $('#lista_id').val();
	let filial = $('#filial').val()

	// $('#codBarras').focus()
	console.clear()
	temp = [];
	if(categoria != 'todos'){
		$.get(path + 'produtos/produtosDaCategoria', 
		{
			categoria_id: categoria, 
			lista_id: lista_id,
			filial_id: filial,
		})
		.done((res) => {
			console.log(res)
			
			call(res)
		})
		.fail((err) => {
			
			call(null)
		})
	}else{
		$.get(path + 'produtos/produtosRandom', {lista_id: lista_id, filial_id: filial})
		.done((res) => {
			console.log(res)
			call(res)
		})
		.fail((err) => {
			console.log(err)
			call(null)
		})
	}
	call(temp)
}

$('#pesquisa-produto-lateral').keyup(() => {
	let pesquisa = $('#pesquisa-produto-lateral').val();
	let filial = $('#filial').val()
	let lista_id = $('#lista_id').val();
	
	if(pesquisa.length > 1){
		$.get(path + 'produtos/autocomplete', 
		{
			pesquisa: pesquisa, 
			filial_id: filial,
			lista_id: lista_id,
			categoria: CATEGORIASELECIONADA
		})
		.done((res) => {
			console.log(res)
			montaProdutosPorCategoria(res, (html) => {
				$('#prods').html(html)
			})
		})
		.fail((err) => {
			console.log(err)
		})

	}
})

function montaProdutosPorCategoria(produtos, call){

	$('#prods').html('')
	let lista_id = $('#lista_id').val();

	let html = '';
	produtos.map((p) => {
		// 
		html += '<div class="col-sm-12 col-lg-6 col-md-6 col-xl-6" id="atalho_add" '
		html += 'onclick="adicionarProdutoRapido2(\''+ p.id +'\')">'
		html += '<div class="card card-custom gutter-b example example-compact">'
		html += '<div class="card-header" style="height: 200px;">'
		html += '<div class="symbol symbol-circle symbol-lg-100">'
		if(p.imagem == ''){
			html += '<img class="img-prod" src="/imgs/no_image.png">'
		}else{
			html += '<img class="img-prod" src="/imgs_produtos/'+p.imagem+'">'
		}
		html += '</div>'
		html += '<h6 style="font-size: 12px; width: 100%" class="kt-widget__label">'
		html += p.nome.substr(0, 40) + '</h6>'
		html += '<h6 style="font-size: 12px;" class="text-danger" class="kt-widget__label">R$ '
		if(lista_id == 0){
			html += formatReal(parseFloat(p.valor_venda).toFixed(casas_decimais).replace('.', ',')) + '</h6>'
		}else{
			let v = 0;
			p.lista_preco.map((l) => {
				if(lista_id == l.lista_id){
					html += formatReal(parseFloat(l.valor).toFixed(casas_decimais).replace('.', ',')) + '</h6>'

				}
			})
		}
		if(p.gerenciar_estoque == 1){
			html += '<h6 style="font-size: 10px; margin-right: -15px;" class="text-info" class="kt-widget__label">';
			html += 'Estoque: '
			html += p.estoqueAtual
			html += '</h6>'
		}

		html += '</div></div></div>'
	})

	call(html)
}

function adicionarProdutoRapido(produto){
    let lista_id = $('#lista_id').val();
    produto = JSON.parse(produto);
    PRODUTO = produto;

    let precoNormal = parseFloat(produto.valor_venda).toFixed(2);
    let precoPromo = produto.preco_promocional ? parseFloat(produto.preco_promocional).toFixed(2) : null;

    console.log("⚡ Adicionando produto rápido. Preço normal:", precoNormal, "| Preço promocional:", precoPromo);

    let precoFinal = precoPromo ? precoPromo : precoNormal;

    $('#valor_item').val(precoFinal);
    $('#quantidade').val(QUANTIDADE);
    QUANTIDADE = 1;
    addItem();
}


function selectAssessor(){
	let id = $('#select-assessor').val()
	let a = ACESSORES.find((i) => { return i.id == id })
	let nome = a.razao_social
	$('#btn_informar_assessor').text(nome)
}

function adicionarProdutoRapido2(id){
    let lista_id = $('#lista_id').val();
    let filial_id = $('#filial').val();

    $('#pesquisa-produto-lateral').val("");
    if(id){
        $.get(path + 'produtos/autocompleteProduto', {id: id, lista_id: lista_id, filial_id: filial_id})
        .done((p) => {
            // Se o produto possuir lotes, exibe o modal para selecionar o lote
            if(p.lotes && p.lotes.length > 0){
                // Armazena o produto globalmente para usar depois
                CURRENT_PRODUCT = p;
                // Preenche o select do modal com os lotes disponíveis
                let loteOptions = '<option value="">Selecione o lote</option>';
                p.lotes.forEach(function(lote) {
                    loteOptions += `<option value="${lote.id}">Lote: ${lote.numero_lote} - Validade: ${lote.data_validade}</option>`;
                });
                $('#loteSelect').html(loteOptions);
                // Exibe o modal de seleção de lote
                $('#loteModal').modal('show');
            } else {
                // Caso o produto não tenha lotes, atribui normalmente e adiciona o item
                PRODUTO = p;
                let precoNormal = parseFloat(p.valor_venda).toFixed(2);
                let precoPromo = p.preco_promocional ? parseFloat(p.preco_promocional).toFixed(2) : null;
                console.log("🚀 Produto encontrado. Preço normal:", precoNormal, "| Preço promocional:", precoPromo);
                let precoFinal = precoPromo ? precoPromo : precoNormal;
                $('#valor_item').val(precoFinal);
                $('#quantidade').val(QUANTIDADE);
                QUANTIDADE = 1;
                addItem(); // adiciona o item sem lote
            }
        })
        .fail((err) => {
            console.log("❌ Erro ao buscar produto:", err);
        });
    }
}

function novaHora() {
	var date = new Date();
	let v = [date.getHours(), date.getMinutes()].map(pad).join(':');
	$('#horas').html(v);
}

function novaData() {
	var date = new Date();
	let v = [date.getDate(), date.getMonth()+1, date.getFullYear()].map(pad).join('/');
	$('#data').html(v);
}

function apontarObs(){
	let obs = $('#obs').val();
	OBSERVACAO = obs;

	$('#modal-obs').modal('hide')
}

function setarObservacaoItem(){
	let obs = $('#obs-item').val();
	OBSERVACAOITEM = obs;

	$('#modal-obs-item').modal('hide')
}

$('#autocomplete-cliente').on('keyup', () => {
	$('#cliente-nao').css('display', 'block');
	CLIENTE = null;
})

function formatRealTotal(v){
	return parseFloat(v).toFixed(2).replace('.', ',')
}

function formatReal(v, style = true){
	return style
	? v.toLocaleString('pt-br',{style: 'currency', currency: 'BRL', minimumFractionDigits: casas_decimais})
	: v.toLocaleString('pt-br',{currency: 'BRL', minimumFractionDigits: casas_decimais});
}

function getProdutos(data){
	$.ajax
	({
		type: 'GET',
		url: path + 'produtos/all',
		dataType: 'json',
		success: function(e){
			data(e)

		}, error: function(e){
			
		}

	});
}

function getClientes(data){
	$.ajax
	({
		type: 'GET',
		url: path + 'clientes/all',
		dataType: 'json',
		success: function(e){
			data(e)
		}, error: function(e){
			
		}

	});
}

function getCliente(id, data){
	$.ajax
	({
		type: 'GET',
		url: path + 'clientes/find/'+id,
		dataType: 'json',
		success: function(e){
			data(e)
		}, error: function(e){
			
		}

	});
}

function getVendasEmAbertoContaCredito(id, data){
	$.ajax
	({
		type: 'GET',
		url: path + 'vendasEmCredito/somaVendas/'+id,
		dataType: 'json',
		success: function(e){
			data(e)
		}, error: function(e){
			
		}

	});
}

$('#codBarras').keyup((v) => {
	setTimeout(() => {
		let cod = v.target.value
		if(cod.length > 7){
			$('#codBarras').val('')
			getProdutoCodBarras(cod, (data) => {
				if(data){
					setTimeout(() => {
						addItem();
					}, 400)
				}else{

				}
			})

		}
	}, 500)
})

$('#barcode').keypress(function(event){
	var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode == '13'){
		buscarProdutoCodigoBarras()  
	}
});

function buscarProdutoCodigoBarras(){
	getProdutoCodBarras($('#barcode').val(), (data) => {
		if(data){
			setTimeout(() => {
				addItem();
			}, 400)
		}else{

		}
	})
	$('#modal-barcode').modal('hide')
}

$('#focus-codigo').click(() => {
	$('#codBarras').focus()
})

$('#focus-codigo').dblclick(() => {

	$('#modal-cod-barras').modal('show')
	$('#cod-barras2').focus()
})

$('#lista_id').change(() => {
	let lista = $('#lista_id').val();
	filtroCategoria('todos')
})

$('#select-doc').change(() => {
	let tipo = $('#select-doc').val()
	if(tipo == 'CPF'){
		$('#tipo-doc').html('CPF')
		$('#cpf').attr("placeholder", "CPF")
		$('#cpf').mask('000.000.000-00', {reverse: true});
	}else{
		$('#tipo-doc').html('CNPJ')
		$('#cpf').attr("placeholder", "CNPJ")
		$('#cpf').mask('00.000.000/0000-00', {reverse: true});
	}
})

function getProduto(id, data){
	
	$.ajax
	({
		type: 'GET',
		url: path + 'produtos/getProdutoVenda/' + id + '/' + LISTAID,
		dataType: 'json',
		success: function(e){
			data(e)
		}, error: function(e){
			
		}
	});
}

function montaGrade(referencia){
	let prods = PRODUTOS.filter((x) => {
		if(referencia == x.referencia_grade) return x
	})
	
	let html = ''
	prods.map((p) => {
		html += '<div class="row" style="height: 40px">'
		html += '<div class="col-sm-8 col-lg-8 col-10">'
		html += '<h4>'+ p.str_grade +'</h4>'
		html += '</div>'
		html += '<div class="col-sm-2 col-lg-2 col-2">'
		html += '<button onclick="selectGrade('+p.id+')" class="btn btn-success btn-sm btn-block">'
		html += '<i class="la la-check"></i></button>'
		html += '</div></div>'
	})
	$('.grade-prod').html(html)
}

function selectGrade(id){
	let p = PRODUTOS.filter((x) => { return x.id == id })
	p = p[0]
	PRODUTOGRADE = p
	LIMITEDESCONTO = parseFloat(p.limite_maximo_desconto);
	VALORDOPRODUTO = parseFloat(p.valor_venda);
	
	let lista_id = $('#lista_id').val()

	$('#quantidade').val('1')
	if(lista_id == 0){
		$('#valor_item').val(p.valor_venda)
	}else{
		p.lista_preco.map((l) => {
			if(lista_id == l.lista_id){
				$('#valor_item').val(l.valor)
			}
		})
	}
	$('#modal-grade').modal('hide')
}

$('#finalizar-venda').click(() => {
	// $('#modal-venda').modal('show')
	// verificaPrecoAlterado(function(){ $('#modal-venda').modal('show') });
	let caixa_livre = $('#caixa_livre').val()
	if(caixa_livre == 1 && !VENDEDOR_ID){
		swal("Atenção", "Informe o vendedor", "warning")
		return;
	}
	if(TOTAL > 0){

		let tipoPagamento = $('#tipo-pagamento').val()
		if(tipoPagamento == 17){
			$('#finalizar-venda').addClass('spinner')

			$.get(path + 'vendasCaixa/pix', {valor: TOTAL-DESCONTO+VALORACRESCIMO})
			.done((success) => {
				$('#finalizar-venda').removeClass('spinner')
				swal("Sucesso", "Chave PIX gerada", "success")
				.then(() => {
					$(".qrcode").attr("src", "data:image/jpeg;base64,"+success['qrcode']);
					$('#modal-pix').modal('show')
					let payment_id = success['payment_id']
					let pay = false
					setInterval(() => {
						if(pay == false){
							$.get(path + 'vendasCaixa/consultaPix/'+payment_id)
							.done((res) => {

								if(res == "approved"){
									$('#modal-pix').modal('hide')
									if(pay == false){
										swal("Sucesso", "Pagamento aprovado", "success")
										.then(() => {
											// $('#modal-venda').modal('show')
											verificaPrecoAlterado(function(){ $('#modal-venda').modal('show') });
											setTimeout(() => {
												$('.btn-close').addClass('d-none')
											}, 100)
										})
									}
									pay = true

								}
							})
							.fail((err) => {
								
							})
						}
					}, 1000)
				})
			}).fail((err) => {
				
				$('#finalizar-venda').removeClass('spinner')
				if(err.status == 401){
					// swal("Erro", err.responseJSON.message, "error")
					$('#modal-venda').modal('show')
				}else{
					swal("Erro", err.responseJSON.message, "error")
				}
			})
		}else{
			verificaPrecoAlterado(function(){ $('#modal-venda').modal('show') });
		}
	}else{
		swal("Erro", "Total da venda precisa ser maior que zero", "error")
	}
})

function somaQuantidadeProdutoAdicionado(produto, quantidadeAdicionar, call){
	console.clear()

	
	let quantidade = 0;
	ITENS.map((p) => {
		if(p.codigo == produto.id){
			quantidade += parseFloat(p.quantidade)
		}
	})

	quantidade += parseFloat(quantidadeAdicionar);

	if(produto.gerenciar_estoque == 1 && (!produto.estoque || produto.estoque.quantidade < quantidade)){
		call(false)
	}else{
		call(true)
	}
}

function addItem(valorDigitado) {
    if (!caixaAberto) {
        swal("Erro", "Abra o caixa para vender!!", "error");
        return;
    }

    // Se vier de seleção de grade
    if (PRODUTOGRADE) {
        PRODUTO = PRODUTOGRADE;
    }

    // Preço base do produto
    let precoNormal = parseFloat(PRODUTO.valor_venda);
    let precoPromo  = PRODUTO.preco_promocional ? parseFloat(PRODUTO.preco_promocional) : null;
    // Se o usuário digitou manualmente, usar esse valor; senão, promo ou normal
    let precoAplicado = (valorDigitado > 0)
        ? valorDigitado
        : (precoPromo !== null ? precoPromo : precoNormal);

    // Valor como string "12.34"
    let valorItem   = precoAplicado.toFixed(2).replace(",", ".");
    let quantidade  = $('#quantidade').val().replace(",", ".");

    console.log("🛒 Adicionando produto:", PRODUTO.nome);
    console.log("💰 Preço aplicado:", precoAplicado);
    console.log("📦 Quantidade:", quantidade);

    if (!PRODUTO || valorItem <= 0 || quantidade <= 0) {
        swal('Cuidado', 'Informe corretamente para continuar', 'warning');
        return;
    }

    // Verifica estoque ou produto já incluso
    verificaProdutoIncluso((call) => {
        $('#codBarras').val('');
        if (call < 0) {
            swal('Cuidado', 'Informe corretamente para continuar', 'warning');
            return;
        }

        let estoqueAtual = PRODUTO.estoque ? parseFloat(PRODUTO.estoque.quantidade) : 0;
        if (PRODUTO.gerenciar_estoque == 1 &&
            (parseFloat(quantidade) + parseFloat(call) > estoqueAtual)) {
            swal("Erro", 'O estoque atual deste produto é de ' + estoqueAtual, "warning");
            $('#quantidade').val('1');
            return;
        }

        // Adiciona o produto nos arrays
        PRODUTOS.push(PRODUTO);
        TOTAL += parseFloat(valorItem) * parseFloat(quantidade);

        // Cria o objeto item: guarda o valor original e o valor final (que poderá ser alterado depois)
        let item = {
            cont:            Math.floor(Math.random() * 10000),
            obs:             OBSERVACAOITEM,
            id:              PRODUTO.id,
            nome:            PRODUTO.nome + (PRODUTO.lote_id ? " (Lote: " + PRODUTO.lote_id + ")" : ""),
            quantidade:      quantidade,
            valor_original:  valorItem, // valor base antes de qualquer desconto
            valor:           valorItem, // valor final (ainda sem desconto)
            desconto:        0,         // desconto inicial zero
            lote_id:         PRODUTO.lote_id || null,
            adicionado_na_troca: is_troca,
            imagem:          PRODUTO.imagem,
            item_atacado:    ITEMATACADO
        };
        ITENS.push(item);

        // Atualiza UI
        limparCamposFormProd();
        atualizaTotal();
        $('#salvar-venda, #finalizar-venda').prop('disabled', ITENS.length === 0);
        $('#body').html(montaTabela());
        setTimeout(() => {
            $('.datatable-table').scrollTop($('.datatable-table')[0].scrollHeight);
        }, 100);

        // Se estiver configurado para isso, já preenche valor recebido com o total
        setTimeout(() => {
            if ($('#PDV_VALOR_RECEBIDO').val() == "1") {
                let totalVendaStr = $('#total-venda').text()
                    .replace(/[^\d,.-]/g, '')
                    .replace(",", ".");
                $('#valor_recebido').val(totalVendaStr).trigger('change');
            }
        }, 200);

        // Reseta variáveis de estado
        PRODUTO = null;
        ITEMATACADO = 0;
        OBSERVACAOITEM = "";
        $('#obs-item').val('');
        $('#kt_select2_1').val('').change();
    });

    vendaAlterada();
    $('#codBarras').focus();
    QUANTIDADE = 1;
}

function setaObservacao(){
	$('#modal-obs').modal('show')
}
function validaPass(call){
	let senha = $('#pass').val()
	if(senha != "" && !SENHADESBLOQUEADA){

		swal({
			title: 'Desconto de item',
			text: 'Informe a senha!',
			content: {
				element: "input",
				attributes: {
					placeholder: "Digite a senha",
					type: "password",
				},
			},
			button: {
				text: "Desbloquear!",
				closeModal: false,
				type: 'error'
			},
			confirmButtonColor: "#DD6B55",
		}).then(v => {
			if(v.length > 0){
				$.get(path+'configNF/verificaSenha', {senha: v})
				.then(
					res => {

						SENHADESBLOQUEADA = true
						call(true)
					},
					err => {

						swal.close()
						swal("Erro", "Senha incorreta", "error")
						.then(() => {
							call(false)

						});
					}
					)
			}else{
				location.reload()
			}
		})
	}else{
		call(true)
	}
}

function setaDesconto(){

	validaPass((sim) => {
		if(sim){
			if(TOTAL == 0){
				swal("Erro", "Total da venda é igual a zero", "warning")
			}else{
				swal({
					title: 'Valor desconto?',
					text: 'Ultilize ponto(.) ao invés de virgula!',
					content: "input",
					button: {
						text: "Ok",
						closeModal: false,
						type: 'error'
					}
				}).then(v => {
					if(v) {
						console.clear()
						let desconto = v;
						if(desconto.substring(0, 1) == "%"){
							let perc = desconto.substring(1, desconto.length);
							if(PERCENTUALMAXDESCONTO > 0){
								if(parseFloat(perc) > parseFloat(PERCENTUALMAXDESCONTO)){
									swal.close()

									setTimeout(() => {
										swal("Erro", "Máximo de desconto permitido é de " + PERCENTUALMAXDESCONTO + "%", "error")
										$('#valor_desconto').html('0,00')
									},500)
									return;
								}
							}
							DESCONTO = TOTAL * (perc/100);


							if(DESCONTO > 0){
								$('#valor_item').attr('disabled', 'disabled')
								$('.btn-mini-desconto').attr('disabled', 'disabled')
							}else{
								$('#valor_item').removeAttr('disabled')
								$('.btn-mini-desconto').removeAttr('disabled')
							}
						}else{
							
							desconto = desconto.replace(",", ".")
							desconto = parseFloat(desconto)

							if(PERCENTUALMAXDESCONTO > 0){
								let tempDesc = TOTAL*PERCENTUALMAXDESCONTO/100
								if(tempDesc < desconto){
									swal.close()

									setTimeout(() => {
										swal("Erro", "Máximo de desconto permitido é de R$ " + formatReal(tempDesc) , "error")
										$('#valor_desconto').html('0,00')
									},500)
									return;
								}
							}
							
							DESCONTO = parseFloat(desconto)

							if(DESCONTO > 0){
								$('#valor_item').attr('disabled', 'disabled')
								$('.btn-mini-desconto').attr('disabled', 'disabled')
							}else{
								$('#valor_item').removeAttr('disabled')
								$('.btn-mini-desconto').removeAttr('disabled')
							}
						}
						if(desconto.length == 0) DESCONTO = 0;

						$('#valor_desconto').html(formatReal(DESCONTO))
						atualizaTotal()

					}
					swal.close()
					$('#codBarras').focus()

				});
			}
		}
	});
}


function setaQuantidade(){

	swal({
		title: 'Quantidade do próximo item',
		text: 'Ultilize ponto(.) ao invés de virgula!',
		content: "input",
		button: {
			text: "Ok",
			closeModal: false,
			type: 'error'
		}
	}).then(v => {
		if(v) {
			if(v.length == 0){
				QUANTIDADE = 1;
			}else{
				QUANTIDADE = v
			}
		}

		swal.close()
		$('#codBarras').focus()

	});

}


function setaAcresicmo(){
	if(TOTAL == 0){
		swal("Erro", "Total da venda é igual a zero", "warning")
	}else{
		swal({
			title: 'Valor acrescimo?',
			text: 'Ultilize ponto(.) ao invés de virgula!',
			content: "input",
			button: {
				text: "Ok",
				closeModal: false,
				type: 'error'
			}
		}).then(v => {
			if(v) {

				let acrescimo = v;
				if(acrescimo > 0){
					DESCONTO = 0;
					$('#valor_desconto').html(formatReal(DESCONTO))
				}

				let total = TOTAL+VALORBAIRRO;

				if(acrescimo.substring(0, 1) == "%"){
					let perc = acrescimo.substring(1, acrescimo.length);
					VALORACRESCIMO = total * (perc/100);
				}else{
					acrescimo = acrescimo.replace(",", ".")
					VALORACRESCIMO = parseFloat(acrescimo)
				}

				if(acrescimo.length == 0) VALORACRESCIMO = 0;
				// atualizaTotal();
				VALORACRESCIMO = parseFloat(VALORACRESCIMO)
				$('#valor_acrescimo').html(formatReal(VALORACRESCIMO))

				atualizaTotal()
				$('#codBarras').focus()
			}
			swal.close()

		});
	}
}

$('#adicionar-item').click(() => {
    let valorManual = $('#valor_item').val();
    let valor = parseFloat(valorManual.replace(",", "."));

    let menorValorPossivel = VALORDOPRODUTO - (VALORDOPRODUTO * (LIMITEDESCONTO/100));
    
    if(LIMITEDESCONTO == 0){
        addItem(valor); // Passamos o valor manualmente
        $('#produto-search').val('');
    } else {
        if(valor >= menorValorPossivel){
            addItem(valor);
            if(parseFloat(valor) != parseFloat(PTEMP.valor_venda)){
                $('.btn-seta-desconto').attr('disabled', 1);
            }
        } else {
            swal("Erro", "Mínimo permitido para este item R$ " + menorValorPossivel.toFixed(2), "error");
        }
    }
});


function descontoItem(cont) {
    if (PERMITEDESCONTO == 0) {
        swal("Alerta", "Sem permissão para desconto!", "warning");
        return;
    }

    // 1. Encontra o item
    let item = ITENS.find(x => x.cont == cont);
    if (!item) return;

    // 2. Traz o produto para limites
    let p = PRODUTOS.find(x => x.id == item.id);
    if (!p) return;
    PRODUTO = p;

    // 3. Limite e valor base
    const LIMIT = parseFloat(PRODUTO.limite_maximo_desconto) || 0;
    const baseValor = parseFloat(item.valor_original);
    const minValor = baseValor - (baseValor * (LIMIT / 100));

    // 4. Pergunta desconto
    swal({
        title: 'Desconto do item',
        text: 'Use % no início para desconto percentual!',
        content: "input",
        button: {
            text: "Ok",
            closeModal: false,
            type: 'error'
        }
    }).then(v => {
        if (!v) return;

        // 5. Interpreta input
        v = v.replace(",", ".").trim();
        let vDesc = 0;
        if (v.charAt(0) === "%") {
            let perc = parseFloat(v.substring(1));
            if (!isNaN(perc)) vDesc = baseValor * (perc / 100);
        } else {
            let abs = parseFloat(v);
            if (!isNaN(abs)) vDesc = abs;
        }

        // 6. Calcula valor final e checa limite
        let valorFinal = baseValor - vDesc;
        if (LIMIT > 0 && valorFinal < minValor) {
            swal("Erro", `Mínimo permitido: R$ ${minValor.toFixed(2)}`, "error")
            .then(() => {
                swal.close();
                setTimeout(() => $("#kt_select2_1").select2("open"), 150);
            });
            return;
        }

        // 7. Grava no item e atualiza tabela
        item.desconto = vDesc;
        item.valor    = valorFinal.toFixed(2);

        swal.close();
        ajustaValorItem(valorFinal, item.id);
        setTimeout(() => $("#kt_select2_1").select2("open"), 150);
    });
}

function ajustaValorItem(novoValor, id){
	TOTAL = 0;
	for(let i=0; i<ITENS.length; i++){
		if(id == ITENS[i].id){
			ITENS[i].valor = novoValor+'';
		}
		TOTAL += parseFloat((ITENS[i].valor * ITENS[i].quantidade));
	}

	let t = montaTabela();
	atualizaTotal();
	$('#body').html(t);
}

function atualizaTotal() {
	// Recalcula o TOTAL com base nos itens atuais
	TOTAL = 0;
	ITENS.forEach(function(item) {
	  TOTAL += parseFloat(item.valor) * parseFloat(item.quantidade);
	});
	
	// Verifica o valor recebido e habilita/desabilita os botões conforme o tipo de pagamento
	let valor_recebido = $('#valor_recebido').val();
	if (!valor_recebido) valor_recebido = 0;
	if (valor_recebido > 0) {
	  valor_recebido = valor_recebido.replace(",", ".");
	  valor_recebido = parseFloat(valor_recebido);
	}
	
	if ($('#tipo-pagamento').val() == '01') {
	  if ((TOTAL + VALORBAIRRO + VALORACRESCIMO - DESCONTO) > valor_recebido) {
		$('#salvar-venda, #finalizar-venda, #finalizar-rascunho, #finalizar-consignado').attr('disabled', true);
	  } else {
		$('#salvar-venda, #finalizar-venda, #finalizar-rascunho, #finalizar-consignado').removeAttr('disabled');
	  }
	} else {
	  $('#salvar-venda, #finalizar-venda, #finalizar-rascunho, #finalizar-consignado').removeAttr('disabled');
	}
	
	if (!$('#valor_recebido').val()) {
	  $('#salvar-venda, #finalizar-venda, #finalizar-rascunho, #finalizar-consignado').attr('disabled', true);
	}
	
	// Atualiza o elemento que mostra o total da venda
	if (is_troca) {
	  $('#total-original').html(convertFloatToMoeda(VENDA.valor_total));
	  $('#total-venda').html(formatRealTotal(TOTAL + VALORBAIRRO + VALORACRESCIMO - VENDA.desconto - TOTALTROCAANTERIOR));
	  setTimeout(() => {
		let restante = parseFloat(VENDA.valor_total) - (TOTAL - VENDA.desconto);
		$('#total-restante').html(convertFloatToMoeda(restante > 0 ? restante : 0));
	  }, 100);
	} else {
	  let totalFinal = TOTAL + VALORBAIRRO + VALORACRESCIMO - DESCONTO;
	  $('#total-venda').html(formatRealTotal(totalFinal));
	  $('.total-venda').html("R$ " + formatRealTotal(totalFinal));

	// Atualiza o campo valor_recebido se estiver configurado para isso
	if($('#PDV_VALOR_RECEBIDO').val() == "1"){
		$('#valor_recebido').val(formatRealTotal(totalFinal)).trigger('change');
	}
  }
}

function montaTabela(){
	let t = "";
	let quantidades = 0;

	// console.clear()
	
	ITENS.map((v) => {
		

		t += `<tr class="datatable-row" style="left: 0px;">`
		t += '<td class="datatable-cell">'
		t += '<span class="codigo" style="width: 50px;">'
		if(v.imagem != ""){
			t += '<img width="50" class="img-prod" src="/imgs_produtos/'+v.imagem+'">'
		}else{
			t += '<img width="50" class="img-prod" src="/imgs/no_image.png">'
		}
		t += '</span>'
		t += '</td>'

		t += '<td class="datatable-cell">'
		t += '<span class="codigo" style="width: 50px;">'
		t += v.id
		t += '</span></td>'

		t += '<td class="datatable-cell">'
		t += '<span class="codigo" style="width: 200px;">'
		t += v.nome + (v.obs ? " [OBS: "+v.obs+"]" : "")
		t += v.item_atacado ? ' <span style="color: red"> - atacado</span>' : ''
		t += '</span></td>'

		if(is_troca){
			t += '<td class="datatable-cell">'
			t += '<span class="codigo" style="width: 120px;">' + v.quantidade + '</span>'
			t += '</td>'
		}else{
			t += '<td class="datatable-cell">'
			t += '<span class="codigo" style="width: 120px;">'
			t += '<div class="form-group mb-2">'
			t += '<div class="input-group">'
			t += '<div class="input-group-prepend">'
			t += '<button onclick="subtraiItem('+v.cont+')" class="btn btn-danger" type="button">-</button>'
			t += '</div>'
			t += '<input type="text" readonly class="form-control" value="'+v.quantidade+'">'
			t += '<div class="input-group-append">'
			t += '<button onclick="incrementaItem('+v.cont+')" class="btn btn-success" type="button">+</button>'
			t += '</div></div></div></span></td>'
		}

		t += '<td class="datatable-cell">'
		t += '<span class="codigo" style="width: 120px;">'
		t += formatReal(v.valor).replace(".", ",")
		t += '<button onclick="descontoItem('+v.cont+')" class="btn btn-sm" type="button">'
		t += '<i class="la la-dollar-sign text-danger"></i></button>'
		t += '</span></td>'

		t += '<td class="datatable-cell">'
		t += '<span class="codigo" style="width: 120px;">'
		try{
			t += formatReal((v.valor.replace(",", ".")) * (v.quantidade.replace(",", ".")))
		}catch{
			t += formatReal((v.valor) * (v.quantidade))

		}
		t += '</span></td>'

		
        // Coluna com botões
        t += '<td style="width: 90px;">';
            t += '<button class="btn btn-sm btn-warning" onclick="editItem('+ v.cont +')"><i class="la la-edit"></i></button>';
    
            // Botão de remover: se for troca, usa uma lógica; se não, usa outra.
            if(is_troca){
                t += '<button class="btn btn-danger" onclick="removeItem('+ v.cont +')"><i class="la la-trash"></i></button>';
            } else {
                t += '<button data-id="'+ v.cont +'" class="btn btn-sm btn-danger btn-delete-pass" onclick="deleteItem('+ v.cont +')"><i class="la la-trash icon-trash"></i></button>';
            }
    
        t += '</td>';
		t += '</tr>';

		quantidades += parseInt(v.quantidade);
	});

	$('#qtd-itens').html(ITENS.length);
	$('#_qtd').html(quantidades);
	return t
}

// Função para abrir o modal de edição com os dados do item
function editItem(cont) {
	// Localiza o item no array ITENS
	let item = ITENS.find(x => x.cont == cont);
	if(!item) {
	  console.warn("Item não encontrado para cont =", cont);
	  return;
	}
  
	// Preenche o modal com os dados do item
	$('#edit-cont').val(item.cont);
	$('#edit-descricao').val(item.nome);
	$('#edit-quantidade').val(item.quantidade);
	$('#edit-valor').val(parseFloat(item.valor).toFixed(2).replace('.', ','));
  
	// Abre o modal
	$('#modal-edit-item').modal('show');
} 
  
  $('#btn-salvar-edit').click(function() {
	// Pega o "cont" do campo hidden
	let cont = $('#edit-cont').val();
  
	// Localiza o item no array
	let item = ITENS.find(x => x.cont == cont);
	if(!item) {
	  console.warn("Item não encontrado para cont =", cont);
	  return;
	}
  
	// Atualiza os dados do item com os valores do modal
	item.nome = $('#edit-descricao').val();
	item.quantidade = $('#edit-quantidade').val().replace(",", ".");
	item.valor = $('#edit-valor').val().replace(",", ".");
  
	// Recalcula o total e possivelmente atualiza a variável TOTAL
	atualizaTotal();
  
	// Reconstrói a tabela com os itens atualizados
	let novaTabela = montaTabela();
	$('#body').html(novaTabela);
  
	// Fecha o modal de edição
	$('#modal-edit-item').modal('hide');
  
	// Foca no campo de pesquisa de produtos (exemplo com select2)
	$('#kt_select2_1').select2("open");
});  

$('#btn-cancelar-edit').click(function() {
	$('#modal-edit-item').modal('hide');
	setTimeout(() => {
	  $("#kt_select2_1").select2("open");
	}, 150);
});  

$('#modal-edit-item').on('shown.bs.modal', function () {
	$('#edit-descricao').trigger('focus').select();
});

$('#edit-descricao').on('focus', function() {
	$(this).select();
});  

$('#edit-quantidade').on('focus', function() {
	$(this).select();
}); 

$('#edit-valor').on('focus', function() {
	$(this).select();
}); 

$('#edit-valor').on('blur', function() {
	let val = $(this).val().replace(',', '.');
	let num = parseFloat(val);
	if (!isNaN(num)) {
	  // Formata com duas casas decimais e substitui o ponto pela vírgula
	  $(this).val(num.toFixed(2).replace('.', ','));
	}
});  

$('#edit-valor').on('keydown', function(e) {
	if (e.which === 13 || e.which === 9) {
	  e.preventDefault(); // Previne o comportamento padrão (como inserir nova linha ou mover o foco automaticamente)
	  $('#btn-salvar-edit').focus();
	}
});

$('#modal-edit-item').on('keydown', function(e) {
	if (e.which === 27) { // ESC
	  e.preventDefault();
	  $(this).modal('hide');
	  // Depois de fechar o modal, dá um pequeno delay para garantir que ele fechou e então foca o campo de pesquisa
	  setTimeout(function() {
		$('#kt_select2_1').select2('open').focus();
	  }, 150);
	}
});  

function subtraiItem(id){
	let temp = [];
	let soma = 0
	ITENS.map((v) => {
		if(v.cont != id){
			temp.push(v)
			soma += parseFloat(v.valor.replace(',','.'))*(v.quantidade.replace(',','.'));
		}else{
			let q = parseFloat(v.quantidade.replace(",", "."))
			if(q > 1){
				v.quantidade = (parseFloat(v.quantidade) - 1) + "";
				soma += parseFloat(v.valor.replace(',','.')*v.quantidade.replace(',','.'));
				temp.push(v)
			}
		}
	});
	TOTAL = soma
	if(PDV_VALOR_RECEBIDO == 1){
		// $('#valor_recebido').val(TOTAL)
		$('#valor_recebido').val(TOTAL.toFixed(2).replace(".", ","))
	}
	ITENS = temp
	let t = montaTabela();
	atualizaTotal();
	$('#body').html(t);
	vendaAlterada();
}

$('#click-client').click(() => {

	$('#inp-valor_cashback').attr('disabled', 1)
	//$('#kt_select2_3').val('null').change()
	$('#modal-cliente').modal('show')
	$('.valor_cashback').text('R$ ' + convertFloatToMoeda(0))
	$('.info_cash_back').text('')
	$('#inp-valor_cashback').val('')
})

function selecionarCliente(){
	let cliente = $('#kt_select2_3').val();
	CLIENTES.map((c) => {
		if(c.id == cliente){
			CLIENTE = c

		}
	})

	let valor_cashback = $('#inp-valor_cashback').val()
	if(valor_cashback){
		valor_cashback = convertMoedaToFloat(valor_cashback)
		DESCONTO = valor_cashback
		$('#valor_desconto').text(convertFloatToMoeda(DESCONTO))
	}

	atualizaTotal()
	$('#conta_credito-btn').removeClass('disabled')
	$('#modal-cliente').modal('hide')
}

var cashBackCliente = null
var valorCashBack = 0
$('#kt_select2_3').change(() => {
	console.clear()
	if($('#kt_select2_3').val() != 'null'){
		$.get(path+'clientes/findOne/'+$('#kt_select2_3').val())
		.done((success) => {
			// console.log(success)
			$('.valor_cashback').text('R$ ' + convertFloatToMoeda(success.valor_cash_back))
			if(success.config){
				valorCashBack = success.valor_cash_back
				cashBackCliente = success.config
				$('.info_cash_back').text('*percentual de cashback para uso ' + success.config.percentual_maximo_venda + '%')
			}

			if(success.valor_cash_back > 0 && TOTAL > 0){
				$('#inp-valor_cashback').removeAttr('disabled')
			}

		})
		.fail((err) => {
			console.log(err)
		})
	}
})

$('#inp-valor_cashback').blur(() => {
	validaCashBack()
})

function validaCashBack(){
	let valor_setado = $('#inp-valor_cashback').val()
	valor_setado = valor_setado.replace(",", ".")
	valor_setado = parseFloat(valor_setado)

	console.log("valor_setado", valor_setado)
	if(cashBackCliente){
		let percentual_maximo_venda = cashBackCliente.percentual_maximo_venda
		let valor_maximo = TOTAL * (percentual_maximo_venda/100)
		console.log("valor_maximo", valor_maximo)
		console.log("percentual_maximo_venda", percentual_maximo_venda)

		if(valor_setado > valor_maximo){
			swal("Erro", "Valor máximo permitido R$ " + convertFloatToMoeda(valor_maximo), "warning")
			$('#inp-valor_cashback').val('')
		}else if(valor_setado > valorCashBack){
			swal("Erro", "Valor ultrapassou R$ " + convertFloatToMoeda(valorCashBack), "warning")
			$('#inp-valor_cashback').val('')
		}else{

		}
	}
}


function deleteItem(id){

	let temp = [];
	let soma = 0
	ITENS.map((v) => {
		if(v.cont != id){
			temp.push(v)
			soma += parseFloat(v.valor.replace(',','.'))*(v.quantidade.replace(',','.'));
		}
	});
	setTimeout(() => {
		TOTAL = soma
		if(PDV_VALOR_RECEBIDO == 1){
			$('#valor_recebido').val(TOTAL.toFixed(2).replace(".", ","))
		}
		ITENS = temp
		let t = montaTabela();
		atualizaTotal();
		$('#body').html(t);
	}, 50)

}

$(document).on("click", ".btn-delete-pass", function (e) {
	let id = $(this).data('id')
	$.get(path+'configNF/verifica-senha-acesso')
	.done((success) => {
		if(success == 'sim'){
			swal({
				title: 'Você está certo?',
				text: 'Informe a senha para remover!',
				content: {
					element: "input",
					attributes: {
						placeholder: "Senha",
						type: "password",
					},
				},
				button: {
					text: "Remover!",
					closeModal: false,
					type: 'success'
				},
			}).then(v => {
				if(v.length > 0){
					$.get(path+'configNF/verificaSenha', {senha: v})
					.done((success) => {
						swal.close()
						deleteItem(id)
						setTimeout(function(){
							if ($('#PDV_VALOR_RECEBIDO').val() == "1") {
								var totalVendaStr = $('#total-venda').text().trim();
								totalVendaStr = totalVendaStr.replace("R$", "").trim();
								$('#valor_recebido').val(totalVendaStr).trigger('change');
							}
							$("#kt_select2_1").select2("open");
						}, 150);
						swal("Sucesso", "Item removido", "success")
					})
					.fail((err) => {
						swal("Erro", "Senha incorreta", "error")
						.then(() => {

						})
					})
				}else{
					location.reload()
				}
			})
		}

	})
	.fail((err) => {
		swal({
			title: "Você está certo?",
			text:
			"Deseja remover este item!",
			icon: "warning",
			buttons: true,
			buttons: ["Cancelar", "Excluir"],
			dangerMode: true
		}).then(isConfirm => {
			if (isConfirm) {
				deleteItem(id)
				setTimeout(function(){
					if ($('#PDV_VALOR_RECEBIDO').val() == "1") {
						var totalVendaStr = $('#total-venda').text().trim();
						totalVendaStr = totalVendaStr.replace("R$", "").trim();
						$('#valor_recebido').val(totalVendaStr).trigger('change');
					}
					$("#kt_select2_1").select2("open");
				}, 150);
				swal("Sucesso", "Item removido", "success")

			} else {
				swal("Este item está salvo!");
			}
		});
	})

});

function verificaProdutoInclusoAtalho(id, call){
	let cont = 0;
	ITENS.map((rs) => {
		if(id == rs.cont){
			cont += parseFloat(rs.quantidade);
		}
	})
	call(cont);
}

function incrementaItem(id){
	let temp = [];
	let soma = 0
	console.clear()
	
	ITENS.map((v) => {
		
		if(v.cont != id){
			temp.push(v)
			soma += parseFloat(v.valor.replace(',','.'))*parseFloat(v.quantidade.replace(",", "."));
		}else{
			let prod = PRODUTOS.filter((p) => { return p.id == v.id})
			prod = prod[0]
			quantidade = (parseFloat(v.quantidade))

			verificaProdutoInclusoAtalho(id, (call) => {
				if(prod.gerenciar_estoque == 1 && (quantidade + 1 > parseFloat(prod.estoqueAtual))){
					swal("Erro", 'O estoque atual deste produto é de ' + prod.estoqueAtual, "warning")
					temp.push(v)
					soma += parseFloat(v.valor.replace(',','.'))*(v.quantidade);
				}else{
					v.quantidade = (parseFloat(call)+1) + "";
					soma += parseFloat(v.valor.replace(',','.')) * parseFloat(v.quantidade.replace(",", "."));
					temp.push(v)
				}
			})
		}
	});

	TOTAL = soma
	ITENS = temp
	if(PDV_VALOR_RECEBIDO == 1){
		// $('#valor_recebido').val((TOTAL))
		$('#valor_recebido').val(TOTAL.toFixed(2).replace(".", ","))
	}
	let t = montaTabela();
	atualizaTotal();
	$('#body').html(t);

	vendaAlterada();
}

function limparCamposFormProd(){
	$('#autocomplete-produto').val('');
	$('#quantidade').val('1');
	$('#valor_item').val(parseFloat(0).toFixed(casas_decimais));
}

function verificaProdutoIncluso(call){
	let cont = 0;
	ITENS.map((rs) => {
		if(PRODUTO.id == rs.id){
			cont += parseFloat(rs.quantidade);
		}
	})
	call(cont);
}

function getProdutoCodBarras(cod, data){
	let tamanho = ITENS.length;

	$.ajax
	({
		type: 'GET',
		url: path + 'produtos/getProdutoCodBarras/'+cod,
		dataType: 'json',
		success: function(e){
			data(e)
			if(e){

				console.clear()
				console.log(e)
				PRODUTO = e;
				PRODUTO.nome = PRODUTO.nome+' '+PRODUTO.str_grade;

				$('#nome-produto').html(e.nome);
				let lista_id = $('#lista_id').val()
				if(lista_id > 0){
					e.lista_preco.map((l) => {
						if(lista_id == l.lista_id){
							$('#valor_item').val(parseFloat(l.valor).toFixed(casas_decimais));
						}
					})
				}else{
					$('#valor_item').val(parseFloat(e.valor_venda).toFixed(casas_decimais));
				}

				$('#quantidade').val(QUANTIDADE);
			}else{
				if(cod.length > 10){
					//validar pelo cod balança

					let id = parseInt(cod.substring(1, DIGITOBALANCA));

					$.get(path+'produtos/getProdutoCodigoReferencia/'+id)
					.done((res) => {

						let valor = cod.substring(7,12);

						let temp = valor.substring(0,3) + '.' +valor.substring(3,5);
						valor = parseFloat(temp)
						

						PRODUTO = res;

						$('#nome-produto').html(PRODUTO.nome);
						let quantidade = QUANTIDADE;
						if(PRODUTO.unidade_venda == 'KG'){
							if(TIPOUNIDADEBALANCA == 1){
								let valor_venda = PRODUTO.valor_venda;
								quantidade = valor/valor_venda;
								quantidade = quantidade.toFixed(3);
								valor = valor_venda;
							}else{

								quantidade = valor/10
								quantidade = quantidade.toFixed(2)
								valor = parseFloat(PRODUTO.valor_venda).toFixed(casas_decimais);
							}
						}
						$('#valor_item').val(valor);
						$('#quantidade').val(quantidade);
						let tamanho2 = ITENS.length;
						if(tamanho2 == tamanho){
							
							$('#adicionar-item').trigger('click');
						}

					})
					.fail((err) => {
						// alert('Produto nao encontrado!')
						swal("Erro", 'Produto nao encontrado!!', "warning")

						$('#autocomplete-produto').val('')

					})

				}
			}

		}, error: function(e){
			
		}

	});
}

function verificaCaixa(data){
	$.ajax
	({
		type: 'GET',
		url: path + 'aberturaCaixa/verificaHoje',
		dataType: 'json',
		success: function(e){
			
			data(e)

		}, error: function(e){
			
		}

	});
}

function abrirCaixa(){
	let token = $('#_token').val();
	let valor = $('#valor').val();
	let filial_id = $('#filial_id') ? $('#filial_id').val() : null;
	let conta_id = null
	if($('#conta_id').length){
		conta_id = $('#conta_id').val()
		if(!conta_id){
			swal("Alerta", "Selecione uma conta", "warning")
			return
		}
	}

	if(filial_id == -1){
		filial_id = null
	}

	valor = valor.length >= 0 ? valor.replace(",", ".") : 0;
	if(parseFloat(valor) >= 0){
		$.ajax
		({
			type: 'POST',
			url: path + 'aberturaCaixa/abrir',
			dataType: 'json',
			data: {
				valor: $('#valor').val(),
				_token: token,
				filial_id: filial_id,
				conta_id: conta_id
			},
			success: function(e){
				caixaAberto = true;
				$('#modal1').modal('hide');
				swal("Sucesso", "Caixa aberto", "success").then(() => {
					location.reload()
				})

			}, error: function(e){
				$('#modal1').modal('hide');
				swal("Erro", "Erro ao abrir caixa", "error")
				
			}

		});
	}else{
		// alert('Insira um valor válido')
		swal("Erro", 'Insira um valor válido', "warning")

	}

}

function sangriaCaixa(){
	let token = $('#_token').val();
	
	if(sangriaRequest == false){
		sangriaRequest = true

		let conta_id = null
		if($('#conta_sagria_id').length){
			conta_id = $('#conta_sagria_id').val()
			if(!conta_id){
				swal("Alerta", "Selecione uma conta", "warning")
				return
			}
		}

		$.ajax
		({
			type: 'POST',
			url: path + 'sangriaCaixa/save',
			dataType: 'json',
			data: {
				valor: $('#valor_sangria').val(),
				observacao: $('#obs_sangria').val(),
				_token: token,
				conta_id: conta_id
			},
			success: function(e){
				sangriaRequest = false
				caixaAberto = true;
				$('#modal2').modal('hide');
				$('#valor_sangria').val('');
				swal("Sucesso", "Sangria realizada!", "success")
				.then(() => {
					window.open(path+'sangriaCaixa/imprimir/'+e.id)
				})

				$('#valor_sangria').val('')
				$('#obs_sangria').val('')

			}, error: function(e){
				sangriaRequest = false
				$('#valor_sangria').val('')
				$('#obs_sangria').val('')
				
				try{
					swal("Erro", e.responseJSON, "error")
					.then(() => {
						$('#modal2').modal('hide');
					})
				}catch{
					swal("Erro", "Erro ao realizar sangria!", "error")
					.then(() => {
						$('#modal2').modal('hide');
					})
				}

			}

		});
	}
}

function suprimentoCaixa(){
	let token = $('#_token').val();
	let conta_id = null
	if($('#conta_suprimento_id').length){
		conta_id = $('#conta_suprimento_id').val()
		if(!conta_id){
			swal("Alerta", "Selecione uma conta", "warning")
			return
		}
	}
	if(sangriaRequest == false){
		sangriaRequest = true
		$.ajax
		({
			type: 'POST',
			url: path + 'suprimentoCaixa/save',
			dataType: 'json',
			data: {
				valor: $('#valor_suprimento').val(),
				tipo: $('#tipo_suprimento').val(),
				obs: $('#obs_suprimento').val(),
				_token: token,
				conta_id: conta_id
			},
			success: function(e){
				sangriaRequest = false
				$('#modal-supri').modal('hide');
				$('#valor_suprimento').val('');
				$('#obs_suprimento').val('');
				swal("Sucesso", "suprimento realizado!", "success")
				.then(() => {
					window.open(path+'suprimentoCaixa/imprimir/'+e.id)
				})

			}, error: function(e){
				sangriaRequest = false
				$('#valor_suprimento').val('');
				$('#obs_suprimento').val('');
				
				swal("Erro", "Erro ao realizar suprimento de caixa!", "error")

			}

		});
	}
}

function getSangriaDiaria(data){
	$.ajax
	({
		type: 'GET',
		url: path + 'sangriaCaixa/diaria',
		dataType: 'json',
		success: function(e){
			data(e)

		}, error: function(e){
			
		}

	});
}

function getSuprimentoDiario(data){
	$.ajax
	({
		type: 'GET',
		url: path + 'suprimentoCaixa/diaria',
		dataType: 'json',
		success: function(e){
			data(e)

		}, error: function(e){
			
		}

	});
}

function getAberturaDiaria(data){

	$.ajax
	({
		type: 'GET',
		url: path + 'aberturaCaixa/verificaHoje',
		dataType: 'json',
		success: function(e){
			
			data(e)

		}, error: function(e){
			
		}

	});
}

function getVendaDiaria(data){
	$.ajax
	({
		type: 'GET',
		url: path + 'vendasCaixa/diaria',
		dataType: 'json',
		success: function(e){
			data(e)

		}, error: function(e){
			
		}

	});
}

function fluxoDiario(){
	$('#preloader1').css('display', 'block');
	getSangriaDiaria((sangrias) => {
		getSuprimentoDiario((suprimentos) => {

			let elem = "";
			let totalSangria = 0;
			let totalSuprimento = 0;
			sangrias.map((v) => {

				elem += "<p> Horario: "
				elem += "<strong>" + v.data_registro.substring(10, 16) + "</strong>, Valor: "
				elem += "<strong> R$ " + formatReal(v.valor) + "</strong>, Usuario: "
				elem += "<strong class='text-info'>" + v.nome_usuario + "</strong>, Obs: "
				elem += "<strong class='text-info'>" + v.observacao + "</strong>"

				elem += "</p>";
				totalSangria += parseFloat(v.valor);
			})

			elem += "<h6>Total: <strong class='text-danger'>" + formatReal(totalSangria) + "</strong></h6>";
			elem += "<hr>"
			$('#fluxo_sangrias').html(elem)
			elem = ""
			suprimentos.map((v) => {

				elem += "<p> Horario: "
				elem += "<strong>" + v.created_at.substring(10, 16) + "</strong>, Valor: "
				elem += "<strong> R$ " + formatReal(v.valor) + "</strong>, Usuario: "
				elem += "<strong class='text-info'>" + v.nome_usuario + "</strong>, Obs: "
				elem += "<strong class='text-info'>" + v.observacao + "</strong>"
				elem += "</p>";
				totalSuprimento += parseFloat(v.valor);
			})
			elem += "<h6>Total: <strong class='text-danger'>" + formatReal(totalSuprimento) + "</strong></h6>";
			elem += "<hr>"

			$('#fluxo_suprimentos').html(elem)

			getAberturaDiaria((abertura) => {
				abertura = abertura.replace(",", ".")
				elem = "<p> Valor: ";
				elem += "<strong class='text-danger'>R$ "+formatReal(abertura)+"</strong>";
				elem += "</p>";
				elem += "<hr>"

				$('#fluxo_abertura_caixa').html(elem);
				getVendaDiaria((vendas) => {

					elem = "";
					let totalVendas = 0;
					vendas.map((v) => {
						
						elem += "<p> Horário: "
						elem += "<strong>" + v.data_registro.substring(11, 16) + "</strong>, Valor: "
						elem += "<strong>" + formatReal(parseFloat(v.valor_total)) + "</strong>, Tipo Pagamento: "
						elem += "<strong>" + v.tipo_pagamento + "</strong>"
						elem += "</p>";
						totalVendas += parseFloat(parseFloat(v.valor_total));
					})
					elem += "<h6>Total: <strong class='text-primary'>" + formatReal(totalVendas) + "</strong></h6>";
					elem += "<hr>";
					$('#fluxo_vendas').html(elem);
					$('#total_caixa').html(formatReal((totalVendas+parseFloat(abertura)) - totalSangria + totalSuprimento));

					$('#preloader1').css('display', 'none');
				});
			})
		})
	})
	if(caixaAberto){
		$('#modal3').modal('open');
	}else{

		// var $toastContent = $('<span>Por favor abra o caixa!</span>').add($('<button class="btn-flat toast-action">OK</button>'));
		// Materialize.toast($toastContent, 5000);
		swal('Erro', 'Por favor abra o caixa!', 'error')
		.then(() => {
			location.reload();
		})
	}
}

function esconderTodasMoedas(){
	$('.50_reais').css('display', 'none');
	$('.20_reais').css('display', 'none');
	$('.10_reais').css('display', 'none');
	$('.5_reais').css('display', 'none');
	$('.2_reais').css('display', 'none');
	$('.1_real').css('display', 'none');
	$('.50_centavo').css('display', 'none');
	$('.25_centavo').css('display', 'none');
	$('.50_centavo').css('display', 'none');
	$('.5_centavo').css('display', 'none');
}

$('#valor_recebido').on('keyup', (event) => {
	esconderTodasMoedas();
	PAGMULTI = []
	let t = TOTAL;
	let v = $('#valor_recebido').val();
	v = v.replace(",", ".");

	let troco = v - (TOTAL - DESCONTO + VALORACRESCIMO);
	if(troco > 0){
		$('#valor-troco').html(formatReal(troco))
	}else{
		$('#valor-troco').html('R$ 0,00')
	}

	if(ITENS.length > 0 && (parseFloat(v) >= (TOTAL + VALORBAIRRO + VALORACRESCIMO - DESCONTO))){
		$('#salvar-venda').removeAttr('disabled');
		$('#finalizar-venda').removeAttr('disabled');
		$('#finalizar-rascunho').removeAttr('disabled');
		$('#finalizar-consignado').removeAttr('disabled');
	}else{
		$('#salvar-venda').attr('disabled', true);
		$('#finalizar-venda').attr('disabled', true);
		$('#finalizar-rascunho').attr('disabled', true);
		$('#finalizar-consignado').attr('disabled', true);
	}

	if(v.length > 0 && parseFloat(v) > TOTAL && TOTAL > 0){
		v = parseFloat(v);

		if (event.keyCode === 13) {

			let troco = v - (t - DESCONTO + VALORACRESCIMO);
			$("#valor_troco").html(formatReal(troco))
			$('#modal4').modal('show');

			let resto = troco;
			notas = [];

			if(parseInt(troco / 50) > 0 && resto > 0){
				numeroNotas = parseInt(resto/50);
				resto = troco % 50;
				$('#qtd_50_reais').html(' X'+numeroNotas);
				$('.50_reais').css('display', 'inline-block');

			}
			if(parseInt(resto / 20) > 0){
				numeroNotas = parseInt(resto/20);
				$('#qtd_20_reais').html(' X'+numeroNotas);
				resto = resto%(20*numeroNotas);
				$('.20_reais').css('display', 'inline-block');

			}
			if(parseInt(resto / 10) > 0){
				numeroNotas = parseInt(resto/10);
				$('#qtd_10_reais').html(' X'+numeroNotas);
				resto = resto%(10*numeroNotas);
				$('.10_reais').css('display', 'inline-block');

			}
			if(parseInt(resto / 5) > 0){
				numeroNotas = parseInt(resto/5);
				$('#qtd_5_reais').html(' X'+numeroNotas);
				resto = duasCasas(resto%(5*numeroNotas));
				$('.5_reais').css('display', 'inline-block');

			}
			if(parseInt(resto / 2) > 0){
				numeroNotas = parseInt(resto/2);
				$('#qtd_2_reais').html(' X'+numeroNotas);
				resto = duasCasas(resto%(2*numeroNotas));
				$('.2_reais').css('display', 'inline-block');

			}

			if(parseInt(resto / 1) > 0){
				numeroNotas = parseInt(resto/1);
				$('#qtd_1_real').html(' X'+numeroNotas);
				resto = duasCasas(resto%(1*numeroNotas));
				$('.1_real').css('display', 'inline-block');

			}

			if(parseInt(resto / 0.5) > 0){
				numeroNotas = parseInt(resto/0.5);
				$('#qtd_50_centavos').html(' X'+numeroNotas);
				resto = duasCasas(resto%(0.5*numeroNotas));
				$('.50_centavo').css('display', 'inline-block');

			}

			if(parseInt(resto / 0.25) > 0){
				numeroNotas = parseInt(resto/0.25);
				$('#qtd_25_centavos').html(' X'+numeroNotas);
				resto = duasCasas(resto%(0.25*numeroNotas));
				$('.25_centavo').css('display', 'inline-block');

			}

			if(parseInt(resto / 0.10) > 0){
				numeroNotas = parseInt(resto/0.10);
				$('#qtd_10_centavos').html(' X'+numeroNotas);
				resto = duasCasas(resto%(0.10*numeroNotas));
				$('.10_centavo').css('display', 'inline-block');

			}


			if(parseInt(resto / 0.05) > 0){
				numeroNotas = parseInt(resto/0.05);
				$('#qtd_5_centavos').html(' X'+numeroNotas);
				resto = resto%(0.05*numeroNotas);
				$('.5_centavo').css('display', 'inline-block');

			}

		}
	}
})

function duasCasas(valor){
	return parseFloat(valor.toFixed(2));
}

$('#autocomplete-produto').on('keyup', () => {
	let val = $('#autocomplete-produto').val();
	if($.isNumeric(val) && val.length > 6){
		getProdutoCodBarras(val, (data) => {
			setTimeout(() => {
				addItem();

			}, 400)
		})
	}
})

function verificaCliente(){

	if(CLIENTE == null){

		$('#modal-venda').modal('hide');
		$('#modal-cpf-nota').modal('show');
		$('#modal-cpf-nota').on('shown.bs.modal', function () {
			$('#cpf').focus()
		})
	}
	else{
		finalizarVenda('fiscal')
	}
}

function validaCpf(){

	if(CLIENTE != null) return true;

	let strCPF = $('#cpf').val();
	let nome = $('#nome').val();
	if(strCPF.length == 0) return true;

	// if(nome == '' || nome == null || nome.length == 0) return false;

	strCPF = strCPF.replace(".", "");
	strCPF = strCPF.replace(".", "");
	strCPF = strCPF.replace("-", "");
	if(strCPF.length == 11){
		var Soma;
		var Resto;
		Soma = 0;
		if (strCPF == "00000000000") return false;

		if (strCPF == "11111111111" ||
			strCPF == "22222222222" ||
			strCPF == "33333333333" ||
			strCPF == "44444444444" ||
			strCPF == "55555555555" ||
			strCPF == "66666666666" ||
			strCPF == "77777777777" ||
			strCPF == "88888888888" ||
			strCPF == "99999999999")
			return false;

		for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
			Resto = (Soma * 10) % 11;

		if ((Resto == 10) || (Resto == 11))  Resto = 0;
		if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;;

		Soma = 0;
		for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
			Resto = (Soma * 10) % 11;

		if ((Resto == 10) || (Resto == 11))  Resto = 0;
		if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;;

		return true;
	}else{
		let cnpj = strCPF
		cnpj = cnpj.replace(/[^\d]+/g,'');

		if (cnpj.length != 14)
			return false;

		if (cnpj == "00000000000000" ||
			cnpj == "11111111111111" ||
			cnpj == "22222222222222" ||
			cnpj == "33333333333333" ||
			cnpj == "44444444444444" ||
			cnpj == "55555555555555" ||
			cnpj == "66666666666666" ||
			cnpj == "77777777777777" ||
			cnpj == "88888888888888" ||
			cnpj == "99999999999999")
			return false;


		tamanho = cnpj.length - 2
		numeros = cnpj.substring(0,tamanho);
		digitos = cnpj.substring(tamanho);
		soma = 0;
		pos = tamanho - 7;
		for (i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2)
				pos = 9;
		}
		resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		if (resultado != digitos.charAt(0))
			return false;

		tamanho = tamanho + 1;
		numeros = cnpj.substring(0,tamanho);
		soma = 0;
		pos = tamanho - 7;
		for (i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2)
				pos = 9;
		}
		resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		if (resultado != digitos.charAt(1))
			return false;

		return true;
	}
}

$('#tipo_pagamento').change(() => {
	let tipo = $('#tipo_pagamento').val();
	if(tipo != ''){
		if(CLIENTE == null && (tipo == '02' || tipo == '05' || tipo == '06' || tipo == '14' || tipo == '15')){
			swal("Alerta", "É necessário informar o cliente para esse tipo de pagamento!", "warning")
			$('#tipo_pagamento').val('').change()
		}else{
			let allowed_types = ['02', '05','06', '14', '15', '99'];

			// if(allowed_types.includes(tipo)){
			// 	$('#observacao_pagamento').fadeIn();
			// 	$('.vencimento_pagamento').fadeIn();
			// }else{
			// 	$('#observacao_pagamento').fadeOut();
			// 	$('.vencimento_pagamento').fadeOut();
			// }
		}
		if(tipo == '99'){
			$('#modal-pag-outros').modal('show')
		}
	}else{
		// $('#observacao_pagamento').fadeOut();
		// $('.vencimento_pagamento').fadeOut();
	}
})

// $('#tipo_pagamento_2').change(() => {
// 	let tipo = $('#tipo_pagamento_2').val();
// 	if(tipo == '06'){
// 		if(CLIENTE == null){
// 			swal("Alerta", "Informe o cliente!", "warning")
// 			$('#tipo_pagamento_2').val('01').change()
// 		}
// 	}
// })

// $('#tipo_pagamento_3').change(() => {
// 	let tipo = $('#tipo_pagamento_3').val();
// 	if(tipo == '06'){
// 		if(CLIENTE == null){
// 			swal("Alerta", "Informe o cliente!", "warning")
// 			$('#tipo_pagamento_3').val('01').change()
// 		}
// 	}
// })

$('#tipo-pagamento').change(() => {
	$('#valor_recebido').val('');
	PAGMULTI = []
	let tipo = $('#tipo-pagamento').val();

	if(tipo == '06'){
		if(CLIENTE == null){
			swal("Alerta", "Informe o cliente!", "warning")
			$('#tipo-pagamento').val('--').change()
		}
	}

	if(tipo == '03' || tipo == '04'){
		if(exibe_modal_cartoes == 1){
			$('#modal-cartao').modal('show')
			if(tipo == '03'){
				$('#modal-cartao .tipo-cartao').text(' - Cartão de Crédito')
			}else{
				$('#modal-cartao .tipo-cartao').text(' - Cartão de Débito')
			}
		}
	}

	if(tipo == '99'){
		$('#modal-pag-outros').modal('show')
	}

	if(tipo == '01'){
		$('#valor_recebido').removeAttr('disabled');
		$('#finalizar-venda').attr('disabled', true);
		$('#finalizar-rascunho').attr('disabled', true);
		$('#salvar-venda').attr('disabled', true);
		$('#finalizar-consignado').attr('disabled', true);

	}else{
		$('#valor_recebido').attr('disabled', 'true');
		$('#finalizar-venda').removeAttr('disabled');
		$('#finalizar-rascunho').removeAttr('disabled');
		$('#salvar-venda').removeAttr('disabled');
		$('#finalizar-consignado').removeAttr('disabled');
	}

	$('#codBarras').focus();
})

function salvarConsignado(){
	if(CLIENTE == null){
		swal("Atenção", "Selecione o cliente", "error")
	}else{
		swal({
			title: "Alerta",
			text: "Deseja salvar em consignado?",
			icon: "warning",
			buttons: ["Não", 'Sim'],
			dangerMode: true,
		}).then((v) => {
			if (v) {
				finalizarVenda('nao_fiscal', 0, 1)
			} else {
			}
		});
	}
}

function salvarRascuho(){
	verificaPrecoAlterado(function(){
		swal({
			title: "Alerta",
			text: "Deseja salvar como rascunho?",
			icon: "warning",
			buttons: ["Não", 'Sim'],
			dangerMode: true,
		}).then((v) => {
			if (v) {
				finalizarVenda('nao_fiscal', 1)
			} else {
			}
		});
	});
}

function salvarVenda(){
	verificaPrecoAlterado(function(){
		swal({
			title: "Alerta",
			text: "Deseja salvar as alterações?",
			icon: "warning",
			buttons: ["Não", 'Sim'],
			dangerMode: true,
		}).then((v) => {
			if (v) {
				salvarVendaPdv('fiscal', 0)
			} else {
			}
		});
	});
}


var cupomImpressao = parseInt(document.getElementById("PDV_CUPOM_IMPRESSAO").value, 10) || 3;
console.log("Cupom Impressao:", cupomImpressao);

var ENVIANDO = false
function finalizarVenda(acao, rascunho = 0, consignado = 0) {

	if($('#tipo-pagamento').val() == '--' && !is_preVenda && !is_troca){
		swal("Alerta", "Informe um tipo de pagamento", "warning");
		return false;
	}

	$('#btn_nao_fiscal').attr('disabled')

	if(ENVIANDO == false){
		ENVIANDO = true
		let validCpf = validaCpf();
		if(validCpf == true || acao != 'fiscal'){

			let valorRecebido = parseFloat($('#valor_recebido').val().replace(",", "."));
			let troco = 0;
			if(valorRecebido > (TOTAL + VALORACRESCIMO + VALORBAIRRO - DESCONTO)){
				troco = valorRecebido - (TOTAL + VALORACRESCIMO + VALORBAIRRO - DESCONTO);
			}

			let desconto = DESCONTO;
			let obs = $('#obs').val();

			let valor_cashback = $('#inp-valor_cashback').val()
			if(valor_cashback){
				valor_cashback = convertMoedaToFloat(valor_cashback)
			}else{
				valor_cashback = 0
			}

			let js = {
				id: VENDA != null ? VENDA.id : 0,
				itens: ITENS,
				pag_multi: PAGMULTI,
				cliente: CLIENTE != null ? CLIENTE.id : null,
				valor_total: TOTAL,
				acrescimo: VALORBAIRRO + VALORACRESCIMO,
				troco: troco,
				tipo_pagamento: $('#tipo-pagamento').val(),
				os_id: $('#os_id') ? $('#os_id').val() : 0,
				dfe_id: $('#dfe_id') ? $('#dfe_id').val() : 0,
				forma_pagamento: '',
				dinheiro_recebido: valorRecebido ? valorRecebido : TOTAL,
				acao: acao,
				nome: $('#nome-cpf').val() ? $('#nome-cpf').val() : "",
				cpf: $('#cpf').val(),
				delivery_id: $('#delivery_id').val(),
				pedido_local: $('#pedidoLocal').val() ? true : false,
				codigo_comanda: COMANDA,
				pedido_ifood: PEDIDOIFOOD,
				desconto: desconto ? desconto : 0,
				valor_cashback: valor_cashback,
				nao_permitir_credito: $('#inp-nao_permitir_credito').is(':checked') ? '1' : 0,
				observacao: obs,
				tipo_pagamento_1: TIPOPAG1,
				tipo_pagamento_2: TIPOPAG2,
				tipo_pagamento_3: TIPOPAG3,
				valor_pagamento_1: VALORPAG1,
				valor_pagamento_2: VALORPAG2,
				valor_pagamento_3: VALORPAG3,
				obs_pagamento_1: OBSPAG1,
				obs_pagamento_2: OBSPAG2,
				obs_pagamento_3: OBSPAG3,
				credito_troca: 0,
				consignado: consignado,
				funcionario_id: null,
				rascunho: rascunho,
				vencimento_pagamento_1: VENCPAG1,
				vencimento_pagamento_2: VENCPAG2,
				vencimento_pagamento_3: VENCPAG3,
				agendamento_id: $('#agendamento_id').val(),
				filial_id: $('#filial').val() == "null" ? null : $('#filial').val(),
				bandeira_cartao: $('#bandeira_cartao').val() ? $('#bandeira_cartao').val() : '99',
				cAut_cartao: $('#cAut_cartao').val() ? $('#cAut_cartao').val() : '',
				cnpj_cartao: $('#cnpj_cartao').val() ? $('#cnpj_cartao').val() : '',
				descricao_pag_outros: $('#descricao_pag_outros').val() ? $('#descricao_pag_outros').val() : '',
				senha_alterar_preco: SENHA_ALTERAR_PRECO ? SENHA_ALTERAR_PRECO : '',
				venda_alvo: VENDA != null ? VENDA.id : null,
				prevenda_nivel: PREVENDA_NIVEL,
				vendedor_id: VENDEDOR_ID,
				assessor_id: $('#select-assessor').val(),
				isPrevenda: VENDA?.isPrevenda ? true : false,
				produtosRemovidos: REMOVIDO_NA_TROCA,
				produtosAdicionados: ADICIONADO_NA_TROCA,
			}

			console.log(js)

			let token = $('#_token').val();
			$('.modal-loading').css('display', 'block')
			if(acao != 'credito'){
				let url = '';
				if(is_troca){
					url = path + 'vendasCaixa/save/troca';
					$('#btn-finalizarTrocaFiscal').addClass('disabled')
				}else if(is_preVenda){
					url = path + 'vendasCaixa/save/prevenda';
				}else{
					url = path + 'vendasCaixa/save';
				}

				$('#btn_nao_fiscal').addClass('disabled')
				$.ajax
				({
					type: 'POST',
					url: url,
					dataType: 'json',
					data: {
						venda: js,
						_token: token
					},
					success: function(e){
						console.log(e)
						audioSuccess()
						let imprimeTroca = 0;
						
						$('.modal-loading').css('display', 'none')

						if(acao == 'fiscal'){
							$('#preloader2').css('display', 'block');
							$('#preloader9').css('display', 'block');
							emitirNFCe(e.id);
						}else if(is_preVenda){
							swal("Sucesso", "Pré venda salva", "success")
							.then((v) => {
								if(PREVENDA_NIVEL == 1){
									window.open(path + 'nfce/imprimirRascunhoPrevenda/'+e.id, '_blank');
								}else{
									window.open(path + 'nfce/imprimirPreVenda/'+e.id, '_blank');
								}
								location.href=path+'frenteCaixa/prevenda';
							})
						} else {
							// Verifica o valor de cupomImpressao
							if (cupomImpressao == 1 || cupomImpressao == 2) {
								// Para cupomImpressao 1 e 2, chama o arquivo cupom_direto
								if (cupomImpressao == 2) {
									// Opção 2: Direto mas pergunta antes – usa confirm()
									if (confirm("Deseja imprimir o comprovante?")) {
										window.open(path + 'nfce/cupom_direto/' + e.id, '_blank');
									} else {
										location.href = path + 'frenteCaixa';
										return;
									}
								} else {
									// Opção 1: Direto – Imprime sem perguntar
									window.open(path + 'nfce/cupom_direto/' + e.id, '_blank');
								}
								// Se não houver comissão do assessor e nenhum pagamento múltiplo, redireciona para frenteCaixa
								if (e.comissao_acessor == false && PAGMULTI.length == 0) {
									location.href = path + 'frenteCaixa';
									return;
								}
								return; // Interrompe a execução para evitar o swal abaixo
							} else if (cupomImpressao == 3) {
								// Opção 3: Fluxo atual - abre o PDF direto, sem swal.
								window.open(path + 'nfce/imprimirNaoFiscal/' + e.id, '_blank');
								
								// Se não houver comissão do assessor e nenhum pagamento múltiplo, redireciona para frenteCaixa
								if (e.comissao_acessor == false && PAGMULTI.length == 0) {
									location.href = path + 'frenteCaixa';
									return;
								}	  
									  // Aqui, pode incorporar o restante do fluxo (ticket, duplicatas, assessor) como no exemplo acima.
									  if (ATALHOS.imprimir_ticket_troca == 1) {
										  swal({
											  title: "Sucesso",
											  text: "Deseja imprimir o ticket de troca",
											  icon: "success",
											  buttons: ["Não", "Imprimir"],
											  dangerMode: true,
										  })
										  .then((ticketConfirma) => {
											  if (ticketConfirma) {
												  window.open(path + 'nfce/ticket-troca/' + e.id, '_blank');
												  if (e.comissao_acessor == false && PAGMULTI.length == 0) {
													  location.href = path + 'frenteCaixa';
												  }
											  } else {
												  if (e.comissao_acessor == false && PAGMULTI.length == 0) {
													  location.href = path + 'frenteCaixa';
												  }
											  }
											  imprimeTroca = 1;
											  if (is_troca) {
												  location.href = path + 'frenteCaixa/troca';
											  }
										  });
									  } else {
										  if (e.comissao_acessor == false && PAGMULTI.length == 0) {
											  location.href = path + 'frenteCaixa';
										  }
									  }
						
									  if (PAGMULTI.length > 0) {
										  swal({
											  title: "Sucesso",
											  text: "Deseja imprimir as duplicatas",
											  icon: "success",
											  buttons: ["Não", "Imprimir"],
											  dangerMode: true,
										  })
										  .then((dupConfirma) => {
											  if (dupConfirma) {
												  window.open(path + 'vendas/carne?id=' + e.id + '&tipo_venda=venda_caixas', '_blank');
												  location.href = path + 'frenteCaixa';
											  } else {
												  location.href = path + 'frenteCaixa';
											  }
										  });
									  }
							
									  if (e.comissao_acessor) {
										  swal({
											  title: "Sucesso",
											  text: "Deseja imprimir comprovante do assessor?",
											  icon: "success",
											  buttons: ["Não", "Imprimir"],
											  dangerMode: true,
										  })
										  .then((assessorConfirma) => {
											  if (assessorConfirma) {
												  window.open(path + 'nfce/imprimirComprovanteAssessor/' + e.id, '_blank');
												  location.href = path + 'frenteCaixa';
											  } else {
												  location.href = path + 'frenteCaixa';
											  }
										  });
									  } else {
										  if (PAGMULTI.length == 0 && imprimeTroca != 0) {
											  location.href = path + 'frenteCaixa';
										  }
									  }
									  
									  if (is_troca && ATALHOS.imprimir_ticket_troca == 0) {
										  location.href = path + 'frenteCaixa/troca';
									  }
									
							}
						}						

					}, error: function(e){
						$('.modal-loading').css('display', 'none')
						console.log(e)
						ENVIANDO = false
						$('#btn_nao_fiscal').removeAttr('disabled')
						$('#btn_nao_fiscal').removeClass('disabled')

						$('#preloader2').css('display', 'none');
						$('#preloader9').css('display', 'none');
						$('#modal-venda').modal('hide')
						swal("Ops!!", "Erro ao salvar venda!!", "error")

					}

				});
}else{

	if(CLIENTE == null){
		swal("Alerta", "Informe um cliente para conta crédito", "warning")
	}else{

		if(CLIENTE.limite_venda < parseFloat(CLIENTE.totalEmAberto) + TOTAL){
			swal({
				text: "Valor do limite de conta crédito ultrapassado, confirma a venda?!",
				title: 'Cuidado',
				icon: 'warning',
				buttons: ["Não", "Vender"],
			}).then(sim => {
				if (sim) {
					salvarCredito(js, token)
				}else{
					$('#preloader2').css('display', 'none');
					$('#preloader9').css('display', 'none');
					$('#modal-venda').modal('hide')
				}
			});

		}else{
			salvarCredito(js, token)
		}
	}
}

$('#kt_select2_3').val('null').change();
}else{
	swal('Erro', 'CPF/CNPJ Inválido!', 'error')
}

}
}

function salvarVendaPdv(acao, rascunho = 0, consignado = 0) {
	if($('#tipo-pagamento').val() == '--' && !is_preVenda && !is_troca){
		swal("Alerta", "Informe um tipo de pagamento", "warning");
		return false;
	}
	$('#btn_nao_fiscal').attr('disabled')
	if(ENVIANDO == false){
		ENVIANDO = true
		let validCpf = validaCpf();
		if(validCpf == true || acao != 'fiscal'){
			let valorRecebido = parseFloat($('#valor_recebido').val().replace(",", "."));
			let troco = 0;
			if(valorRecebido > (TOTAL + VALORACRESCIMO + VALORBAIRRO - DESCONTO)){
				troco = valorRecebido - (TOTAL + VALORACRESCIMO + VALORBAIRRO - DESCONTO);
			}
			let desconto = DESCONTO;
			let obs = $('#obs').val();
			let valor_cashback = $('#inp-valor_cashback').val()
			if(valor_cashback){
				valor_cashback = convertMoedaToFloat(valor_cashback)
			}else{
				valor_cashback = 0
			}
			let js = {
				id: VENDA != null ? VENDA.id : 0,
				itens: ITENS,
				pag_multi: PAGMULTI,
				cliente: CLIENTE != null ? CLIENTE.id : null,
				valor_total: TOTAL,
				acrescimo: VALORBAIRRO + VALORACRESCIMO,
				troco: troco,
				tipo_pagamento: $('#tipo-pagamento').val(),
				os_id: $('#os_id') ? $('#os_id').val() : 0,
				forma_pagamento: '',
				dinheiro_recebido: valorRecebido ? valorRecebido : TOTAL,
				acao: acao,
				nome: $('#nome-cpf').val() ? $('#nome-cpf').val() : "",
				cpf: $('#cpf').val(),
				delivery_id: $('#delivery_id').val(),
				pedido_local: $('#pedidoLocal').val() ? true : false,
				codigo_comanda: COMANDA,
				pedido_ifood: PEDIDOIFOOD,
				desconto: desconto ? desconto : 0,
				valor_cashback: valor_cashback,
				nao_permitir_credito: $('#inp-nao_permitir_credito').is(':checked') ? '1' : 0,
				observacao: obs,
				tipo_pagamento_1: TIPOPAG1,
				tipo_pagamento_2: TIPOPAG2,
				tipo_pagamento_3: TIPOPAG3,
				valor_pagamento_1: VALORPAG1,
				valor_pagamento_2: VALORPAG2,
				valor_pagamento_3: VALORPAG3,
				obs_pagamento_1: OBSPAG1,
				obs_pagamento_2: OBSPAG2,
				obs_pagamento_3: OBSPAG3,
				credito_troca: 0,
				consignado: consignado,
				funcionario_id: null,
				rascunho: rascunho,
				vencimento_pagamento_1: VENCPAG1,
				vencimento_pagamento_2: VENCPAG2,
				vencimento_pagamento_3: VENCPAG3,
				agendamento_id: $('#agendamento_id').val(),
				filial_id: $('#filial').val() == "null" ? null : $('#filial').val(),
				bandeira_cartao: $('#bandeira_cartao').val() ? $('#bandeira_cartao').val() : '99',
				cAut_cartao: $('#cAut_cartao').val() ? $('#cAut_cartao').val() : '',
				cnpj_cartao: $('#cnpj_cartao').val() ? $('#cnpj_cartao').val() : '',
				descricao_pag_outros: $('#descricao_pag_outros').val() ? $('#descricao_pag_outros').val() : '',
				senha_alterar_preco: SENHA_ALTERAR_PRECO ? SENHA_ALTERAR_PRECO : '',
				venda_alvo: VENDA != null ? VENDA.id : null,
				prevenda_nivel: PREVENDA_NIVEL,
				vendedor_id: VENDEDOR_ID,
				assessor_id: $('#select-assessor').val(),
				isPrevenda: VENDA?.isPrevenda ? true : false,
				produtosRemovidos: REMOVIDO_NA_TROCA,
				produtosAdicionados: ADICIONADO_NA_TROCA,
				isEdit: true,
			}
			console.log(js)
			let token = $('#_token').val();
			$('.modal-loading').css('display', 'block')
			if(acao != 'credito'){
				let url = '';
				if(is_troca){
					url = path + 'vendasCaixa/save/troca';
					$('#btn-finalizarTrocaFiscal').addClass('disabled')
				}else if(is_preVenda){
					url = path + 'vendasCaixa/save/prevenda';
				}else{
					url = path + 'vendasCaixa/save';
				}
				$('#btn_nao_fiscal').addClass('disabled')
				$.ajax
				({
					type: 'POST',
					url: url,
					dataType: 'json',
					data: {
						venda: js,
						_token: token
					},
					success: function(e){
						console.log(e)
						audioSuccess()
						let imprimeTroca = 0;
						
						$('.modal-loading').css('display', 'none')
						if(acao == 'fiscal'){
							$('#preloader2').css('display', 'block');
							$('#preloader9').css('display', 'block');
							//emitirNFCe(e.id);	
							location.href= path + 'frenteCaixa/list';
							swal({
								title: "Sucesso",
								text: "Venda PDV alterada com sucesso!",
								icon: "success",
							})
							.then((v) => {
							});
						}
					}, error: function(e){
						$('.modal-loading').css('display', 'none')
						console.log(e)
						ENVIANDO = false
						$('#btn_nao_fiscal').removeAttr('disabled')
						$('#btn_nao_fiscal').removeClass('disabled')
						$('#preloader2').css('display', 'none');
						$('#preloader9').css('display', 'none');
						$('#modal-venda').modal('hide')
						swal("Ops!!", "Erro ao salvar venda!!", "error")
					}
				});
			}
			$('#kt_select2_3').val('null').change();
		}else{
			swal('Erro', 'CPF/CNPJ Inválido!', 'error')
		}
	}
}

function salvarCredito(js, token){
	$.ajax
	({
		type: 'POST',
		url: path + 'vendas/salvarCrediario',
		dataType: 'json',
		data: {
			venda: js,
			_token: token
		},
		success: function(e){
			$('#modal-venda').modal('hide')
			audioSuccess()
			window.open(path + 'vendas/imprimirPedido/'+e.id, '_blank');
			// $('#modal-credito').modal('open');
			// $('#evento-conta-credito').html('Venda salva na conta crédito do cliente ' +
			// 	CLIENTE.razao_social)
			swal("Sucesso", "Venda salva na conta crédito do cliente " + CLIENTE.razao_social, "success")
			.then(() => {
				location.href = path + 'frenteCaixa'
			})

		}, error: function(e){
			
			$('#preloader2').css('display', 'none');
			$('#preloader9').css('display', 'none');
			$('#modal-venda').modal('hide')
			swal("Ops!!", "Erro ao salvar venda!!", "error")
		}

	});
}

$('#btn-cpf').keypress(function(event) {
	if (event.key === "Enter") {
		finalizarVenda('fiscal')
	}
});

function emitirNFCe(vendaId){
	// $('#modal-venda').modal('close')
	// $('#preloader_'+vendaId).css('display', 'inline-block');

	
	$('#btn_verifica_cliente').addClass('spinner')
	$('#btn-cpf').addClass('spinner')
	$('#btn-cupom').addClass('spinner')
	$('#btn-cpf').attr('disabled', true)
	$('#btn_verifica_cliente').attr('disabled', true)
	$('#btn_envia_'+vendaId).addClass('spinner')
	$('#btn_envia_'+vendaId).addClass('disabled')
	$('#btn_envia_grid_'+vendaId).addClass('spinner')
	$('#btn_envia_grid_'+vendaId).addClass('disabled')

	let token = $('#_token').val();
	$.ajax
	({
		type: 'POST',
		url: path + 'nfce/gerar',
		dataType: 'json',
		data: {
			vendaId: vendaId,
			_token: token
		},
		success: function(e){

			$('#modal-cpf-nota').modal('hide')
			$('#btn-cpf').removeClass('spinner')
			$('#btn_verifica_cliente').removeClass('spinner')
			$('#btn-cupom').removeClass('spinner')
			$('#btn-cpf').removeAttr('disabled')
			$('#btn_verifica_cliente').removeAttr('disabled')
			$('#btn_envia_'+vendaId).removeClass('spinner')
			$('#btn_envia_'+vendaId).removeClass('disabled')
			$('#btn_envia_grid_'+vendaId).removeClass('spinner')
			$('#btn_envia_grid_'+vendaId).removeClass('disabled')


			let recibo = e;
			let retorno = recibo.substring(0,4);
			let mensagem = recibo.substring(5,recibo.length);
			if(retorno == 'Erro'){
				try{
					
					let m = JSON.parse(mensagem);
					swal("Algo deu errado!", "[" + m.protNFe.infProt.cStat + "] : " + m.protNFe.infProt.xMotivo, "error")
					.then(() => {
						location.href=path+'frenteCaixa';
					})
				}catch{
					
					swal("Algo deu errado!", mensagem, "error").then(() => {
						location.href=path+'frenteCaixa';
					})
				}
			}

			else if(retorno == 'erro'){
				// $('#modal-alert-erro').modal('show');
				// $('#evento-erro').html("WebService sefaz em manutenção, falha de comunicação SOAP")
				swal("Algo deu errado!", "WebService sefaz em manutenção, falha de comunicação SOAP", "error").then(() => {
					location.reload()
				})


			}
			else if(e == 'Apro'){
				swal("Cuidado", "Esta NFCe já esta aprovada, não é possível enviar novamente!", "warning").then(() => {
					location.reload()
				})
			}
			else if(e == 'OFFL'){
				swal("Alerta", "NFCe gerada em contigência!", "success").then(() => {
					window.open(path + 'nfce/imprimir/'+vendaId, '_blank');
					location.reload()
				})
			}
			else{
				$('#modal-venda').modal('hide')
				swal("Sucesso", "NFCe gerada com sucesso RECIBO: " +recibo, "success")
				.then(() => {
					window.open(path + 'nfce/imprimir/'+vendaId, '_blank');

					if(ATALHOS.imprimir_ticket_troca == 1){
						swal({
							title: "Sucesso",
							text: "Deseja imprimir o ticket de troca",
							icon: "success",
							buttons: ["Não", 'Imprimir'],
							dangerMode: true,
						}).then((v) => {
							if (v) {
								window.open(path + 'nfce/ticket-troca/'+vendaId, '_blank');
								location.href=path+'frenteCaixa';
							}else{
								location.href=path+'frenteCaixa';
							}
						});
					}else{
						location.href=path+'frenteCaixa';
					}
					// if(is_troca){
					// 	location.href="/frenteCaixa";
					// }else{
					// 	location.reload();
					// }
				})
				// $('#evento').html("NFCe gerada com sucesso RECIBO: " +recibo)

			}
			$('#btn_envia_'+vendaId).removeClass('spinner')
			$('#btn_envia_grid_'+vendaId).removeClass('spinner')
			// $('#preloader2').css('display', 'none');
			// $('#preloader9').css('display', 'none');
			// $('#preloader1').css('display', 'none');
		}, error: function(err){
			
			// $('#preloader_'+vendaId).css('display', 'none');
			$('#btn-cpf').removeAttr('spinner')
			$('#btn-cpf').removeClass('disabled')
			$('#btn_envia_'+vendaId).removeClass('spinner')
			$('#btn_envia_'+vendaId).removeClass('disabled')
			$('#btn_envia_grid_'+vendaId).removeClass('spinner')
			$('#btn_envia_grid_'+vendaId).removeClass('disabled')

			let js = err.responseJSON;
			// deletarVenda(vendaId)
			// swal("Algo errado", js, "error").then(() => {
			// 	location.reload()
			// })
			// var $toastContent = $('<span>Erro ao enviar NFC-e</span>').add($('<button class="btn-flat toast-action">OK</button>'));
			// Materialize.toast($toastContent, 5000);
			// $('#preloader2').css('display', 'none');
			// $('#preloader9').css('display', 'none');


			
			if(js.message){
				swal("Algo errado", js.message, "error")

			}else{
				let err = "";
				js.map((v) => {
					err += v + "\n";
				});
				// alert(err);
				swal("Erro", err, "warning")

			}
			$('#btn-cpf').removeClass('spinner')


			// $('#preloader1').css('display', 'none');

		}
	})

$('#cpf').val('')

}

function deletarVenda(id){
	$.get(path + 'nfce/deleteVenda/'+id)
	.done((data) => {
		
	})
	.fail((err) => {
		
	})

}

function removerVenda(id){
	let senha = $('#pass').val()
	if(senha != ""){

		swal({
			title: 'Cancelamento de venda',
			text: 'Informe a senha!',
			content: {
				element: "input",
				attributes: {
					placeholder: "Digite a senha",
					type: "password",
				},
			},
			button: {
				text: "Enviar!",
				closeModal: false,
				type: 'error'
			},
			confirmButtonColor: "#DD6B55",
		}).then(v => {
			if(v.length > 0){
				$.get(path+'configNF/verificaSenha', {senha: v})
				.then(
					res => {
						location.href="/frenteCaixa/deleteVenda/"+id;
					},
					err => {
						swal("Erro", "Senha incorreta", "error")
						.then(() => {
							location.reload()
						});
					}
					)
			}else{
				location.reload()
			}
		})
	}else{
		location.href="/frenteCaixa/deleteVenda/"+id;
	}
}

function redireciona(){
	location.href=path+'frenteCaixa';
}

function modalCancelar(id){
	$('#modal').modal('show');
	$('#venda_id').val(id)
}


function cancelar(){

	$('#btn_cancelar_nfce').addClass('spinner');

	let justificativa = $('#justificativa').val();
	let id = $('#venda_id').val();
	let token = $('#_token').val();
	$.ajax
	({
		type: 'POST',
		data: {
			id: id,
			justificativa: justificativa,
			_token: token
		},
		url: path + 'nfce/cancelar',
		dataType: 'json',
		success: function(e){
			$('#btn_cancelar_nfce').removeClass('spinner');

			// alert(e.retEvento.infEvento.xMotivo)
			swal("Sucesso", e.retEvento.infEvento.xMotivo, "success")
			.then((v) => {
				location.reload()
			})

		}, error: function(e){
			$('#btn_cancelar_nfce').removeClass('spinner');

			
			let js = e.responseJSON;
			if(e.status == 404){
				// alert(js.mensagem)
				swal("Erro", js.mensagem, "warning")

			}else{
				// alert(js.retEvento.infEvento.xMotivo)
				swal("Erro", js.retEvento.infEvento.xMotivo, "warning")

				// Materialize.toast('Erro de comunicação contate o desenvolvedor', 5000)

			}
		}
	});
}

function verItens(){
	$('#modal-itens').modal('open');
	let t = montaTabela();
	$('#body-modal').html(t);

}

function modalWhatsApp(){
	$('#modal-whatsApp').modal('show')
}

function enviarWhatsApp(){
	let celular = $('#celular').val();
	let texto = $('#texto').val();

	let mensagem = texto.split(" ").join("%20");

	let celularEnvia = '55'+celular.replace(' ', '');
	celularEnvia = celularEnvia.replace('-', '');
	let api = 'https://api.whatsapp.com/send?phone='+celularEnvia
	+'&text='+mensagem;
	window.open(api)
}

function setarComanda(cod){
	$('#cod-comanda').val(cod)
	$('#modal-mesas').modal('hide')
	setTimeout(() => {
		apontarComanda()
	}, 200)
}

function apontarComanda(){
	$('.btn-apontar').addClass('spinner')
	let cod = $('#cod-comanda').val()
	$.get(path+'pedidos/itensParaFrenteCaixa', {cod: cod})
	.done((success) => {
		ITENS = []
		TOTAL = 0
		$('.btn-apontar').removeClass('spinner')
		montarComanda(success, (rs) => {
			if(rs){
				COMANDA = cod;
				$('#modal-comanda').modal('hide')
				swal("", "Comanda setada!!!", "success")


			}
		})
	})
	.fail((err) => {
		$('.btn-apontar').removeClass('spinner')
		if(err.status == 401){
			swal("", "Nada encontrado!!!", "error")
		}
		
	})
}

function montarComanda(itens, call){
	let cont = 0;
	itens.map((v) => {
		let nome = '';
		let valorUnit = 0;
		if(v.sabores.length > 0){

			let cont = 0;
			v.sabores.map((sb) => {
				cont++;
				valorUnit = v.maiorValor;
				nome += sb.produto.produto.nome +
				(cont == v.sabores.length ? '' : ' | ')
			})


		}else{
			nome = v.produto.nome;
			valorUnit = v.valor
		}	
		let item = {
			// cont: cont+1,
			cont: Math.floor(Math.random() * 10000),
			id: v.produto_id,
			nome: nome,
			quantidade: v.quantidade,
			valor: parseFloat(valorUnit) + parseFloat(v.valorAdicional),
			pizza: v.maiorValor ? true : false,
			itemPedido: v.item_pedido,
			imagem: v.imagem
		}

		ITENS.push(item)
		TOTAL += parseFloat(item.valor)*(item.quantidade);
	});
	let t = montaTabela();

	atualizaTotal();
	$('#body').html(t);
	call(true)
}

$('#acrescimo').keyup(() => {
	let acrescimo = $('#acrescimo').val();
	if(acrescimo > 0){
		$('#desconto').val('0')
	}

	let total = TOTAL+VALORBAIRRO;

	if(acrescimo.substring(0, 1) == "%"){

		let perc = acrescimo.substring(1, acrescimo.length);

		VALORACRESCIMO = total * (perc/100);

	}else{
		acrescimo = acrescimo.replace(",", ".")
		VALORACRESCIMO = parseFloat(acrescimo)
	}

	if(acrescimo.length == 0) VALORACRESCIMO = 0;
	atualizaTotal();


})

function consultarNFCe(id){
	$('#btn_consulta_' + id).addClass('spinner')
	$('#btn_consulta_grid_' + id).addClass('spinner')
	$.get(path + 'nfce/consultar/'+id)
	.done((data) => {
		$('#btn_consulta_' + id).removeClass('spinner')
		$('#btn_consulta_grid_' + id).removeClass('spinner')

		
		let js = JSON.parse(data)
		
		swal("Consulta", "[" + js.protNFe.infProt.cStat + "] " + js.protNFe.infProt.xMotivo ,"success");
	})
	.fail((err) => {
		$('#btn_consulta_' + id).removeClass('spinner')
		$('#btn_consulta_grid_' + id).removeClass('spinner')
		
	})
}

$('#btn-plus').click((target) => {
	let quantidade = parseInt($('#quantidade').val());
	$('#quantidade').val(quantidade+1)
})

$('#click-multi').click(() => {
	let caixa_livre = $('#caixa_livre').val()
	if(caixa_livre == 1 && !VENDEDOR_ID){
		swal("Atenção", "Informe o vendedor", "warning")
		return;
	}
	$("#tipo-pagamento").val($("#tipo-pagamento option:eq(1)").val());
	$('#modal-pag-mult').modal('show')
	$('#v-multi').html(formatReal(TOTAL+VALORACRESCIMO - DESCONTO))

	if(TOTAL <= 0){
		swal("Erro", "Valor da venda deve ser maior que Zero!!", "error")
		.then(() => {
			$('#modal-pag-mult').modal('hide')
		})
	}
	$('#vl_restante').html(formatReal(TOTAL+VALORACRESCIMO - DESCONTO))
	$('#total-multi').html(formatReal(TOTAL+VALORACRESCIMO - DESCONTO))
	setTimeout(() => {
		calcParcelas()
	}, 10)
})

$('#btn-ok-multi').click(() => {

	$('#modal-pag-mult').modal('hide')
	// $('#modal-venda').modal('show')

	verificaPrecoAlterado(function(){ $('#modal-venda').modal('show') });

	// VALORPAG1 = $('#valor_pagamento_1').val() ? parseFloat($('#valor_pagamento_1').val().replace(",", ".")) : 0;
	// VALORPAG2 = $('#valor_pagamento_2').val() ? parseFloat($('#valor_pagamento_2').val().replace(",", ".")) : 0;
	// VALORPAG3 = $('#valor_pagamento_3').val() ? parseFloat($('#valor_pagamento_3').val().replace(",", ".")) : 0;

	// TIPOPAG1 = $('#tipo_pagamento_1').val()
	// TIPOPAG2 = $('#tipo_pagamento_2').val()
	// TIPOPAG3 = $('#tipo_pagamento_3').val()

	// OBSPAG1 = $('#observacao_pagamento_1').val()
	// OBSPAG2 = $('#observacao_pagamento_2').val()
	// OBSPAG3 = $('#observacao_pagamento_3').val()

	// VENCPAG1 = $('#vencimento_pagamento_1').val()
	// VENCPAG2 = $('#vencimento_pagamento_2').val()
	// VENCPAG3 = $('#vencimento_pagamento_3').val()

	// let validaData1 = ""
	// let validaData2 = ""
	// let validaData3 = ""
	// if(VENCPAG1 != ""){
	// 	validaData1 = validadata(VENCPAG1, 1)
	// }

	// if(VENCPAG2 != ""){
	// 	validaData2 = validadata(VENCPAG2, 2)
	// }

	// if(VENCPAG3 != ""){
	// 	validaData3 = validadata(VENCPAG3, 3)
	// }

	// if((TIPOPAG1 == '06' || TIPOPAG2 == '06' || TIPOPAG3 == '06') && CLIENTE == null){
	// 	swal("Alerta", "Informe um cliente!", "warning")
	// }else if(validaData1 != "" || validaData2 != "" || validaData3 != ""){
	// 	swal("Alerta", validaData1 + "\n" + validaData2 + "\n" + validaData3, "warning")
	// }else{
	// 	$('#modal-pag-mult').modal('hide')
	// 	
	// 	
	// 	$('#modal-venda').modal('show')
	// }
})

function validadata(d){
   var data = d; // pega o valor do input
   if(!data){
   	return "Informe o vencimento";
   }

   if(data.length < 10){
   	return "Informe a data corretamente";
   }
   data = data.replace(/\//g, "-"); // substitui eventuais barras (ex. IE) "/" por hífen "-"
   var data_array = data.split("-"); // quebra a data em array
   var dia = data_array[2];
   var mes = data_array[1];
   var ano = data_array[0];

   // para o IE onde será inserido no formato dd/MM/yyyy
   if(data_array[0].length != 4){
   	dia = data_array[0];
   	mes = data_array[1];
   	ano = data_array[2];
   }

   var hoje = new Date();
   var d1 = hoje.getDate();
   var m1 = hoje.getMonth()+1;
   var a1 = hoje.getFullYear();

   var d1 = new Date(a1, m1, d1);
   var d2 = new Date(ano, mes, dia);

   var diff = d2.getTime() - d1.getTime();
   diff = diff / (1000 * 60 * 60 * 24);

   if(diff < 0){
   	return "Data do pagamento não pode ser anterior ao dia de hoje!";
   }else{
   	return ""
   }

}

$('#close-multi').click(() => {
	$('#modal-pag-mult').modal('hide')
	VALORPAG1 = 0
	VALORPAG2 = 0
	VALORPAG3 = 0
	TIPOPAG1 = ''
	TIPOPAG2 = ''
	TIPOPAG3 = ''
})
//modal-venda

function montaAtalhos(){
	if(ATALHOS != null){
		if(ATALHOS.finalizar != ""){
			Mousetrap.bind(ATALHOS.finalizar, function(e) {
				e.preventDefault();
				let v = $('#valor_recebido').val();
				let tp = $('#tipo-pagamento').val()
				v = v.replace(",", ".");
				if(ITENS.length > 0 && ((parseFloat(v) >= TOTAL) || tp != '01' )){
					$('#finalizar-venda').trigger('click');
				}else{
					swal("Cuidado", "Venda sem itens, ou valor recebido inferior ao total da venda", "warning")
				}
			});
		}
		if(ATALHOS.reiniciar != ""){
			Mousetrap.bind(ATALHOS.reiniciar, function(e) {
				e.preventDefault();
				location.href = '/frenteCaixa'
			});
		}
		if(ATALHOS.editar_desconto != ""){
			Mousetrap.bind(ATALHOS.editar_desconto, function(e) {
				e.preventDefault();
				setaDesconto()
			});
		}
		if(ATALHOS.editar_acrescimo != ""){
			Mousetrap.bind(ATALHOS.editar_acrescimo, function(e) {
				e.preventDefault();
				setaAcresicmo()
			});
		}
		if(ATALHOS.editar_observacao != ""){
			Mousetrap.bind(ATALHOS.editar_observacao, function(e) {
				e.preventDefault();
				setaObservacao()
			});
		}
		if(ATALHOS.setar_valor_recebido != ""){
			Mousetrap.bind(ATALHOS.setar_valor_recebido, function(e) {
				e.preventDefault();
				$('#valor_recebido').val(TOTAL);
				// Fecha o select2 (campo de pesquisa de produto)
				$("#kt_select2_1").select2("close");
				// Foca e seleciona o campo valor_recebido
				$('#valor_recebido').focus().select();
				$('#finalizar-venda').removeAttr('disabled');
			});
		}	
		if(ATALHOS.forma_pagamento_dinheiro != ""){
			Mousetrap.bind(ATALHOS.forma_pagamento_dinheiro, function(e) {
				e.preventDefault();
				$('#tipo-pagamento').val('01').change()
			});
		}
		if(ATALHOS.forma_pagamento_debito != ""){
			Mousetrap.bind(ATALHOS.forma_pagamento_debito, function(e) {
				e.preventDefault();
				$('#tipo-pagamento').val('04').change()
			});
		}
		if(ATALHOS.forma_pagamento_credito != ""){
			Mousetrap.bind(ATALHOS.forma_pagamento_credito, function(e) {
				e.preventDefault();
				$('#tipo-pagamento').val('03').change()
			});
		}

		if(ATALHOS.forma_pagamento_pix != ""){
			Mousetrap.bind(ATALHOS.forma_pagamento_pix, function(e) {
				e.preventDefault();
				$('#tipo-pagamento').val('17').change()
			});
		}

		if(ATALHOS.setar_leitor != ""){
			Mousetrap.bind(ATALHOS.setar_leitor, function(e) {
				e.preventDefault();
				$('#codBarras').focus()
			});
		}

		if(ATALHOS.setar_quantidade != ""){
			Mousetrap.bind(ATALHOS.setar_quantidade, function(e) {
				e.preventDefault();
				setaQuantidade()
			});
		}

		if(ATALHOS.balanca_digito_verificador){
			DIGITOBALANCA = ATALHOS.balanca_digito_verificador
		}

		if(ATALHOS != null){
			TIPOUNIDADEBALANCA = ATALHOS.balanca_valor_peso
		}

		if(ATALHOS.finalizar_fiscal != ""){
			Mousetrap.bind(ATALHOS.finalizar_fiscal, function(e) {
				e.preventDefault();
				if($('#modal-venda').hasClass('show')){
					verificaCliente()
				}
			});
		}

		if(ATALHOS.finalizar_nao_fiscal != ""){
			Mousetrap.bind(ATALHOS.finalizar_nao_fiscal, function(e) {
				e.preventDefault();
				if($('#modal-venda').hasClass('show')){
					$('#btn_nao_fiscal').trigger('click')
				}
			});
		}
	}

}

function apontarCodigoDeBarras(){
	let codBarras = $('#cod-barras2').val()
	if(codBarras.length > 0){
		getProdutoCodBarras(codBarras, (data) => {
			if(data){
				setTimeout(() => {
					addItem();
				}, 400)

			}else{
			}
			$('#cod-barras2').val('')
			$('#modal-cod-barras').modal('hide')
		})
	}else{
		swal("Alerta", "Informe o código", "warning")
	}
}

$('.pula').keypress(function(e){

	var tecla = (e.keyCode?e.keyCode:e.which);
	// 
	if(tecla == 13){

		let campo = $('.pula');
		indice = campo.index(this);
		if(campo[indice+1] != null){
			let proximo = campo[indice + 1];
			proximo.focus();
		}
	}
	// e.preventDefault(e);
	// return false;
})

function inutilizar(){

	let justificativa = $('#justificativa_inut').val();
	let nInicio = $('#nInicio').val();
	let nFinal = $('#nFinal').val();


	if(!justificativa){
		swal("Erro", "Informe a justificativa", "error")
		return;
	}

	if(!nInicio || !nFinal){
		swal("Erro", "Informe a Número inicial e final", "error")
		return;
	}

	// $('#preloader3').css('display', 'block');
	$('#btn-inut-2').addClass('spinner')

	let token = $('#_token').val();
	$.ajax
	({
		type: 'POST',
		data: {
			justificativa: justificativa,
			nInicio: nInicio,
			nFinal: nFinal,
			_token: token
		},
		url: path + 'nfce/inutilizar',
		dataType: 'json',
		success: function(e){
			
			if(e.infInut.cStat == '102'){
				$('#nInicio').val('');
				$('#justificativa_inut').val('');
				$('#nFinal').val('');
				// alert("cStat:" + e.infInut.cStat + "\n" + e.infInut.xMotivo);
				swal("Sucesso", "["+e.infInut.cStat + "] " + e.infInut.xMotivo, "success")
				.then(() => {
					location.reload()
				})
			}else{
				swal("Erro", "["+e.infInut.cStat + "] " + e.infInut.xMotivo, "error")
				.then(() => {
					location.reload()
				})
			}


			// $('#preloader3').css('display', 'none');
			$('#btn-inut-2').removeClass('spinner')

		}, error: function(e){
			
			swal("Erro", "Erro de comunicação contate o desenvolvedor!", "error")
			$('#preloader1').css('display', 'none');
		}
	});
}

function tiposPagamento(t){
	let tipos = []
	tipos['01'] = 'Dinheiro'
	tipos['02'] = 'Cheque'
	tipos['03'] = 'Cartão de Crédito'
	tipos['04'] = 'Cartão de Débito'
	tipos['05'] = 'Crédito Loja'
	tipos['06'] = 'Crediário'
	tipos['10'] = 'Vale Alimentação'
	tipos['11'] = 'Vale Refeição'
	tipos['12'] = 'Vale Presente'
	tipos['13'] = 'Vale Combustível'
	tipos['14'] = 'Duplicata Mercantil'
	tipos['15'] = 'Boleto Bancário'
	tipos['16'] = 'Depósito Bancário'
	tipos['17'] = 'Pagamento Instantâneo (PIX)'
	tipos['90'] = 'Sem pagamento'
	tipos['99'] = 'Outros'
	return tipos[t]
}

function setDate30(vencimento){
	vencimento = convertData2(vencimento)
	let data = new Date(vencimento+'T01:00:00');

	data.setDate(data.getDate() + 30);
	let d = (data.getDate() < 10 ? '0'+data.getDate() : data.getDate()) + '/' + (data.getMonth() < 9 ? '0' +
		(data.getMonth()+1) : (data.getMonth()+1)) + '/' + data.getFullYear();
	$('#vencimento_pagamento').val(d)
}

function convertData2(data){
	let d = data.split('/');
	return d[2] + '-' + d[1] + '-' + d[0];
}

function convertData(data){
	let d = data.split('-');
	return d[2] + '/' + d[1] + '/' + d[0];
}


// modal novo cliente
function novoClienteModal(){
	$('#kt_select2_3').val('null').change()
	$('#add_cliente_modal').modal('show');
}

// Verifica se o usuário irá inserir os dados do contador
$(function () {
	isChecked()
});

$('#info_contador').change(() => {
	isChecked()
})

function isChecked(){
	let checked = $('#info_contador').is(':checked')

	if(checked){
		$('.ct').css('display', 'block')
	}else{
		$('.ct').css('display', 'none')
	}
}

function addNovoCliente(){
	console.clear()
	let js = {
		razao_social: $('#razao_social').val(),
		nome_fantasia: $('#nome_fantasia').val() ? $('#nome_fantasia').val() : '',
		rua: $('#rua').val() ? $('#rua').val() : '',
		numero: $('#numero').val() ? $('#numero').val() : '',
		cpf_cnpj: $('#cpf_cnpj').val() ? $('#cpf_cnpj').val() : '',
		ie_rg: $('#ie_rg').val() ? $('#ie_rg').val() : '',
		bairro: $('#bairro').val() ? $('#bairro').val() : '',
		cep: $('#cep').val() ? $('#cep').val() : '',
		consumidor_final: $('#consumidor_final').val() ? $('#consumidor_final').val() : '',
		contribuinte: $('#contribuinte').val() ? $('#contribuinte').val() : '',
		limite_venda: $('#limite_venda').val() ? $('#limite_venda').val() : '',
		cidade_id: $('#kt_select2_4').val() ? $('#kt_select2_4').val() : NULL,
		telefone: $('#telefone').val() ? $('#telefone').val() : '',
		celular: $('#celular').val() ? $('#celular').val() : '',
	}
	

	if(js.razao_social == ''){
		swal("Erro", "Informe a razão social", "warning")
	}else{
		swal({
			title: "Cuidado",
			text: "Ao salvar o cliente com os dados incompletos não será possível emitir NFe até que edite o seu cadstro?",
			icon: "warning",
			buttons: ["Cancelar", 'Salvar'],
			dangerMode: true,
		})
		.then((v) => {
			if (v) {
				let token = $('#_token').val();
				$.post(path + 'clientes/quickSave',
				{
					_token: token,
					data: js
				})
				.done((res) =>{
					CLIENTE = res;
					CLIENTES.push(res)

					$('#kt_select2_3').append('<option value="'+res.id+'">'+ 
						res.razao_social+'</option>').change();
					swal("Sucesso", "Cliente adicionado!!", 'success')
					.then(() => {
						$('#add_cliente_modal').modal('hide')
						$('#kt_select2_3').val(res.id).change();

					})
				})
				.fail((err) => {
					
					swal("Alerta", err.responseJSON, "warning")
				})
			}
		})
	}

}

// function addNovoCliente(){
// 	$('#salvar_novo_cliente').attr('disabled', true)
// 	$('#salvar_novo_cliente > span').addClass('spinner')

// 	$.fn.serializeObject = function() {
// 		var o = {};
// 		var a = this.serializeArray();
// 		$.each(a, function() {
// 			if (o[this.name]) {
// 				if (!o[this.name].push) {
// 					o[this.name] = [o[this.name]];
// 				}
// 				o[this.name].push(this.value || '');
// 			} else {
// 				o[this.name] = this.value || '';
// 			}
// 		});
// 		return o;
// 	};

// 	let formData = $('#new_client_form').serializeObject();
// 	delete formData._token;

// 	token = $('#_token').val();

// 	$.ajax
// 	({
// 		type: 'POST',
// 		data: {
// 			formData,
// 			_token: token
// 		},
// 		url: path + 'clientes/quickSave',
// 		dataType: 'json',
// 		success: function(e){

// 			swal("", "O Cliente foi Cadastrado com Sucesso!", "success");

// 			$('#add_cliente_modal').modal('hide');

// 			CLIENTES = e.clientes;

//            	// adiciona os clientes ao select
//            	$('#kt_select2_3').empty()
//            	$.each(CLIENTES, (index, cli) => {
//            		$('#kt_select2_3').append(`<option value="${cli.id}">${cli.id} - ${cli.razao_social}</option>`).change();
//            	});
//            	$('#kt_select2_3').val('null').change();

//            	$('#salvar_novo_cliente > span').removeClass('spinner')
//            	$('#salvar_novo_cliente').removeAttr('disabled');

//            }, error: function(e){
//            	let errorText = `Possíveis erros:\n`;

//            	if(e.responseJSON.errors){
//            		$.each(e.responseJSON.errors, (index, err) => {
//            			errorText += `${index}: ${err}\n`;
//            		});
//            	}

//            	swal("Erro!", errorText, "error");

//             // $.alert(errorText, { title: 'Erro ao Cadastrar Novo Usuário!',
//             // close: '', speed: 'normal', isOnly: true,
//             // minTop: 70, autoClose: false, type: 'danger' });

//             $('#salvar_novo_cliente > span').removeClass('spinner')
//             $('#salvar_novo_cliente').removeAttr('disabled');
//         }
//     });
// }

function verificaPrecoAlterado(callback) {
	if($('#senha_alterar_preco').val() != ''){
		
		let alteracoes = '';

		ITENS.forEach(function(item){
			let p = PRODUTOS.find(p => p.id == item.id);

			if(parseFloat(item.valor.replace(",", ".")) != parseFloat(p.valor_venda)){
				alteracoes+= `Item número ${item.cont}: ${formatReal(parseFloat(p.valor_venda))} => ${formatReal(parseFloat(item.valor.replace(",", ".")))}\n`;
			}
		});

		if (alteracoes != '') {
			alteracoes = 'Valor original alterado nos seguintes itens:\n'+alteracoes;
		}else{
			return callback();
		}

		function pedeSenhaSwal(acesso = ''){
			swal({
				title: (acesso == 'negado')?'Senha Incorreta!':'Atenção!',
				text: alteracoes,
				icon: (acesso == 'negado')?'error':'',
			}).then((ok) => {
				if(ok){
					swal({
						title: 'Atenção!',
						text: 'Confirmar alteração de preços para essa venda.',
						content: {
							element: "input",
							attributes: {
								placeholder: "Digite a senha",
								type: "password",
							},
						},
						button: {
							text: "Confirmar!",
							closeModal: false,
							type: 'error'
						},
						confirmButtonColor: "#DD6B55",
					}).then(val => {
						SENHA_ALTERAR_PRECO = val;
						if(val.length > 0){
							$.get(path+'configNF/verificaSenhaAlterarPreco', {senha: val})
							.then(
								res => {
									swal.close();
									return callback();
								},
								err => {
									return pedeSenhaSwal('negado');
								})
						}else{
							pedeSenhaSwal('negado');
						}
					})
				}else{
					return false;
				}
			})
		}

		return pedeSenhaSwal();
	}

	
	return callback();
}

$('#novaTroca').click(() => {
	let nfce = $('#nfceIdentificador').val();
	let id =  $('#idIdentificador').val();

	if(nfce.length > 0){
		location.href=('/frenteCaixa/editTroca?nfce='+nfce)
	}else if(id.length > 0){
		location.href=('/frenteCaixa/editTroca?id='+id)
	}
});

function vendaAlterada(){
	if(is_troca){
		if(TOTAL <= parseFloat(VENDA.valor_total).toFixed(casas_decimais)){

			DESCONTO = TOTAL-DESCONTO;
			atualizaTotal();
			$('#valor_recebido').val(0);

			$('#tipo-pagamento').css('display', 'none');
			$('#valor_recebido').css('display', 'none');
		}else{
			DESCONTO = VENDA.valor_total;
			atualizaTotal();
			$('#valor_recebido').val(TOTAL + VALORBAIRRO - DESCONTO);

			$('#tipo-pagamento').css('display', 'block');
			$('#valor_recebido').css('display', 'block');

			return swal("Atenção", "O valor pós troca irá exceder o valor da venda atual, selecione uma forma de pagamento para receber a diferença!", "warning");
		}
	}
}

function removeItem(cont){
	console.clear()
	let temp = [];
	let soma = 0;
	ITENS.map((v) => {
		if(v.cont != cont){
			temp.push(v)
			soma += parseFloat(v.valor.replace(',','.'))*(v.quantidade.replace(',','.'));


		}else{
			if(v.is_troca){
				REMOVIDO_NA_TROCA.push(v);
			}else{
				ADICIONADO_NA_TROCA = ADICIONADO_NA_TROCA.filter((i) => { return i.cont !== v.cont});
			}
		}
	});
	TOTAL = soma;
	ITENS = temp;
	let t = montaTabela();
	$('#body').html(t);
	vendaAlterada();
	setTimeout(() => {
		atualizaTotal()		
	}, 100)
}

$('#finalizar-troca').click(() => {
	if(ADICIONADO_NA_TROCA.length == 0 || REMOVIDO_NA_TROCA == 0){
		return swal("Aviso", "Remova algum produto e adicione outro para realizar a troca!", "warning");
	}

	$('#modal-emitir-cupom-troca').modal('show');
	// finalizarVenda('nao_fiscal');
});

function finalizarTrocaFiscal(){
	if(CLIENTE == null){
		$('#modal-emitir-cupom-troca').modal('hide');
		$('#modal-cpf-nota').modal('show');
		$('#modal-cpf-nota').on('shown.bs.modal', function () {
			$('#cpf').focus()
		})
	}
	else{
		finalizarVenda('fiscal')
	}
}


// Pré-venda
$('#edit-prevenda').click(() => {
	if(VENDEDOR_ID == ''){
		return swal("Aviso", "Informe um vendedor!", "warning");
	}else if(ITENS.length === 0){
		return swal("Aviso", "Adicione produtos!", "warning");
	}

	$('#edit-prevenda').addClass('spinner');
	$('#edit-prevenda').attr('disabled', true);

	PREVENDA_NIVEL = 0;
	finalizarVenda('nao_fiscal');
});
$('#rascunho-prevenda').click(() => {
	if(VENDEDOR_ID == ''){
		return swal("Aviso", "Informe um vendedor!", "warning");
	}else if(ITENS.length === 0){
		return swal("Aviso", "Adicione produtos!", "warning");
	}


	$('#rascunho-prevenda').addClass('spinner');
	$('#rascunho-prevenda').attr('disabled', true);

	PREVENDA_NIVEL = 1;
	finalizarVenda('nao_fiscal');
});
$('#enviar-prevenda').click(() => {
	if(VENDEDOR_ID == ''){
		return swal("Aviso", "Informe um vendedor!", "warning");
	}else if(ITENS.length === 0){
		return swal("Aviso", "Adicione produtos!", "warning");
	}

	$('#enviar-prevenda').addClass('spinner');
	$('#enviar-prevenda').attr('disabled', true);

	PREVENDA_NIVEL = 2;
	finalizarVenda('nao_fiscal');
});

$('#open_prevenda_lista').click(() => {
	$('#lista_prevenda_container').empty();
	$('#lista_prevenda_container').addClass('spinner');
	$.ajax
	({
		type: 'GET',
		url: path + 'vendasCaixa/prevenda',
		dataType: 'json',
		success: function(res){
			$('#lista_prevenda_container').removeClass('spinner');

			if(res.length === 0){
				$('#lista_prevenda_container').append(`<p>Nenhum resultado.</p>`);
			}else{
				let html = '';
				foreach(res, (v) => {
					html+= `<div class="p-2 m-1 bg-dark text-white rounded d-flex" style="flex-direction: column;gap: 4px;">
					<div>
					<span class="rounded px-1">${v.vendedor}</span>  <span class="rounded px-1">R$${formatReal(parseFloat(v.valor_total).toFixed(casas_decimais).replace('.', ','))}</span>  <span class="rounded px-1">${v.cliente_id ? v.cliente.razao_social : '--'}</span> <span class="rounded px-1">${v.data}</span>
					<br>
					<span class="rounded px-1">Observação: ${v.observacao? v.observacao : ''}</span>
					</div>
					<div>
					<a href="${path}frenteCaixa/edit/${v.id}?pv=t" class="btn btn-sm btn-success">Abrir</a>
					<a href="#" class="retornar_prevenda btn btn-sm btn-warning" data-id="${v.id}"><i class="la la-undo"></i> Retornar</a>
					</div>
					</div>`;
				})
				$('#lista_prevenda_container').append(html);
				setRetornaPrevendaListeners();
			}
		}, error: function(err){
			$('#lista_prevenda_container').removeClass('spinner');
			$('#lista_prevenda_container').append(`<p>ERRO "${err.message}".</p>`)
			
		}

	});
	// AJAX busca as pre-vendas recebidas
})

$('#open_prevenda_lista_edit').click(() => {
	$('#lista_prevenda_container').empty();
	$('#lista_prevenda_container').addClass('spinner');

	$.ajax
	({
		type: 'GET',
		url: path + 'vendasCaixa/prevendaAll',
		dataType: 'json',
		success: function(res){
			$('#lista_prevenda_container').removeClass('spinner');

			if(res.length === 0){
				$('#lista_prevenda_container').append(`<p>Nenhum resultado.</p>`);
			}else{
				let html = '';
				foreach(res, (v) => {
					html+= `<div class="p-2 m-1 bg-dark text-white rounded d-flex" style="flex-direction: column;gap: 4px;">
					<div>
					<span class="rounded px-1">${v.vendedor}</span>  <span class="rounded px-1">R$${formatReal(parseFloat(v.valor_total).toFixed(casas_decimais).replace('.', ','))}</span>  <span class="rounded px-1">${v.cliente_id ? v.cliente.razao_social : '--'}</span> <span class="rounded px-1">${v.data}</span>
					<br>
					<span class="rounded px-1">Observação: ${v.observacao? v.observacao : ''}</span>
					</div>
					<div>
					<a href="${path}frenteCaixa/prevendaEdit/${v.id}" class="btn btn-sm btn-success">Editar</a>
					</div>
					</div>`;
				})
				$('#lista_prevenda_container').append(html);
				setRetornaPrevendaListeners();
			}
		}, error: function(err){
			$('#lista_prevenda_container').removeClass('spinner');
			$('#lista_prevenda_container').append(`<p>ERRO "${err.message}".</p>`)
			
		}

	});
	// AJAX busca as pre-vendas recebidas
})

function setRetornaPrevendaListeners(){
	$('.retornar_prevenda').each((index, item) => {
		$(item).on('click', () => {
			swal({
				title: 'Retornar pré-venda',
				text: 'Tem certeza que deseja retornar a pré-venda número #'+$(item).attr('data-id')+'?',
				button: {
					text: "Ok",
					closeModal: true,
					type: 'error'
				}
			}).then(v => {
				if(v) {
					retornaPrevenda($(item).attr('data-id'));
				}
			});
		})
	})
}

function retornaPrevenda(id){
	$.ajax
	({
		type: 'POST',
		url: path + 'vendasCaixa/prevenda/devolver/'+id,
		data: { _token: $('#_token').val() },
		dataType: 'json',
		success: function(res){
			swal("", `Pré-venda número #${id} retornada.`, "success");
			$('#lista_prevenda_nivel2').modal('hide');
		}, error: function(err){
			
			swal("Erro", `Houve um erro ao tentar retornar a Pré-venda número #${id}! \n ${err.message}`, "warning");
		}

	});
}

// $('#kt_select2_1').keyup(() => {
// 	console.clear()
// 	let pesquisa = $('#kt_select2_1').val();
// 	
// 	if(pesquisa.length > 1){
// 		montaAutocomplete(pesquisa, (res) => {
// 			if(res){
// 				if(res.length > 0){
// 					montaHtmlAutoComplete(res, (html) => {
// 						$('.search-prod').html(html)
// 						$('.search-prod').css('display', 'block')
// 					})

// 				}else{
// 					$('.search-prod').css('display', 'none')
// 				}
// 			}else{
// 				$('.search-prod').css('display', 'none')
// 			}
// 		})
// 	}else{
// 		$('.search-prod').css('display', 'none')
// 	}
// })



// $('#produto-search').keyup(() => {
// 	console.clear()
// 	let pesquisa = $('#produto-search').val();

// 	if(pesquisa.length > 1){
// 		montaAutocomplete(pesquisa, (res) => {
// 			if(res){
// 				if(res.length > 0){
// 					montaHtmlAutoComplete(res, (html) => {
// 						$('.search-prod').html(html)
// 						$('.search-prod').css('display', 'block')
// 					})

// 				}else{
// 					$('.search-prod').css('display', 'none')
// 				}
// 			}else{
// 				$('.search-prod').css('display', 'none')
// 			}
// 		})
// 	}else{
// 		$('.search-prod').css('display', 'none')
// 	}
// })

function montaAutocomplete(pesquisa, call){
	$.get(path + 'produtos/autocomplete', {pesquisa: pesquisa})
	.done((res) => {

		call(res)
	})
	.fail((err) => {
		
		call([])
	})
}

function montaHtmlAutoComplete(arr, call){
	let html = ''
	arr.map((rs) => {
		let p = rs.nome
		if(rs.grade){
			p += ' ' + rs.str_grade
		}

		if(rs.referencia != ""){
			p += ' | REF: ' + rs.referencia
		}

		if(parseFloat(rs.estoqueAtual) > 0){
			p += ' | Estoque: ' + rs.estoqueAtual
		}

		if(parseFloat(rs.valor_venda) > 0){
			p += ' | valor: ' + rs.valor_venda
		}
		html += '<label class="lbl-prod" onclick="selectProd('+rs.id+')">'+p+'</label>'
	})
	call(html)
}

$('.barcode-btn').dblclick(() => {
	$('#modal-barcode').modal('show')
	setTimeout(() => {
		$('#barcode').focus()
	}, 500)
})

function renderizarPagamento(){
	console.clear()
	simulaParcelas((res) => {
		let html = '';
		res.map((rs) => {
			html += '<option value="'+rs.indice+'">';
			html += rs.indice + 'x R$ ' +  rs.valor.replace(".", ",");
			html += '</option>';
		})

		$('#qtd_parcelas').html(html)
	});
}

function simulaParcelas(call){
	let parcelamento_maximo = parseInt($('#parcelamento_maximo').val())
	let total = parseFloat((TOTAL+VALORACRESCIMO - DESCONTO).toFixed(2))
	let temp = [];

	for(let i = 1; i <= parcelamento_maximo; i++){
		let vp = total/i;
		js = {
			'indice': i,
			'valor': vp.toFixed(2)
		}
		temp.push(js)
	}
	call(temp)
}


$('#gerar-pagamentos').click(() => {

	let total = parseFloat((TOTAL+VALORACRESCIMO - DESCONTO).toFixed(2))
	let quantidade = $('#qtd_parcelas').val();
	let intervalo = parseInt($('#intervalo').val());

	var $tr = $('.table-dynamic').find(".dynamic-form").first();
	let now = new Date
	let mes = now.getMonth()+1
	if(mes < 10) mes = "0"+mes;

	let dia = now.getDate();
	if(dia < 10) dia = "0"+dia;

	let hoje = now.getFullYear() + '-' + mes + '-' + dia
	let data = new Date(hoje+'T01:00:00');


	let soma = 0;
	let vp = parseFloat(parseFloat(total/quantidade).toFixed(2));
	let valor = 0;

	$('.table-dynamic tbody').html('')
	for(let i = 1; i <= quantidade; i++){

		data.setDate(data.getDate() + intervalo);
		if(i == quantidade){
			valor = total - soma
		}else{
			valor = vp;
		}

		soma += vp;

		let datestr = data.getFullYear() + "-" + (data.getMonth() < 9 ? '0' + 
			(data.getMonth()+1) : (data.getMonth()+1)) + "-" + (data.getDate() < 10 ? '0'+data.getDate() : data.getDate())

		var $clone = $tr.clone();
		
		$clone.show();
		$clone.find("input,select").val("");
		$clone.find(".entrada_pagamento").val("0").change();
		$clone.find(".valor_pagamento").val(valor.toFixed(2).replace(".", ","));
		if(CLIENTE != null){
			$clone.find(".tipo_pagamento").val('06').change();
		}else{
			$clone.find(".tipo_pagamento").val('01').change();
		}
		$clone.find(".vencimento_pagamento").val(datestr);

		$('.table-dynamic').append($clone);
	}

	$('#modal-pagamentos').modal('hide');
	calcParcelas()

})

$('body').on('change', '.tipo_pagamento', function() {
	if($(this).val() == '06'){
		if(CLIENTE == null){
			swal("Alerta", "Informe o cliente!", "warning")
			$('.tipo_pagamento').val('').change()
		}
	}
});

function getTipoPagamento(indice){
	let tipos = {
		'01': 'Dinheiro',
		'02': 'Cheque',
		'03': 'Cartão de Crédito',
		'04': 'Cartão de Débito',
		'05': 'Crédito Loja',
		'06': 'Crediário',
		'10': 'Vale Alimentação',
		'11': 'Vale Refeição',
		'12': 'Vale Presente',
		'13': 'Vale Combustível',
		'14': 'Duplicata Mercantil',
		'15': 'Boleto Bancário',
		'16': 'Depósito Bancário',
		'17': 'Pagamento Instantâneo (PIX)',
		'90': 'Sem Pagamento',
		'99': 'Outros',
	}

	return tipos[indice]
}

$('body').on('blur', '.entrada_pagamento', function() {
	if($(this).val() == 1){
		let somo_entrada = 0;
		let total_venda = parseFloat((TOTAL+VALORACRESCIMO - DESCONTO).toFixed(2))
		let contEntrada = 0;
		$(".valor_pagamento").each(function () {
			let entrada = $(this).closest('td').next().next().find('select').val()
			if(entrada == 1){
				somo_entrada += convertMoedaToFloat($(this).val())
				contEntrada++
			}
		})
		let dif = total_venda - somo_entrada

		let vp = dif/($(".valor_pagamento").length - contEntrada)
		let sum = somo_entrada
		$(".valor_pagamento").each(function (index, item) {
			let entrada = $(this).closest('td').next().next().find('select').val()
			if(entrada == 0){
				var is_last_item = (index == ($(".valor_pagamento").length - 1));
				if(!is_last_item){
					sum += vp
					$(this).val(convertFloatToMoeda(vp))
				}else{
					$(this).val(convertFloatToMoeda(total_venda-sum))
				}
			}
		})
	}
});

$('body').on('blur', '.inp-pag', function() {
	calcParcelas()
});
//PAGMULTI
function calcParcelas(){
	$('#btn-ok-multi').attr('disabled', true)

	var total = 0
	let total_venda = parseFloat((TOTAL+VALORACRESCIMO - DESCONTO).toFixed(2))

	$(".valor_pagamento").each(function () {
		total += convertMoedaToFloat($(this).val())
	})
	total = arredondaTotal(total)
	console.log(total)
	let dif = parseFloat(total_venda - total)
	$('#vl_restante').text("R$ " + convertFloatToMoeda(dif))
	if(total_venda == total){
		$('#btn-ok-multi').removeAttr('disabled')
		$('.btn-clone-tbl').attr('disabled', 1)
	}else{
		$('.btn-clone-tbl').removeAttr('disabled')
	}
}

function arredondaTotal(total){
	return parseFloat(total).toFixed(2)
}

function setaParcelas(){
	PAGMULTI = []
	console.clear()
	$(".dynamic-form").each(function () {
		let tipo_pagamento = $(this).find('.tipo_pagamento').val()
		let valor_pagamento = $(this).find('.valor_pagamento').val()
		let entrada_pagamento = $(this).find('.entrada_pagamento').val()
		let vencimento_pagamento = $(this).find('.vencimento_pagamento').val()
		let observacao_pagamento = $(this).find('.observacao_pagamento').val()
		PAGMULTI.push({
			tipo: tipo_pagamento,
			valor: valor_pagamento,
			obs: observacao_pagamento,
			entrada: entrada_pagamento,
			vencimento: vencimento_pagamento,
		})
	})
	
}

$(document).on("keyup", ".valor_pagamento", function() {
	let t = $(this).closest('td').prev().find('select').val()
	console.log(t)
	if(!t){
		swal("Alerta", "Selecione primeiro a forma de pagamento", "warning")
		$(this).val('')
	}
})

$('.btn-clone-tbl').on("click", function() {
	console.clear()
	var $elem = $(this)
	.closest(".row")
	.prev()
	.find(".table-dynamic");

	let total_venda = parseFloat((TOTAL+VALORACRESCIMO - DESCONTO).toFixed(2))
	var total = 0

	$(".valor_pagamento").each(function () {
		total += convertMoedaToFloat($(this).val())
	})

	var hasEmpty = false;

	if(total > total_venda){
		var hasEmpty = true;
	}

	if (hasEmpty) {
		swal(
			"Atenção",
			"Soma das parcelas excede o valor total!",
			"warning"
			);
		return;
	}

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
	$("tbody .custom-select-prod").select2("destroy");
	var $tr = $elem.find(".dynamic-form").first();
	var $clone = $tr.clone();

	$clone.show();
	$clone.find("input,select").val("");

	$clone.find(".entrada_pagamento").val(0).change();
	$clone.find(".vencimento_pagamento").val(getDateNow());

	$elem.append($clone);
	calcParcelas()
	
})

function getDateNow(){
	const today = new Date();
	const yyyy = today.getFullYear();
	let mm = today.getMonth() + 1; 
	let dd = today.getDate();

	if (dd < 10) dd = '0' + dd;
	if (mm < 10) mm = '0' + mm;

	return yyyy + "-" + mm + "-" + dd;
}

$(document).delegate(".btn-line-delete", "click", function(e) {
	e.preventDefault();
	swal({
		title: "Você esta certo?",
		text: "Deseja remover esse item mesmo?",
		icon: "warning",
		buttons: true
	}).then(willDelete => {
		if (willDelete) {
			var trLength = $(this)
			.closest("tr")
			.closest("tbody")
			.find("tr")
			.not(".dynamic-form-document").length;
			if (!trLength || trLength > 1) {
				$(this)
				.closest("tr")
				.remove();
				calcParcelas()
			} else {
				swal(
					"Atenção",
					"Você deve ter ao menos um item na lista",
					"warning"
					);
			}
		}
	});
});

function convertMoedaToFloat(value) {
	if (!value) {
		return 0;
	}
	var number_without_mask = value.replaceAll(".", "").replaceAll(",", ".");
	return parseFloat(number_without_mask.replace(/[^0-9\.]+/g, ""));
}

function convertFloatToMoeda(value) {
	value = parseFloat(value)
	return value.toLocaleString("pt-BR", {
		minimumFractionDigits: casas_decimais,
		maximumFractionDigits: casas_decimais
	});
}

function getMesas(){
	console.clear()
	$.get(path + 'pedidos/get-mesas')
	.done((data) => {
		$('#modal-mesas .modal-body').html(data)
	})
	.fail((err) => {
		console.log(err)

	})
}

// Quando o usuário confirmar a seleção do lote:
$('#confirmLote').on('click', function(){
    let selectedLoteId = $('#loteSelect').val();
    if(selectedLoteId == ""){
        swal("Atenção", "Selecione um lote!", "warning");
        return;
    }
    // Atribui o lote selecionado ao objeto do produto
    PRODUTO = CURRENT_PRODUCT; // garante que usamos o produto que foi buscado
    PRODUTO.lote_id = selectedLoteId;
    // Opcional: você pode incluir outras informações do lote, se necessário,
    // por exemplo, exibir o número do lote no item da venda.
    $('#loteModal').modal('hide');
    // Agora, prossegue com a adição do item
    let precoNormal = parseFloat(PRODUTO.valor_venda).toFixed(2);
    let precoPromo = PRODUTO.preco_promocional ? parseFloat(PRODUTO.preco_promocional).toFixed(2) : null;
    let precoFinal = precoPromo ? precoPromo : precoNormal;
    $('#valor_item').val(precoFinal);
    $('#quantidade').val(QUANTIDADE);
    QUANTIDADE = 1;
    addItem(); // Agora, addItem() deve utilizar PRODUTO que possui o atributo lote_id
});
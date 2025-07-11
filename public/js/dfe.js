var array = [];
var codigo = "";
var nome = "";
var ncm = "";
var cfop = "";
var unidade = "";
var valor = "";
var valorCompra = "";
var quantidade = "";
var codBarras = "";
var cest = "";
var nNf = 0;
var semRegitro;
var PRODUTO = null

$(function () {
	let uri = window.location.pathname;
	if(uri.split('/')[2] == 'novaConsulta'){
		if(!$('#empresa_filial').val()){
			filtrar();
		}
	}else {
		try{
			array = JSON.parse($('#docs').val());
		}catch{
			array = [];
		}
	}

	SUBCATEGORIAS = JSON.parse($('#subs').val())

	fatura = JSON.parse($('#fatura').val());
	TOTAL = parseFloat($('#total').val())
	semRegitro = $('#prodSemRegistro').val();
	if(semRegitro == 0){
		$('#salvarNF').removeAttr("disabled");
		$('.sem-registro').css('display', 'none');
	}
	verificaProdutoSemRegistro();

	montaHtmlFatura((html) => {
		$('#fatura-html').html(html)
	})

	setTimeout(() => {
		montaSubs()
	}, 100)
});

$('#tipo_evento').change(() => {
	let tipo = $('#tipo_evento').val();
	if(tipo == 3 || tipo == 4){
		$('#div-just').css('display', 'block')
	}else{
		$('#div-just').css('display', 'none')
	}
})

$('#btn-buscar-documentos').click(() => {
	let local = $('#locais').val()
	if(local){
		filtrar(local)
	}else{
		swal("Alerta", "Selecione o local", "warning")
	}
})

function filtrar(local = -1){
	$('#aguarde').removeClass('d-none')

	$.get(path + 'dfe/getDocumentosNovos', {local: local})
	.done((value) => {
		$('#preloader1').css('display', 'none')
		$('#aguarde').css('display', 'none')

		if(value.length > 0){
			montaTabela(value, (html) => {
				$('table tbody').html(html)
				$('#table').css('display', 'block')
			})
			swal("Sucesso", "Foram encontrados " + value.length + " novos registros!", "success")
		}else{
			swal("Sucesso", "A requisição obteve sucesso, porém sem novos registros!!", "success")
			$('#sem-resultado').css('display', 'block')

		}

	})
	.fail(err => {
		console.log(err)
		$('#preloader1').css('display', 'none')
		$('#aguarde').css('display', 'none')
		try{
			swal("Erro", err.responseJSON.message, "warning")
		}catch{
			swal("Erro", "Erro inesperado!!", "warning")
		}
	})
}

function montaTabela(array, call){
	let html = '';
	array.map((v) => {

		html += '<tr class="datatable-row">';
		html += '<td class="datatable-cell"><span class="codigo" style="width: 300px;" id="id">'
		+ v.nome[0] + '</span></td>'
		html += '<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">'
		+ v.documento[0] + '</span></td>'
		html += '<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">'
		+ v.valor[0] + '</span></td>'
		html += '<td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">'
		+ v.chave[0] + '</span></td>'
		html += '</tr>';
	})

	call(html)
}

function setarEvento(chave){

	array.map((element) => {
		if(element.chave == chave){

			$('#nome').val(element.nome)
			$('#cnpj').val(element.documento)
			$('#valor').val(element.valor)
			$('#data_emissao').val(element.data_emissao)
			$('#num_prot').val(element.num_prot)
			$('#chave').val(element.chave)
		}

	})

}

function gerarCode(){
	$.get(path+'produtos/gerarCodigoEan')
	.done((res) => {
		$('#codBarras').val(res)
	})
	.fail((err) => {
		swal("Erro", "Erro ao buscar código", "error")
	})
}

function linkProduto() {
    console.log("🔹 Atribuindo item já cadastrado...");

    // 🔹 Capturar os valores da tela de Adicionar Produto
    let id_xml = $('#id_xml').val() ? $('#id_xml').val().trim() : null;
    let codBarras_xml = $('#codBarras').val() ? $('#codBarras').val().trim() : null;

    if (!id_xml) {
        console.warn("⚠️ ID XML não encontrado! Tentando buscar via tabela...");
        id_xml = $('tr.produto-linha.selected').data('id_xml') || null;
    }

    if (!codBarras_xml) {
        console.warn("⚠️ Código de Barras XML não encontrado! Buscando alternativa...");
        codBarras_xml = $('tr.produto-linha.selected').data('codBarras_xml') || null;
    }

    console.log("🔹 ID XML Selecionado:", id_xml);
    console.log("🔹 Código de Barras (XML - cEAN):", codBarras_xml);

    if (!id_xml || !codBarras_xml) {
        console.error("❌ ERRO: ID XML ou Código de Barras não encontrado!");
        swal("Erro", "ID XML ou Código de Barras não encontrado!", "error");
        return;
    }

    // 🔹 Buscar a linha correta na tabela
    let linha = $(`tr[data-id_xml="${id_xml}"][data-codBarras_xml="${codBarras_xml}"]`);
    console.log("🔹 Buscando linha com ID:", `tr[data-id_xml="${id_xml}"][data-codBarras_xml="${codBarras_xml}"]`);

    if (!linha.length) {
        console.error("❌ ERRO: Não foi possível encontrar a linha do produto na tabela!");
        swal("Erro", "Não foi possível encontrar o produto na tabela!", "error");
        return;
    }

    console.log("✅ Linha encontrada!");

	// 🔹 Armazenar os valores nos campos ocultos do modal
	$('#id_xml_atribuir').val(id_xml);
	$('#codBarras_xml_atribuir').val(codBarras_xml);

    // 🔹 Preencher os campos na tela de atribuição de produto
    $('#produto-search').val(""); // ❌ Nome do produto começa vazio para ser informado manualmente
    $('#produto-search').attr('data-produto-id', id_xml);

    $('#estoque').val("1"); // ✅ Quantidade vem preenchida com 1

    $('#valor_venda2').val(""); // ✅ Mantém valor de venda vazio
    $('#valor_compra2').val(""); // ✅ Mantém valor de compra vazio

    console.log("✅ Informações preenchidas na tela de atribuição!");

    // 🔹 Exibir modal de atribuição
    $('#modal-link').modal('toggle');
}

$('#kt_select2_1').change(() => {
	let produto = $('#kt_select2_1').val()
	if(produto != 'null'){

		produto = JSON.parse(produto);
		$('#valor_venda2').val(parseFloat(produto.valor_venda).toFixed(casas_decimais))
		$('#valor_compra2').val(parseFloat(produto.valor_compra).toFixed(casas_decimais))
	}else{
		$('#valor_venda2').val('')
	}
})

$('#salvarLink').click(() => {
    console.log("🔹 Iniciando processo de vinculação...");

    let id_produto = $('#produto-search').attr('data-produto-id'); // ID do produto no sistema
    let codBarras_produto = $('#codBarras_atribuir').val().trim();
if (!codBarras_produto || codBarras_produto === '') {
    codBarras_produto = $('#produto-search').attr('data-codBarras') || 'SEM GTIN';
}

    console.log("==== DEPURAÇÃO DO SALVAR ====");
    console.log("🔹 Produto ID Selecionado:", id_produto);
    console.log("🔹 Código de Barras (Produto Cadastrado):", codBarras_produto);

    // 🔹 Capturar os valores armazenados nos campos ocultos
    let id_xml = $('#id_xml_atribuir').val() ? $('#id_xml_atribuir').val().trim() : null;
    let codBarras_xml = $('#codBarras_xml_atribuir').val() ? $('#codBarras_xml_atribuir').val().trim() : null;
    let id_fornecedor = $('#idFornecedor').val();
    let filial_id = $('#filial_id').val();
    filial_id = filial_id == '-1' ? null : filial_id;

    console.log("🔹 ID XML Capturado:", id_xml);
    console.log("🔹 Código de Barras (XML - cEAN):", codBarras_xml);
    console.log("🔹 ID Fornecedor:", id_fornecedor);
    console.log("🔹 Filial ID:", filial_id);

    // 🔹 Verificar se os campos obrigatórios estão preenchidos
    if (!id_produto || !id_xml || !codBarras_xml) {
        console.error("❌ ERRO: Campos obrigatórios ausentes!");
        swal("Erro", "ID XML, Código de Barras ou Produto não encontrados!", "error");
        return;
    }

    let token = $('#_token').val();

    // 🔹 Enviar os dados para salvar na tabela produto_mapeamento
    $.ajax({
        type: 'POST',
        url: path + 'dfe/vincularProdutoMapeamento',
        data: {
            id_produto: id_produto, // ID do produto no sistema
            id_xml: id_xml, // ID do XML (cProd)
            id_fornecedor: id_fornecedor,
            codBarras_xml: codBarras_xml, // Código de barras do XML (cEAN)
            codBarras_produto: codBarras_produto, // Código de barras cadastrado
            empresa_id: $('#empresa_id').val(),
            filial_id: filial_id,
            _token: token
        },
        dataType: 'json',
        success: function (response) {
            console.log("✅ Produto vinculado com sucesso!", response);
            swal({
                title: 'Sucesso!',
                text: 'Produto vinculado com sucesso!',
                icon: 'success',
                buttons: false,
                timer: 300
            }).then(() => {
                location.reload();
            });
        },
        error: function (xhr) {
            console.error("❌ Erro ao vincular o produto!", xhr);
            swal("Erro", "Falha ao vincular o produto!", "error");
        }
    });
});

$('#salvarEdit').click(() => {
    let id = $('#idEdit').val().trim(); 
    let nome = $('#nomeEdit').val().trim();
    let conversao = $('#conv_estoqueEdit').val().trim();
    let valorVenda = $('#valorVendaEdit').val().trim();
    let valorCompra = $('#valorCompraEdit').val().trim();
    let percentualLucro = $('#percentualLucroEdit').val().trim().replace(",", "."); 

    if (!id) {
        alert("Erro: ID do produto não encontrado!");
        return;
    }

    percentualLucro = parseFloat(percentualLucro);
    if (isNaN(percentualLucro)) {
        percentualLucro = 0;
    }

    let token = $('#_token').val();

    console.log("🟢 Enviando dados para atualização:");
    console.log({ id, nome, conversao, valorVenda, valorCompra, percentualLucro });

    $.ajax({
        type: 'POST',
        data: {
            nome: nome,
            conversao_unitaria: conversao,
            valor_venda: valorVenda.replace(",", "."),
            valor_compra: valorCompra.replace(",", "."),
            percentual_lucro: percentualLucro, // ✅ Envia corretamente para o Laravel
            _token: token
        },
        url: `/produtos/updateProduto/${id}`, 
        dataType: 'json',
        success: function(e) {
            console.log(`✅ Produto ID: ${id} atualizado com sucesso.`);
            console.log("🔥 Resposta do servidor:", e);
            $('#modal2').modal('hide');
            location.reload(); 
        },
        error: function(xhr) {
            console.error(`❌ Erro ao atualizar produto ID: ${id}`, xhr);
            alert('Erro ao salvar as alterações. Verifique os campos e tente novamente.');
        }
    });
});

function verificaProdutoSemRegistro(){
	if(semRegitro == 0){
		$('#salvarNF').removeAttr("disabled");
		$('.sem-registro').css('display', 'none');
	}else{
		$('.prodSemRegistro').html(semRegitro);
	}
}

function _construct(codigo, nome, codBarras, ncm, cfop, unidade, valor, quantidade, cfop_entrada, cest){

	this.codigo = codigo;
	this.nome = nome;
	this.ncm = ncm;
	this.cest = cest;
	this.cfop = cfop;
	this.unidade = unidade;
	this.valor = valor;
	this.quantidade = quantidade;
	this.codBarras = codBarras;
	this.cfopEntrda = cfop_entrada;
}

function cadProd(elemento, id_xml, codBarras_xml) {
    console.log("🔹 Cadastrando produto...");
    console.log("🔹 Código do Produto (ID XML):", id_xml);
    console.log("🔹 Código de Barras do Produto:", codBarras_xml);

    // Buscar a linha correta na tabela baseada no ID XML e Código de Barras
     let linha = $(elemento).closest('tr');

    if (!linha.length) {
        console.error("❌ ERRO: Não foi possível encontrar a linha do produto na tabela!");
        swal("Erro", "Não foi possível encontrar o produto na tabela!", "error");
        return;
    }
    console.log("✅ Linha encontrada!");

    // Capturar dados da linha
    let nome = linha.find('.nome').text().trim();
    let ncm = linha.find('.ncm').text().trim();
    let cfop = linha.find('.cfop').text().trim();
    let unidade = linha.find('.unidade').text().trim();
    let valor = linha.find('.valor').text().trim(); // valor de compra
    let cfop_entrada = linha.find('.cfop_entrada').text().trim();
    let cest = linha.find('.cest').text().trim();
    let valor_ipi = linha.find('.valor_ipi').text().trim() || "0,00";
    let outras_despesas = linha.find('.outras_despesas').text().trim() || "0,00";
    let valor_venda = valor; // valor de venda inicialmente igual ao valor de compra
    let conversao_estoque = "1"; // conversão padrão

    // Capturar apenas a quantidade correta (sem o subtotal, se houver)
    let quantidadeText = linha.find('.quantidade').first().text().trim();
    let quantidadeAjustada = quantidadeText.split(' ')[0].replace(/[^\d,.-]/g, '') || "0";

    console.log("🔹 Quantidade bruta:", quantidadeText);
    console.log("🔹 Quantidade ajustada:", quantidadeAjustada);
    
    // Armazena o valor original da quantidade e valor unitário
    $('#quantidade').val(quantidadeAjustada);
    $('#quantidade').data('original', quantidadeAjustada);

    $('#valor').val(valor);
    $('#valor').data('original', valor);

    // Armazena o subtotal original (obtido da célula da tabela – dentro do span em .quantidade)
    let subtotalOriginal = linha.find('.quantidade span').text().trim();
    $('#subtotal').data('original', subtotalOriginal);

    // Verificar se a unidade existe na lista de unidades
    getUnidadeMedida((data) => {
        let unidadeValida = data.includes(unidade) ? unidade : "UN";

        if (unidadeValida !== unidade) {
            console.warn(`⚠️ Unidade '${unidade}' não encontrada! Definindo para 'UN'.`);
            swal("Atenção", `A unidade '${unidade}' não está cadastrada no sistema. Foi alterada para 'UN'.`, "warning");
        }

        // Preencher os campos no modal
        $('#id_xml').val(id_xml);
        $('#nome').val(nome);
        $('#ncm').val(ncm);
        $('#cfop').val(cfop);
        $('#CEST').val(cest);
        $('#un_compra').val(unidade);
        $('#unidade_venda').val(unidadeValida).change();
        $('#valor').val(valor);
        $('#valor_venda').val(valor_venda);
        $('#quantidade').val(quantidadeAjustada);
        $('#cfop_entrada').val(cfop_entrada);

        // Preenche os dois campos:
        // O campo visível (#codBarras) com o valor original (que pode ser modificado depois)
        $('#codBarras').val(codBarras_xml);
        // O campo oculto (#codBarras_xml) é preenchido e não será alterado
        $('#codBarras_xml').val(codBarras_xml);

        $('#referencia_xml').val(id_xml);
        $('#valor_ipi').val(valor_ipi);
        $('#outras_despesas').val(outras_despesas);
        $('#conv_estoque').val(conversao_estoque);

        // Define o atributo de ID do produto para uso na atualização da tabela
        $('#produto-search').attr('data-produto-id', id_xml);

        // Exibir o modal de cadastro
        $('#modal1').modal('toggle');
    });
}

function deleteProd(item){
	if (confirm('Deseja excluir este item, se confirmar sua NF ficará informal?')) { 
		var tr = $(item).closest('tr');	
		tr.fadeOut(500, function() {	      
			tr.remove();  
			verificaTabelaVazia();	
			verificaProdutoSemRegistro();
		});	

		return false;
	}
}

function editProd(id) {
    let produtoId = $('#th_prod_id_' + id).text().trim();
    $('#idEdit').val(id);

    // Tente recuperar os dados do botão de edição
    let btn = $("#th_acao2_" + id);
    let id_xmlFromBtn = btn.data("id_xml");
    let codBarras_xmlFromBtn = btn.data("codbarras_xml");
    console.log("Dados do botão:", "id_xml:", id_xmlFromBtn, "codBarras_xml:", codBarras_xmlFromBtn);

    $.ajax({
        type: 'GET',
        url: baseUrl + '/produtos/getProduto/' + produtoId,
        dataType: 'json',
        success: function(e) {
            if (!e) {
                console.error(`❌ Produto não encontrado no banco para ID: ${produtoId}`);
                return;
            }

            console.log("🔍 Produto retornado pelo backend:", e);

            // Atualiza os campos do modal
            $("#nomeEdit").val(e.nome);
            $("#conv_estoqueEdit").val(e.conversao_unitaria);

            let valorVendaAtual = parseFloat(e.valor_venda) || 0;
            $("#valorVendaAtual").attr("value", valorVendaAtual.toFixed(2).replace('.', ','));

            let percentualLucro = parseFloat(e.percentual_lucro) || 0;
            $("#percentualLucroEdit").val(percentualLucro.toFixed(2).replace('.', ','));

            // Usa os dados do botão, se disponíveis, como prioridade
            let id_xml = id_xmlFromBtn || (e.id_xml || e.referencia_xml || e.codigo_xml || e.codigo || e.referencia);
            let codBarras_xml = codBarras_xmlFromBtn || (e.codBarras || e.codigoBarras || e.codigo_de_barras);

            if (!id_xml) {
                console.warn(`⚠️ ID XML não encontrado no JSON (Produto ID: ${produtoId}).`);
            } else {
                console.log(`🔹 id_xml: ${id_xml} | codBarras_xml: ${codBarras_xml}`);
            }

            // Define safeCodBarrasLocal a partir de codBarras_xml
            let safeCodBarrasLocal = "";
            if (codBarras_xml) {
                safeCodBarrasLocal = $.escapeSelector(codBarras_xml);
            } else {
                console.warn("⚠️ CodBarras não informado para o produto.");
            }

            // Busca o valor de compra na linha da tabela
            let valorCompra = $(`#tr_${id_xml}_${safeCodBarrasLocal} .valor`).text().trim();
            if (!valorCompra || valorCompra === "-1" || valorCompra === "" || valorCompra === "NaN") {
                valorCompra = $(`#th_prod_valor_compra_${produtoId}`).text().trim();
            }
            if (!valorCompra || valorCompra === "-1" || valorCompra === "" || valorCompra === "NaN") {
                console.warn("⚠️ Nenhum valor de compra encontrado, definindo como 0,00.");
                valorCompra = "0,00";
            }
            console.log(`✅ Valor de Compra Final Capturado: ${valorCompra}`);
            $("#valorCompraEdit").val(valorCompra);

            let valorCompraFloat = parseFloat(valorCompra.replace(',', '.')) || 0;
            let novoValorVenda = (percentualLucro > 0)
                ? valorCompraFloat * (1 + (percentualLucro / 100))
                : valorVendaAtual;
            console.log("🚀 Calculando Novo Valor de Venda:");
            console.log("Valor Venda Atual:", valorVendaAtual);
            console.log("Valor Compra:", valorCompraFloat);
            console.log("Percentual Lucro:", percentualLucro);
            console.log("Novo Valor de Venda Calculado:", novoValorVenda.toFixed(2));

            $("#valorVendaEdit").val(novoValorVenda.toFixed(2).replace('.', ','));

            // Configura listeners para atualização dinâmica
            $("#percentualLucroEdit").off("input").on("input", atualizarPrecoVenda);
            $("#valorVendaEdit").off("input").on("input", atualizarMargemLucro);

            // Listener para conv_estoqueEdit: recalcula o valor de compra e atualiza o de venda
            $("#conv_estoqueEdit").off("input").on("input", function() {
                let convEstoque = parseFloat($(this).val().replace(",", ".")) || 1;
                if (id_xml && safeCodBarrasLocal) {
                    let selector = safeCodBarrasLocal;
                    let quantidadeTexto = $(`#qtd_xml_${id_xml}_${selector}`).text().trim();
                    let quantidadeNota = parseFloat(quantidadeTexto.replace(",", ".")) || 0;
                    if (quantidadeNota > 10000) {
                        console.warn(`⚠️ Quantidade muito alta (${quantidadeNota}), ajustando...`);
                        quantidadeNota = quantidadeNota / 10000;
                    }
                    let subtotalTexto = $(`#tr_${id_xml}_${selector} td.quantidade span`).text().trim();
                    let subtotalNota = parseFloat(subtotalTexto.replace(/\./g, "").replace(",", ".")) || 0;
                    let totalConversao = quantidadeNota * convEstoque;
                    let novoValorCompra = (totalConversao > 0) ? (subtotalNota / totalConversao) : 0;
                    console.log(`🔄 Recalculando Valor de Compra: Qtd: ${quantidadeNota}, Conv: ${convEstoque}, Subtotal: ${subtotalNota}, Novo Valor: ${novoValorCompra.toFixed(2)}`);
                    $("#valorCompraEdit").val(novoValorCompra.toFixed(2).replace('.', ','));
                    $(`#th_prod_valor_compra_${produtoId}`).text(novoValorCompra.toFixed(2).replace('.', ','));
                    atualizarPrecoVenda();
                } else {
                    console.warn("⚠️ id_xml ou safeCodBarrasLocal não disponível. Usando fallback baseado no produtoId.");
                    let quantidadeTexto = $(`#th_qtd_compra_${produtoId}`).text().trim();
                    let quantidadeNota = parseFloat(quantidadeTexto.replace(",", ".")) || 0;
                    let subtotalTexto = $(`#th_subtotal_nota_${produtoId}`).text().trim();
                    let subtotalNota = parseFloat(subtotalTexto.replace(/\./g, "").replace(",", ".")) || 0;
                    let totalConversao = quantidadeNota * convEstoque;
                    let novoValorCompra = (totalConversao > 0) ? (subtotalNota / totalConversao) : 0;
                    console.log(`🔄 [Fallback] Recalculando Valor de Compra: Qtd: ${quantidadeNota}, Conv: ${convEstoque}, Subtotal: ${subtotalNota}, Novo Valor: ${novoValorCompra.toFixed(2)}`);
                    $("#valorCompraEdit").val(novoValorCompra.toFixed(2).replace('.', ','));
                    $(`#th_prod_valor_compra_${produtoId}`).text(novoValorCompra.toFixed(2).replace('.', ','));
                    atualizarPrecoVenda();
                }
            });

            $('#modal2').modal('show');
        },
        error: function(e) {
            console.error(`❌ Erro ao buscar produto no banco para edição (ID: ${produtoId})`, e);
        }
    });
}

function verificaTabelaVazia(){
	if($('table tbody tr').length == 0){
		$('#salvarNF').addClass("disabled");
	}
}

function validaItem(){
	let nome = $('#nome').val()
	let ncm = $('#ncm').val()
	let cfop = $('#cfop').val()
	let valor = $('#valor').val()
	let valor_venda = $('#valor_venda').val()
	let un_compra = $('#un_compra').val()
	let unidade_venda = $('#unidade_venda').val()

	let unidadesMedida = ["AMPOLA","BALDE","BANDEJ","BARRA","BISNAG","BLOCO","BOBINA","BOMB","CAPS","CART","CENTO","CJ","CM","CM2","CX","CX2","CX3","CX5","CX10","CX15","CX20","CX25","CX50","CX100","DISP","DUZIA","EMBAL","FARDO","FOLHA","FRASCO","GALAO","GF","GRAMAS","JOGO","KG","L","KIT","LATA","LITRO","M","M2","M3","MILHEI","ML","MWH","PACOTE","PALETE","PARES","PC","POTE","K","RESMA","ROLO","SACO","SACOLA","TAMBOR","TANQUE","TON","TUBO","UN","VASIL","VIDRO"];

	if ((unidade_venda === 'AMPOLA' && !un_compra) || (!unidadesMedida.includes(unidade_venda) || !unidadesMedida.includes(un_compra))) {
        unidade_venda = 'UN';
        un_compra = 'UN';
    }

	if(nome && ncm && cfop && valor && valor_venda && un_compra && unidade_venda){
		return true
	}else{
		return false
	}
}

$('#categoria_id').change(() => {
	montaSubs()
})

function montaSubs(){
	let categoria_id = $('#categoria_id').val()
	let subs = SUBCATEGORIAS.filter((x) => {
		return x.categoria_id == categoria_id
	})

	let options = ''
	subs.map((s) => {
		options += '<option value="'+s.id+'">'
		options += s.nome
		options += '</option>'
	})
	$('#sub_categoria_id').html('<option value="">selecione</option>')
	$('#sub_categoria_id').append(options)
}

function refreshPageAfterSave() {
    // Aguarda 1 segundo (opcional) e atualiza a página
    setTimeout(function() {
        location.reload();
    }, 300);
}

$(document).ready(function () {
    window.fornecedorID = $('#idFornecedor').val();
    console.log("fornecedorID global:", window.fornecedorID);
    console.log("🔹 Iniciando atualização automática dos produtos vinculados...");

    $('.produto-linha').each(function () {
        let id_xml = $(this).data('id_xml');
        let codBarras_xml = $(this).data('codbarras_xml');
        let id_fornecedor = $('#idFornecedor').val();

        console.log(`🔹 Verificando produto ID XML: ${id_xml} | CodBarras: ${codBarras_xml} | Fornecedor: ${id_fornecedor}`);

        $.ajax({
            type: 'GET',
            url: `/dfe/produto_mapeamento/getProdutoPeloXml`,
            data: {
                id_xml: id_xml,
                codBarras_xml: codBarras_xml,
                id_fornecedor: id_fornecedor
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.id_produto) {
                    let id_produto = res.id_produto;
                    console.log(`✅ Produto mapeado encontrado! ID: ${id_produto}`);

                    $.ajax({
                        type: 'GET',
                        url: baseUrl + `/produtos/getProduto/${id_produto}`,
                        dataType: 'json',
                        success: function (produto) {
                            if (produto) {
                                console.log(`🔹 Produto encontrado: ${produto.nome}`);

                                // Escapando o código de barras para o seletor
                                let selectorCodBarras = $.escapeSelector(codBarras_xml);

                                // Pegando os valores corretos da tabela utilizando o seletor escapado
                                let quantidadeTexto = $(`#qtd_xml_${id_xml}_${selectorCodBarras}`).text().trim();
                                let subtotalTexto = $(`#tr_${id_xml}_${selectorCodBarras} td.quantidade span`).text().trim();
                                let conversaoTexto = produto.conversao_unitaria ? produto.conversao_unitaria.toString() : "1";

                                // Tratamento correto dos valores (removendo separadores de milhar e ajustando decimais)
                                let quantidadeNota = parseFloat(quantidadeTexto.replace(",", "."));
                                let conversao = parseFloat(conversaoTexto.replace(",", ".")) || 1;
                                let subtotalNota = parseFloat(subtotalTexto.replace(/\./g, "").replace(",", "."));

                                // Se a quantidade parecer muito alta, ajustamos para um formato decimal correto
                                if (quantidadeNota > 10000) {
                                    console.warn(`⚠️ Quantidade muito alta (${quantidadeNota}), ajustando...`);
                                    quantidadeNota = quantidadeNota / 10000; // Convertendo 2500000 para 250
                                }

                                // Aplicando a conversão de unidade corretamente
                                let quantidadeConvertida = quantidadeNota * conversao;

                                // Cálculo correto do novo valor unitário de compra
                                let novoValorCompra = (quantidadeConvertida > 0) ? (subtotalNota / quantidadeConvertida) : 0;

                                console.log(`🔹 Quantidade Convertida: ${quantidadeNota} x ${conversao} = ${quantidadeConvertida}`);
                                console.log(`🔹 Subtotal da Nota: ${subtotalNota}`);
                                console.log(`🔹 Novo Valor Unitário de Compra: ${novoValorCompra.toFixed(2)}`);

                                if (quantidadeConvertida > 100000) {
                                    console.warn(`⚠️ 🚨 Quantidade Convertida MUITO ALTA (${quantidadeConvertida}). Verifique a conversão de unidade!`);
                                }

                                if (novoValorCompra === 0) {
                                    console.warn(`⚠️ Valor de compra calculado como 0 para Produto: ${produto.nome}`);
                                }

                                // Atualiza quantidade convertida na tela
                                $(`#qtd_aux_${id_xml}_${selectorCodBarras}`).text(quantidadeConvertida.toFixed(3).replace(".", ","));

                                // Atualiza o valor unitário de compra na tabela
                                let valorElemento = $(`#tr_${id_xml}_${selectorCodBarras} .valor`);
                                if (valorElemento.length > 0) {
                                    valorElemento.text(novoValorCompra.toFixed(6).replace(".", ","));
                                } else {
                                    console.warn(`⚠️ Campo de valor de compra não encontrado para Produto: ${produto.nome}`);
                                }

                                // Atualiza o modal de edição se estiver aberto
                                if ($('#modal2').hasClass('show')) {
                                    console.log(`🔹 Atualizando valor de compra no modal para Produto ID: ${id_produto}`);
                                    $("#valorCompraEdit").val(novoValorCompra.toFixed(2).replace(".", ","));
                                }

                                // Se estiver usando DataTables, força atualização
                                if ($.fn.DataTable.isDataTable('#kt_datatable')) {
                                    $('#kt_datatable').DataTable().draw();
                                }
                            }
                        },
                        error: function (xhr) {
                            console.error(`❌ Erro ao buscar produto ID: ${id_produto}`, xhr);
                        }
                    });
                } else {
                    console.warn(`⚠️ Produto não encontrado na tabela produto_mapeado para ID XML: ${id_xml}`);
                }
            },
            error: function (xhr) {
                console.error(`❌ Erro ao buscar produto mapeado com ID XML: ${id_xml}`, xhr);
            }
        });
    });
});

var salvarMassa = false;
var saveProduto = false;

$('#salvar').click(() => {
    if (saveProduto == false) {
        saveProduto = true;

        let valid = validaItem();
        if (!valid) {
            swal("Alerta", "Todos os campos com * são obrigatórios!", "warning");
            saveProduto = false;
            return;
        }

        $('#preloader').css('display', 'block');

        // Captura dos valores ocultos
        let id_xml = $('#id_xml').val();
        // Se o id_xml não contém hífen mas contém underlines, converte os underlines em hífen
        if (id_xml.indexOf('-') === -1 && id_xml.indexOf('_') !== -1) {
            id_xml = id_xml.replace(/_/g, "-");
        }
        $('#id_xml').val(id_xml);
        let codBarras_xml = $('#codBarras_xml').val(); // valor do campo hidden

        console.log("🔹 ID XML Capturado:", id_xml);
        console.log("🔹 Código de Barras XML Capturado:", codBarras_xml);
        console.log("🔹 ID Fornecedor Enviado:", parseInt(window.fornecedorID) || 0);

        // Atualiza referência XML se SEM GTIN
        let codBarras = $('#codBarras').val();
        let referencia_xml = id_xml;
        if (codBarras.trim().toUpperCase() === "SEM GTIN") {
            referencia_xml = id_xml; // Mantém o ID_XML mesmo sem GTIN
        }

        let prod = {
            valorVenda: $('#valor_venda').val(),
            unidadeVenda: $('#unidade_venda').val(),
            referencia_xml: referencia_xml, // Usa o ID XML correto
            conversao_unitaria: $('#conv_estoque').val(),
            categoria_id: $('#categoria_id').val(),
            sub_categoria_id: $('#sub_categoria_id').val(),
            marca_id: $('#marca_id').val(),
            valorCompra: $('#valor').val(),
            nome: $('#nome').val(),
            ncm: $('#ncm').val(),
            cfop: $('#cfop').val(),
            percentual_lucro: $('#percentual_lucro').val(),
            referencia: $('#referencia').val(),
            unidadeCompra: $('#un_compra').val(),
            quantidade: $('#quantidade').val(),
            codBarras: codBarras,                   // Valor digitado/alterado pelo usuário
            codBarras_xml: codBarras_xml,           // Valor original do XML (campo oculto)
            CST_CSOSN: $('#CST_CSOSN').val(),
            CST_PIS: $('#CST_PIS').val(),
            CST_COFINS: $('#CST_COFINS').val(),
            CST_IPI: $('#CST_IPI').val(),
            perc_icms: $('#perc_icms').val(),
            perc_pis: $('#perc_pis').val(),
            perc_cofins: $('#perc_cofins').val(),
            perc_ipi: $('#perc_ipi').val(),
            estoque_minimo: $('#estoque_minimo').val(),
            gerenciar_estoque: $('#gerenciar_estoque').is(':checked') ? 1 : 0,
            inativo: $('#inativo').is(':checked'),
            CEST: $('#CEST').val(),
            anp: $('#anp').val(),
            perc_glp: $('#perc_glp').val(),
            perc_gnn: $('#perc_gnn').val(),
            perc_gni: $('#perc_gni').val(),
            valor_partida: $('#valor_partida').val(),
            unidade_tributavel: $('#unidade_tributavel').val(),
            quantidade_tributavel: $('#quantidade_tributavel').val(),
            largura: $('#largura').val(),
            altura: $('#altura').val(),
            comprimento: $('#comprimento').val(),
            peso_liquido: $('#peso_liquido').val(),
            peso_bruto: $('#peso_bruto').val(),
            filial_id: $('#filial_id').val() ? $('#filial_id').val() : -1
        };

        let token = $('#_token').val();
        let idFornecedor = parseInt(window.fornecedorID) || 0;
        console.log("🔹 ID Fornecedor Enviado:", idFornecedor);

        $.ajax({
            type: 'POST',
            data: {
                produto: prod,
                id_fornecedor: idFornecedor,
                _token: token
            },
            url: path + 'dfe/salvarProdutoDaNota',
            dataType: 'json',
            success: function(e) {
                console.log("✅ Produto salvo com sucesso!", e);
                $('#preloader').css('display', 'none');
                $('#modal1').modal('hide');

                swal({
                    title: "Sucesso",
                    text: "Item salvo",
                    icon: "success",
                    buttons: false,
                    timer: 300
                }).then(function() {
                    location.reload();
                });

                saveProduto = false;
            },
            error: function(e) {
                console.log("❌ Erro ao salvar produto!", e);
                $('#preloader').css('display', 'none');
                saveProduto = false;
            }
        });
    }
});

function salvarProduto() {
    return new Promise((resolve, reject) => {
		const valid = validaItem();
		if (!valid && !salvarMassa) {
			swal("Alerta", "Todos os campos com * são obrigatórios!", "warning");
			return reject('Campos obrigatórios não preenchidos');
		} else if (!valid) {
			return reject('Validação falhou');
		}
        $('#preloader').css('display', 'block');
        let codigo = this.codigo;
		let referencia_xml = $('#codigo_' + codigo + '_' + codBarras).text().trim();
		// Se o código de barras for "SEM GTIN", defina um valor padrão para referencia_xml
		if(codBarras.trim().toUpperCase() === "SEM GTIN"){
			referencia_xml = codigo;
		}	
        $("#th_" + codigo).removeClass("red-text");
        $("#n_" + codigo).html($('#nome').val());
        let prod = {
            valorVenda: $('#valor_venda').val(),
            unidadeVenda: $('#unidade_venda').val(),
            conversao_unitaria: $('#conv_estoque').val(),
            categoria_id: $('#categoria_id').val(),
            sub_categoria_id: $('#sub_categoria_id').val(),
            marca_id: $('#marca_id').val(),
            valorCompra: $('#valor').val(),
            nome: $('#nome').val(),
            ncm: this.ncm,
            cfop: $('#cfop').val(),
            percentual_lucro: $('#percentual_lucro').val(),
            referencia: $('#referencia').val(),
            unidadeCompra: $('#un_compra').val(),
            valor: this.valor,
            quantidade: this.quantidade,
            codBarras: $('#codBarras').val(),
            CST_CSOSN: $('#CST_CSOSN').val(),
            CST_PIS: $('#CST_PIS').val(),
            CST_COFINS: $('#CST_COFINS').val(),
            CST_IPI: $('#CST_IPI').val(),
            perc_icms: $('#perc_icms').val(),
            perc_pis: $('#perc_pis').val(),
            perc_cofins: $('#perc_cofins').val(),
            perc_ipi: $('#perc_ipi').val(),
            estoque_minimo: $('#estoque_minimo').val(),
            gerenciar_estoque: $('#gerenciar_estoque').is(':checked') ? 1 : 0,
            inativo: $('#inativo').is(':checked'),
            CEST: $('#CEST').val(),
            anp: $('#anp').val(),
            perc_glp: $('#perc_glp').val(),
            perc_gnn: $('#perc_gnn').val(),
            perc_gni: $('#perc_gni').val(),
            valor_partida: $('#valor_partida').val(),
            unidade_tributavel: $('#unidade_tributavel').val(),
            quantidade_tributavel: $('#quantidade_tributavel').val(),
            largura: $('#largura').val(),
            altura: $('#altura').val(),
            comprimento: $('#comprimento').val(),
            peso_liquido: $('#peso_liquido').val(),
            peso_bruto: $('#peso_bruto').val(),
            filial_id: $('#filial_id').val() ? $('#filial_id').val() : -1,
			referencia_xml: referencia_xml,
        };

        semRegitro--;
        verificaProdutoSemRegistro();

        let token = $('#_token').val();
		let idFornecedor = parseInt(window.fornecedorID) || 0;
        // Verifique no console:
        console.log("Valor enviado:", idFornecedor);

		$.ajax({
			type: 'POST',
			data: {
				produto: prod,
				id_fornecedor: idFornecedor,
				_token: token
			},
			url: path + 'dfe/salvarProdutoDaNota',
			dataType: 'json',
			success: function(e) {
				let cfop_entrada = $('#cfop_entrada').val();
				$("#th_prod_id_" + codigo).html(e.id);
				$("#cfop_entrada_" + codigo).html(cfop_entrada);
				$("#th_acao1_" + codigo).css('display', 'none');
				$("#th_acao2_" + codigo).css('display', 'block');
				$("#n_" + codigo).removeClass('text-danger');
				$('#preloader').css('display', 'none');
				$('#modal1').modal('hide');
				$('#th_prod_conv_unit_' + codigo).html(conversaoEstoque);
				
				swal({
					title: "Sucesso",
					text: "Item salvo",
					icon: "success",
					buttons: false, // não exibe o botão de confirmação
					timer: 300    // fecha automaticamente após 300 ms
				}).then(function() {
					location.reload();
				});				
		
				saveProduto = false;
			},
			error: function(e) {
				console.log(e);
				$('#preloader').css('display', 'none');
				saveProduto = false;
			}
		});		
    });
}

$('.btn-cancelar').on('click', ()=>{
	$('#modal1').attr('importando', 1)
});

async function processProdutos(produtos) {
	$('#modal1').attr('importando', 0)
	$('#registerModal').modal('hide');
	$('#registrandoProdutos').modal('show');
	$('#total').html(produtos.length)
	var i = 0;
	salvarMassa = true
	saveProduto= false
	let produtosComErro = [];
    for (const produto of produtos) {
		document.getElementById('modal1').style.opacity = '0';
		document.getElementById('modal1').style.top = '400px';
		var pImportando = $('#modal1').attr('importando')
		if(pImportando == 1){
			break;
		}
        await new Promise(resolve => {
            setTimeout(() => {
				if(produto.querySelector('.btn-add')){
					produto.querySelector('.btn-add').click();
					if (!saveProduto) {
						saveProduto = true;
						salvarProduto()
							.then(response => {
								console.log('Produto salvo com sucesso:', response);
							})
							.catch(error => {
								console.error('Erro ao salvar produto:', error);
								produtosComErro.push(produto);
							})
							.finally(() => {
								saveProduto = false;
							});
					}
				}
				i++
				$('#qtdImportados').html(i)
				resolve();
            }, 500);
        });
    }
	if (produtosComErro.length > 0) {
		let mensagemErro = `Importação concluída com erros! \n\n ${produtosComErro.length} produtos não foram registrados corretamente e permanecem destacados em vermelho. Por favor, verifique e corrija-os`;
		swal('Atenção', mensagemErro, 'warning').then(() => {
			location.reload(true);
		});
	} else {
		swal('Sucesso', 'Importação realizada com sucesso!', 'success').then(() => {
			location.reload(true);
		});
	}
}

function registrarTodosProdutos() {

	var produtos = document.querySelectorAll('.datatable-body tr');
    var produtosData = [];
	processProdutos(produtos);

    // var produtos = document.querySelectorAll('.datatable-body tr');
    // var produtosData = [];

    // produtos.forEach(function (produto) {
    //     var codigo = produto.querySelector('.codigo').innerText;
    //     var nome = produto.querySelector('.nome').innerText;
    //     var ncm = produto.querySelector('.ncm').innerText;
    //     var cfop = produto.querySelector('.cfop').innerText;
    //     var codBarras = produto.querySelector('.codBarras').innerText;
    //     var unidade = produto.querySelector('.unidade').innerText;
    //     var valor = produto.querySelector('.valor').innerText;
    //     var quantidade = produto.querySelector('.quantidade').innerText;
    //     var produtoId = produto.querySelector('.cod').innerText;
    //     var conversaoUnitaria = produto.querySelector('.conv_estoque').innerText;
    //     var valorCompra = produto.querySelector('.valor_compra').innerText;
    //     var valorVenda = produto.querySelector('.valor_venda').innerText;

    //     $('#modal1').modal('show');

    //     setTimeout(function() {
    //         var dadosAdicionais = {
    //             valorVendaAdicional: $('#valor_venda').val(),
    //             unidadeVenda: $('#unidade_venda').val(),
    //             conversaoEstoque: $('#conv_estoque').val(),
    //             categoria_id: $('#categoria_id').val(),
    //             sub_categoria_id: $('#sub_categoria_id').val(),
    //             marca_id: $('#marca_id').val(),
    //             valorCompraAdicional: $('#valor').val(),
    //             nomeAdicional: $('#nome').val(),
    //             cfopAdicional: $('#cfop').val(),
    //             percentualLucro: $('#percentual_lucro').val(),
    //             referencia: $('#referencia').val(),
    //             unidadeCompra: $('#un_compra').val(),
    //             CST_CSOSN: $('#CST_CSOSN').val(),
    //             CST_PIS: $('#CST_PIS').val(),
    //             CST_COFINS: $('#CST_COFINS').val(),
    //             CST_IPI: $('#CST_IPI').val(),
    //             perc_icms: $('#perc_icms').val(),
    //             perc_pis: $('#perc_pis').val(),
    //             perc_cofins: $('#perc_cofins').val(),
    //             perc_ipi: $('#perc_ipi').val(),
    //             estoque_minimo: $('#estoque_minimo').val(),
    //             gerenciar_estoque: $('#gerenciar_estoque').is(':checked') ? 1 : 0,
    //             inativo: $('#inativo').is(':checked'),
    //             CEST: $('#CEST').val(),
    //             anp: $('#anp').val(),
    //             perc_glp: $('#perc_glp').val(),
    //             perc_gnn: $('#perc_gnn').val(),
    //             perc_gni: $('#perc_gni').val(),
    //             valor_partida: $('#valor_partida').val(),
    //             unidade_tributavel: $('#unidade_tributavel').val(),
    //             quantidade_tributavel: $('#quantidade_tributavel').val(),
    //             largura: $('#largura').val(),
    //             altura: $('#altura').val(),
    //             comprimento: $('#comprimento').val(),
    //             peso_liquido: $('#peso_liquido').val(),
    //             peso_bruto: $('#peso_bruto').val(),
    //             filial_id: $('#filial_id').val() ? $('#filial_id').val() : -1
    //         };

    //         $('#modal1').modal('hide');
	// 		$('#registerModal').modal('hide');

    //         produtosData.push({
    //             codigo: codigo,
    //             nome: nome,
    //             ncm: ncm,
    //             cfop: cfop,
    //             codBarras: codBarras,
    //             unidade: unidade,
    //             valor: valor,
    //             quantidade: quantidade,
    //             produtoId: produtoId,
    //             conversaoUnitaria: conversaoUnitaria,
    //             valorCompra: valorCompra,
    //             valorVenda: valorVenda,
    //             ...dadosAdicionais
    //         });
    //         if (produtosData.length === produtos.length) {
	// 			let token = $('#_token').val();
    //             $.ajax({
    //                 type: 'POST',
    //                 data: {
    //                     produto: produtosData,
    //                     _token: token
    //                 },
    //                 url: path + 'produtos/registrarProdutos',
    //                 dataType: 'json',
    //                 success: function(e) {
	// 					swal('Sucesso', 'Produtos salvos com sucesso!', 'success')
    //                     .then(function() {
    //                         location.reload();
    //                     });
    //                     saveProduto = false;
    //                 },
    //                 error: function(e) {
    //                     console.log(e);
    //                     $('#preloader').css('display', 'none');
    //                     saveProduto = false;
    //                 }
    //             });
    //         }
    //     }, 500);
    // });
}

var salvando = false;
$('#salvarNF').click(() => {

	$('#salvarNF').addClass('spinner')
	$('#salvarNF').attr('disabled', 'disabled')
	if(salvando == false){
		salvando = true;
		$('#preloader2').css('display', 'block');

		salvarNF((data) => {
			if(data.id){
				salvarItens(data.id, (v) => { //data.id codigo da compra

					if(v){
						salvarFatura(data.id, (f) => {
							$('#modal1').modal('hide');
							$('#preloader2').css('display', 'none');
							sucesso();

						})
					}
				})
			}
		})
	}
})

function salvarFatura(compra_id, call){
	
	retorno = [];
	let token = $('#_token').val();
	let cont = 0; 

	if(fatura.length > 0){
		fatura.map((item) => {
			cont++;
			item.numero = item.numero;
			item.referencia = "Parcela "+cont+", da NF " + $('#nNf').val();
			item.compra_id = compra_id;
			item.categoria_conta_id = $('#categoria_conta_id').val();
			item.numero_nota_fiscal = $('#nNf').val();

			$.ajax
			({
				type: 'POST',
				data: {
					parcela: item,
					_token: token
				},
				url: path + 'contasPagar/salvarParcela',
				dataType: 'json',
				success: function(e){
					call(e)

				}, error: function(e){
					console.log(e)
					$('#preloader2').css('display', 'none');
				}

			});
		})
	}else{
		sucesso();
		$('#preloader2').css('display', 'none');
	}
}


function sucesso(){
	audioSuccess()
	$('#content').css('display', 'none');
	$('#anime').css('display', 'block');
	setTimeout(() => {
		location.href = path+'compras';
	}, 4000)
}

$('#filial_id').change(() => {
	$('#salvarNF').removeAttr("disabled");
})

function salvarNF(call){
	
	let valor_nf = $('#valorDaNF').html()
	valor_nf = valor_nf.replace('R$','');
	valor_nf = valor_nf.replace(',','.');
	let js = {
		fornecedor_id: $('#idFornecedor').val(),
		nNf: $('#nNf').val(),
		data_emissao: $('#data_emissao').val(),
		valor_nf: valor_nf,
		observacao: '',
		lote: $('#lote').val(),
		desconto: $('#vDesc').val(),
		xml_path: $('#pathXml').val(),
		categoria_conta_id: 1, // Valor fixo
		chave: $('#chave').val(),
		filial_id: $('#filial_id') ? $('#filial_id').val() : -1,
		total_ipi: $('#valor_total_ipi').text(),
		total_outras_despesas: $('#total_outras_despesas').val(),
		total_substituicao_tributaria: $('#total_substituicao_tributaria').text(),
		total_seguro: $('#total_seguro').text()
	}
	console.log(js)

	let token = $('#_token').val();

	$.ajax
	({
		type: 'POST',
		data: {
			nf: js,
			_token: token
		},
		url: path + 'dfe/salvarNfFiscal',
		dataType: 'json',
		success: function(e){
			call(e)

		}, error: function(e){
			console.log(e)
			$('#preloader2').css('display', 'none');
		}

	});
}

function getUnidadeMedida(call){
	$.ajax
	({
		type: 'GET',
		url: path + 'produtos/getUnidadesMedida',
		dataType: 'json',
		success: function(e){
			call(e)

		}, error: function(e){
			console.log(e)
		}

	});
}

$('#conv_estoque').on('input', () => {
    let conversao = parseFloat($('#conv_estoque').val().replace(',', '.')) || 1;
    
    // Recupera a quantidade original armazenada (com fallback)
    let originalQtd = $('#quantidade').data('original');
    if (!originalQtd) {
        console.warn("Valor original da quantidade não definido; usando o valor atual.");
        originalQtd = $('#quantidade').val();
    }
    let quantidadeOriginal = parseFloat(originalQtd.toString().replace(',', '.')) || 1;
    
    let percentualLucro = parseFloat($('#percentual_lucro').val().replace(',', '.')) || 0;

    if (conversao <= 0) {
        console.warn("⚠️ Conversão inválida! Usando valor padrão de 1.");
        conversao = 1;
    }

    // Calcula a nova quantidade convertida
    let quantidadeTotal = quantidadeOriginal * conversao;

    // Recupera o subtotal original armazenado (fixo, da nota)
    let subtotalData = $('#subtotal').data('original');
    let subtotal = subtotalData ? parseFloat(subtotalData.toString().replace(',', '.')) : 0;
    
    // Novo valor unitário calculado com base no subtotal original e na nova quantidade
    let novoValorUnitario = (quantidadeTotal > 0) ? (subtotal / quantidadeTotal) : 0;
    
    // Atualiza os campos do modal
    $('#quantidade').val(quantidadeTotal.toFixed(2).replace('.', ','));
    $('#valor').val(novoValorUnitario.toFixed(2).replace('.', ','));

    // Atualiza a tabela, se o ID do produto e o código de barras estiverem definidos
    let id_produto = $('#produto-search').attr('data-produto-id');
    let cod_barras = $('#codBarras').val();

    if (id_produto && cod_barras) {
        console.log(`🔹 Atualizando tabela para Produto ID: ${id_produto}, Código de Barras: ${cod_barras}`);
        // Atualiza a quantidade na tabela
        $(`#qtd_aux_${id_produto}_${cod_barras}`).text(quantidadeTotal.toFixed(2).replace('.', ','));
        // Atualiza o valor unitário na tabela
        $(`#th_prod_valor_compra_${id_produto}`).text(novoValorUnitario.toFixed(2).replace('.', ','));
        // Atualiza o subtotal com o valor original fixo
        let subtotalElement = $(`#tr_${id_produto}_${cod_barras}`).find('.quantidade span');
        if (subtotalElement.length > 0) {
            subtotalElement.text(subtotal.toFixed(2).replace('.', ','));
        }
    } else {
        console.warn("⚠️ ID do produto ou código de barras não encontrado.");
    }

    console.log(`🔹 Quantidade Total Atualizada: ${quantidadeTotal}`);
    console.log(`🔹 Novo Valor Unitário de Compra: ${novoValorUnitario}`);
    console.log(`🔹 Subtotal Original: ${subtotal}`);

    // Atualiza o valor de venda com base no percentual de lucro
    let valorVenda = (percentualLucro > 0)
                     ? novoValorUnitario * (1 + (percentualLucro / 100))
                     : novoValorUnitario;
    $('#valor_venda').val(valorVenda.toFixed(2).replace('.', ','));
});

function salvarItens(id, call) {
    let token = $('#_token').val();
    
    $('table tbody tr').each(function () {
        let id_xml = $(this).data('id_xml');
        let codBarras_xml = $(this).data('codbarras_xml');
        let produto_id = parseInt($(this).find('.cod').html());

        if (!produto_id) {
            console.warn(`⚠️ Produto sem ID no sistema! Ignorando...`);
            return;
        }

        // Captura o valor de compra atualizado da tela
        let valorConvertido = $(this).find('.valor').text().trim().replace(",", ".");
        let quantidadeConvertida = $(this).find('.quantidade').first().text().trim().split(/\s+/)[0].replace(",", ".");
        quantidadeConvertida = parseFloat(quantidadeConvertida);

        
        // Se o valor estiver vazio ou inválido, define um fallback
        if (!valorConvertido || isNaN(parseFloat(valorConvertido))) {
            valorConvertido = $(this).find('.valor_compra').text().trim().replace(",", "."); // Usa o original se não encontrar
        }

        console.log(`✅ Produto ID: ${produto_id} | Quantidade Convertida: ${quantidadeConvertida} | Valor Final: ${valorConvertido}`);

        let js = {
            cod_barras: $(this).find('.codBarras').html(),
            nome: $(this).find('.nome').html(),
            produto_id: produto_id,
            compra_id: id,
            unidade: $(this).find('.unidade').html(),
            quantidade: quantidadeConvertida,
            valor: $(this).find('.valor').html(),
            valor_venda: $(this).find('.valor_venda').html(),
            valor_compra: valorConvertido, // 🔥 Agora pega o valor convertido corretamente
            cfop_entrada: $(this).find('#cfop_entrada_input').val(),
            conversao_unitaria: $(this).find('.conv_estoque').html(),
            said: $(this).find('#codigo_siad_input').val(),
            filial_id: $('#filial_id') ? $('#filial_id').val() : -1,
            valor_ipi: $(this).find('.valor_ipi').html(),
            outras_despesas: $(this).find('.outras_despesas').html(),
            substituicao_tributaria: $(this).find('.substituicao_tributaria').html(),
            valor_seguro: $(this).find('.valor_seguro').html(),
        };

        $.ajax({
            type: 'POST',
            data: {
                produto: js,
                _token: token
            },
            url: path + 'dfe/salvarItem',
            dataType: 'json',
            success: function (e) {
                console.log(`✅ Produto ID: ${produto_id} salvo com sucesso.`);
            },
            error: function (e) {
                console.error(`❌ Erro ao salvar produto ID: ${produto_id}`, e);
                $('#preloader2').css('display', 'none');
            }
        });
    });

    call(true);
}

$('#add-pag').click(() => {
	let vencimento = $('#kt_datepicker_3').val();
	let valor_parcela = $('#valor_parcela').val();
	if(vencimento.length<10 || valor_parcela < 0){
		swal("Erro", "Informe o valor da parcela e vencimento", "error")
	}else{
		somaFatura((res) => {
			valor_parcela = valor_parcela.replace(",", ".")
			let soma = res + parseFloat(valor_parcela)

			if(soma <= TOTAL){
				let js = {
					numero: fatura.length+1,
					vencimento: vencimento,
					valor_parcela: parseFloat(valor_parcela),
					rand: Math.floor(Math.random() * 10000)
				}

				fatura.push(js)
				montaHtmlFatura((html) => {
					$('#fatura-html').html(html)
				})
			}else{
				swal({
					title: "Alerta", 
					text: "Valor total de parcelas ultrapassado, deseja continuar?", 
					icon : "warning",
					buttons: [
					'Cancelar',
					'Confirmar'
					],
				})
				.then(
					(Confirmar) => {
						let js = {
							numero: fatura.length+1,
							vencimento: vencimento,
							valor_parcela: parseFloat(valor_parcela),
							rand: Math.floor(Math.random() * 10000)
						}

						fatura.push(js)
						montaHtmlFatura((html) => {
							$('#fatura-html').html(html)
						})
					},
					(Cancelar) => {}
					)
			}
		})
	}
})

function somaFatura(call){
	let soma = 0;
	fatura.map((rs) => {

		let v = 0;
		try{
			v = parseFloat(rs.valor_parcela.replace(",", "."))
		}catch{
			v = parseFloat(rs.valor_parcela)
		}
		soma += v
	})
	call(soma)
}

function montaHtmlFatura(call){
	let html = '';
	fatura.map((f) => {
		html += '<div class="col-sm-12 col-lg-6 col-md-6 col-xl-4">'
		html += '<div class="card card-custom gutter-b example example-compact text-white">'
		html += '<div class="card-body">'
		html += '<div class="card-title">'
		html += '<h3 style="width: 230px; font-size: 20px; height: 10px;" class="text-dark"> R$ '
		html += maskMoney(f.valor_parcela)
		html += '</h3> <br><a class="delete-parcela" onclick="deleteParcela('+f.rand+')"><i class="la la-trash text-danger"></i></a></div>'
		html += '<div class="car">'
		html += '<div class="kt-widget__info">'
		html += '<span class="kt-widget__label text-dark">Número:</span>'
		html += '<a target="_blank" class="kt-widget__data text-success"> '
		html += f.numero
		html += '</a></div>'
		html += '<div class="kt-widget__info">'
		html += '<span class="kt-widget__label text-dark">Vencimento:</span>'
		html += '<a target="_blank" class="kt-widget__data text-success"> '
		html += f.vencimento
		html += '</a></div>'
		html += '</div></div></div></div>'
	});
	call(html)
}

function deleteParcela(rand){
	let arr = [];
	fatura.map((rs) => {
		if(rs.rand != rand){
			arr.push(rs)
		}
	})
	fatura = arr;
	montaHtmlFatura((html) => {
		$('#fatura-html').html(html)
	})

}

function maskMoney(v){
	try{
		v = v.replace(",", ".");
		v = parseFloat(v);
	}catch{
	}
	return v.toFixed(2).replace(".", ",");
}

$('#percentual_lucro').keyup(() => {

	let valorCompra = parseFloat($('#valor').val().replace(',', '.'));
	let percentualLucro = parseFloat($('#percentual_lucro').val().replace(',', '.'));

	if(valorCompra > 0 && percentualLucro > 0){
		let valorVenda = valorCompra + (valorCompra * (percentualLucro/100));
		valorVenda = formatReal(valorVenda);
		valorVenda = valorVenda.replace('.', '')
		valorVenda = valorVenda.substring(3, valorVenda.length)

		$('#valor_venda').val(valorVenda)
	}else{
		$('#valor_venda').val('0')
	}
})

$('#valor_venda').keyup(() => {
	let valorCompra = parseFloat($('#valor').val().replace(',', '.'));
	let valorVenda = parseFloat($('#valor_venda').val().replace(',', '.'));

	if(valorCompra > 0 && valorVenda > 0){
		let dif = (valorVenda - valorCompra)/valorCompra*100;

		$('#percentual_lucro').val(dif)
	}else{
		$('#percentual_lucro').val('0')
	}
})

function formatReal(v){
	return v.toLocaleString('pt-br', {style: 'currency', currency: 'BRL', minimumFractionDigits: casas_decimais});
}

$('#produto-search').keyup(() => {
	console.clear()
	let pesquisa = $('#produto-search').val();

	if(pesquisa.length > 1){
		montaAutocomplete(pesquisa, (res) => {
			if(res){
				if(res.length > 0){
					montaHtmlAutoComplete(res, (html) => {
						$('.search-prod').html(html)
						$('.search-prod').css('display', 'block')
					})

				}else{
					$('.search-prod').css('display', 'none')
				}
			}else{
				$('.search-prod').css('display', 'none')
			}
		})
	}else{
		$('.search-prod').css('display', 'none')
	}
})

function montaAutocomplete(pesquisa, call){
	$.get(path + 'produtos/autocomplete', {pesquisa: pesquisa})
	.done((res) => {

		call(res)
	})
	.fail((err) => {
		console.log(err)
		call([])
	})
}

function montaHtmlAutoComplete(arr, call) {
    let html = '';

    arr.map((rs) => {
        let p = rs.nome;
        if (rs.grade) {
            p += ' ' + rs.str_grade;
        }
        if (rs.referencia && rs.referencia !== "") {
            p += ' | REF: ' + rs.referencia;
        }
        if (parseFloat(rs.estoqueAtual) > 0) {
            p += ' | Estoque: ' + rs.estoqueAtual;
        }

        // 🔥 Adiciona verificação no ID antes de montar o HTML
        if (!rs.id) {
            console.error("Erro: Produto sem ID:", rs);
            return;
        }

        html += `<label onclick="selectProd(${rs.id})">${p}</label>`;
    });

    call(html);
}

function selectProd(id) {
    console.log("Selecionado ID:", id); // 🔥 Verifica se o ID está correto

    let lista_id = $('#lista_id').val();
    
    if (!id) {
        console.error("Erro: ID do produto indefinido.");
        swal("Erro", "Produto inválido!", "error");
        return;
    }

    $.get(path + 'produtos/autocompleteProduto', { id: id, lista_id: lista_id })
    .done((res) => {
        console.log("Resposta da API:", res); // 🔥 Verifica a resposta

        if (!res || typeof res !== 'object') {
            console.error("Erro: Resposta inválida da API.");
            swal("Erro", "Produto não encontrado!", "error");
            return;
        }

        PRODUTO = res;

        let p = PRODUTO.nome;
        if (PRODUTO.referencia && PRODUTO.referencia !== "") {
            p += ' | REF: ' + PRODUTO.referencia;
        }

        $('#valor_venda2').val(parseFloat(PRODUTO.valor_venda || 0).toFixed(2));
        $('#valor_compra2').val(parseFloat(PRODUTO.valor_compra || 0).toFixed(2));
        $('#produto-search').val(p);

        // 🔥 Armazena o ID do produto como atributo no input
        $('#produto-search').attr('data-produto-id', PRODUTO.id);

        // 🔥 Atualiza o código de barras corretamente
        if (PRODUTO.codBarras && PRODUTO.codBarras.trim() !== "") {
            $('#produto-search').attr('data-codBarras', PRODUTO.codBarras);
            $('#codBarras_atribuir').val(PRODUTO.codBarras);
            console.log("🔹 Código de Barras armazenado:", PRODUTO.codBarras);
        } else {
            $('#produto-search').attr('data-codBarras', 'SEM GTIN');
            $('#codBarras_atribuir').val('SEM GTIN');
            console.warn("⚠️ Produto sem código de barras, atribuindo 'SEM GTIN'");
        }
    })
    .fail((err) => {
        console.error("Erro na requisição AJAX:", err);
        swal("Erro", "Erro ao encontrar produto", "error");
    });

    $('.search-prod').css('display', 'none');
}

$('.selecionar-produto').click(function () {
    let id_produto = $(this).data('id');
    let codBarras = $(this).data('codbarras');
    let linha = $(this).data('linha'); // Pegando a linha associada

    console.log("Produto Selecionado:", id_produto);
    console.log("Código de Barras:", codBarras);
    console.log("Linha do Produto:", linha);

    // Agora podemos identificar o ID XML correto da linha específica
    let id_xml_elemento = $(`#linha_${linha} .codigo`).attr('id');
    console.log("Elemento ID XML encontrado:", id_xml_elemento);
});

// Função para recalcular o preço de venda com base no valor de compra e no percentual de lucro
function atualizarPrecoVenda() {
    let valorCompra = parseFloat($('#valorCompraEdit').val().replace(',', '.')) || 0;
    let percentualLucro = parseFloat($('#percentualLucroEdit').val().replace(',', '.')) || 0;

    console.log(`💰 Recalculando Novo Valor Venda - Valor Compra: ${valorCompra}, % Lucro: ${percentualLucro}`);

    if (valorCompra > 0) {
        let novoPrecoVenda = valorCompra * (1 + (percentualLucro / 100));
        $('#valorVendaEdit').val(novoPrecoVenda.toFixed(2).replace('.', ','));
    }
}

function atualizarMargemLucro() {
    // Remove tudo que não for dígito e trata o valor de compra
    let valorCompraStr = $('#valorCompraEdit').val().replace(/\D/g, '');
    let valorCompra = (valorCompraStr.length > 2) 
                      ? parseFloat(valorCompraStr) / 100 
                      : parseFloat(valorCompraStr);

    // Remove tudo que não for dígito e trata o valor de venda
    let valorVendaStr = $('#valorVendaEdit').val().replace(/\D/g, '');
    let novoValorVenda = (valorVendaStr.length > 2)
                         ? parseFloat(valorVendaStr) / 100
                         : parseFloat(valorVendaStr);

    console.log(`📊 Recalculando % Lucro - Valor Compra: ${valorCompra}, Novo Valor Venda: ${novoValorVenda}`);

    if (valorCompra > 0) {
        let percentualLucro = ((novoValorVenda - valorCompra) / valorCompra) * 100;
        $('#percentualLucroEdit').val(percentualLucro.toFixed(2).replace('.', ','));
    }
}

// Atualiza o valor de venda quando o campo valorCompraEdit for alterado
$('#valorCompraEdit').off('input').on('input', function() {
    atualizarPrecoVenda();
});

// Atualiza o valor de venda quando o percentual de lucro for alterado
$('#percentualLucroEdit').off('input').on('input', function() {
    atualizarPrecoVenda();
});

// Quando o valor de venda é alterado manualmente, atualiza a margem de lucro
$('#valorVendaEdit').off('input').on('input', function() {
    let valorVendaStr = $(this).val().replace(/\D/g, ''); // Remove tudo que não for dígito
    let valorVenda = parseFloat(valorVendaStr) / 100; // Ajusta as casas decimais

    let valorCompraStr = $('#valorCompraEdit').val().replace(/\D/g, '');
    let valorCompra = parseFloat(valorCompraStr) / 100;
    
    if (!isNaN(valorVenda) && !isNaN(valorCompra) && valorCompra > 0) {
        let percentualLucro = ((valorVenda - valorCompra) / valorCompra) * 100;
        console.log("Valor Venda Corrigido:", valorVenda.toFixed(2));
        console.log("Valor Compra:", valorCompra.toFixed(2));
        console.log("Margem Calculada:", percentualLucro.toFixed(2));
        $('#percentualLucroEdit').val(percentualLucro.toFixed(2).replace('.', ','));
    }
});

// Atualiza a margem de lucro se o usuário alterar manualmente o valor de venda
$('#valorVendaEdit').off("input").on("input", atualizarMargemLucro);

$("#conv_estoqueEdit").off("input").on("input", function() {
    let convEstoque = parseFloat($(this).val().replace(",", "."));
    if (isNaN(convEstoque) || convEstoque <= 0) {
        convEstoque = 1;
    }
    if (id_xml && safeCodBarras) {
        // Se tivermos id_xml, usamos os seletores baseados nele
        let selector = safeCodBarras;
        // Recupera a quantidade de compra da tela principal
        let quantidadeTexto = $(`#qtd_xml_${id_xml}_${selector}`).text().trim();
        let quantidadeNota = parseFloat(quantidadeTexto.replace(",", ".")) || 0;
        if (quantidadeNota > 10000) {
            console.warn(`⚠️ Quantidade muito alta (${quantidadeNota}), ajustando...`);
            quantidadeNota = quantidadeNota / 10000;
        }
        // Recupera o subtotal da nota da tela principal
        let subtotalTexto = $(`#tr_${id_xml}_${selector} td.quantidade span`).text().trim();
        let subtotalNota = parseFloat(subtotalTexto.replace(/\./g, "").replace(",", ".")) || 0;
        // Calcula o novo valor de compra
        let totalConversao = quantidadeNota * convEstoque;
        let novoValorCompra = (totalConversao > 0) ? (subtotalNota / totalConversao) : 0;
        console.log(`🔄 Recalculando Valor de Compra: Qtd: ${quantidadeNota}, Conv: ${convEstoque}, Subtotal: ${subtotalNota}, Novo Valor: ${novoValorCompra.toFixed(2)}`);
        $("#valorCompraEdit").val(novoValorCompra.toFixed(2).replace('.', ','));
        // Atualiza o fallback também
        $(`#th_prod_valor_compra_${produtoId}`).text(novoValorCompra.toFixed(2).replace('.', ','));
        atualizarPrecoVenda();
    } else {
        // Fallback: quando não há id_xml, utiliza seletores baseados no produtoId
        console.warn("⚠️ id_xml não disponível. Usando fallback baseado no produtoId.");
        // Supondo que você possua elementos na tela com esses IDs (adicione-os se necessário)
        let quantidadeTexto = $(`#th_qtd_compra_${produtoId}`).text().trim();
        let quantidadeNota = parseFloat(quantidadeTexto.replace(",", ".")) || 0;
        let subtotalTexto = $(`#th_subtotal_nota_${produtoId}`).text().trim();
        let subtotalNota = parseFloat(subtotalTexto.replace(/\./g, "").replace(",", ".")) || 0;
        let totalConversao = quantidadeNota * convEstoque;
        let novoValorCompra = (totalConversao > 0) ? (subtotalNota / totalConversao) : 0;
        console.log(`🔄 [Fallback] Recalculando Valor de Compra: Qtd: ${quantidadeNota}, Conv: ${convEstoque}, Subtotal: ${subtotalNota}, Novo Valor: ${novoValorCompra.toFixed(2)}`);
        $("#valorCompraEdit").val(novoValorCompra.toFixed(2).replace('.', ','));
        $(`#th_prod_valor_compra_${produtoId}`).text(novoValorCompra.toFixed(2).replace('.', ','));
        atualizarPrecoVenda();
    }
});

// Função unificada para recalcular os valores
$('.porcentagem_venda, .preco_venda, select.base_calculo').on('input change blur', function() {
    var $linha = $(this).closest('tr');
    
    // Obtém a base de cálculo selecionada: "bruto" ou "liquido"
    var baseCalc = $linha.find('select.base_calculo').val();
    
    // Recupera o custo com base na opção selecionada
    var custoText;
    if (baseCalc === 'bruto') {
        custoText = $linha.find('span.valor_bruto').text().trim();
    } else { 
        // Utiliza a célula 'span.valor' (custo líquido)
        custoText = $linha.find('span.valor').text().trim();
    }
    // Converte o valor para número, tratando a formatação brasileira (ex: 1.234,56)
    var custo = parseFloat(custoText.replace(/\./g, '').replace(',', '.')) || 0;
    
    // Identifica de qual elemento veio o evento
    // Se for o input de porcentagem, recalcula o preço de venda
    if ($(this).hasClass('porcentagem_venda')) {
        var percentual = parseFloat($(this).val().replace(',', '.')) || 0;
        if (custo > 0) {
            var novoPreco = custo * (1 + (percentual / 100));
            $linha.find('input.preco_venda').val(novoPreco.toFixed(2));
        }
    }
    // Se for o input de preço, recalcula a porcentagem
    else if ($(this).hasClass('preco_venda')) {
        var preco = parseFloat($(this).val().replace(',', '.')) || 0;
        if (custo > 0) {
            var novoPercentual = ((preco / custo) - 1) * 100;
            $linha.find('input.porcentagem_venda').val(novoPercentual.toFixed(2));
        }
    }
    // Se for o select de base de cálculo, recalcule o preço (usando o valor atual da porcentagem)
    else if ($(this).is('select.base_calculo')) {
        var percentualAtual = parseFloat($linha.find('input.porcentagem_venda').val().replace(',', '.')) || 0;
        if (custo > 0) {
            var novoPreco = custo * (1 + (percentualAtual / 100));
            $linha.find('input.preco_venda').val(novoPreco.toFixed(2));
        }
    }
});
 
// Opcional: Atualiza o registro no backend quando os campos perdem o foco
$('.porcentagem_venda, .preco_venda').on('blur', function(){
   var $linha = $(this).closest('tr');
   var produtoId = $linha.find('td.cod').text().trim();
   var novaPorcentagem = $linha.find('input.porcentagem_venda').val().trim();
   var novoPrecoVenda = $linha.find('input.preco_venda').val().trim();
   
   $.ajax({
       url: path + 'compraFiscal/atualizarPrecoVenda',
       type: 'POST',
       data: {
           produto_id: produtoId,
           porcentagem_venda: novaPorcentagem,
           preco_venda: novoPrecoVenda,
           _token: $('#_token').val()
       },
       success: function(response) {
           console.log("Dados atualizados com sucesso!", response);
       },
       error: function(err) {
           console.error("Erro ao atualizar os valores:", err);
       }
   });
});

$(document).ready(function() {
    // Para cada linha, mantenha o valor original do campo preco_venda_atual, que já veio do cadastro
    $('.produto-linha').each(function() {
        var $linha = $(this);
        // Aqui, pegamos o valor definido no HTML (atributo value)
        var precoOriginal = $linha.find('input.preco_venda_atual').attr('value');
        $linha.find('input.preco_venda_atual').val(precoOriginal);
    });
    
    // Remova qualquer binding para tentar alterar o campo readonly
    $('input.preco_venda_atual').off('input change blur');
});

function recalcularPrecoVenda($linha) {
    // Obtém a base de cálculo selecionada: "bruto" ou "liquido"
    var baseCalc = $linha.find('select.base_calculo').val();
    var custo = 0, custoText;

    if (baseCalc === 'bruto') {
        // Lê o custo bruto do span "valor_bruto"
        custoText = $linha.find('span.valor_bruto').text().trim();
        custo = parseFloat(custoText.replace(/\./g, '').replace(',', '.')) || 0;
    } else {
        // Para "líquido", o custo é definido como:
        // valor_bruto + valor_ipi + outras_despesas + substituicao_tributaria + valor_Seguro - desconto_unit
        
        function parseValor(selector) {
            var txt = $linha.find(selector).text().trim();
            return parseFloat(txt.replace(/\./g, '').replace(',', '.')) || 0;
        }
        
        var bruto = parseValor('span.valor_bruto');
        var ipi = parseValor('span.valor_ipi');
        var outras = parseValor('span.outras_despesas');
        var substituicao = parseValor('span.substituicao_tributaria');
        var seguro = parseValor('span.valor_Seguro');
        var desconto = parseValor('span.desconto_unit');
        
        custo = bruto + ipi + outras + substituicao + seguro - desconto;
        
        // Se quiser, pode exibir os valores para depuração:
        console.log("Cálculo para 'líquido':", {
            bruto: bruto,
            ipi: ipi,
            outras: outras,
            substituicao: substituicao,
            seguro: seguro,
            desconto: desconto,
            custoLiquido: custo
        });
    }
    
    // Pega o percentual atual do input de porcentagem
    var percentualText = $linha.find('input.porcentagem_venda').val().trim();
    var percentual = parseFloat(percentualText.replace(',', '.')) || 0;
    
    // Calcula o novo preço de venda: Preço = custo * (1 + percentual/100)
    var novoPreco = custo * (1 + (percentual / 100));
    $linha.find('input.preco_venda').val(novoPreco.toFixed(2));
}

$(document).ready(function() {
    // Ao carregar a página, percorre cada linha e recalcula o preço de venda
    $('.produto-linha').each(function() {
        var $linha = $(this);
        recalcularPrecoVenda($linha);
    });
    
    // Atualiza o preço de venda editável conforme o usuário altera a porcentagem ou a base de cálculo
    $('.porcentagem_venda, select.base_calculo').on('input change blur', function() {
        var $linha = $(this).closest('tr');
        recalcularPrecoVenda($linha);
    });
});

function atualizarTodosProdutos() {
    var produtosArray = [];
    // Obtém os valores globais definidos na área de atualização em massa
    var novaBase = $('#update_base_calculo').val();
    var novoPercentual = $('#update_percentual_venda').val();

    // Percorre cada linha de produto na tabela
    $('.produto-linha').each(function() {
        var $linha = $(this);
        // Atualiza o select e o input de porcentagem conforme a escolha global
        $linha.find('select.base_calculo').val(novaBase);
        $linha.find('input.porcentagem_venda').val(novoPercentual);
        
        // Recalcula o preço de venda (a função já atualiza o campo editável "preco_venda")
        recalcularPrecoVenda($linha);
        
        // Prepara os dados para atualização no banco
        var produtoId = $linha.find('td.cod').text().trim();
        var porcentagemVenda = $linha.find('input.porcentagem_venda').val().trim();
        var precoVenda = $linha.find('input.preco_venda').val().trim();
        
        produtosArray.push({
            produto_id: produtoId,
            porcentagem_venda: porcentagemVenda,
            preco_venda: precoVenda
        });
    });
    
    // Envia os dados coletados via AJAX para o endpoint de atualização em massa
    $.ajax({
        url: path + 'compraFiscal/atualizarPrecoVendaEmMassa',
        type: 'POST',
        data: {
            produtos: produtosArray,
            empresa_id: $('#empresa_id').val(), // se necessário
            _token: $('#_token').val()
        },
        success: function(response) {
            console.log("Todos os produtos foram atualizados com sucesso!", response);
            alert("Produtos atualizados com sucesso!");
        },
        error: function(err) {
            console.error("Erro na atualização em massa:", err);
            alert("Houve um erro ao atualizar os produtos.");
        }
    });
}

if (parseInt('{{ $dadosNf["contSemRegistro"] }}') === 0) {
    // Habilita os campos
    $('#update_base_calculo, #update_percentual_venda, button[onclick="atualizarTodosProdutos()"]').removeAttr('disabled');
    $('.produto-linha select.base_calculo, .produto-linha input.porcentagem_venda, .produto-linha input.preco_venda').removeAttr('disabled');
}

function detalharCusto(element) {
    var $linha = $(element).closest('tr');
    
    // Produto (exemplo: se tiver um span com a classe "nome")
    var nomeProduto = $linha.find('span.nome').text().trim();
    $('#dc_produto_nome').val(nomeProduto);
    
    // valor_bruto
    var vb = $linha.find('span.valor_bruto').text().trim();
    $('#dc_valor_bruto').val(vb);
    
    // valor_ipi
    var vi = $linha.find('span.valor_ipi').text().trim();
    $('#dc_valor_ipi').val(vi);
    
    // outras_despesas
    var od = $linha.find('span.outras_despesas').text().trim();
    $('#dc_outras_despesas').val(od);
    
    // substituicao_tributaria
    var st = $linha.find('span.substituicao_tributaria').text().trim();
    $('#dc_substituicao_tributaria').val(st);
    
    // valor_Seguro
    var vs = $linha.find('span.valor_Seguro').text().trim();
    $('#dc_valor_seguro').val(vs);
    
    // desconto_unit
    var du = $linha.find('span.desconto_unit').text().trim();
    $('#dc_desconto_unit').val(du);
    
    // custo_liquido (span.valor)
    var cl = $linha.find('span.valor').text().trim();
    $('#dc_custo_liquido').val(cl);
    
    // Abre o modal
    $('#modalDetalhamento').modal('show');
}

$('#modalDetalhamento').on('shown.bs.modal', function () {
    let scrollTop = $(window).scrollTop(); // quanto a página já foi rolada
    let windowHeight = $(window).height();
    let modalHeight = $('#modalDetalhamento .modal-dialog').outerHeight();
    let newTop = scrollTop + (windowHeight - modalHeight) / 2;

    // Atualiza a posição do modal dinamicamente
    $('#modalDetalhamento .modal-dialog').css('top', newTop > 0 ? newTop : 50);
});
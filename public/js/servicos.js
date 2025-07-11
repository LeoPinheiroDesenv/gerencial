$(function(){
	setTimeout(() => {
		$("#kt_select2_3").select2({
			minimumInputLength: 2,
			language: "pt-BR",
			placeholder: "Digite para buscar o produto",
			width: "100%",
			escapeMarkup: function (markup) {
				return markup; // Permite renderizar HTML
			},
			templateResult: function (data) {
				if (!data.id) {
					return data.text;
				}

				let precoNormal = parseFloat(data.valor_venda).toFixed(2).replace(".", ",");
				let precoPromo = data.preco_promocional ? parseFloat(data.preco_promocional).toFixed(2).replace(".", ",") : null;

				let precoFinal = precoPromo
					? `<del style="color:gray; margin-right: 8px;">R$ ${precoNormal}</del> <span style="color:red; font-weight:bold;"> R$ ${precoPromo} (Promoção!)</span>`
					: `<span>R$ ${precoNormal}</span>`;

				let markup = `
					<div style="display:flex; flex-direction:column;">
						<span>${data.nome} ${data.grade ? " " + data.str_grade : ""}</span>
						<span>${precoFinal}</span>
						${data.referencia ? `<span style="font-size:12px; color:gray;">Ref: ${data.referencia}</span>` : ""}
						${data.estoqueAtual > 0 ? `<span style="font-size:12px; color:green;">Estoque: ${data.estoqueAtual}</span>` : ""}
					</div>
				`;

				return $(markup);
			},
			templateSelection: function (data) {
				if (!data.id) {
					return data.text;
				}

				let precoPromo = data.preco_promocional ? ` <span style="color:red; font-weight:bold;">(Promoção!)</span>` : "";

				return `${data.nome} ${data.grade ? " " + data.str_grade : ""} | R$ ${parseFloat(data.valor_venda).toFixed(2).replace(".", ",")}${precoPromo}`;
			},
			ajax: {
				cache: true,
				url: path + 'produtos/autocomplete',
				dataType: "json",
				data: function(params) {
					console.clear();
					let filial = $('#filial').val();
					let lista_id = null; // Pode ser ajustado conforme necessário

					return {
						pesquisa: params.term,
						filial_id: filial,
						lista_id: lista_id
					};
				},
				processResults: function(response) {
					var results = [];

					$.each(response, function(i, v) {
						let obj = {
							id: v.id,
							nome: v.nome,
							valor_venda: v.valor_venda,
							preco_promocional: v.preco_promocional,
							referencia: v.referencia,
							estoqueAtual: v.estoqueAtual,
							grade: v.grade,
							str_grade: v.str_grade
						};
						results.push(obj);
					});

					return {
						results: results
					};
				}
			}
		});

		$('.select2-selection__arrow').addClass('select2-selection__arroww')
		$('.select2-selection__arrow').removeClass('select2-selection__arrow')
	}, 100);
});


$("#kt_select2_3").change(() => {
	let id = $("#kt_select2_3").val();
	if(id){
		$.get(path + 'produtos/autocompleteProduto', {id: id, lista_id: null})
		.done((res) => {
			if (!res) {
				swal("Erro", "Produto não encontrado!", "error");
				return;
			}

			let precoNormal = parseFloat(res.valor_venda).toFixed(casas_decimais).replace(".", ",");
			let precoPromo = res.preco_promocional ? parseFloat(res.preco_promocional).toFixed(casas_decimais).replace(".", ",") : null;

			let precoFinal = precoPromo ? precoPromo : precoNormal;

			console.log("Preço final aplicado:", precoFinal);

			$('#valor_prod').val(precoFinal);
			$('.qtd').val('1');
		})
		.fail((err) => {
			console.log(err);
			swal("Erro", "Erro ao encontrar produto", "error");
		});
	}
});


$("#kt_select2_1").change(function() {
	let opt = $(this).find(":selected")
	let vl = $(this).closest('div').next().find('input')
	let qtd = $(this).closest('div').next().next().find('input')
	let v = opt.data('value')+""
	vl.val(v.replace(".", ","))
	qtd.val(1)
})



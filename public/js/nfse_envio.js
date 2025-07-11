
function redireciona(){
	location.href= path + "cteos";
}
var EMITINDO = false;

function validaBtns(){
	let cont = 0;
	let estado = "";

	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			estado = $(this).find('.estado_'+id).html();
			cont++;
		}
	})

	if(cont > 1 || cont == 0){
		desabilitaBotoes(cont);
	}else{
		habilitaBotoes();

		if(estado == 'novo'){
			$('#btn-enviar').removeClass("disabled");
			$('#btn-imprimir').addClass("disabled");
			$('#btn-consultar').addClass("disabled");
			$('#btn-cancelar').addClass("disabled");
			$('#btn-xml').addClass("disabled");
		} else if(estado == 'rejeitado'){
			$('#btn-enviar').removeClass("disabled");
			$('#btn-imprimir').addClass("disabled");
			$('#btn-consultar').removeClass("disabled");
			$('#btn-cancelar').addClass("disabled");
			$('#btn-xml').addClass("disabled")
		} else if(estado == 'cancelado'){
			$('#btn-enviar').addClass("disabled");
			$('#btn-imprimir').addClass("disabled");
			$('#btn-consultar').removeClass("disabled");
			$('#btn-cancelar').addClass("disabled");
			$('#btn-xml').addClass("disabled");
		} else if(estado == 'aprovado'){
			$('#btn-enviar').addClass("disabled");
			$('#btn-imprimir').removeClass("disabled");
			$('#btn-consultar').removeClass("disabled");
			$('#btn-cancelar').removeClass("disabled");
			$('#btn-xml').removeClass("disabled");
		} else if(estado == 'processando'){
			$('#btn-enviar').addClass("disabled");
			$('#btn-imprimir').addClass("disabled");
			$('#btn-consultar').removeClass("disabled");
			$('#btn-cancelar').addClass("disabled");
			$('#btn-xml').addClass("disabled");
		}

	}
}

function desabilitaBotoes(cont){
	$('#btn-enviar').addClass("disabled");
	$('#btn-imprimir').addClass("disabled");
	$('#btn-consultar').addClass("disabled");
	$('#btn-cancelar').addClass("disabled");
	$('#btn-xml').addClass("disabled");
	$('#btn-imprimir-cancelar').addClass("disabled");
}

function habilitaBotoes(){
	$('#btn-enviar').removeClass("disabled");
	$('#btn-imprimir').removeClass("disabled");
	$('#btn-consultar').removeClass("disabled");
	$('#btn-cancelar').removeClass("disabled");
	$('#btn-xml').removeClass("disabled");
	$('#btn-imprimir-cancelar').removeClass("disabled");
}

function enviar(){

	if(!EMITINDO){
		EMITINDO = true;
		$('#btn-enviar').addClass('spinner');
		$('#btn-enviar').addClass('disabled');
		let id = 0
		$('#body tr').each(function(){
			if($(this).find('#checkbox input').is(':checked'))
				id = $(this).find('#id').html();
		})

		let token = $('#_token').val();
		$.ajax
		({
			type: 'POST',
			data: {
				id: id,
				_token: token
			},
			url: path + 'nfse/enviar',
			dataType: 'json',
			success: function(e){
				console.log(e)
				EMITINDO = false;
				$('#btn-enviar').removeClass('spinner');
				$('#btn-enviar').removeClass('disabled');

				swal("Sucesso", "NFSe gerada com sucesso, código de verificação: " + e.codigo_verificacao, "success")
				.then(() => {
					window.open(e.pdf_nfse)
					setTimeout(() => {
						location.reload()
					}, 100)
				})

			}, error: function(e){
				$('#btn-enviar').removeClass('spinner');
				$('#btn-enviar').removeClass('disabled');

				EMITINDO = false;
				console.log(e)

				if(e.status == 401){
					let json = e.responseJSON
					let link_xml = json.xml
					console.log("link_xml", link_xml)
					// swal("Algo deu errado", json.motivo[0] + " xml: " + link_xml, "error")

					let motivo = Array.isArray(json.motivo) ? json.motivo[0] : json.motivo

					let icon = "error"
					let title = "Algo deu errado"
					if(motivo == "Lote enviado para processamento"){
						icon = "warning"
						title = "Aguarde"
					}
					swal({
						title: title,
						text: motivo,
						icon: icon,
						buttons: ["Fechar", 'Ver XML'],
						dangerMode: true,
					})
					.then((v) => {
						if (v) {
							if(link_xml){
								window.open(link_xml, '_blank');
							}else{
								swal("Erro", "Não existe nenhum XML para visualizar", "error")
							}
						} else {
						}
						location.reload()

					});
				}else{
					swal("Algo deu errado", e.responseJSON, "error")
					// location.reload()
					
				}

			}
		});
	}
	
}

function transmitirNfse(id){
	if(!EMITINDO){
		EMITINDO = true
		$('#btn_transmitir_grid_'+id).addClass('spinner')
		$('#btn_transmitir_grid_'+id).addClass('disabled')
		let token = $('#_token').val();
		$.ajax
		({
			type: 'POST',
			data: {
				id: id,
				_token: token
			},
			url: path + 'nfse/enviar',
			dataType: 'json',
			success: function(e){
				console.log(e)
				EMITINDO = false;
				$('#btn-enviar').removeClass('spinner');
				$('#btn-enviar').removeClass('disabled');

				swal("Sucesso", "NFSe gerada com sucesso, código de verificação: " + e.codigo_verificacao, "success")
				.then(() => {
					window.open(e.pdf_nfse)
				})

			}, error: function(e){
				EMITINDO = false;
				console.log(e)

				if(e.status == 401){
					let json = e.responseJSON
					swal("Algo deu errado", json.motivo[0], "error")
				}else{
					swal("Algo deu errado", e.responseJSON, "error")
				}

				$('#btn-enviar').removeClass('spinner');
				$('#btn-enviar').removeClass('disabled');

			}
		});
	}
}


function imprimir(){
	let id = 0;
	let cont = 0;
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++
		}
	})

	if(cont > 1){
		swal("Erro", "Selecione apenas um documento para impressão!", "error")

	}else{
		window.open(path+"nfse/imprimir/"+id, "_blank");
	}
}

function imprimirCCe(){
	let id = 0;
	let cont = 0;
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++
		}
	})

	if(cont > 1){
		swal("Erro", "Selecione apenas um documento para impressão!", "error")

	}else{
		window.open(path+"cteos/imprimirCCe/"+id, "_blank");
	}
}

function imprimirCancela(){
	let id = 0;
	let cont = 0;
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++
		}
	})

	if(cont > 1){
		swal("Erro", "Selecione apenas um documento para impressão!", "error")

	}else{
		window.open(path+"cteos/imprimirCancela/"+id, "_blank");
	}
}

function consultar(){
	let id = 0;
	let cont = 0;
	$('#btn-consultar').addClass('spinner')
	$('#btn-consultar').addClass('disabled')
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++;
		}
	})

	if(cont > 1){
		swal("Erro", "Selecione apenas um documento para consultar!", "error")

	}else{
		let token = $('#_token').val();
		$.ajax
		({
			type: 'POST',
			data: {
				id: id,
				_token: token
			},
			url: path + 'nfse/consultar',
			dataType: 'json',
			success: function(e){
				let js = JSON.parse(e)
				console.log(js)

				swal("Sucesso", js.motivo, "success").then(() => {
					location.reload()
				})

				$('#btn-consultar').removeClass('spinner')
				$('#btn-consultar').removeClass('disabled')
				
			}, error: function(e){
				console.log(e)
				try{
					swal("Erro", e.responseJSON, "error").then(() => {
						location.reload()
					})
				}catch{
					swal("Erro", "Erro consulte o console", "error")
				}
				$('#btn-consultar').removeClass('spinner')
				$('#btn-consultar').removeClass('disabled')
			}
		});
	}
}

function cancelarCTe(id, nf){
	$('#modal1_aux').modal('show')
	$('#numero_cancelamento2').html(nf)
	$('#id_cancela').val(id)
}

function corrigirCTe(id, nf){
	$('#modal4_aux').modal('show')
	$('#numero_correcao_aux').html(nf)
	$('#id_correcao').val(id)
}

function consultarCTe(id){

	$('#btn_consulta_grid_'+id).addClass('spinner')
	$('#btn_consulta_grid_'+id).addClass('disabled')
	let token = $('#_token').val();
	$.ajax
	({
		type: 'POST',
		data: {
			id: id,
			_token: token
		},
		url: path + 'cteos/consultar',
		dataType: 'json',
		success: function(e){
			let js = JSON.parse(e)

			$('#btn_consulta_grid_'+id).removeClass('spinner')
			$('#btn_consulta_grid_'+id).removeClass('disabled')
			swal("Sucesso", "Status: " + js.xMotivo + " - chave: " + js.protCTe.infProt.chCTe + ", protocolo: " + js.protCTe.infProt.nProt, "success")

		}, error: function(e){
			console.log(e)
			swal("Erro", "Erro consulte o console", "error")
			$('#btn_consulta_grid_'+id).removeClass('spinner')
			$('#btn_consulta_grid_'+id).removeClass('disabled')
		}
	});

}

function setarNumero(buscarCliente = false){
	let id = 0;
	let nf = 0;
	let cont = 0;
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			nf = $(this).find('#numero_nfse').html();
			id = $(this).find('#id').html();
			$('#numero_cancelamento').html(nf)
			$('#numero_email').html(nf)
			$('#numero_correcao').html(nf)
			$('#numero_nf').html(nf)
			$('#numero_cte').val(nf)

			cont++;
		}
	})
	
	if(cont > 1){
		Materialize.toast('Selecione apenas um documento para continuar!', 5000)
	}
}

function buscarDadosCliente(){
	let id = 0;
	let cont = 0;

	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++;
		}
	})

	if(cont > 1){
		Materialize.toast('Selecione apenas um documento para continuar!', 5000)
	}else{

		$.get(path+'cteos/consultar_cliente/'+id)
		.done(function(data){
			data = JSON.parse(data)

			$('#email').val(data.email)
			$('#venda_id').val(id)

			if(data.email){
				$('#info-email').html('*Este é o email do cadastro');
			}else{
				$('#info-email').html('*Este cliente não possui email cadastrado');
			}
		})
		.fail(function(err){
			console.log(err)
		})
	}
}

function cancelar(){
	$('#btn-cancelar-2').addClass('spinner')
	$('#btn-cancelar-2').addClass('disabled')
	let id = 0;
	let cont = 0;
	let motivo = $('#motivo').val();
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++;
		}
	})

	if(cont > 1){
		Materialize.toast('Selecione apenas um documento para cancelar!', 5000)
	}else{
		let token = $('#_token').val();
		$.ajax({
			type: 'POST',
			data: {
				id: id,
				motivo: motivo,
				_token: token
			},
			url: path + 'nfse/cancelar',
			dataType: 'json',
			success: function(e){
				$('#btn-cancelar-2').removeClass('spinner')
				$('#btn-cancelar-2').removeClass('disabled')
				console.log(e)

				try{
					let js = JSON.parse(e);
					if(js.msg == 'Erro ao cancelar NFS-e'){
						console.log(js)
						swal("Algo deu errado", js.errors[0], "error")
					}else{
						// Fechar o modal de cancelamento
						$('#modalCancelamento').modal('hide');
						
						// Mostrar a mensagem de sucesso
						swal("Sucesso", js.motivo, "success")
						.then(() => {
							// Atualizar a página
							location.reload();  // Atualiza a página inteira
						})
					}
				}catch{
					swal("Algo deu errado", e, "error")
				}
			}, error: function(e){
				console.log(e)
				$('#btn-cancelar-2').removeClass('spinner')
				$('#btn-cancelar-2').removeClass('disabled')
				swal("Erro", "Erro ao tentar cancelar o documento!", "error")
			}
		});
	}
}


function cancelar2(){
	$('#btn-cancelar-3').addClass('spinner');
	$('#btn-cancelar-3').addClass('disabled');
	let id = $('#id_cancela').val();
	let token = $('#_token').val();
	let justificativa = $('#justificativa2').val();
	$.ajax({
		type: 'POST',
		data: {
			id: id,
			justificativa: justificativa,
			_token: token
		},
		url: path + 'cteos/cancelar',
		dataType: 'json',
		success: function(e){
			let js = JSON.parse(e);

			$('#btn-cancelar-3').removeClass('spinner');
			$('#btn-cancelar-3').removeClass('disabled');

			if(js.infEvento.cStat == '101' || js.infEvento.cStat == '135' || js.infEvento.cStat == '155'){
				swal("Sucesso", js.infEvento.xMotivo, "success")
				.then(() => {
					// Fechar o modal de cancelamento
					$('#modalCancelamento2').modal('hide');
					
					// Abrir o link para impressão em uma nova janela
					window.open(path+"cteos/imprimirCancela/"+id, "_blank");
					
					// Atualizar a página
					location.reload();
				});
			}else{
				swal("Erro", js.infEvento.xMotivo, "error");
			}

		}, error: function(e){
			console.log(e);
			$('#btn-cancelar-3').removeClass('spinner');
			$('#btn-cancelar-3').removeClass('disabled');
			swal("Erro", "Erro, veja o console do navegador", "error");
		}
	});
}

function reload(){
	location.reload();
}

function cartaCorrecao(){
	$('#btn-corrigir-2').addClass('spinner');
	$('#btn-corrigir-2').addClass('disabled');

	let id = 0;
	let cont = 0;

	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++;
		}
	})

	if(cont > 1){
		Materialize.toast('Selecione apenas um documento para continuar!', 5000)
	}else{
		let token = $('#_token').val();
		$.ajax
		({
			type: 'POST',
			data: {
				id: id,
				correcao: $('#correcao').val(),
				grupo: $('#grupo').val(),
				campo: $('#campo').val(),
				_token: token
			},
			url: path + 'cteos/cartaCorrecao',
			dataType: 'json',
			success: function(e){

				try{
					let js = JSON.parse(e);

					$('#btn-corrigir-2').removeClass('spinner');
					$('#btn-corrigir-2').removeClass('disabled');
					if(js.infEvento.cStat == '135'){
						swal("Sucesso", js.infEvento.xMotivo, "success")
						.then(() => {
							window.open(path+"cteos/imprimirCCe/"+id, "_blank");
							location.reload();
						})
					}else{
						swal("Erro", js.infEvento.xMotivo, "error")
					}
				}catch{
					swal("Erro", e, "error")
				}
				$('#btn-corrigir-2').removeClass('spinner');
				$('#btn-corrigir-2').removeClass('disabled');
			}, error: function(e){
				console.log(e)
				swal("Erro", "Consulte o console do navegador!", "error")
				$('#btn-corrigir-2').removeClass('spinner');
				$('#btn-corrigir-2').removeClass('disabled');
			}
		});
	}
}

function cartaCorrecaoAux(){
	$('#btn-corrigir-3').addClass('spinner');
	$('#btn-corrigir-3').addClass('disabled');

	let id = $('#id_correcao').val()
	
	let token = $('#_token').val();

	let js = {
		id: id,
		correcao: $('#correcao2').val(),
		grupo: $('#grupo2').val(),
		campo: $('#campo2').val(),
		_token: token
	}

	$.ajax
	({
		type: 'POST',
		data: js,
		url: path + 'cteos/cartaCorrecao',
		dataType: 'json',
		success: function(e){

			let js = JSON.parse(e);

			$('#btn-corrigir-3').removeClass('spinner');
			$('#btn-corrigir-3').removeClass('disabled');

			if(js.infEvento.cStat == '135'){
				swal("Sucesso", js.infEvento.xMotivo, "success")
				.then(() => {
					window.open(path+"cteos/imprimirCCe/"+id, "_blank");
					location.reload()
				})
			}else{
				swal("Erro", js.infEvento.xMotivo, "error")

			}

		}, error: function(e){
			console.log(e)
			swal("Erro", "Consulte o console do navegador!", "error")
			$('#btn-corrigir-3').removeClass('spinner');
			$('#btn-corrigir-3').removeClass('disabled');
		}
	});
	
}

function inutilizar(){

	let justificativa = $('#justificativa-inut').val();
	let nInicio = $('#nInicio').val();
	let nFinal = $('#nFinal').val();
	
	$('#btn-inut-2').addClass('spinner');
	$('#btn-inut-2').addClass('disabled');


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
		url: path + 'cteos/inutilizar',
		dataType: 'json',
		success: function(js){

			$('#btn-inut-2').removeClass('spinner');
			$('#btn-inut-2').removeClass('disabled');

			console.log(js.infInut.cStat)
			if(js.infInut.cStat == '102' || js.infInut.cStat == '135' || js.infInut.cStat == '155'){

				swal("Sucesso", js.infInut.xMotivo, "success")
				.then(() => {
					$('#modal3').modal('hide')
				})
				
			}else{

				swal("Erro", "[" + js.infInut.cStat + "] - " + js.infInut.xMotivo, "error")

			}

		}, error: function(e){
			console.log(e)
			swal("Erro", "Consulte o console do navegador!", "error")

			$('#btn-inut-2').removeClass('spinner');
			$('#btn-inut-2').removeClass('disabled');
		}
	});
	
}

$(function () {
	validaBtns();
	var w = window.innerWidth
	if(w < 900){
		$('#grade').trigger('click')
	}
})

$('#checkbox input').click(() => {
	validaBtns();
})

function enviarEmailXMl(){
	$('#btn-send').addClass('spinner')
	$('#btn-send').addClass('disabled')
	let id = 0;
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked'))
			id = $(this).find('#id').html();
	})

	let email = $('#email').val();

	$.get(path+'nfse/enviarXml', {id: id, email: email})
	.done(function(data){

		$('#btn-send').removeClass('spinner')
		$('#btn-send').removeClass('disabled')
		// alert('Email enviado com sucesso!');
		swal("Sucesso", "Email enviado com sucesso!", "success")
		.then(() => {
			$('#modal5').modal('hide')
		})

	})
	.fail(function(err){
		console.log(err)
		$('#btn-send').removeClass('spinner')
		$('#btn-send').removeClass('disabled')
		// alert('Erro ao enviar email!')
		swal("Erro", "Erro ao enviar email!", "error")

	})
}



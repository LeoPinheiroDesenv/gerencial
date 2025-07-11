<?php

namespace App\Helpers;
use App\Models\Usuario;
use App\Models\Tributacao;

class Menu {

	protected $menu;
	public function __construct(){
		$value = session('user_logged');
		$tributacao = null;
		if($value){
			$empresa_id = $value['empresa'];
			$tributacao = Tributacao::
			where('empresa_id', $empresa_id)
			->first();
		}
		$this->menu = [
			[
				'titulo' => 'Cadastros',
				'icone' => $this->getIcone('Cadastros'),
				'subs' => [
					[
						'nome' => 'Categorias de produtos',
						'rota' => '/categorias'
					],
					
					[
						'nome' => 'Produtos',
						'rota' => '/produtos'
					],
					[
						'nome' => 'Marcas',
						'rota' => '/marcas'
					],
					[
						'nome' => 'Clientes',
						'rota' => '/clientes'
					],
					[
						'nome' => 'Grupos de cliente',
						'rota' => '/gruposCliente'
					],
										[
						'nome' => 'Cashback',
						'rota' => '/cashback-config'
					],
					[
						'nome' => 'Assessor',
						'rota' => '/acessores'
					],

					[
						'nome' => 'Fornecedores',
						'rota' => '/fornecedores'
					],
					[
						'nome' => 'Transportadoras',
						'rota' => '/transportadoras'
					],
					// [
					// 	'nome' => 'Funcionários',
					// 	'rota' => '/funcionarios'
					// ],
					[
						'nome' => 'Categorias de serviços',
						'rota' => '/categoriasServico'
					],
					[
						'nome' => 'Serviços',
						'rota' => '/servicos'
					],
					[
						'nome' => 'Lista de preços',
						'rota' => '/listaDePrecos'
					],
				    [
						'nome' => 'Lista de Promoções',
						'rota' => '/listapromocao'
					],
					[
						'nome' => 'Categorias de contas',
						'rota' => '/categoriasConta'
					],
					[
						'nome' => 'Veiculos',
						'rota' => '/veiculos'
					],
					[
						'nome' => 'Contas bancárias',
						'rota' => '/contaBancaria'
					],
					

				    [
						'nome' => 'Contas financeiras',
					 	'rota' => '/contaFinanceira'
				    ],
					[
						'nome' => 'Formas de pagamento',
						'rota' => '/formasPagamento'
					],
					[
						'nome' => 'Taxas de pagamento',
						'rota' => '/taxas-pagamento'
					],
					[
						'nome' => 'Usuários',
						'rota' => '/usuarios'
					]
				]
			],
			[
				'titulo' => 'Entradas',
				'icone' => $this->getIcone('Entradas'),
				'subs' => [
					[
						'nome' => 'Importação de XML',
						'rota' => '/compraFiscal'
					],
					[
						'nome' => 'Compra manual',
						'rota' => '/compraManual'
					],
					[
						'nome' => 'Compras',
						'rota' => '/compras'
					],
					[
						'nome' => 'Cotações',
						'rota' => '/cotacao'
					],
					[
						'nome' => 'Manifesto',
						'rota' => '/dfe'
					]
				]
			],

			[
				'titulo' => 'Estoque',
				'icone' => $this->getIcone('Estoque'),
				'subs' => [
					[
						'nome' => 'Ajuste de estoque',
						'rota' => '/estoque'
					],
					[
						'nome' => 'Apontameto de produçao',
						'rota' => '/estoque/apontamentoProducao'
					],
					[
						'nome' => 'Inventário',
						'rota' => '/inventario'
					],
					[
						'nome' => 'Transferencia de estoque',
						'rota' => '/transferencia'
					]
				]
			],

			[
				'titulo' => 'Vendas',
				'icone' => $this->getIcone('Vendas'),
				'subs' => [

					[
						'nome' => 'Caixa',
						'rota' => '/caixa'
					],
					[
						'nome' => 'Vendas',
						'rota' => '/vendas'
					],
					[
						'nome' => 'Nova venda',
						'rota' => '/vendas/nova'
					],
					[
						'nome' => 'Frente de caixa',
						'rota' => '/frenteCaixa'
					],
					[
						'nome' => 'Vendas PDV',
						'rota' => '/frenteCaixa/list'
					],
					[
						'nome' => 'Devolução PDV',
						'rota' => '/frenteCaixa/devolucao'
					],

					[
						'nome' => 'Pré venda',
						'rota' => '/frenteCaixa/prevenda'
					],
					[
						'nome' => 'Novo orçamento',
						'rota' => '/orcamentoVenda/create'
					],
					[
						'nome' => 'Orçamentos',
						'rota' => '/orcamentoVenda'
					],
					[
						'nome' => 'Ordem de serviço',
						'rota' => '/ordemServico'
					],
					// [
					// 	'nome' => 'Emissão de NFSe',
					// 	'rota' => '/nfse'
					// ],
					// [
					// 	'nome' => 'Conta crédito',
					// 	'rota' => '/vendasEmCredito'
					// ],
					
					[
						'nome' => 'Devolução NFe',
						'rota' => '/devolucao'
					],
					[
						'nome' => 'Agendamentos',
						'rota' => '/agendamentos'
					],
					[
						'nome' => 'Importação',
						'rota' => '/vendas/importacao'
					],

					[
						'nome' => 'Trocas/Devoluções',
						'rota' => '/trocas'
					],
					
					[
						'nome' => 'Emissão de NFe',
						'rota' => '/nferemessa'
					],
					// [
					// 	'nome' => 'NFS-e',
					// 	'rota' => $tributacao != null ? $tributacao->link_nfse : 'sss',
					// 	'target' => true
					// ]
				]
			],

			//[
			//	'titulo' => 'Vendas Balcão',
			//	'icone' => $this->getIcone('VendaBalcao'),
			//	'subs' => [
			//		[
			//			'nome' => 'Nova Venda',
			//			'rota' => '/vendas-balcao/create'
			//		],
			//		[
			//			'nome' => 'Caixa Recebimento',
			//			'rota' => '/vendas-balcao'
			//		]
			//	]
			//],

			[
				'titulo' => 'Financeiro',
				'icone' => $this->getIcone('Financeiro'),
				'subs' => [
					[
						'nome' => 'Contas a pagar',
						'rota' => '/contasPagar'
					],
					[
						'nome' => 'Contas a receber',
						'rota' => '/contasReceber'
					],
					[
						'nome' => 'Movimetação de caixa',
						'rota' => '/fluxoCaixa'
					],
					[
						'nome' => 'Gráficos',
						'rota' => '/graficos'
					],
					[
						'nome' => 'Contas caixa',
						'rota' => '/contas-empresa'
					],
					[
						'nome' => 'Plano de contas',
						'rota' => '/plano-contas'
					],
					[
					 	'nome' => 'Emitir - DAS',
					 	'rota' => env('LINK_DAS'),
					 	'target' => true
				    ],
				    [
					 	'nome' => 'Declaração - SIMEI',
					 	'rota' => env('LINK_FAT'),
					 	'target' => true
				    ]
				]
			],

			[
				'titulo' => 'Serviço',
				'icone' => $this->getIcone('NFSe'),
				'subs' => [
				    [
						'nome' => 'Ordem de serviço',
						'rota' => '/ordemServico'
					],
										[
						'nome' => 'Agendamentos',
						'rota' => '/agendamentos'
					],
					[
						'nome' => 'Listar',
						'rota' => '/nfse'
					],
					[
						'nome' => 'Nova NFSe',
						'rota' => '/nfse/create'
					],
					[
						'nome' => 'Emitente',
						'rota' => '/nfse-config'
					]
				]
			],

			
			[
				'titulo' => 'CTe',
				'icone' => $this->getIcone('CT-e'),
				'subs' => [
					[
						'nome' => 'Lista',
						'rota' => '/cte'
					],
					[
						'nome' => 'Nova',
						'rota' => '/cte/nova'
					],
					[
						'nome' => 'Categorias de despesa',
						'rota' => '/categoriaDespesa'
					],
					[
						'nome' => 'Manifesto',
						'rota' => '/cte/manifesta'
					]
				]
			],

			[
				'titulo' => 'CTe Os',
				'icone' => $this->getIcone('CTeOs'),
				'subs' => [
					[
						'nome' => 'Lista',
						'rota' => '/cteos'
					],
					[
						'nome' => 'Nova',
						'rota' => '/cteos/nova'
					]
				]
			],
			[
				'titulo' => 'MDFe',
				'icone' => $this->getIcone('MDF-e'),
				'subs' => [
					[
						'nome' => 'Lista',
						'rota' => '/mdfe'
					],
					[
						'nome' => 'Nova',
						'rota' => '/mdfe/nova'
					],
					[
						'nome' => 'Motoristas',
						'rota' => '/motoristas'
					]
				]
			],

			[
				'titulo' => 'Catraca',
				'icone' => $this->getIcone('Catraca'),
				'subs' => [
					[
						'nome' => 'Configuração',
						'rota' => '/config-catraca'
					]
				]
			],

			[
				'titulo' => 'Eventos',
				'icone' => $this->getIcone('Eventos'),
				'subs' => [
					[
						'nome' => 'Novo evento',
						'rota' => '/eventos/novo'
					],
					[
						'nome' => 'Atendimento',
						'rota' => '/eventos'
					],
					[
						'nome' => 'Movimetação',
						'rota' => '/eventos/movimentacao'
					]
				]
			],

			[
				'titulo' => 'Locação',
				'icone' => $this->getIcone('Locacao'),
				'subs' => [
					[
						'nome' => 'Nova locação',
						'rota' => '/locacao/novo'
					],
					[
						'nome' => 'Listar',
						'rota' => '/locacao'
					]
				]
			],

			[
				'titulo' => 'Pedidos',
				'icone' => $this->getIcone('Pedidos'),
				'subs' => [
					[
						'nome' => 'Comandas',
						'rota' => '/pedidos'
					],
					[
						'nome' => 'Adicionais',
						'rota' => '/deliveryComplemento'
					],
					[
						'nome' => 'Telas de Pedido',
						'rota' => '/telasPedido'
					],
					[
						'nome' => 'Controle de pedidos',
						'rota' => '/controleCozinha/selecionar'
					],
					[
						'nome' => 'Cadastrar mesas',
						'rota' => '/mesas'
					],
					[
						'nome' => 'Controle de comandas',
						'rota' => '/pedidos/controleComandas'
					],
					[
						'nome' => 'APK controle de comandas',
						'rota' => '/pedidos/upload'
					],
					[
						'nome' => 'Impressoras',
						'rota' => '/impressoras'
					]
				]
			],

			[
				'titulo' => 'Delivery',
				'icone' => $this->getIcone('Delivery'),
				'subs' => [
					[
						'nome' => 'Configuração',
						'rota' => '/configDelivery'
					],
					[
						'nome' => 'Pedidos',
						'rota' => '/pedidosDelivery'
					],
					[
						'nome' => 'Bairros',
						'rota' => '/bairrosDeliveryLoja'
					],
				    [
					 	'nome' => 'Categorias de delivery',
					 	'rota' => '/categoriaDeLoja'
				    ],
					[
						'nome' => 'Categorias de produto',
						'rota' => '/deliveryCategoria'
					],
					[
						'nome' => 'Produtos',
						'rota' => '/deliveryProduto'
					],
					[
						'nome' => 'Adicionais para produtos',
						'rota' => '/deliveryComplemento'
					],
					[
						'nome' => 'Carrossel',
						'rota' => '/carrosselDelivery'
					],
					[
						'nome' => 'Frente de pedido',
						'rota' => '/pedidosDelivery/frente'
					],
					[
						'nome' => 'Funcionamento',
						'rota' => '/funcionamentoDelivery'
					],

				    [
					 	'nome' => 'Push',
					 	'rota' => '/push'
				    ],
					[
						'nome' => 'Tamanhos de pizza',
						'rota' => '/tamanhosPizza'
					],
					[
						'nome' => 'Clientes',
						'rota' => '/clientesDelivery'
					],
					[
						'nome' => 'Cupons de Desconto',
						'rota' => '/codigoDesconto'
					],
					[
						'nome' => 'Motoboy',
						'rota' => '/motoboys'
					],
					[
						'nome' => 'Atendimento',
						'rota' => '/pedidosMesa'
					],
					[
						'nome' => 'Cadastrar mesas',
						'rota' => '/mesas'
					],
					[
						'nome' => 'Controle de pedidos',
						'rota' => '/controleCozinha/selecionar'
					],
				]
			],

			[
				'titulo' => 'iFood',
				'icone' => $this->getIcone('ifood'),
				'subs' => [
					[
						'nome' => 'Configuração API',
						'rota' => '/ifood/config'
					],
					[
						'nome' => 'Configuração da Loja',
						'rota' => '/ifood/loja'
					],
					[
						'nome' => 'Catálogos',
						'rota' => '/ifood/catalogos'
					],
					[
						'nome' => 'Produtos',
						'rota' => '/ifood/products'
					],
					[
						'nome' => 'Pedidos',
						'rota' => '/ifood/pedidos'
					],

				]
			],

			[
				'titulo' => 'Ecommerce',
				'icone' => $this->getIcone('Ecommerce'),
				'subs' => [
					[
						'nome' => 'Configuração',
						'rota' => '/configEcommerce'
					],
					[
						'nome' => 'Categorias',
						'rota' => '/categoriaEcommerce'
					],
					[
						'nome' => 'Produtos',
						'rota' => '/produtoEcommerce'
					],
					[
						'nome' => 'Pedidos',
						'rota' => '/pedidosEcommerce'
					],
					[
						'nome' => 'Carrossel',
						'rota' => '/carrosselEcommerce'
					],
					[
						'nome' => 'Blog Autor',
						'rota' => '/autorPost'
					],

					[
						'nome' => 'Blog Categoria',
						'rota' => '/categoriaPosts'
					],

					[
						'nome' => 'Blog Post',
						'rota' => '/postBlog'
					],

					[
						'nome' => 'Contatos',
						'rota' => '/contatoEcommerce'
					],

					[
						'nome' => 'Informativo cliente',
						'rota' => '/informativoEcommerce'
					],

					[
						'nome' => 'Clientes',
						'rota' => '/clienteEcommerce'
					],

					[
						'nome' => 'Cupons de desconto',
						'rota' => '/cuponsEcommerce'
					],

					[
						'nome' => 'Ver site',
						'rota' => '/configEcommerce/verSite',
						'target' => true
					]
				]
			],

			[
				'titulo' => 'Mercado Livre',
				'icone' => $this->getIcone('Mercado Livre'),
				'subs' => [
					[
						'nome' => 'Configuração',
						'rota' => '/config-mercado-livre'
					],

					[
						'nome' => 'Perguntas',
						'rota' => '/mercado-livre-perguntas'
					],

					[
						'nome' => 'Produtos',
						'rota' => '/mercado-livre-produtos-news'
					],
					[
						'nome' => 'Pedidos',
						'rota' => '/mercado-livre-pedidos'
					],
				]
			],
			[
				'titulo' => 'WooCommerce',
				'icone' => '<span class="svg-icon menu-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
						<path fill="#000000" d="M3.5,21 L20.5,21 C21.3284271,21 22,20.3284271 22,19.5 L22,8.5 C22,7.67157288 21.3284271,7 20.5,7 L10,7 L7.43933983,4.43933983 C7.15803526,4.15803526 6.77650439,4 6.37867966,4 L3.5,4 C2.67157288,4 2,4.67157288 2,5.5 L2,19.5 C2,20.3284271 2.67157288,21 3.5,21 Z" opacity="0.3"/>
					</svg>
				</span>',
				'subs' => [
					[
						'nome' => 'Configuração',
						'rota' => '/woocommerce/config'
					],
					[
						'nome' => 'Produtos',
						'rota' => '/woocommerce/produtos'
					],
					[
						'nome' => 'Pedidos',
						'rota' => '/woocommerce/pedidos'
					],
					[
						'nome' => 'Sincronização',
						'rota' => '/woocommerce/sincronizacao'
					]
				]
					],

			[
				'titulo' => 'Nuvem Shop',
				'icone' => $this->getIcone('NumverShop'),
				'subs' => [
					[
						'nome' => 'Configuração',
						'rota' => '/nuvemshop/config'
					],
					[
						'nome' => 'Categorias',
						'rota' => '/nuvemshop/categorias'
					],
					[
						'nome' => 'Produtos',
						'rota' => '/nuvemshop/produtos'
					],
					[
						'nome' => 'Pedidos',
						'rota' => '/nuvemshop/pedidos'
					],
					[
						'nome' => 'Clientes',
						'rota' => '/nuvemshop/clientes'
					],
				]
			],

			[
				'titulo' => 'Relatórios',
				'icone' => $this->getIcone('Relatórios'),
				'subs' => [
					[
						'nome' => 'Relatórios',
						'rota' => '/relatorios'
					],
					[
						'nome' => 'DRE',
						'rota' => '/dre'
					]
				]
			],

			[
				'titulo' => 'Gestão Pessoal',
				'icone' => $this->getIcone('GestaoPessoal'),
				'subs' => [
					[
						'nome' => 'Funcionarios',
						'rota' => '/funcionarios'
					],

					[
						'nome' => 'Novo Funcionario',
						'rota' => '/funcionarios/new'
					],
					[
						'nome' => 'Eventos',
						'rota' => '/eventosFuncionario'
					],
					[
						'nome' => 'Funcionarios x Eventos',
						'rota' => '/funcionarioEventos'
					],
					[
						'nome' => 'Apuração Mensal',
						'rota' => '/apuracaoMensal'
					],
					[
						'nome' => 'Uso e consumo',
						'rota' => '/uso-consumo'
					],
					
				]
			],
			[
				'titulo' => 'Configurações',
				'icone' => $this->getIcone('Configurações'),
				'subs' => [
					[
						'nome' => 'Configurar emitente',
						'rota' => '/configNF'
					],
					[
						'nome' => 'Filial',
						'rota' => '/filial'
					],
					[
						'nome' => 'Configurar email',
						'rota' => '/configEmail'
					],
					[
						'nome' => 'SPED ICMS/IPI',
						'rota' => '/sped'
					],
					[
						'nome' => 'Dados do contador',
						'rota' => '/escritorio'
					],
					[
						'nome' => 'Natureza de operação',
						'rota' => '/naturezaOperacao'
					],
					[
						'nome' => 'Tributação',
						'rota' => '/tributos'
					],
					[
						'nome' => 'Op. Interestadual - Difal',
						'rota' => '/difal'
					],
					[
						'nome' => 'Cancelamento',
						'rota' => '/cancelamento'
					],
					[
						'nome' => 'Enviar XML',
						'rota' => '/enviarXml'
					],
					[
						'nome' => 'Ticket',
						'rota' => '/tickets'
					],
					[
						'nome' => 'Sintegra',
						'rota' => '/sintegra'
					]
				]
			],
			[
				'titulo' => 'Plug4Market',
				'icone' => '<span class="svg-icon menu-icon"><i class="fas fa-plug"></i></span>',
				'subs' => [
					[
						'nome' => 'Produtos',
						'rota' => '/plug4market/products'
					],
					[
						'nome' => 'Categorias',
						'rota' => '/plug4market/categories'
					],
					[
						'nome' => 'Pedidos',
						'rota' => '/plug4market/orders'
					],
					[
						'nome' => 'Configurações',
						'rota' => '/plug4market/settings'
					],
					[
						'nome' => 'Logs',
						'rota' => '/plug4market/settings/logs'
					]

				]
			],
		];
	}

	public function getMenu(){
		return $this->menu;
	}

	public function getIcone($titulo){
		if($titulo == 'Cadastros'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"></rect>
			<rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
			<path d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z" fill="#000000" opacity="0.3"></path>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Entradas'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<rect fill="#000000" opacity="0.3" transform="translate(9.000000, 12.000000) rotate(-270.000000) translate(-9.000000, -12.000000) " x="8" y="6" width="2" height="12" rx="1" />
			<path d="M20,7.00607258 C19.4477153,7.00607258 19,6.55855153 19,6.00650634 C19,5.45446114 19.4477153,5.00694009 20,5.00694009 L21,5.00694009 C23.209139,5.00694009 25,6.7970243 25,9.00520507 L25,15.001735 C25,17.2099158 23.209139,19 21,19 L9,19 C6.790861,19 5,17.2099158 5,15.001735 L5,8.99826498 C5,6.7900842 6.790861,5 9,5 L10.0000048,5 C10.5522896,5 11.0000048,5.44752105 11.0000048,5.99956624 C11.0000048,6.55161144 10.5522896,6.99913249 10.0000048,6.99913249 L9,6.99913249 C7.8954305,6.99913249 7,7.89417459 7,8.99826498 L7,15.001735 C7,16.1058254 7.8954305,17.0008675 9,17.0008675 L21,17.0008675 C22.1045695,17.0008675 23,16.1058254 23,15.001735 L23,9.00520507 C23,7.90111468 22.1045695,7.00607258 21,7.00607258 L20,7.00607258 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(15.000000, 12.000000) rotate(-90.000000) translate(-15.000000, -12.000000) " />
			<path d="M16.7928932,9.79289322 C17.1834175,9.40236893 17.8165825,9.40236893 18.2071068,9.79289322 C18.5976311,10.1834175 18.5976311,10.8165825 18.2071068,11.2071068 L15.2071068,14.2071068 C14.8165825,14.5976311 14.1834175,14.5976311 13.7928932,14.2071068 L10.7928932,11.2071068 C10.4023689,10.8165825 10.4023689,10.1834175 10.7928932,9.79289322 C11.1834175,9.40236893 11.8165825,9.40236893 12.2071068,9.79289322 L14.5,12.0857864 L16.7928932,9.79289322 Z" fill="#000000" fill-rule="nonzero" transform="translate(14.500000, 12.000000) rotate(-90.000000) translate(-14.500000, -12.000000) " />
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'GestaoPessoal'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M3.5,21 L20.5,21 C21.3284271,21 22,20.3284271 22,19.5 L22,8.5 C22,7.67157288 21.3284271,7 20.5,7 L10,7 L7.43933983,4.43933983 C7.15803526,4.15803526 6.77650439,4 6.37867966,4 L3.5,4 C2.67157288,4 2,4.67157288 2,5.5 L2,19.5 C2,20.3284271 2.67157288,21 3.5,21 Z" fill="#000000" opacity="0.3"/>
			<path d="M12,13 C10.8954305,13 10,12.1045695 10,11 C10,9.8954305 10.8954305,9 12,9 C13.1045695,9 14,9.8954305 14,11 C14,12.1045695 13.1045695,13 12,13 Z" fill="#000000" opacity="0.3"/>
			<path d="M7.00036205,18.4995035 C7.21569918,15.5165724 9.36772908,14 11.9907452,14 C14.6506758,14 16.8360465,15.4332455 16.9988413,18.5 C17.0053266,18.6221713 16.9988413,19 16.5815,19 C14.5228466,19 11.463736,19 7.4041679,19 C7.26484009,19 6.98863236,18.6619875 7.00036205,18.4995035 Z" fill="#000000" opacity="0.3"/>
			</g>
			</svg>
			</span>';
		}
		if($titulo == 'Estoque'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<rect fill="#000000" opacity="0.3" x="4" y="5" width="16" height="2" rx="1" />
			<rect fill="#000000" opacity="0.3" x="4" y="13" width="16" height="2" rx="1" />
			<path d="M5,9 L13,9 C13.5522847,9 14,9.44771525 14,10 C14,10.5522847 13.5522847,11 13,11 L5,11 C4.44771525,11 4,10.5522847 4,10 C4,9.44771525 4.44771525,9 5,9 Z M5,17 L13,17 C13.5522847,17 14,17.4477153 14,18 C14,18.5522847 13.5522847,19 13,19 L5,19 C4.44771525,19 4,18.5522847 4,18 C4,17.4477153 4.44771525,17 5,17 Z" fill="#000000" />
			</g>
			</svg>
			</span>';
		}
		if($titulo == 'Financeiro'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<path d="M5,19 L20,19 C20.5522847,19 21,19.4477153 21,20 C21,20.5522847 20.5522847,21 20,21 L4,21 C3.44771525,21 3,20.5522847 3,20 L3,4 C3,3.44771525 3.44771525,3 4,3 C4.55228475,3 5,3.44771525 5,4 L5,19 Z" fill="#000000" fill-rule="nonzero" />
			<path d="M8.7295372,14.6839411 C8.35180695,15.0868534 7.71897114,15.1072675 7.31605887,14.7295372 C6.9131466,14.3518069 6.89273254,13.7189711 7.2704628,13.3160589 L11.0204628,9.31605887 C11.3857725,8.92639521 11.9928179,8.89260288 12.3991193,9.23931335 L15.358855,11.7649545 L19.2151172,6.88035571 C19.5573373,6.44687693 20.1861655,6.37289714 20.6196443,6.71511723 C21.0531231,7.05733733 21.1271029,7.68616551 20.7848828,8.11964429 L16.2848828,13.8196443 C15.9333973,14.2648593 15.2823707,14.3288915 14.8508807,13.9606866 L11.8268294,11.3801628 L8.7295372,14.6839411 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(14.000019, 10.749981) scale(1, -1) translate(-14.000019, -10.749981) " />
			</g>
			</svg>
			</span>';
		}
		if($titulo == 'Configurações'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<path d="M8,3 L8,3.5 C8,4.32842712 8.67157288,5 9.5,5 L14.5,5 C15.3284271,5 16,4.32842712 16,3.5 L16,3 L18,3 C19.1045695,3 20,3.8954305 20,5 L20,21 C20,22.1045695 19.1045695,23 18,23 L6,23 C4.8954305,23 4,22.1045695 4,21 L4,5 C4,3.8954305 4.8954305,3 6,3 L8,3 Z" fill="#000000" opacity="0.3" />
			<path d="M11,2 C11,1.44771525 11.4477153,1 12,1 C12.5522847,1 13,1.44771525 13,2 L14.5,2 C14.7761424,2 15,2.22385763 15,2.5 L15,3.5 C15,3.77614237 14.7761424,4 14.5,4 L9.5,4 C9.22385763,4 9,3.77614237 9,3.5 L9,2.5 C9,2.22385763 9.22385763,2 9.5,2 L11,2 Z" fill="#000000" />
			<rect fill="#000000" opacity="0.3" x="10" y="9" width="7" height="2" rx="1" />
			<rect fill="#000000" opacity="0.3" x="7" y="9" width="2" height="2" rx="1" />
			<rect fill="#000000" opacity="0.3" x="7" y="13" width="2" height="2" rx="1" />
			<rect fill="#000000" opacity="0.3" x="10" y="13" width="7" height="2" rx="1" />
			<rect fill="#000000" opacity="0.3" x="7" y="17" width="2" height="2" rx="1" />
			<rect fill="#000000" opacity="0.3" x="10" y="17" width="7" height="2" rx="1" />
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'VendaBalcao'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M5.94290508,4 L18.0570949,4 C18.5865712,4 19.0242774,4.41271535 19.0553693,4.94127798 L19.8754445,18.882556 C19.940307,19.9852194 19.0990032,20.9316862 17.9963398,20.9965487 C17.957234,20.9988491 17.9180691,21 17.8788957,21 L6.12110428,21 C5.01653478,21 4.12110428,20.1045695 4.12110428,19 C4.12110428,18.9608266 4.12225519,18.9216617 4.12455553,18.882556 L4.94463071,4.94127798 C4.97572263,4.41271535 5.41342877,4 5.94290508,4 Z" fill="#000000" opacity="0.3"/>
			<path d="M7,7 L9,7 C9,8.65685425 10.3431458,10 12,10 C13.6568542,10 15,8.65685425 15,7 L17,7 C17,9.76142375 14.7614237,12 12,12 C9.23857625,12 7,9.76142375 7,7 Z" fill="#000000"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Vendas'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<rect fill="#000000" opacity="0.3" x="11.5" y="2" width="2" height="4" rx="1" />
			<rect fill="#000000" opacity="0.3" x="11.5" y="16" width="2" height="5" rx="1" />
			<path d="M15.493,8.044 C15.2143319,7.68933156 14.8501689,7.40750104 14.4005,7.1985 C13.9508311,6.98949895 13.5170021,6.885 13.099,6.885 C12.8836656,6.885 12.6651678,6.90399981 12.4435,6.942 C12.2218322,6.98000019 12.0223342,7.05283279 11.845,7.1605 C11.6676658,7.2681672 11.5188339,7.40749914 11.3985,7.5785 C11.2781661,7.74950085 11.218,7.96799867 11.218,8.234 C11.218,8.46200114 11.2654995,8.65199924 11.3605,8.804 C11.4555005,8.95600076 11.5948324,9.08899943 11.7785,9.203 C11.9621676,9.31700057 12.1806654,9.42149952 12.434,9.5165 C12.6873346,9.61150047 12.9723317,9.70966616 13.289,9.811 C13.7450023,9.96300076 14.2199975,10.1308324 14.714,10.3145 C15.2080025,10.4981676 15.6576646,10.7419985 16.063,11.046 C16.4683354,11.3500015 16.8039987,11.7268311 17.07,12.1765 C17.3360013,12.6261689 17.469,13.1866633 17.469,13.858 C17.469,14.6306705 17.3265014,15.2988305 17.0415,15.8625 C16.7564986,16.4261695 16.3733357,16.8916648 15.892,17.259 C15.4106643,17.6263352 14.8596698,17.8986658 14.239,18.076 C13.6183302,18.2533342 12.97867,18.342 12.32,18.342 C11.3573285,18.342 10.4263378,18.1741683 9.527,17.8385 C8.62766217,17.5028317 7.88033631,17.0246698 7.285,16.404 L9.413,14.238 C9.74233498,14.6433354 10.176164,14.9821653 10.7145,15.2545 C11.252836,15.5268347 11.7879973,15.663 12.32,15.663 C12.5606679,15.663 12.7949989,15.6376669 13.023,15.587 C13.2510011,15.5363331 13.4504991,15.4540006 13.6215,15.34 C13.7925009,15.2259994 13.9286662,15.0740009 14.03,14.884 C14.1313338,14.693999 14.182,14.4660013 14.182,14.2 C14.182,13.9466654 14.1186673,13.7313342 13.992,13.554 C13.8653327,13.3766658 13.6848345,13.2151674 13.4505,13.0695 C13.2161655,12.9238326 12.9248351,12.7908339 12.5765,12.6705 C12.2281649,12.5501661 11.8323355,12.420334 11.389,12.281 C10.9583312,12.141666 10.5371687,11.9770009 10.1255,11.787 C9.71383127,11.596999 9.34650161,11.3531682 9.0235,11.0555 C8.70049838,10.7578318 8.44083431,10.3968355 8.2445,9.9725 C8.04816568,9.54816454 7.95,9.03200304 7.95,8.424 C7.95,7.67666293 8.10199848,7.03700266 8.406,6.505 C8.71000152,5.97299734 9.10899753,5.53600171 9.603,5.194 C10.0970025,4.85199829 10.6543302,4.60183412 11.275,4.4435 C11.8956698,4.28516587 12.5226635,4.206 13.156,4.206 C13.9160038,4.206 14.6918294,4.34533194 15.4835,4.624 C16.2751706,4.90266806 16.9686637,5.31433061 17.564,5.859 L15.493,8.044 Z" fill="#000000" />
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Pedidos'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<polygon points="0 0 24 0 24 24 0 24" />
			<path d="M3.52270623,14.028695 C2.82576459,13.3275941 2.82576459,12.19529 3.52270623,11.4941891 L11.6127629,3.54050571 C11.9489429,3.20999263 12.401513,3.0247814 12.8729533,3.0247814 L19.3274172,3.0247814 C20.3201611,3.0247814 21.124939,3.82955935 21.124939,4.82230326 L21.124939,11.2583059 C21.124939,11.7406659 20.9310733,12.2027862 20.5869271,12.5407722 L12.5103155,20.4728108 C12.1731575,20.8103442 11.7156477,21 11.2385688,21 C10.7614899,21 10.3039801,20.8103442 9.9668221,20.4728108 L3.52270623,14.028695 Z M16.9307214,9.01652093 C17.9234653,9.01652093 18.7282432,8.21174298 18.7282432,7.21899907 C18.7282432,6.22625516 17.9234653,5.42147721 16.9307214,5.42147721 C15.9379775,5.42147721 15.1331995,6.22625516 15.1331995,7.21899907 C15.1331995,8.21174298 15.9379775,9.01652093 16.9307214,9.01652093 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" />
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'CT-e'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<path d="M8,17 C8.55228475,17 9,17.4477153 9,18 L9,21 C9,21.5522847 8.55228475,22 8,22 L3,22 C2.44771525,22 2,21.5522847 2,21 L2,18 C2,17.4477153 2.44771525,17 3,17 L3,16.5 C3,15.1192881 4.11928813,14 5.5,14 C6.88071187,14 8,15.1192881 8,16.5 L8,17 Z M5.5,15 C4.67157288,15 4,15.6715729 4,16.5 L4,17 L7,17 L7,16.5 C7,15.6715729 6.32842712,15 5.5,15 Z" fill="#000000" opacity="0.3" />
			<path d="M2,11.8650466 L2,6 C2,4.34314575 3.34314575,3 5,3 L19,3 C20.6568542,3 22,4.34314575 22,6 L22,15 C22,15.0032706 21.9999948,15.0065399 21.9999843,15.009808 L22.0249378,15 L22.0249378,19.5857864 C22.0249378,20.1380712 21.5772226,20.5857864 21.0249378,20.5857864 C20.7597213,20.5857864 20.5053674,20.4804296 20.317831,20.2928932 L18.0249378,18 L12.9835977,18 C12.7263047,14.0909841 9.47412135,11 5.5,11 C4.23590829,11 3.04485894,11.3127315 2,11.8650466 Z M6,7 C5.44771525,7 5,7.44771525 5,8 C5,8.55228475 5.44771525,9 6,9 L15,9 C15.5522847,9 16,8.55228475 16,8 C16,7.44771525 15.5522847,7 15,7 L6,7 Z" fill="#000000"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'CTeOs'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M19,11 L21,11 C21.5522847,11 22,11.4477153 22,12 C22,12.5522847 21.5522847,13 21,13 L19,13 C18.4477153,13 18,12.5522847 18,12 C18,11.4477153 18.4477153,11 19,11 Z M3,11 L5,11 C5.55228475,11 6,11.4477153 6,12 C6,12.5522847 5.55228475,13 5,13 L3,13 C2.44771525,13 2,12.5522847 2,12 C2,11.4477153 2.44771525,11 3,11 Z M12,2 C12.5522847,2 13,2.44771525 13,3 L13,5 C13,5.55228475 12.5522847,6 12,6 C11.4477153,6 11,5.55228475 11,5 L11,3 C11,2.44771525 11.4477153,2 12,2 Z M12,18 C12.5522847,18 13,18.4477153 13,19 L13,21 C13,21.5522847 12.5522847,22 12,22 C11.4477153,22 11,21.5522847 11,21 L11,19 C11,18.4477153 11.4477153,18 12,18 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
			<circle fill="#000000" opacity="0.3" cx="12" cy="12" r="2"/>
			<path d="M12,17 C14.7614237,17 17,14.7614237 17,12 C17,9.23857625 14.7614237,7 12,7 C9.23857625,7 7,9.23857625 7,12 C7,14.7614237 9.23857625,17 12,17 Z M12,19 C8.13400675,19 5,15.8659932 5,12 C5,8.13400675 8.13400675,5 12,5 C15.8659932,5 19,8.13400675 19,12 C19,15.8659932 15.8659932,19 12,19 Z" fill="#000000" fill-rule="nonzero"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'NFSe'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<g transform="translate(12.500000, 12.000000) rotate(-315.000000) translate(-12.500000, -12.000000) translate(6.000000, 1.000000)" fill="#000000" opacity="0.3">
			<path d="M0.353553391,7.14644661 L3.35355339,7.14644661 C3.4100716,7.14644661 3.46549471,7.14175791 3.51945496,7.13274826 C3.92739876,8.3050906 5.04222146,9.14644661 6.35355339,9.14644661 C8.01040764,9.14644661 9.35355339,7.80330086 9.35355339,6.14644661 C9.35355339,4.48959236 8.01040764,3.14644661 6.35355339,3.14644661 C5.04222146,3.14644661 3.92739876,3.98780262 3.51945496,5.16014496 C3.46549471,5.15113531 3.4100716,5.14644661 3.35355339,5.14644661 L0.436511831,5.14644661 C0.912589923,2.30873327 3.3805571,0.146446609 6.35355339,0.146446609 C9.66726189,0.146446609 12.3535534,2.83273811 12.3535534,6.14644661 L12.3535534,19.1464466 C12.3535534,20.2510161 11.4581229,21.1464466 10.3535534,21.1464466 L2.35355339,21.1464466 C1.24898389,21.1464466 0.353553391,20.2510161 0.353553391,19.1464466 L0.353553391,7.14644661 Z" transform="translate(6.353553, 10.646447) rotate(-360.000000) translate(-6.353553, -10.646447) "/>
			<rect x="2.35355339" y="13.1464466" width="8" height="2" rx="1"/>
			<rect x="3.35355339" y="17.1464466" width="6" height="2" rx="1"/>
			</g>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'MDF-e'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<path d="M16.5428932,17.4571068 L11,11.9142136 L11,4 C11,3.44771525 11.4477153,3 12,3 C12.5522847,3 13,3.44771525 13,4 L13,11.0857864 L17.9571068,16.0428932 L20.1464466,13.8535534 C20.3417088,13.6582912 20.6582912,13.6582912 20.8535534,13.8535534 C20.9473216,13.9473216 21,14.0744985 21,14.2071068 L21,19.5 C21,19.7761424 20.7761424,20 20.5,20 L15.2071068,20 C14.9309644,20 14.7071068,19.7761424 14.7071068,19.5 C14.7071068,19.3673918 14.7597852,19.2402148 14.8535534,19.1464466 L16.5428932,17.4571068 Z" fill="#000000" fill-rule="nonzero" />
			<path d="M7.24478854,17.1447885 L9.2464466,19.1464466 C9.34021479,19.2402148 9.39289321,19.3673918 9.39289321,19.5 C9.39289321,19.7761424 9.16903559,20 8.89289321,20 L3.52893218,20 C3.25278981,20 3.02893218,19.7761424 3.02893218,19.5 L3.02893218,14.136039 C3.02893218,14.0034307 3.0816106,13.8762538 3.17537879,13.7824856 C3.37064094,13.5872234 3.68722343,13.5872234 3.88248557,13.7824856 L5.82567301,15.725673 L8.85405776,13.1631936 L10.1459422,14.6899662 L7.24478854,17.1447885 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" />
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Eventos'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M11.6734943,8.3307728 L14.9993074,6.09979492 L14.1213255,5.22181303 C13.7308012,4.83128874 13.7308012,4.19812376 14.1213255,3.80759947 L15.535539,2.39338591 C15.9260633,2.00286161 16.5592283,2.00286161 16.9497526,2.39338591 L22.6066068,8.05024016 C22.9971311,8.44076445 22.9971311,9.07392943 22.6066068,9.46445372 L21.1923933,10.8786673 C20.801869,11.2691916 20.168704,11.2691916 19.7781797,10.8786673 L18.9002333,10.0007208 L16.6692373,13.3265608 C16.9264145,14.2523264 16.9984943,15.2320236 16.8664372,16.2092466 L16.4344698,19.4058049 C16.360509,19.9531149 15.8568695,20.3368403 15.3095595,20.2628795 C15.0925691,20.2335564 14.8912006,20.1338238 14.7363706,19.9789938 L5.02099894,10.2636221 C4.63047465,9.87309784 4.63047465,9.23993286 5.02099894,8.84940857 C5.17582897,8.69457854 5.37719743,8.59484594 5.59418783,8.56552292 L8.79074617,8.13355557 C9.76799113,8.00149544 10.7477104,8.0735815 11.6734943,8.3307728 Z" fill="#000000"/>
			<polygon fill="#000000" opacity="0.3" transform="translate(7.050253, 17.949747) rotate(-315.000000) translate(-7.050253, -17.949747) " points="5.55025253 13.9497475 5.55025253 19.6640332 7.05025253 21.9497475 8.55025253 19.6640332 8.55025253 13.9497475"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Catraca'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M3.5,3 L9.5,3 C10.3284271,3 11,3.67157288 11,4.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L3.5,20 C2.67157288,20 2,19.3284271 2,18.5 L2,4.5 C2,3.67157288 2.67157288,3 3.5,3 Z M9,9 C8.44771525,9 8,9.44771525 8,10 L8,12 C8,12.5522847 8.44771525,13 9,13 C9.55228475,13 10,12.5522847 10,12 L10,10 C10,9.44771525 9.55228475,9 9,9 Z" fill="#000000" opacity="0.3"/>
			<path d="M14.5,3 L20.5,3 C21.3284271,3 22,3.67157288 22,4.5 L22,18.5 C22,19.3284271 21.3284271,20 20.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,4.5 C13,3.67157288 13.6715729,3 14.5,3 Z M20,9 C19.4477153,9 19,9.44771525 19,10 L19,12 C19,12.5522847 19.4477153,13 20,13 C20.5522847,13 21,12.5522847 21,12 L21,10 C21,9.44771525 20.5522847,9 20,9 Z" fill="#000000" transform="translate(17.500000, 11.500000) scale(-1, 1) translate(-17.500000, -11.500000) "/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Delivery'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24" />
			<path d="M2.88070511,5.66588911 C5.49624739,3.97895289 8.61140593,3 11.9552112,3 C15.2990164,3 18.4141749,3.97895289 21.0297172,5.66588911 L11.9552112,22 L2.88070511,5.66588911 Z" fill="#000000" opacity="0.3" />
			<circle fill="#000000" opacity="0.3" cx="9.5" cy="9.5" r="1.5" />
			<circle fill="#000000" opacity="0.3" cx="15.5" cy="7.5" r="1.5" />
			<circle fill="#000000" opacity="0.3" cx="12.5" cy="15.5" r="1.5" />
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Relatórios'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M5,3 L6,3 C6.55228475,3 7,3.44771525 7,4 L7,20 C7,20.5522847 6.55228475,21 6,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z M10,3 L11,3 C11.5522847,3 12,3.44771525 12,4 L12,20 C12,20.5522847 11.5522847,21 11,21 L10,21 C9.44771525,21 9,20.5522847 9,20 L9,4 C9,3.44771525 9.44771525,3 10,3 Z" fill="#000000"/>
			<rect fill="#000000" opacity="0.3" transform="translate(17.825568, 11.945519) rotate(-19.000000) translate(-17.825568, -11.945519) " x="16.3255682" y="2.94551858" width="3" height="18" rx="1"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Locacao'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M4.875,20.75 C4.63541667,20.75 4.39583333,20.6541667 4.20416667,20.4625 L2.2875,18.5458333 C1.90416667,18.1625 1.90416667,17.5875 2.2875,17.2041667 C2.67083333,16.8208333 3.29375,16.8208333 3.62916667,17.2041667 L4.875,18.45 L8.0375,15.2875 C8.42083333,14.9041667 8.99583333,14.9041667 9.37916667,15.2875 C9.7625,15.6708333 9.7625,16.2458333 9.37916667,16.6291667 L5.54583333,20.4625 C5.35416667,20.6541667 5.11458333,20.75 4.875,20.75 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
			<path d="M2,11.8650466 L2,6 C2,4.34314575 3.34314575,3 5,3 L19,3 C20.6568542,3 22,4.34314575 22,6 L22,15 C22,15.0032706 21.9999948,15.0065399 21.9999843,15.009808 L22.0249378,15 L22.0249378,19.5857864 C22.0249378,20.1380712 21.5772226,20.5857864 21.0249378,20.5857864 C20.7597213,20.5857864 20.5053674,20.4804296 20.317831,20.2928932 L18.0249378,18 L12.9835977,18 C12.7263047,14.0909841 9.47412135,11 5.5,11 C4.23590829,11 3.04485894,11.3127315 2,11.8650466 Z M6,7 C5.44771525,7 5,7.44771525 5,8 C5,8.55228475 5.44771525,9 6,9 L15,9 C15.5522847,9 16,8.55228475 16,8 C16,7.44771525 15.5522847,7 15,7 L6,7 Z" fill="#000000"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Ecommerce'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M5.94290508,4 L18.0570949,4 C18.5865712,4 19.0242774,4.41271535 19.0553693,4.94127798 L19.8754445,18.882556 C19.940307,19.9852194 19.0990032,20.9316862 17.9963398,20.9965487 C17.957234,20.9988491 17.9180691,21 17.8788957,21 L6.12110428,21 C5.01653478,21 4.12110428,20.1045695 4.12110428,19 C4.12110428,18.9608266 4.12225519,18.9216617 4.12455553,18.882556 L4.94463071,4.94127798 C4.97572263,4.41271535 5.41342877,4 5.94290508,4 Z" fill="#000000" opacity="0.3"/>
			<path d="M7,7 L9,7 C9,8.65685425 10.3431458,10 12,10 C13.6568542,10 15,8.65685425 15,7 L17,7 C17,9.76142375 14.7614237,12 12,12 C9.23857625,12 7,9.76142375 7,7 Z" fill="#000000"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'Mercado Livre'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M18.1446364,11.84388 L17.4471627,16.0287218 C17.4463569,16.0335568 17.4455155,16.0383857 17.4446387,16.0432083 C17.345843,16.5865846 16.8252597,16.9469884 16.2818833,16.8481927 L4.91303792,14.7811299 C4.53842737,14.7130189 4.23500006,14.4380834 4.13039941,14.0719812 L2.30560137,7.68518803 C2.28007524,7.59584656 2.26712532,7.50338343 2.26712532,7.4104669 C2.26712532,6.85818215 2.71484057,6.4104669 3.26712532,6.4104669 L16.9929851,6.4104669 L17.606173,3.78251876 C17.7307772,3.24850086 18.2068633,2.87071314 18.7552257,2.87071314 L20.8200821,2.87071314 C21.4717328,2.87071314 22,3.39898039 22,4.05063106 C22,4.70228173 21.4717328,5.23054898 20.8200821,5.23054898 L19.6915238,5.23054898 L18.1446364,11.84388 Z" fill="#000000" opacity="0.3"/>
			<path d="M6.5,21 C5.67157288,21 5,20.3284271 5,19.5 C5,18.6715729 5.67157288,18 6.5,18 C7.32842712,18 8,18.6715729 8,19.5 C8,20.3284271 7.32842712,21 6.5,21 Z M15.5,21 C14.6715729,21 14,20.3284271 14,19.5 C14,18.6715729 14.6715729,18 15.5,18 C16.3284271,18 17,18.6715729 17,19.5 C17,20.3284271 16.3284271,21 15.5,21 Z" fill="#000000"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'NumverShop'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M14,9 L14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 L10,9 L8,9 L8,8 C8,5.790861 9.790861,4 12,4 C14.209139,4 16,5.790861 16,8 L16,9 L14,9 Z M14,9 L14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 L10,9 L8,9 L8,8 C8,5.790861 9.790861,4 12,4 C14.209139,4 16,5.790861 16,8 L16,9 L14,9 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
			<path d="M6.84712709,9 L17.1528729,9 C17.6417121,9 18.0589022,9.35341304 18.1392668,9.83560101 L19.611867,18.671202 C19.7934571,19.7607427 19.0574178,20.7911977 17.9678771,20.9727878 C17.8592143,20.9908983 17.7492409,21 17.6390792,21 L6.36092084,21 C5.25635134,21 4.36092084,20.1045695 4.36092084,19 C4.36092084,18.8898383 4.37002252,18.7798649 4.388133,18.671202 L5.86073316,9.83560101 C5.94109783,9.35341304 6.35828794,9 6.84712709,9 Z" fill="#000000"/>
			</g>
			</svg>
			</span>';
		}

		if($titulo == 'ifood'){
			return '<span class="svg-icon menu-icon">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<rect x="0" y="0" width="24" height="24"/>
			<path d="M15,15 L15.9974233,16.1399123 C16.3611054,16.555549 16.992868,16.5976665 17.4085046,16.2339844 C17.4419154,16.20475 17.4733423,16.1733231 17.5025767,16.1399123 L18.5,15 L21,15 C20.4185426,17.9072868 17.865843,20 14.9009805,20 L9.09901951,20 C6.13415704,20 3.58145737,17.9072868 3,15 L15,15 Z" fill="#000000"/>
			<path d="M21,9 L3,9 L3,9 C3.58145737,6.09271316 6.13415704,4 9.09901951,4 L14.9009805,4 C17.865843,4 20.4185426,6.09271316 21,9 Z" fill="#000000"/>
			<rect fill="#000000" opacity="0.3" x="2" y="11" width="20" height="2" rx="1"/>
			</g>
			</svg>
			</span>';
		}
	}

	private function getMenuContador(){
		return [
			[
				'titulo' => 'Empresas',
				'icone' => $this->getIcone('Pedidos'),
				'subs' => [
					[
						'nome' => 'listar',
						'rota' => '/contador/empresas'
					],
					
				],

			],
			[
				'titulo' => 'Cadastros',
				'icone' => $this->getIcone('Cadastros'),
				'subs' => [
					[
						'nome' => 'Clientes',
						'rota' => '/contador/clientes'
					],
					[
						'nome' => 'Fornecedores',
						'rota' => '/contador/fornecedores'
					],
					[
						'nome' => 'Produtos',
						'rota' => '/contador/produtos'
					],
					
				],

			],
			[
				'titulo' => 'Vendas',
				'icone' => $this->getIcone('Vendas'),
				'subs' => [
					[
						'nome' => 'Vendas NFe',
						'rota' => '/contador/vendas'
					],
					[
						'nome' => 'PDV NFCe',
						'rota' => '/contador/pdv'
					],
				]
			]
		];

	}

	public function preparaMenu(){
		$value = session('user_logged');
		$contador = session('tipo_contador');

		if($contador){
			return $this->getMenuContador();;
		}
		if(!$value) return [];
		$menu = $this->getMenu();
		$menu = $this->trataModulos($menu);

		if($value['super']){
			return $menu;
		}

		$usuario = Usuario::
		where('id', $value['id'])
		->first();

		$permissoesAtivas = json_decode($usuario->empresa->permissao);
		if(!$permissoesAtivas){
			$permissoesAtivas = json_decode($usuario->permissao);
		}

		// for($i=0; $i < sizeof($menu); $i++){
		// 	$temp = false;
		// 	foreach($menu[$i]['subs'] as $s){

		// 		if(in_array($s['rota'], $permissoesAtivas)){
		// 			$temp = true;
		// 		}
		// 	}
		// 	$menu[$i]['ativo'] = $temp;
		// }

		$empresa = $usuario->empresa;

		$tributacao = Tributacao::
		where('empresa_id', $empresa->id)
		->first();

		$plano = $empresa->planoEmpresa->plano;

		for($i=0; $i < sizeof($menu); $i++){
			$temp = false;

			for($j=0; $j < sizeof($menu[$i]['subs']); $j++){

				if(in_array($menu[$i]['subs'][$j]['rota'], $permissoesAtivas)){
					$temp = true;
				}else{

					if($menu[$i]['subs'][$j]['nome'] != 'NFS-e'){
						if($tributacao != null && $tributacao->regime == 2){
							if($menu[$i]['subs'][$j]['nome'] != 'Emitir - DAS' && $menu[$i]['subs'][$j]['nome'] != 'Declaração - SIMEI'){
								$menu[$i]['subs'][$j]['rota_ativa'] = false;
							}
						}else{
							$menu[$i]['subs'][$j]['rota_ativa'] = false;
						}
					}
				}

				// if($menu[$i]['titulo'] == 'Delivery' && !$plano->delivery){
				// 	$temp = false;
				// }
			}
			$menu[$i]['ativo'] = $temp;
		}

		return $menu;
	}

	private function trataModulos($menu){
		$temp = [];
		foreach($menu as $m){
			$add = true;
			if($m['titulo'] == 'Eventos' && env('EVENTO') == 0){
				$add = false;
			}

			if($m['titulo'] == 'Pedidos' && env('PEDIDO_LOCAL') == 0){
				$add = false;
			}

			if($m['titulo'] == 'Delivery' && env('DELIVERY') == 0){
				$add = false;
			}

			if($m['titulo'] == 'Ecommerce' && env('ECOMMERCE') == 0){
				$add = false;
			}

			if($m['titulo'] == 'Nuvem Shop' && env('ECOMMERCE') == 0){
				$add = false;
			}

			if($m['titulo'] == 'Locação' && env('LOCACAO') == 0){
				$add = false;
			}

			if($m['titulo'] == 'Catraca' && env('CATRACA') == 0){
				$add = false;
			}

			if($m['titulo'] == 'iFood' && env('IFOOD') == 0){
				$add = false;
			}

			if($add){
				array_push($temp, $m);
			}
		}
		return $temp;
	}

	private function videos(){
		// return [
		// 	'/clientes' => 'https://drive.google.com/file/d/1V36kdpyioAzT88vqo1t0IxLwEQxXpjW5/view?usp=sharing',
		// 	'/categorias' => 'https://drive.google.com/file/d/12D5RKtu1AxzfSWroR9h3_zfa-0hKIaY9/view?usp=sharing',
		// 	'/configNF' => 'https://drive.google.com/file/d/120C6aeXLjlliKfMhWQQBSOiGByaLSgTh/view?usp=sharing',
		// 	'/escritorio' => 'https://drive.google.com/file/d/164C9nKVL2QycIT5phdwCUAQUbuOyusMj/view?usp=sharing',
		// 	'/naturezaOperacao' => 'https://drive.google.com/file/d/1NOfNSNxTQ2-VY6qewn9U_vGRxu_IzPxd/view?usp=sharing',
		// 	'/tributos' => 'https://drive.google.com/file/d/1KfUQXwcUUvRwyIEv7igzrDfWQ0Frrf_D/view?usp=sharing',
		// 	'/fornecedores' => 'https://drive.google.com/file/d/1FmJTVMJnUEz0PFujMCpGjh591nJ5Uae-/view?usp=sharing',
		// 	'/produtos' => 'https://drive.google.com/file/d/1ndXAccVUHIMNC0Ll53NeOm3Y48xGj3bf/view?usp=sharing',
		// 	'/produtos/importacao' => 'https://drive.google.com/file/d/1NvOSsed7AgMgE44cEzOJAiWMnhAglJ6W/view?usp=sharing',
		// 	'/divisaoGrade' => 'https://drive.google.com/file/d/1-ReMDrm04mnlaKk1f-zq5aO2s4r81Kce/view?usp=sharing',
		// 	'/compraFiscal' => 'https://drive.google.com/file/d/18o_GlEiHC7TRAPZ0l6vCZxaQuDhZoJ5a/view?usp=sharing',
		// 	'/compraManual' => 'https://drive.google.com/file/d/13pDN7ET19mIr6Ge9Pl7jEt_5Gq7Vpn69/view?usp=sharing',
		// 	'/compras' => 'https://drive.google.com/file/d/13pDN7ET19mIr6Ge9Pl7jEt_5Gq7Vpn69/view?usp=sharing',
		// 	'/compras/emitirEntrada' => 'https://drive.google.com/file/d/1WN7YD3Cmr720MBwHQZ4rr7S5nBNT1jDV/view?usp=sharing',
		// 	'/cotacao' => 'https://drive.google.com/file/d/1eZBuE-T_whQ2mD8fwkwDpLr43jhMMs1f/view?usp=sharing',
		// 	'/dfe' => 'https://drive.google.com/file/d/150WTYsrSftNNfx2kwgFqX0-rRKcaIz8M/view?usp=sharing',
		// 	'/estoque' => 'https://drive.google.com/file/d/1EMyWD5oWhIj2gaOC4hJywNeJGnBiEooz/view?usp=sharing',
		// 	'/caixa' => 'https://drive.google.com/file/d/1s3eUF-novBql18jvvnN9ATRKknYRTsfM/view?usp=sharing',
		// 	'/vendas' => 'https://drive.google.com/file/d/13eXTX1B6z_K-8ijARwDFfU2fyH6M2rbC/view?usp=sharing',
		// 	'/vendas/nova' => 'https://drive.google.com/file/d/1oGcn4AMmrYlMP6fBS_lOqa_W6EWooRkD/view?usp=sharing',
		// 	'/vendas/detalhar' => 'https://drive.google.com/file/d/11JV_AkXEhLtoYRYapqN5FIIVncllNcCg/view?usp=sharing',
		// 	'/orcamentoVenda' => 'https://drive.google.com/file/d/1b3aP2N2BhJzt4_H_7R6VCj_l2LjhgxbF/view?usp=sharing',
		// 	'/listaDePrecos' => 'https://drive.google.com/file/d/1OT8JlPAiAwgpsvemJ4EfvQepx4bAVwQ1/view?usp=sharing',
		// 	'/frenteCaixa' => 'https://drive.google.com/file/d/1Km8lJFaetXVEU-6OYHgP4tzLSN-bnHOY/view?usp=sharing',
		// 	'/frenteCaixa/devolucao' => 'https://drive.google.com/file/d/1to_8xsGXlzO_-oI7GGJIBvy7OhVplD3b/view?usp=sharing',
		// 	'/marcas' => 'https://drive.google.com/file/d/1oGcn4AMmrYlMP6fBS_lOqa_W6EWooRkD/view?usp=sharing'
		// ];
	}

	public function getUrlVideo($uri){
		$temp = explode("/", $uri);
		$uri = "/".$temp[1];
		if(isset($temp[2])){
			$uri .= "/".$temp[2];
		}

		$videosArray = $this->videos();
		if(isset($videosArray[$uri]) && env("VIDEO_AJUDA") == 1){
			return $videosArray[$uri];
		}
		return null;
	}
}
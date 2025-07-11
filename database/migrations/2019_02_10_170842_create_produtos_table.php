<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')
            ->on('categorias')->onDelete('cascade');

            $table->integer('sub_categoria_id')->unsigned()->nullable();
            $table->foreign('sub_categoria_id')->references('id')
            ->on('sub_categorias')->onDelete('cascade');

            $table->integer('marca_id')->unsigned()->nullable();
            $table->foreign('marca_id')->references('id')->on('marcas')
            ->onDelete('cascade');

            $table->string('nome', 100);
            $table->string('cor', 20);
            $table->integer('referencia_balanca')->default(0);
            
            $table->decimal('valor_venda', 22,7)->default(0);
            $table->decimal('valor_compra', 22,7)->default(0);
            $table->boolean('reajuste_automatico')->default(0);
            $table->decimal('percentual_lucro', 10,2)->default(0);
            $table->string('NCM', 13)->default("");
            $table->string('codBarras', 15)->default("");
            $table->string('CEST', 10)->default("");
            $table->string('CST_CSOSN', 3)->default("");
            $table->string('CST_PIS', 3)->default("");
            $table->string('CST_COFINS', 3)->default("");
            $table->string('CST_IPI', 3)->default("");

            $table->string('CST_CSOSN_EXP', 3)->default("");

            $table->string('unidade_compra', 10);
            $table->string('conversao_unitaria')->default(1);
            $table->string('unidade_venda', 10);

            $table->boolean('composto')->default(false);
            $table->boolean('valor_livre');

            $table->decimal('perc_icms', 10,2)->default(0);
            $table->decimal('perc_pis', 10,2)->default(0);
            $table->decimal('perc_cofins', 10,2)->default(0);
            $table->decimal('perc_ipi', 10,2)->default(0);
            $table->decimal('perc_iss', 10,2)->default(0);
            $table->string('cListServ', 5)->nullable();

            $table->string('CFOP_saida_estadual', 5);
            $table->string('CFOP_saida_inter_estadual', 5);

            $table->string('codigo_anp', 10);
            $table->string('descricao_anp', 95);
            $table->decimal('perc_glp', 5,2)->default(0);
            $table->decimal('perc_gnn', 5,2)->default(0);
            $table->decimal('perc_gni', 5,2)->default(0);
            $table->decimal('valor_partida', 10, 2)->default(0);
            $table->string('unidade_tributavel', 10)->default('');
            $table->decimal('quantidade_tributavel', 10, 2)->default(0);

            $table->string('imagem', 100);
            $table->integer('alerta_vencimento');
            $table->boolean('gerenciar_estoque');
            $table->integer('estoque_minimo')->default(0);
            $table->string('referencia', 25)->default('');

            $table->decimal('pRedBC', 5,2)->default(0);
            $table->decimal('pDif', 5,2)->default(0);
            $table->string('cBenef',10)->default('');

            $table->decimal('largura', 6, 2)->default(0);
            $table->decimal('comprimento', 6, 2)->default(0);
            $table->decimal('altura', 6, 2)->default(0);
            $table->decimal('peso_liquido', 8, 3)->default(0);
            $table->decimal('peso_bruto', 8, 3)->default(0);
            
            $table->decimal('limite_maximo_desconto', 5, 2)->default(0);

            $table->string('referencia_grade', 20)->default('');
            $table->boolean('grade')->default(false);
            $table->string('str_grade', 20)->default('');

            $table->decimal('perc_icms_interestadual', 10,2)->default(0);
            $table->decimal('perc_icms_interno', 10,2)->default(0);
            $table->decimal('perc_fcp_interestadual', 10,2)->default(0);
            $table->boolean('inativo')->default(false);

            $table->string('renavam', 20)->default('');
            $table->string('placa', 9)->default('');
            $table->string('chassi', 30)->default('');
            $table->string('combustivel', 15)->default('');
            $table->string('ano_modelo', 9)->default('');
            $table->string('cor_veiculo', 20)->default('');

            $table->decimal('valor_locacao', 10,4)->default(0);

            $table->string('lote', 10)->default('');
            $table->string('vencimento', 10)->default('');
            $table->integer('origem')->default(0);

            $table->string('tipo_dimensao', 15)->default('');
            $table->decimal('perc_comissao', 5, 2)->default(0);
            $table->decimal('valor_comissao', 9, 2)->default(0);
            $table->decimal('acrescimo_perca', 5, 2)->default(0);

            $table->string('nuvemshop_id', 20)->default('');
            $table->string('ifood_id', 40)->default('');
            $table->text('info_tecnica_composto')->nullable();
            $table->text('observacao')->nullable();

            $table->string('CST_CSOSN_entrada', 3)->default("");
            $table->string('CST_PIS_entrada', 3)->default("");
            $table->string('CST_COFINS_entrada', 3)->default("");
            $table->string('CST_IPI_entrada', 3)->default("");

            $table->string('CFOP_entrada_estadual', 5);
            $table->string('CFOP_entrada_inter_estadual', 5);

            $table->decimal('custo_assessor', 10,2)->default(0);
            $table->boolean('envia_controle_pedidos')->default(0);
            $table->string('cenq_ipi', 3)->default("999");
            $table->integer('tela_pedido_id')->default(0);

            $table->integer('modBCST')->default(0);
            $table->integer('modBC')->default(0);
            $table->decimal('pICMSST', 5, 2)->default(0);
            $table->text('locais');

            $table->decimal('perc_frete', 6, 2)->default(0);
            $table->decimal('perc_outros', 6, 2)->default(0);
            $table->decimal('perc_mlv', 6, 2)->default(0);
            $table->decimal('perc_mva', 6, 2)->default(0);
            $table->decimal('qBCMonoRet', 10, 4)->default(0);
            $table->decimal('adRemICMSRet', 10, 4)->default(0);
            $table->decimal('pBio', 10, 4)->default(0);
            $table->boolean('tipo_servico')->default(0);
            $table->integer('indImport')->default(0);
            $table->string('cUFOrig', 2)->nullable();
            $table->decimal('pOrig', 5, 2)->default(0);
            $table->decimal('peso', 12, 3)->default(0);
            $table->text('info_adicional_item');

            $table->decimal('valor_atacado', 22,7)->default(0);
            $table->integer('quantidade_atacado')->nullable();

            $table->string('referencia_xml', 50)->nullable();
            $table->string('sku', 30)->nullable();
            $table->string('tipo_item_sped', 2)->nullable();

            $table->string('mercado_livre_id', 20)->nullable();
            $table->string('mercado_livre_link', 255)->nullable();
            $table->decimal('mercado_livre_valor', 12, 4)->nullable();
            $table->string('mercado_livre_categoria', 20)->nullable();
            $table->string('condicao_mercado_livre', 20)->nullable();
            $table->integer('quantidade_mercado_livre')->nullable();
            $table->string('mercado_livre_tipo_publicacao', 20)->nullable();
            $table->string('mercado_livre_youtube', 100)->nullable();
            $table->text('mercado_livre_descricao');
            $table->string('mercado_livre_status', 20);
            $table->string('mercado_livre_modelo', 100)->nullable();

            $table->decimal('perc_icms_compra', 5, 2)->nullable();
            $table->decimal('perc_diferenca_icms', 5, 2)->nullable();
            $table->decimal('perc_ipi_compra', 5, 2)->nullable();
            $table->decimal('frete_compra', 10, 2)->nullable();
            $table->decimal('custo_financeiro_compra', 10, 2)->nullable();
            $table->decimal('embalagem_compra', 10, 2)->nullable();
            $table->decimal('desconto_compra', 10, 2)->nullable();
            $table->decimal('custo_adicional_compra', 10, 2)->nullable();
            $table->decimal('custo_produto', 10, 2)->nullable();

            $table->decimal('perc_imposto_federal', 5, 2)->nullable();
            $table->decimal('perc_imposto_estadual', 5, 2)->nullable();
            $table->decimal('custo_financeiro', 10, 2)->nullable();
            $table->decimal('comissao', 5, 2)->nullable();
            $table->decimal('capital_giro', 10, 2)->nullable();
            $table->decimal('custo_operacional', 10, 2)->nullable();
            $table->decimal('rentabilidade', 5, 2)->nullable();
            $table->decimal('lucro_liquido', 10, 2)->nullable();

            // alter table produtos add column perc_icms_compra decimal(5,2) default null;
            // alter table produtos add column perc_diferenca_icms decimal(5,2) default null;
            // alter table produtos add column perc_ipi_compra decimal(5,2) default null;
            // alter table produtos add column frete_compra decimal(10, 2) default null;
            // alter table produtos add column custo_financeiro_compra decimal(10, 2) default null;
            // alter table produtos add column embalagem_compra decimal(10, 2) default null;
            // alter table produtos add column desconto_compra decimal(10, 2) default null;
            // alter table produtos add column custo_adicional_compra decimal(10, 2) default null;
            // alter table produtos add column custo_produto decimal(10, 2) default null;

            // alter table produtos add column perc_imposto_federal decimal(5,2) default null;
            // alter table produtos add column perc_imposto_estadual decimal(5,2) default null;
            // alter table produtos add column custo_financeiro decimal(10, 2) default null;
            // alter table produtos add column comissao decimal(5,2) default null;
            // alter table produtos add column capital_giro decimal(10, 2) default null;
            // alter table produtos add column custo_operacional decimal(10, 2) default null;
            // alter table produtos add column rentabilidade decimal(5,2) default null;
            // alter table produtos add column lucro_liquido decimal(10, 2) default null;

            $table->timestamps();
        });
}

    // alter table produtos add column valor_atacado decimal(22,7) default 0;
    // alter table produtos add column quantidade_atacado integer default null;

    // alter table produtos add column perc_glp decimal(5,2) default 0;
    // alter table produtos add column perc_gnn decimal(5,2) default 0;
    // alter table produtos add column perc_gni decimal(5,2) default 0;
    // alter table produtos add column valor_partida decimal(10, 2) default 0;
    // alter table produtos add column unidade_tributavel varchar(4) default '';
    // alter table produtos add column quantidade_tributavel decimal(10, 2) default 0;

    // alter table produtos add column perc_icms_interestadual decimal(10, 2) default 0;
    // alter table produtos add column perc_icms_interno decimal(10, 2) default 0;
    // alter table produtos add column perc_fcp_interestadual decimal(10, 2) default 0;


    // alter table produtos add column renavam varchar(20) default '';
    // alter table produtos add column placa varchar(9) default '';
    // alter table produtos add column chassi varchar(30) default '';
    // alter table produtos add column combustivel varchar(15) default '';
    // alter table produtos add column ano_modelo varchar(9) default '';
    // alter table produtos add column cor_veiculo varchar(20) default '';
    // alter table produtos add column reajuste_automatico boolean default 0;

    // alter table produtos add column valor_locacao decimal(10, 4) default 0;

    // alter table produtos add column lote varchar(10) default '';
    // alter table produtos add column vencimento varchar(10) default '';
    // alter table produtos add column origem integer default 0;

    // alter table produtos add column tipo_dimensao varchar(15) default '';
    // alter table produtos add column perc_comissao decimal(5,2) default 0;
    // alter table produtos add column acrescimo_perca decimal(5,2) default 0;

    // alter table produtos add column nuvemshop_id varchar(20) default '';
    // alter table produtos add column ifood_id varchar(40) default '';
    // alter table produtos add column info_tecnica_composto text;
    // alter table produtos add column observacao text;

    // alter table produtos add column CST_CSOSN_entrada varchar(3) default '';
    // alter table produtos add column CST_PIS_entrada varchar(3) default '';
    // alter table produtos add column CST_COFINS_entrada varchar(3) default '';
    // alter table produtos add column CST_IPI_entrada varchar(3) default '';
    // alter table produtos add column CFOP_entrada_estadual varchar(5) default '';
    // alter table produtos add column CFOP_entrada_inter_estadual varchar(5) default '';

    // alter table produtos add column custo_assessor decimal(10,2) default 0;
    // alter table produtos add column envia_controle_pedidos boolean default 0;

    // alter table produtos add column cenq_ipi varchar(3) default '999';
    // alter table produtos add column tela_pedido_id integer default 0;

    // alter table produtos add column modBCST integer default 0;
    // alter table produtos add column modBC integer default 0;
    // alter table produtos add column pICMSST decimal(5,2) default 0;

    // alter table produtos add column locais text;

    // alter table produtos add column perc_frete decimal(6,2) default 0;
    // alter table produtos add column perc_outros decimal(6,2) default 0;
    // alter table produtos add column perc_mlv decimal(6,2) default 0;
    // alter table produtos add column perc_mva decimal(6,2) default 0;

    // alter table produtos add column valor_comissao decimal(9,2) default 0;

    // alter table produtos add column qBCMonoRet decimal(10,4) default 0;
    // alter table produtos add column adRemICMSRet decimal(10,4) default 0;
    // alter table produtos add column pBio decimal(10,4) default 0;
    // alter table produtos add column tipo_servico boolean default 0;

    // alter table produtos add column cUFOrig varchar(2) default null;
    // alter table produtos add column pOrig decimal(5,2) default 0;
    // alter table produtos add column indImport integer default 0;

    // alter table produtos modify column adRemICMSRet decimal(10,4) default 0;
    // alter table produtos modify column valor_venda decimal(22,7) default 0;
    // alter table produtos add column peso decimal(12,3) default 0;
    // alter table produtos add column info_adicional_item text;
    // alter table produtos modify column unidade_tributavel varchar(10) default '';
    // alter table produtos add column referencia_xml varchar(50) default null;
    // alter table produtos add column sku varchar(30) default null;

    // alter table produtos add column tipo_item_sped varchar(2) default null;

// alter table produtos add column mercado_livre_id varchar(20) default null;
// alter table produtos add column mercado_livre_link varchar(255) default null;
// alter table produtos add column mercado_livre_valor decimal(12, 4) default null;

// alter table produtos add column mercado_livre_categoria varchar(20) default null;
// alter table produtos add column condicao_mercado_livre varchar(20) default null;
// alter table produtos add column quantidade_mercado_livre integer default null;
// alter table produtos add column mercado_livre_tipo_publicacao varchar(20) default null;
// alter table produtos add column mercado_livre_youtube varchar(100) default null;
// alter table produtos add column mercado_livre_descricao text;
// alter table produtos add column mercado_livre_status varchar(20) default null;
// alter table produtos add column mercado_livre_modelo varchar(100) default null;

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}

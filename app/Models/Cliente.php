<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pais;
class Cliente extends Model
{
	protected $fillable = [
		'razao_social', 'nome_fantasia', 'bairro', 'numero', 'rua', 'cpf_cnpj', 'telefone', 
		'celular', 'email', 'cep', 'ie_rg', 'consumidor_final', 'limite_venda', 'cidade_id', 
		'contribuinte', 'rua_cobranca', 'numero_cobranca', 'bairro_cobranca', 'cep_cobranca', 
		'cidade_cobranca_id', 'empresa_id', 'cod_pais', 'id_estrangeiro', 'grupo_id', 
		'contador_nome', 'contador_telefone', 'funcionario_id', 'observacao', 
		'contador_email', 'data_aniversario', 'complemento', 'nuvemshop_id',
		'imagem', 'data_nascimento', 'instagram', 'facebook', 'linkedin', 'tiktok', 'whatsapp',
		'inativo', 'acessor_id', 'rua_entrega', 'numero_entrega', 'bairro_entrega', 'cep_entrega',
		'cidade_entrega_id', 'nome_entrega', 'cpf_cnpj_entrega', 'valor_cashback',
		'nome_responsavel', 'cpf_responsavel', 'rg_responsavel', 'data_nascimento_responsavel',
		'rua_responsavel', 'numero_responsavel', 'bairro_responsavel', 'cidade_responsavel',
		'complemento_responsavel', 'cep_responsavel', 'email_responsavel', 'telefone_responsavel'
	];

	protected $appends = [ 'status' ];

	public function getStatusAttribute()
    {
        return $this->inativo ? 0 : 1;
    }

	public function getEnderecoAttribute()
	{
		return "$this->rua, $this->numero - $this->bairro " . $this->cidade->info;
	}

	public function cidade(){
		return $this->belongsTo(Cidade::class, 'cidade_id');
	}

	public function cidadeEntrega(){
		return $this->belongsTo(Cidade::class, 'cidade_entrega_id');
	}

	public function receitaOtica(){
		return $this->hasOne(ClienteOtica::class, 'cliente_id');
	}

	public function cashBacks(){
		return $this->hasMany(CashBackCliente::class, 'cliente_id');
	}

	public function uploads(){
		return $this->hasMany(ClienteUpload::class, 'cliente_id');
	}

	public static function tiposPesquisa(){
		return [
			'razao_social' => 'Razão social',
			'nome_fantasia' => 'Nome fantasia',
			'telefone' => 'Telefone',
		];
	}

	public static function estados(){
		return [
			"AC",
			"AL",
			"AM",
			"AP",
			"BA",
			"CE",
			"DF",
			"ES",
			"GO",
			"MA",
			"MG",
			"MS",
			"MT",
			"PA",
			"PB",
			"PE",
			"PI",
			"PR",
			"RJ",
			"RN",
			"RS",
			"RO",
			"RR",
			"SC",
			"SE",
			"SP",
			"TO"	
		];
	}

	public static function verificaCadastrado($cnpj){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$forn = Cliente::where('cpf_cnpj', $cnpj)
		->where('empresa_id', $empresa_id)
		->first();

		return $forn;
	}

	public function getPais(){
		$pais = Pais::where('codigo', $this->cod_pais)->first();
		return $pais->nome;
	}

	public function valorCredito(){
		return TrocaVenda::
		where('cliente_id', $this->id)
		->where('status', 0)
		->sum('valor_credito');
	}
}

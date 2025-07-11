<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tributacao extends Model
{
	protected $fillable = [
		'icms', 'pis', 'cofins', 'regime', 'ipi', 'ncm_padrao', 'empresa_id', 'link_nfse',
		'perc_ap_cred', 'exclusao_icms_pis_cofins'
	];

	public static function regimes(){
		return [ 
			0 => 'Simples',
			1 => 'Normal',
			2 => 'MEI',
		];
	}

	public function getCRT(){
		if($this->regime == 0) return 1;
		elseif($this->regime == 1) return 3;
		elseif($this->regime == 2) return 4;
	}
}

<?php 
use Illuminate\Support\Facades\DB;
use App\Models\EscritorioContabil;
use App\Models\RecordLog;
use App\Models\ConfigNota;
use App\Models\Filial;
use App\Models\Usuario;
use App\Models\ErroLog;
use App\Models\SuperAdminAlerta;
use App\Models\Redirect;
use App\Models\ConfigSystem;

function is_adm(){
	$usr = session('user_logged');
	return $usr['adm'];
}

function get_id_user(){
	$usr = session('user_logged');
	return $usr['id'];
}

function __replace($valor){
	return str_replace(",", ".", $valor);
}

function moeda($valor){
	return number_format($valor, 2, ',', '');
}
// function moeda_format($valor){
// 	return number_format($valor, 2, ',', '.');
// }

function __date($data, $time = true){
	if($time){
		return \Carbon\Carbon::parse($data)->format('d/m/Y H:i');
	}else{
		return \Carbon\Carbon::parse($data)->format('d/m/Y');
	}
}

function valida_objeto($objeto){
	$usr = session('user_logged');
	if(isset($objeto['empresa_id']) && $objeto['empresa_id'] == $usr['empresa']){
		return true;
	}else{
		return false;
	}
}

function tabelasArmazenamento(){
	// indice nome da tabela, valor em kb
	return [
		'clientes' => 5,
		'produtos' => 8,
		'fornecedors' => 4,
		'vendas' => 4,
		'venda_caixas' => 4,
		'transportadoras' => 4,
		'orcamentos' => 4,
		'categorias' => 4,
	];
}

function isSuper($login){
	$arrSuper = explode(',', env("USERMASTER"));

	if(in_array($login, $arrSuper)){
		return true;
	}
	return false;
}

function getSuper(){
	$arrSuper = explode(',', env("USERMASTER"));

	return $arrSuper[0];
}

function importaXmlSieg($file, $empresa_id){
	$escritorio = EscritorioContabil::
	where('empresa_id', $empresa_id)
	->first();

	if($escritorio != null && $escritorio->token_sieg != ""){
		$url = "https://api.sieg.com/aws/api-xml.ashx";

		$curl = curl_init();

		$headers = [];

		$data = $file;
		curl_setopt($curl, CURLOPT_URL, $url . "?apikey=".$escritorio->token_sieg."&email=".$escritorio->email);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$xml = json_decode(curl_exec($curl));
		if(isset($xml->Message)){
			if($xml->Message == 'Importado com sucesso'){
				return $xml->Message;
			}
		}
		return false;
	}else{
		return false;
	}
}

function __saveLog($record){
	RecordLog::create($record);
}

function __saveError($error, $empresa_id){
	ErroLog::create([
		'arquivo' => $error->getFile(),
		'linha' => $error->getLine(),
		'erro' => $error->getMessage(),
		'empresa_id' => $empresa_id
	]);

	__saveAlertSuper('Erro no sistema', $error->getMessage(), $empresa_id);
}

function __saveAlertSuper($tipo, $mensagem, $empresa_id){
	SuperAdminAlerta::create([
		'tipo' => $tipo,
		'mensagem' => $mensagem,
		'empresa_id' => $empresa_id
	]);
}

function __saveRedirect($empresa_id, $rota, $local){
	$redirect = Redirect::where('empresa_id', $empresa_id)
	->where('local', $local)->first();
	if($redirect == null){
		Redirect::create([
			'empresa_id' => $empresa_id,
			'rota' => $rota,
			'local' => $local
		]);
	}else{
		$redirect->local = $local;
		$redirect->rota = $rota;
		$redirect->save();
	}
}

function __getRedirect($empresa_id, $local){
	$redirect = Redirect::where('empresa_id', $empresa_id)
	->where('local', $local)->first();
	if($redirect != null){
		return $redirect->rota;
	}
	return "";
}


function valor_por_extenso($valor = 0, $maiusculas = false) {

	$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
	$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões",
		"quatrilhões");

	$c = array("", "cem", "duzentos", "trezentos", "quatrocentos",
		"quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
	$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta",
		"sessenta", "setenta", "oitenta", "noventa");
	$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze",
		"dezesseis", "dezesete", "dezoito", "dezenove");
	$u = array("", "um", "dois", "três", "quatro", "cinco", "seis",
		"sete", "oito", "nove");

	$z = 0;
	$rt = "";

	$valor = number_format($valor, 2, ".", ".");
	$inteiro = explode(".", $valor);
	for($i=0;$i<count($inteiro);$i++)
		for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
			$inteiro[$i] = "0".$inteiro[$i];

		$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
		for ($i=0;$i<count($inteiro);$i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd &&
				$ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
			if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) &&
				($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
		}

	if(!$maiusculas){
		return($rt ? $rt : "zero");
	} else {

		if ($rt) $rt=ereg_replace(" E "," e ",ucwords($rt));
		return (($rt) ? ($rt) : "Zero");
	}

}

function __locaisAtivosUsuario($usuario){
	$locais = $usuario->locais != 'null' && $usuario->locais != '' ? json_decode($usuario->locais) : [];
	$locaisRetorno = [];
	$locaisRetorno['-1'] = 'Matriz';

	foreach($locais as $l){
		if($l != -1){
			$f = Filial::where('status', 1)->where('id', $l)->first();
			if($f != null){
				$locaisRetorno[$f->id] = $f->descricao;
			}
		}
	}
	return $locaisRetorno;

}

function __getLocaisUsarioLogado(){
	$usr = Usuario::find(get_id_user());
	$locais = [];
	$loc = $usr->locais != null ? json_decode($usr->locais) : [];
	return $loc;
}

function getLocaisUsarioLogado(){
	$usr = Usuario::find(get_id_user());
	$locais = [];
	$loc = $usr->locais != null && $usr->locais != 'null' ? json_decode($usr->locais) : [];
	if(sizeof($loc) > 0){
		foreach($loc as $l){
			$f = Filial::find($l);
			if($l == '-1'){
				$locais['-1'] = 'Matriz';
			}else{
				if($f != null){
					$locais[$f->id] = $f->descricao;
				}
			}
		}

	}
	return $locais;

}

function __locaisAtivos(){
	$usr = session('user_logged');

	$locais = getLocaisUsarioLogado();
	if(sizeof($locais) > 0){
		return $locais;
	}
	// $config = ConfigNota::
	// where('empresa_id', $usr['empresa'])
	// ->first();
	$filiais = Filial::
	where('empresa_id', $usr['empresa'])
	->where('status', 1)
	->get();
	
	$locais['-1'] = 'Matriz';

	// foreach($filiais as $f){
	// 	$locais[$f->id] = $f->descricao;
	// }
	return $locais;
}

function __locaisAtivosAll(){
	$usr = session('user_logged');

	$filiais = Filial::
	where('empresa_id', $usr['empresa'])
	->where('status', 1)
	->get();
	
	$locais['-1'] = 'Matriz';

	foreach($filiais as $f){

		$locais[$f->id] = $f->descricao;
	}
	return $locais;
}

function __get_local_padrao(){
	$usr = Usuario::find(get_id_user());
	// if($usr->local_padrao == -1) return NULL;
	return $usr->local_padrao;
}

function __user_all_locations(){
	if(sizeof(__locaisAtivosAll()) == sizeof(__locaisAtivos()))
		return true;
	else
		return false;
}

function __view_locais_select_home($lbl = "Local", $filial_id = null){
	$locais = __locaisAtivos();

	if(sizeof($locais) > 1){

		$local_padrao = __get_local_padrao();

		$html = '<div class="form-group col-12 col-lg-2">';
		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div><div class="input-group">';
		$html .= '<select id="filial_id" name="filial_id" class="form-control custom-select">';
		if(__user_all_locations()){
			$html .= '<option value="">--</option>';
		}
		foreach($locais as $key => $l){
			$html .= '<option '. ($local_padrao == $key ? 'selected' : '') .' value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div></div>';
	}else{
		$v = array_key_first($locais);
		if($v == -1){
			$html = "";
		}else{
			$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
		}
	}

	return $html;
}

function __view_locais_select_relatorios(){
	$locais = __locaisAtivos();
	$html = "";
	if(sizeof($locais) > 1){

		$html = '<div class="form-group col-12 col-md-6">';
		$html .= '<label class="col-form-label">Local</label>';
		$html .= '<select name="filial_id" class="form-control custom-select w-100">';
		$html .= '<option value="">--</option>';
		foreach($locais as $key => $l){
			$html .= '<option value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div>';
	}

	return $html;
}

function __view_locais_select_filtro($lbl = "Local", $filial_id = null){
	$locais = __locaisAtivos();
	if(sizeof($locais) > 1){
		if($filial_id == null){

			$url = request()->fullUrl();
			if (!str_contains($url, 'filtro')) {
				$filial_id = __get_local_padrao();
			}
		}

		$html = '<div class="form-group col-12 col-lg-2">';
		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div><div class="input-group">';
		$html .= '<select id="locais" name="filial_id" class="form-control custom-select">';
		$html .= '<option value="">--</option>';
		foreach($locais as $key => $l){
			$html .= '<option '.($filial_id == $key ? 'selected' : '').' value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div></div>';
	}else{

		$v = array_key_first($locais);
		if($v == -1){
			$html = "";
		}else{
			$html = "<input type='hidden' name='filial_id' value='$v' />";
		}
	}

	return $html;
}

function __view_locais_select_filtro_xml($filial_id = null){
	$locais = __locaisAtivos();

	if(sizeof($locais) > 1){

		if($filial_id == null){
			$filial_id = __get_local_padrao();
		}

		$html = '<div class="form-group col-12 col-lg-2">';
		$html .= '<label class="col-form-label">Local</label>';
		$html .= '<div><div class="input-group">';
		$html .= '<select id="locais" name="filial_id" class="form-control custom-select">';
		foreach($locais as $key => $l){
			$html .= '<option '.($filial_id == $key ? 'selected' : '').' value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div></div>';
	}else{
		$v = array_key_first($locais);
		if($v == -1){
			$html = "";
		}else{
			$html = "<input type='hidden' name='filial_id' value='$v' />";
		}
	}

	return $html;
}

function __view_locais_select($lbl = "Local"){
	$locais = __locaisAtivos();

	if(sizeof($locais) > 1){
		$local_padrao = __get_local_padrao();
		$html = '<div class="form-group col-lg-2 col-sm-6">';

		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div>';
		$html .= '<select name="filial_id" id="filial_id" class="form-control custom-select" required>';
		$html .= '<option value="">--</option>';
		foreach($locais as $key => $l){
			$html .= '<option '. ($local_padrao == $key ? 'selected' : '') .' value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		
		$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
	}

	return $html;
}

function __view_locais_select_pdv($lbl = "Local"){
	$locais = __locaisAtivos();

	if(sizeof($locais) > 1){

		$html = '<div class="form-group col-lg-12 col-sm-6">';

		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div>';
		$html .= '<select name="filial_id" id="filial_id" class="form-control custom-select" required>';
		$html .= '<option value="">--</option>';
		foreach($locais as $key => $l){
			$html .= '<option value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		
		$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
	}

	return $html;
}

function __setMask($doc){
	$doc = preg_replace('/[^0-9]/', '', $doc);
	$mask = '##.###.###/####-##';
	if (strlen($doc) == 11) {
		$mask = '###.###.###-##';
	}
	return __mask($doc, $mask);
}

function __mask($val, $mask){
	$maskared = '';
	$k = 0;
	for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
		if ($mask[$i] == '#') {
			if (isset($val[$k])) {
				$maskared .= $val[$k++];
			}
		} else {
			if (isset($mask[$i])) {
				$maskared .= $mask[$i];
			}
		}
	}

	return $maskared;
}

function __view_locais_select_transfencia($lbl, $variavel){
	$locais = __locaisAtivos();

	if(sizeof($locais) > 1){

		$html = '<div class="form-group col-lg-2 col-sm-6">';

		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div>';
		$html .= '<select name="'.$variavel.'" id="'.$variavel.'" class="form-control custom-select" required>';
		$html .= '<option value="">--</option>';
		foreach($locais as $key => $l){
			$html .= '<option value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		
		$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
	}

	return $html;
}

function __view_locais_select_edit($lbl, $local_id){
	$locais = __locaisAtivos();

	if(sizeof($locais) > 1){
		if(!$local_id){
			$local_id = -1;
		}
		$html = '<div class="form-group col-lg-2 col-sm-6">';

		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div>';
		$html .= '<select name="filial_id" id="filial_id" class="form-control custom-select" required>';
		$html .= '<option value="">--</option>';
		foreach($locais as $key => $l){
			$html .= '<option '. ($key == $local_id ? 'selected' : '') .' value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		
		$html = "<input type='hidden' id='filial_id' name='filial_id' value='$v' />";
	}

	return $html;
}

function __view_locais($lbl = "Locais de acesso"){
	$locais = __locaisAtivos();

	if(sizeof($locais) > 1){

		$html = '<div class="form-group validated col-sm-10 col-lg-3">';
		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div class="">';
		$html .= '<select id="locais" name="local[]" required class="form-control select2-custom" multiple>';
		foreach($locais as $key => $l){
			$html .= '<option value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		if($v == -1){
			$html = "";
		}else{
			$html = "<input type='hidden' name='local[]' value='$v' />";
		}
	}

	return $html;
}


function __view_locais_user($lbl = "Locais de acesso"){
	$locais = __locaisAtivosAll();

	if(sizeof($locais) > 1){

		$html = '<div class="form-group validated col-sm-10 col-lg-3">';
		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div class="">';
		$html .= '<select id="locais" name="local[]" required class="form-control select2-custom" multiple>';
		foreach($locais as $key => $l){
			$html .= '<option value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		if($v == -1){
			$html = "";
		}else{
			$html = "<input type='hidden' name='local[]' value='$v' />";
		}
	}

	return $html;
}

function __view_locais_user_edit($locais_ativos, $lbl = "Locais de acesso"){
	$locais = __locaisAtivosAll();

	$locais_ativos = $locais_ativos != null && $locais_ativos != 'null' ? json_decode($locais_ativos) : [];
	if(sizeof($locais) > 1){
		$html = '<div class="form-group validated col-sm-10 col-lg-3">';
		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div class="">';
		$html .= '<select id="locais" name="local[]" required class="form-control select2-custom" multiple>';
		foreach($locais as $key => $l){
			$html .= '<option '. (in_array($key, $locais_ativos) ? ' selected ' : '') .' value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		if($v == -1){
			$html = "";
		}else{
			$html = "<input type='' name='local[]' value='$v' />";
		}
	}

	return $html;
}

function __view_locais_edit($locais_ativos, $lbl = "Locais de acesso"){
	$locais = __locaisAtivos();
	if(sizeof($locais) > 1){
		$locais_ativos = $locais_ativos == 'null' ? [] : json_decode($locais_ativos);

		$html = '<div class="form-group validated col-sm-10 col-lg-3">';
		$html .= '<label class="col-form-label">'.$lbl.'</label>';
		$html .= '<div class="">';
		$html .= '<select id="locais" name="local[]" required class="form-control select2-custom" multiple>';
		foreach($locais as $key => $l){
			$html .= '<option '. (in_array($key, $locais_ativos) ? ' selected ' : '') .' value="'.$key.'">'.$l.'</option>';
		}
		$html .= '</select></div></div>';
	}else{
		$v = array_key_first($locais);
		if($v == -1){
			$html = "";
		}else{
			$html = "<input type='hidden' name='local[]' value='$v' />";
		}
	}

	return $html;
}

function __getLocalUser(){
	$padrao = __get_local_padrao();
	if($padrao) return $padrao;

	$usr = Usuario::find(get_id_user());
	$loc = $usr->locais != null ? json_decode($usr->locais) : [];
	if(sizeof($loc) > 0){
		return $loc[0];
	}
	return null;
}

function __get_locais($locais_ativos){
	// $locais_ativos = $locais_ativos ? json_decode($locais_ativos) : [];
	$locais_ativos = $locais_ativos != null && $locais_ativos != 'null' ? json_decode($locais_ativos) : [];

	$html = "";
	foreach($locais_ativos as $l){
		$f = Filial::find($l);
		if($l == '-1'){
			$html .= "Matriz | ";
		}else{
			if($f != null){
				$html .= "$f->descricao | ";
			}
		}

	}

	$html = substr($html, 0, strlen($html)-2);
	return $html;
}

function empresaComFilial(){
	$usr = session('user_logged');

	$filiais = Filial::
	where('empresa_id', $usr['empresa'])
	->where('status', 1)
	->exists();
	return $filiais;
}

function __preparaTexto($texto, $empresa){
	$texto = str_replace("{{nome}}", $empresa->nome, $texto);
	$texto = str_replace("{{rua}}", $empresa->rua, $texto);
	$texto = str_replace("{{numero}}", $empresa->numero, $texto);
	$texto = str_replace("{{bairro}}", $empresa->bairro, $texto);
	$texto = str_replace("{{nome_fantasia}}", $empresa->nome_fantasia, $texto);
	$texto = str_replace("{{email}}", $empresa->email, $texto);
	$texto = str_replace("{{cidade}}", $empresa->cidade, $texto);
	$texto = str_replace("{{cnpj}}", $empresa->cnpj, $texto);
	$texto = str_replace("{{data}}", date("d/m/Y H:i"), $texto);

	$mes =  ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
	$texto = str_replace("{{d}}", date("d"), $texto);
	$texto = str_replace("{{m}}", $mes[(int)date("m")], $texto);
	$texto = str_replace("{{a}}", date("Y"), $texto);
	$texto = str_replace("{{mes}}", date("m"), $texto);

	if($empresa->planoEmpresa){
		$vPlano = $empresa->planoEmpresa->valor;
		if($vPlano == 0){
			$vPlano = $empresa->planoEmpresa->plano->valor;
		}
		$valorPlano = "";
		$valorPlano = "R$ " . moeda($vPlano);
		$nomePlano = $empresa->planoEmpresa->plano->nome;
		$texto = str_replace("{{valor_plano}}", $valorPlano, $texto);
		$texto = str_replace("{{nome_plano}}", $nomePlano, $texto);

		$configSystem = ConfigSystem::first();
		if($configSystem && $configSystem->valor_base_contrato > 0){

			$vb = $vPlano/$configSystem->valor_base_contrato*100;
			$texto = str_replace("{{valor_base}}", moeda($vb), $texto);
		}
	}

	$texto = str_replace("{{uf}}", $empresa->uf, $texto);
	$texto = str_replace("{{cep}}", $empresa->cep, $texto);
	$texto = str_replace("{{representante_legal}}", $empresa->representante_legal, $texto);
	$texto = str_replace("{{cpf_representante_legal}}", $empresa->cpf_representante_legal, $texto);

	return $texto;
}







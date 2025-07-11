<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemUpdate;

class AppUpdateController extends Controller
{
    public function __construct(){
        $this->middleware(function ($request, $next) {
            $value = session('user_logged');
            if(!$value){
                return redirect("/login");
            }

            if(!$value['super']){
                return redirect('/graficos');
            }
            return $next($request);
        });
    }

    public function index(){
        try{
            $system = SystemUpdate::first();
            if($system == null){
                $version = env("VERSION");
            }else{
                $version = $system->version;
            }
        }catch(\Exception $e){
            $version = env("VERSION");
        }
        return view('update.index', compact('version'))
        ->with('title', 'Atualização do Sistema');
    }

    public function download(){
        $url = env("URLUPADTE"). "/api/download";
        $downloaded = public_path()."/../update.zip";
        $new_file = fopen($downloaded, "w") or die("cannot open" . $downloaded);

        $cd = curl_init();

        $payload = json_encode([
            'serial' => env("SERIALNUMBER"),
            'app_version' => env("APPVERSION"),
            'ip' => $this->get_client_ip(),
            'address' => $this->getAddress()
        ]);

        curl_setopt($cd, CURLOPT_URL, $url);
        curl_setopt($cd, CURLOPT_FILE, $new_file);
        curl_setopt($cd, CURLOPT_TIMEOUT, 30);
        curl_setopt($cd, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($cd, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        // curl_setopt($cd, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cd, CURLOPT_POST, true);

        curl_exec($cd);
        if (curl_errno($cd)) {
            session()->flash("mensagem_erro", "the cURL error is : " . curl_error($cd));
            return redirect()->back();
        } else {
            $status = curl_getinfo($cd);
            
            if($status["http_code"] == 200){
                return redirect('/appUpdate/update');
            }else{

                session()->flash("mensagem_erro", "Código do erro: " . $status["http_code"]);
                return redirect()->back();
            }
        }
    }

    public function update(){
        $raiz = public_path();
        $raiz = substr($raiz, 0, strlen($raiz)-7);

        if (!is_dir("$raiz/temp")){
            mkdir("$raiz/temp", 0777, true);
        }

        if(!file_exists("$raiz/update.zip")){
            session()->flash('mensagem_erro', "Arquivo de update não foi encontrado!!");
            return redirect('/appUpdate');
        }

        $logMessage = [];
        $zip = new \ZipArchive();
        if ($zip->open("$raiz/update.zip") === TRUE) {
            $zip->extractTo("$raiz/temp");
            $zip->close();
            array_push($logMessage, "Arquivo zip extraido em /temp");
        }

        $dir = "$raiz/temp";
        $directories = array_diff(scandir($dir), array('..', '.'));
        foreach($directories as $dir){
            $source = "$raiz/temp/$dir";
            $map = $this->mapDiretories($dir);
            if($map != ""){
                $destiny = "$raiz$map";
                // echo $source . "<br>";
                array_push($logMessage, "Alteração de diretório <strong class='text-success'>$source</strong>");

                // echo $destiny . "<br>";
                shell_exec("cp -r $source $destiny");
            }
        }

        // \Artisan::call('migrate', ["--force" => true ]);
        // array_push($logMessage, "Executando as migrations ....");

        if(is_file("$raiz/temp/new_tables.sql")){
            $lines = file_get_contents("$raiz/temp/new_tables.sql");
            $lines = explode(";", $lines);
            foreach($lines as $sql){
                if(trim($sql)){
                    try{
                        \DB::unprepared("$sql;");
                        array_push($logMessage, "Comando SQL executado <strong class='text-info'>$sql</strong>;");

                    }catch(\Exception $e){
                        array_push($logMessage, "Erro ao inserir tabela: " . $e->getMessage() . " - <strong class='text-success'>ISSO NÃO AFETA A ATUALIZAÇÃO</strong>");
                    }
                }
            }
        }

        sleep(1);
        if(is_file("$raiz/temp/comand.sql")){
            $lines = file_get_contents("$raiz/temp/comand.sql");
            $lines = explode(";", $lines);
            foreach($lines as $sql){
                if(trim($sql)){
                    try{
                        \DB::unprepared("$sql;");
                        array_push($logMessage, "Comando SQL executado <strong class='text-info'>$sql;</strong>");

                    }catch(\Exception $e){
                        array_push($logMessage, "Erro ao executar SQL: " . $e->getMessage() . " - <strong class='text-success'>ISSO NÃO AFETA A ATUALIZAÇÃO</strong>");
                    }
                }
            }
        }


        unlink("$raiz/update.zip");
        $this->unlinkr("$raiz/temp");
        session()->flash('mensagem_sucesso', "Sua aplicação foi atualizada!!");
        return view('update/finish', compact('logMessage'))->with('title', 'Atualização');
    }

    private function mapDiretories($dir){
        $mapsDiretories = [
            'app' => '/',
            'routes' => '/',
            'js' => '/public',
            'migrations' => '/database',
            'views' => '/resources',
        ];
        return isset($mapsDiretories[$dir]) ? $mapsDiretories[$dir] : "";
    }

    function unlinkr($dir)
    { 
        $files = array_diff(scandir($dir), array('.', '..')); 

        foreach ($files as $file) { 
            (is_dir("$dir/$file")) ? $this->unlinkr("$dir/$file") : unlink("$dir/$file"); 
        }

        return rmdir($dir); 
    } 

    private function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    private function getAddress(){
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
            $url = "https://";   
        else  
            $url = "http://";     
        $url .= $_SERVER['HTTP_HOST'];
        return $url; 
    }

    public function sql(){
        return view('update.import_sql')->with('title', 'Importar SQL');
    }

    public function sqlStore(Request $request){
        if($request->hasFile('file')){
            $file = $request->file('file');

            $text = file_get_contents($file);
            $lines = explode(";", $text);
            $logMessage = [];
            foreach($lines as $sql){
                if(trim($sql)){
                    try{
                        \DB::unprepared("$sql;");
                        array_push($logMessage, "Comando SQL executado <strong class='text-info'>$sql;</strong>");

                    }catch(\Exception $e){
                        array_push($logMessage, "Erro ao executar SQL: " . $e->getMessage() . " - <strong class='text-success'>ISSO NÃO AFETA A ATUALIZAÇÃO</strong>");
                    }
                }
            }

            return view('update/finish', compact('logMessage'))->with('title', 'Atualização');

        }else{
            session()->flash('mensagem_erro', "Arquivo não foi selecionado!!");
            return redirect()->back();
        }
    }

    public function runSql(Request $request){
        $sql = $request->sql;
        $lines = explode(";", $sql);
        $logMessage = [];

        foreach($lines as $sql){
            if(trim($sql)){
                try{
                    \DB::unprepared("$sql;");
                    array_push($logMessage, "Comando SQL executado <strong class='text-info'>$sql;</strong>");

                }catch(\Exception $e){
                    array_push($logMessage, "Erro ao executar SQL: " . $e->getMessage() . " - <strong class='text-success'>ISSO NÃO AFETA A ATUALIZAÇÃO</strong>");
                }
            }
        }
        return view('update/finish', compact('logMessage'))->with('title', 'Atualização');

    }
}

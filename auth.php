<?php
include_once("load_env.php");

$APP_ID = getenv("APP_ID");
$SECRET_KEY = getenv("SECRET_KEY");
$REDIRECT_URI = getenv("REDIRECT_URI");

$auth_url = "https://auth.mercadolibre.com.ar/authorization?response_type=code&client_id={$APP_ID}&redirect_uri={$REDIRECT_URI}";

if ($APP_ID == false){
    echo("ERROR: APP_ID debe estar seteada en el enviroment");
    exit(1);
}
if ($SECRET_KEY == false){
    echo("ERROR: SECRET_KEY debe estar seteada en el enviroment");
    exit(1);
}

enum TokenActions{
    case Generate;
    case Refresh;
}
Class Token{
    const filepath = 'tokens.json';
    const url ='https://api.mercadolibre.com/oauth/token';

    function __construct(String $user_id, String $code = Null){
        $file = json_decode(file_get_contents(self::filepath));

        if (property_exists($file, $user_id)){ #Si el usuario ya fue autorizado antes
            #Cargamos los datos desde el archivo Json
            $this->access_token = $file->$user_id->access_token;
            $this->refresh_token = $file->$user_id->refresh_token;
            $this->scope = $file->$user_id->scope;
            $this->token_type = $file->$user_id->token_type;
            $this->expires_time = $file->$user_id->expires_time;

            if (time() > $this->expires_time){
                echo ("El Token para el usuario {$user_id} expiro.\ngenerando uno nuevo...\n");
                $this->Action(TokenActions::Refresh);

            }else{
                echo ("INFO: El token del usuario {$user_id} todavia no expiro\n");
                echo ("Expira en ".$this->expires_time-time()." segundos\n");
            } 
        }else{
            echo ("Generando un nuevo token para el usuario {$user_id}\n");
            if ($code == Null){
                echo ("ERROR: Se necesita un code para generar el access_token por primera vez\n");
                exit(1);
            }
            $this->refresh_token = $code;
            $this->Action(TokenActions::Generate);
            echo ("INFO: Token generado con exito\n");
        }

        $file->$user_id = $this;
        if (!file_put_contents(self::filepath, json_encode($file))){
            echo ("ERROR: No fue posible crear el archivo".self::filepath."\n");
            exit(3);
        }
    }

    function Action(TokenActions $action){
        global $APP_ID, $SECRET_KEY, $REDIRECT_URI;

        $data = array(
            'grant_type' => '',
            'client_id' => $APP_ID,
            'client_secret' => $SECRET_KEY,
            'redirect_uri'=> $REDIRECT_URI,
        );

        switch($action){
            case TokenActions::Generate:
                $data['grant_type'] = 'authorization_code';
                $data['code'] = $this->refresh_token;
                break;
            case TokenActions::Refresh:
                $data['grant_type'] = 'refresh_token';
                $data['refresh_token'] = $this->refresh_token;
                break;
            default:
                echo("ERROR: Undefinded Action of TokenActions");
                exit(2);
                break;
        }
        
        $this->request($data);
    }

    function request($data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));

        if (curl_errno($ch)) {
            echo("ERROR: No fue posible obtener el access_token");
            exit(1);
        }else{
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($status_code != 200){
                echo("ERROR: No fue posible obtener el access_token\n");
                echo($result->message."\n");
                exit(1);
            }
        }

        curl_close($ch);

        $this->expires_time = time()+$result->expires_in; 
        $this->access_token = $result->access_token;
        $this->refresh_token = $result->refresh_token;
        $this->user_id = $result->user_id;
    }
}


#$token = new Token('364381193');
#$token = new token('111111111');
if ($_GET){
    if ($_GET["code"]){
        echo "codigo recibido\n";
        $code = $_GET["code"];
        $user_id = explode("-",$code)[2]; #Separar el code para obtener el user_id

        echo ("code: ".$code." user_id: ".$user_id."\n");
        if (!$user_id){
            echo "ERROR: Imposible obtener el user_id del codigo\n";
        }else{
            $token = new Token($user_id, $code);
        }
    }else{
        echo "<h1>Autenticacion</h1>";
        echo ("auth_url: ".$auth_url);
        header("Location: ".$auth_url);
    }
}
?>

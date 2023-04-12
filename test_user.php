<?php
    require_once('auth.php');

    function request(String $url, array $data, array $headers){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        
        if (!empty($data)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);

        #var_dump(json_encode($data));

        $result = json_decode(curl_exec($ch));

        if (curl_errno($ch)) {
            echo("ERROR: curl request error\n".$curl_error($ch)); 
            exit(1);
        }else{
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($status_code != 201 and $status_code != 200){
                echo("ERROR: response code error:".$status_code."\n"); 
                echo($result->message."\n");
                exit(1);
            }
        }

        curl_close($ch);
        return $result;
    }

    function get_test_user(String $access_token){
        $url = 'https://api.mercadolibre.com/users/test_user';
     
        $data = array(
            "site_id" => "MLA",
        );

        $headers = array(
            'Authorization: Bearer '.$access_token,
            'Content-Type: application/json'
        );

        return request($url, $data, $headers);
    }

    function make_question(Token $user, String $text, String $item){
        $url = 'https://api.mercadolibre.com/questions';

        $data = array(
            "text" => $text,
            "item_id" => $item
        );
        $headers = array(
            'Authorization: Bearer '.$user->access_token,
            'Content-Type: application/json'
        );

        return request($url, $data, $headers);
    }

    function get_questions(Token $user){
        $url = 'https://api.mercadolibre.com/my/received_questions/search?sort_fields=date_created&sort_types=DESC';

        $headers = array(
            'Authorization: Bearer '.$user->access_token,
            'Content-Type: application/json'
        );

        return request($url, array(), $headers)->questions;
    }

?>

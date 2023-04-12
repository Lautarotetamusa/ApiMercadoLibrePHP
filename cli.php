<?php
    require_once('functions.php');

    if (count($argv) > 1){
        $users = json_decode(file_get_contents('users.json'));
        switch($argv[1]){
            case "new_test_user":
                $token = new Token('364381193');
                $user = get_test_user($token->access_token);

                array_push($users, $user);
                file_put_contents('users.json', json_encode($users));
                break;
            case "make_question":
                if (count($argv) <3){
                    echo ("ERROR: falta argumento <texto>\n");
                    exit(1);
                } 
                $token = new Token($users[0]->id);
                $res = make_question($token, $argv[2], "MLA1365449777"); 
                #mi venta = MLA1365449777
                #venta test = MLA1392454282
                var_dump($res);
                break;
            case "check_new_questions":
                $last_question = json_decode(file_get_contents('questions.json'));
                $token = new Token('364381193');
                $questions = get_questions($token);
                #var_dump($questions);
                if ($questions[0]->id != $last_question->id){
                    var_dump($questions[0]);
                    echo "nueva pregunta\n";
                    file_put_contents('questions.json', json_encode($questions[0]));

                    #mail('lautarotetamusa@gmail.com', 'Nueva pregunta', '\r'.$questions[0]);
                }else{
                    echo "ninguna pregunta nueva\n";
                }
                break;
            case "send_email":
                $err = mail('lautarotetamusa@gmail.com', 'Nueva pregunta', '\rTest email', 'From: test@test.com');
                var_dump($err);
                break;
            default:
                echo "Unexpected parameter ".$argv[1]."\n";
                break;
        }
    }else{
        echo (
            "USAGE:\n".
            "new_test_user\n".
            "make_question <texto>\n.".
            "check_new_questions\n"
        );
    }
?>

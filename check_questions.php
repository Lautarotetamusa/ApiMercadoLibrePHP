<?php
    include_once('functions.php');
    $INTERVAL = getenv("INTERVAL_SEC"); 
    
    echo "interval: ".$INTERVAL;
    if ($INTERVAL == false){
        echo ("ERROR: La variable de entorno INTERVAL_SEC no esta seteada\n");
        exit(1);
    }

    function checkForStopFlag() { 
        #Se podría agregar condiciones externas para detener las consultas
        return false;
    }
    $active = true;
    $nextTime   = microtime(true) + $INTERVAL;

    while ($active){
        if (microtime(true) >= $nextTime) {
            $last_question = json_decode(file_get_contents('questions.json'));
            $token = new Token('364381193');
            $questions = get_questions($token);
            #var_dump($questions);
            if ($questions[0]->id != $last_question->id){
                var_dump($questions[0]);
                echo "nueva pregunta\n";
                file_put_contents('questions.json', json_encode($questions[0]));
            }else{
                echo "ninguna pregunta nueva\n";
            }

            $nextTime = microtime(true) + $INTERVAL;
        }

        $active = !checkForStopFlag();
    }
?>

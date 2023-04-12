<?php
#Es necesario para que los mensajes se escriban en standar output y no en la web
function stdout(String $text){
    file_put_contents('php://stdout', $text);
}

$post = json_decode(file_get_contents("php://input"));

if ($post){
    stdout(var_export($post, true)."\n");
}
?>

<?php
date_default_timezone_set("America/Sao_Paulo");

define('DBHOST', "localhost");  
define('DBUSER', "root"); 
define('DBPASS', ""); 
define('DBBASE', "cliente_teste"); 
define('DBCONEXAO', "PDO");
include_once 'conexao.class.php' ;
include_once 'vendor/autoload.php'; 

$app = new \Slim\Slim();
$app->contentType("application/json; charset=utf-8");

$app->response()->header('Access-Control-Allow-Origin','*');
$app->response()->header('Access-Control-Allow-Credentials','true'); 
$app->response()->header('Access-Control-Allow-Headers', 'X-Requested-With');
$app->response()->header('Access-Control-Allow-Headers', 'Content-Type');
$app->response()->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, DELETE, PUT'); 
$app->response()->header('Access-Control-Max-Age','86400');

$banco = new Conexao();

$app->post("/novo", function() use($app,$banco){
    $pag = json_decode($app->request()->getBody());
    parse_str($app->request()->getBody(),$campos);
    $banco->inserir("cliente",$campos);
});

$app->post("/editar", function() use($app,$banco){
  $pag = json_decode($app->request()->getBody());
  parse_str($app->request()->getBody(),$campos);
  $banco->atualiza("cliente",$campos,['id'=>$campos['id']]);
});

$app->get("/clientes", function() use($app,$banco){ 
  $itens = $banco->seleciona("cliente");
  $app->response()->setBody(json_encode($itens));
});

$app->delete("/apagar", function() use($app,$banco){ 
  $pag = json_decode($app->request()->getBody());
  parse_str($app->request()->getBody(),$campos);
  $itens = $banco->apaga("cliente",['id'=>$campos['id']]);
});


$app->map("/", function() use($app,$banco) {
  $app->halt(404,"API online");
})->via('GET', 'POST','PUT','DELETE');





$app->run();
?>

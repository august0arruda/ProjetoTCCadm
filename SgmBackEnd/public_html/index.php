<?php
    /*
     * -------------------------------------------------------
     * PROJETO: API
     * DESCRICAO: API RESTful - Application Programming Interface
     *            baseada na arquitetura REST- Representational State
     *            Transfer (Transferência Representacional de Estado).
     * Base da API: Protocolo HTTP usando especificações dos métodos aceitos pelo endpoint.
     * Formato da resposta: JSON.
     * Cliente: TCC PUC Minas
     * Solicitante: Augusto
     * -------------------------------------------------------
     * Descricao do arquivo:
     * endpoint cliente
     * -------------------------------------------------------
     * Desenvolvimento:
     * Augusto Arruda 
     * Email: augusto.rr.arruda@gmail.com
     * Cel: (092) 991848979
     * Manaus, 04 de abril de 2021.
    */

    header('Access-Control-Allow-Origin: *');//liberar acesso a qualquer dominio inclusive o local 
    header('Content-Type: application/json; application/x-www-form-urlencoded');//padrao header tipo JSON

    require_once '../vendor/autoload.php';
    use App\Controllers\RestController;

    if (isset($_REQUEST) && !empty($_REQUEST)) {
        $rest = new RestController($_REQUEST);
        echo $rest->run();
    }

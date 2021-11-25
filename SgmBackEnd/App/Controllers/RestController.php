<?php
    /* Define namespace */
    namespace App\Controllers;

    class RestController
    {
        private $request;
        private $class;
        private $method;
        private $params = array();
        public function __construct($req) {
            $this->request = $req;
            $this->load();
        }

        //Le a url e trata os dados para retornar para classe run
        public function load()
        {
            $newUrl = explode('/', $this->request['url']);
            if ($newUrl[0] === 'api' ) {//se o path possuir o termo api o sistema deixa acessar
                array_shift($newUrl);//remove o item 0 do array (api)
                if (isset($newUrl[0])) {
                    $this->class = ucfirst($newUrl[0]).'Controller';//converte o nome do path para o padrao 1 caracter maiusculo
                    array_shift($newUrl);//remove o item 0 do array  
                    $this->method = strtolower($_SERVER['REQUEST_METHOD']);//captura o metodo detectado pela variavel global (GET, POST, PUT, PATCH, DELETE, OPTIONS)
                    $this->params = $newUrl;//captura o item chave dos metodos (creste,view,update,delete) da classe UserController
                }
            }
        }

        public function run()
        {
            $fail = json_encode(array( 'status' => 'error', 'data' => 'Operação Inválida'), JSON_UNESCAPED_UNICODE);//JSON_UNESCAPED_UNICODE -> corrige caractere utf8
            $methodPath = 'App\Controllers\\';
            $Controller = $methodPath.$this->class ;
            if (class_exists($Controller) && method_exists($Controller, $this->method) ) {
                try {
                    $response = call_user_func_array(array(new $Controller, $this->method), $this->params);
                    http_response_code(200);
                    return json_encode(array('status' => 'sucess', 'data' => $response), JSON_UNESCAPED_UNICODE);//JSON_UNESCAPED_UNICODE -> corrige caractere utf8
                } catch (\Exception $e) {
                    http_response_code(404);
                    return json_encode(array( 'status' => 'error', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE);//JSON_UNESCAPED_UNICODE -> corrige caractere utf8
                }
            }else{
                return $fail;
            }
        }
    }

        
        
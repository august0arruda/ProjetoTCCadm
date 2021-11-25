<?php
/**
 * PROJETO: API
 * DESCRICAO: API RESTful - Application Programming Interface
 *            baseada na arquitetura REST- Representational State
 *            Transfer (Transferência Representacional de Estado).
 * Base da API: Protocolo HTTP usando especificações dos métodos aceitos pelo endpoint.
 * Formato da resposta: JSON.
 * Cliente: TCC PUC Minas
 * Solicitante: Augusto
 *
 * Descricao do arquivo:
 * cadastro do cliente cidadao
 *
 * Desenvolvimento:
 * Augusto Arruda 
 * Email: augusto.rr.arruda@gmail.com
 * Cel: (092) 991848979
 * Manaus, 04 de abril de 2021.
 * 
 */


/* Define namespace */
namespace App\Models;

/* alias/import */
use App\DAO\Database;//banco de dados
use RdKafka;//classe Apache Kakfa

class Auth
{
    private static $fail_logininvalido = "Email ou senha inválido!";
    private static $fail_loginnaovalidado = "Usuário não validado!";
    private static $fail_loginnaoautorizado = "Usuário não autorizado para acesso!";
    private static $fail_loginbloqueado = "Usuário bloqueado!";
    private static $success_criarusuario = "Cadastro de usuario realizado com sucesso!";
    private static $fail_criarusuario = "Falha ao realizar o cadastro do usuário!";
    
    //LISTENER_INSIDE trafego interno na rede Docker
    //LISTENER_DOCKER trafego da maquina Docker-host (localhost)
    //LISTENER_OUTSIDE trafego externo, alcancando o host Docker no ip ${IP_SERVER}
    //private static $topicBrokerList = "{kafka1:19091, kafka2:29091, kafka3:39091}"; //LISTENER_INSIDE
    //private static $topicBrokerList = "{localhost:19092, localhost:29092, localhost:39092}"; //LISTENER_DOCKER
    private static $topic_brokerList = "{192.168.0.18:19093, 192.168.0.18:29093, 192.168.0.18:39093}"; //LISTENER_OUTSIDE
    
    /**
     * autenticar login
     */
    public static function login($data)
    {
        
        $connPdo = Database::connect();
        $connTable_admUsuario =  Database::admUsuario();
        $connTable_admPerfil =  Database::admPerfil();
        
        $sql = 'SELECT
                USR.id USR_ID, 
                USR.perfil USR_PERFILID,
                (SELECT titulo FROM '.$connTable_admPerfil.' WHERE id = USR.perfil) USR_PERFILNOME,
                USR.entmunicipal USR_ENTMUNICIPAL,
                USR.nome USR_NOME,
                USR.cpf USR_CPF,
                USR.rg USR_RG,
                USR.endereco USR_ENDERECO,
                USR.email USR_EMAIL,
                USR.senha USR_SENHA,
                USR.status USR_STATUS
                FROM '.$connTable_admUsuario.' USR
                WHERE USR.email = :Email AND USR.senha = :Senha';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Email', $data['email']);
        $stmt->bindValue(':Senha', md5($data['senha']));
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $retorno = $stmt->fetch(\PDO::FETCH_ASSOC);
            $id = $retorno['USR_ID'];
            $nome = $retorno['USR_NOME'];
            $email = $retorno['USR_EMAIL'];
            $perfilid = $retorno['USR_PERFILID'];
            $perfilnome = $retorno['USR_PERFILNOME'];
            $chaveprivada = Auth::privateKey($data);
            //vai gerar a chavecriptografada a patir do retorno da chave privada
            return Auth::generateKey($id, $nome, $email, $chaveprivada, $perfilid, $perfilnome);
            
        } else {
            throw new \Exception(self::$fail_loginnaoautorizado);
        }
        
    }


    
   /**
     * criacao de usuario pelo proprio usuario
     */
    public static function usercreate($data)
    {
        
        $perfil = 1;
        $senha = md5($data['senha']);
        $status = 1;
        $connPdo = Database::connect();
        $connTable_admUsuario = Database::admUsuario();
        $sql = 'INSERT INTO '.$connTable_admUsuario.' (id, perfil, entmunicipal, nome, cpf, rg, endereco, telefone, email, senha, status) VALUES
                (:Id, :Perfil, :Entmunicipal, :Nome, :Cpf, :Rg, :Endereco, :Telefone, :Email, :Senha, :Status )';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Id', NULL);
        $stmt->bindValue(':Perfil', $perfil);
        $stmt->bindValue(':Entmunicipal', NULL);
        $stmt->bindValue(':Nome', $data['nome']);
        $stmt->bindValue(':Cpf', $data['cpf']);
        $stmt->bindValue(':Rg', $data['rg_consolidado']);
        $stmt->bindValue(':Endereco', $data['endereco_consolidado']);
        $stmt->bindValue(':Telefone', $data['telefone_consolidado']);
        $stmt->bindValue(':Email', $data['email']);
        $stmt->bindValue(':Senha', $senha);
        $stmt->bindValue(':Status', $status);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            //return self::$success_criarusuario;
            //Gerar mensageria
            return Auth::mensagemusercreate($data['email'], $senha);
        } else {
            throw new \Exception(self::$fail_criarusuario);
        }
        
    }


    
    /*
     * MENSAGERIA KAFKA
     * PRODUTOR
     * Gera mensagem para o recurso de mensageria
     */


    public static function mensagemusercreate($email, $senha)
    {
        
        
        /*
         * Buscar dados do usuario criado para anexar dados na mensageria
         */

        $connPdo = Database::connect();
        $connTable_admUsuario =  Database::admUsuario();
        $connTable_admPerfil =  Database::admPerfil();
        $sql = 'SELECT
                USR.id USR_ID, 
                USR.perfil USR_PERFILID,
                (SELECT titulo FROM '.$connTable_admPerfil.' WHERE id = USR.perfil) USR_PERFILNOME,
                USR.entmunicipal USR_ENTMUNICIPAL,
                USR.nome USR_NOME,
                USR.cpf USR_CPF,
                USR.rg USR_RG,
                USR.endereco USR_ENDERECO,
                USR.email USR_EMAIL,
                USR.senha USR_SENHA,
                USR.status USR_STATUS
                FROM '.$connTable_admUsuario.' USR
                WHERE USR.email = :Email AND USR.senha = :Senha';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Email', $email);
        $stmt->bindValue(':Senha', $senha);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $retorno = $stmt->fetch(\PDO::FETCH_ASSOC);
            $usr_id = $retorno['USR_ID'];
            $usr_perfilid = $retorno['USR_PERFILID'];
            $usr_perfilnome = $retorno['USR_PERFILNOME'];
            $usr_entmunicipal = $retorno['USR_ENTMUNICIPAL'];
            $usr_nome = $retorno['USR_NOME'];
            $usr_cpf = $retorno['USR_CPF'];
            $usr_rg = $retorno['USR_RG'];
            $usr_endereco = $retorno['USR_ENDERECO'];
            $usr_email = $retorno['USR_EMAIL'];
            $usr_senha = $retorno['USR_SENHA'];
            $usr_status = $retorno['USR_STATUS'];
        } else {
            throw new \Exception(self::$fail_loginnaoautorizado);
        }

        
        /*
         * Executar mensageria
         */
        
        $topicBrokerList = self::$topic_brokerList;
        /*Definicao das variaveis*/
        $topicName = 'usercreate';
        $topicClient = 'rdkafka';//default
        $topicId = $usr_id;
        $topicTimeoutMs = 10000;
        $topicHeader = [
            'Sistema' => 'SGM-Sistema de Gestao Municipal',
            'API' => 'Mensageria',
            'Descricao' => 'TCC Puc Minas',
            'Desenvolvimento' => 'Augusto Arruda'
        ];
        $topicMessage = [
            'tipo' => 'conta_criada',
            'usr_id' => $usr_id,
            'usr_perfilid' => $usr_perfilid,
            'usr_perfilnome' => $usr_perfilnome,
            'usr_entmunicipal' => $usr_entmunicipal,
            'usr_nome' => $usr_nome,
            'usr_cpf' => $usr_cpf,
            'usr_rg' => $usr_rg,
            'usr_endereco' => $usr_endereco,
            'usr_email' => $usr_email,
            'usr_senha' => $usr_senha,
            'usr_status' => $usr_status,
            'data' => Auth::dataHoraBdados()
        ];
        $topicMessageJson = json_encode($topicMessage);

        /*Instancia as configuracoes do rdkafka*/
        $conf = new RdKafka\Conf();
        $conf->set('client.id', $topicClient);
        //$conf->set('group.id', $topicGroupid);
        //$conf->set('metadata.broker.list', $topicBrokerList);
        //$conf->set('enable.idempotence', 'true');
        $conf->set('log_level', (string) LOG_DEBUG);
        $conf->set('debug', 'all');

        /*Instancia o producer do rdkafka*/
        $producer = new RdKafka\Producer($conf);
        $producer->addBrokers($topicBrokerList);//definicao do(s) Brocker(s)
        $topic = $producer->newTopic($topicName);//cricao do tópico
        //$topic->produce(RD_KAFKA_PARTITION_UA, 0, $topicMessageJson, $topicId);//retorno sem header
        $topic->producev(RD_KAFKA_PARTITION_UA, 0, $topicMessageJson, $topicId, $topicHeader);//retorno com header
        $producer->poll(0);
        //$poll = $producer->poll(0);
        //Ignorar as mensagens que ainda não foram totalmente enviadas 
        //$producer->purge(RD_KAFKA_PURGE_F_QUEUE);
        //Destroi a instância do produtor para garantir que todas as solicitações de produção 
        //enfileiradas e em andamento sejam concluídas sob pena de perda de mensagem
        $result = $producer->flush($topicTimeoutMs);//destroi a instancia do producer

        /*Retorna Instancia o producer do rdkafka*/
        if( $result === 0){
            //echo 'Produção da mensagem com o tópico '.$topicName.' para os brokers '.$topicBrokerList.'!';
            return self::$success_criarusuario;
        }else{
            //echo 'Erro na Produção da mensagem!';
            throw new \Exception(self::$fail_criarusuario);
        }
        
    }


    
    
    
    

    /**
     * gerar chave com dados do usuario autenticado
     */
    public static function generateKey($id, $nome, $email, $chaveprivada, $perfilid, $perfilnome)
    {
        //0-sem tempo definido, 1hora- 3600, 1dia- 86400, 30dias- 2592000, 90dias- 7776000 	
        $validity_time = 86400;
        //Header Token
        //prévias sobre o token, como o algoritmo utilizado para a computação da assinatura
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
            'kid' => 'IdTokenSigninKeyContainer'
        ];

        //Payload - Content
        //O payload é aonde vamos colocar nossa informação, isto é chamado 
        //de public claims. Alguns atributos são definidos diretamente na RFC
        // e são obrigatórios, por isso são chamados de reserved claims. 
        // Ou seja, tudo que é nosso é um public claim, tudo que não é nosso 
        // e é obrigatório é um reserved claim.

        //Carga útil - Conteúdo
        //Puxar nome usuario do  dados bdados mas sem colocar dados sensiveis
        //carrega, tipicamente a data de expiração do token ("EXPiration date"),
        // quem gerou aquele token ("ISSuer"), quando ("Instanciated AT"), 
        // quem deve consumi-lo ("AUDience"), e o que mais for necessário 
        // entre as partes — como por exemplo um ID de usuário

        //https://medium.com/tableless/entendendo-tokens-jwt-json-web-token-413c6d1397f6
        // --- Entendendo tokens JWT (Json Web Token)
        //sub (subject) = Entidade à quem o token pertence, normalmente o ID do usuário;
        //iss (issuer) = Emissor do token;
        //exp (expiration) = Timestamp de quando o token irá expirar;
        //iat (issued at) = Timestamp de quando o token foi criado;
        //aud (audience) = Destinatário do token, representa a aplicação que irá usá-lo.
        
        // caso o parametro do sistema definir 0 desativa a contagem temporal
        if($validity_time === '0'){
            $exp = $validity_time;
            $nbf = $validity_time;
            $authstarttime = $validity_time;
            $authendtime = $validity_time;
        }else{
            $exp = strtotime(Auth::dataHoraBdados()) + $validity_time;
            $nbf = strtotime(Auth::dataHoraBdados());
            $authstarttime = date('d/m/Y H:i:s', $nbf);
            $authendtime = date('d/m/Y H:i:s', $exp);
        }

        //dados payload
        $payload = [
            'uid' => $id,
            'name' => $nome,
            'email' => $email,
            'perfilid' => $perfilid,
            'perfilnome' => $perfilnome,
            'exp' => $exp,
            'nbf' => $nbf,
            'authStartTime' => $authstarttime,
            'authEndTime' => $authendtime,
        ];
        
        //Converter em JSON
        $_header = json_encode($header);
        $_payload = json_encode($payload);
        //Base 64
        $header_ = self::authBase64Encode($_header);
        $payload_ = self::authBase64Encode($_payload);
        //Sign
        $sign = hash_hmac('sha256', $header_.".".$payload_, $chaveprivada, true);
        $sign_ = self::authBase64Encode($sign);
        //Token
        $token = $header_.'.'.$payload_.'.'. $sign_;
        //echo $token;
        return $token;
        //var_dump($token);
    }

   

    /**
     * retorna data e hora do banco de dados
     */
    public static function dataHoraBdados()
    {
        $connPdo = Database::connect();
        $sql = "SELECT sysdate() DATAHORA";
        $stmt = $connPdo->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $retorno = $stmt->fetch(\PDO::FETCH_ASSOC);
            $datahora = $retorno['DATAHORA'];
            return $datahora;

        }else {
            throw new \Exception(self::$fail_logininvalido);
        }

    }
    
        
    
    /**
     * retorna data e hora do banco de dados
     */
    public static function tipoDispositivoDetectado()
    {
        //Define o tipo de dispositivo do usuario - 1-Computador Pessoal, 2-Dispositivo Movel
        $iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
        $ipad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
        $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
        $palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
        $berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
        $ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
        $symbian =  strpos($_SERVER['HTTP_USER_AGENT'],"Symbian");
        if ($iphone || $ipad || $android || $palmpre || $ipod || $berry || $symbian == true){ /*Se este dispositivo for portãtil, faça/escreva o seguinte */ 
            $tipoDispositivoDetectado = "2";//Dispositivo movel
        }else{ 
            $tipoDispositivoDetectado = "1";//Dispositivo pessoal
        }
        return $tipoDispositivoDetectado;
    }
    
        
    
    /**
     * retorna data e hora do banco de dados
     */
    public static function ipDispositivoDetectado()
    {
        //define o IP do dispositivo
        $ipDispositivoDetectado = $_SERVER["REMOTE_ADDR"];
        return $ipDispositivoDetectado;
    }
    
    
    
    
    /**
     * retorna a chave privada (senha no padrao MD5) retornada da base de dados
     */
    public static function privateKey($data)
    {
        
        $connPdo = Database::connect();
        $connTable_admUsuario =  Database::admUsuario();
        $sql = 'SELECT
                id, 
                perfil,
                entmunicipal,
                nome,
                cpf,
                rg,
                endereco,
                email,
                senha,
                status
                FROM '.$connTable_admUsuario.'
                WHERE email = :Email AND senha = :Senha';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Email', $data['email']);
        $stmt->bindValue(':Senha', md5($data['senha']));
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $retorno = $stmt->fetch(\PDO::FETCH_ASSOC);
            $private_key = md5($retorno['email'].$retorno['senha']);//conposicao chave privada
            $usr_status = $retorno['status'];
            if ($usr_status === "0") {
                throw new \Exception(self::$fail_loginnaovalidado);
            }else if ($usr_status === "2") {
                throw new \Exception(self::$fail_loginbloqueado);
            }else if ($usr_status === "3" || $usr_status === "4" ) {
                throw new \Exception(self::$fail_loginnaoautorizado);
            }else if ($usr_status === "1") {
                return $private_key;                
            }else{
                throw new \Exception(self::$fail_loginnaoautorizado);
            }
            
        } else {

            throw new \Exception(self::$fail_loginnaoautorizado);
        }
        
    }

    
    
    /**
     * verifica se a chave JWT.io e válida com uso de formulario (usuario e senha)
     */
    public static function checkAuth($data)
    {
        
        $private_key = Auth::privateKey($data);//retorna a chave privada (senha no padrao MD5) retornada da base de dados
        $datahorabdados = Auth::dataHoraBdados();
        $datahorabdados_timestamp = strtotime($datahorabdados) ;
        $http_header = apache_request_headers();//le o header apache_request_headers — Captura todos os cabeçalhos HTTP
        if (isset($http_header['Authorization']) && $http_header['Authorization'] != null) 
        {
            $bearer = explode (' ', $http_header['Authorization']);
            $token = explode('.', $bearer[1]);
            $header = $token[0];
            $payload = $token[1];
            $sign = $token[2];
            //Conferir Assinatura
            $valid = hash_hmac('sha256', $header.".".$payload, $private_key, true);
            $valid_ = self::authBase64Encode($valid);
            //Conferir se o token ainda esta valido (validacao definido de forma temporal)
            $dadoundecoder = self::authBase64Decode($payload);
            $_dadoundecoder = json_decode($dadoundecoder);//converte os dados json para array PHP
            
            if($sign !== $valid_){//se chave nao for valida
                return false;
            }else if($_dadoundecoder->exp === "0" && $_dadoundecoder->nbf === "0") {//se o padrao for 0 não ha validacao temporal
                return true;
            }else if($datahorabdados_timestamp > $_dadoundecoder->exp) {//se a hora nao for valida
                return 'expired';
            }else{
                return true;
            }
        }
        //default
        return false;
    }

    
    
    
 
    /**
     * verifica se a chave JWT.io e válida sem o uso de formulario
     */
    public static function checkAuthNoform($data)
    {
        //Captura dados do Payload para verificar dados do usuario
        $sgmtoken = explode('.', $data['sgmtoken']);
        $header = $sgmtoken[0];
        $payload = $sgmtoken[1];
        //$sign = $sgmtoken[2];
        $_payload = json_decode(self::authBase64Decode($payload));
        $sign = $sgmtoken[2];
        //Busca dados do usuario capturado pelo payload
        $connPdo = Database::connect();
        $connTable_admUsuario =  Database::admUsuario();
        $connTable_admPerfil =  Database::admPerfil();
        $sql = 'SELECT
                USR.id USR_ID, 
                USR.perfil USR_PERFILID,
                (SELECT titulo FROM '.$connTable_admPerfil.' WHERE id = USR.perfil) USR_PERFILNOME,
                USR.entmunicipal USR_ENTMUNICIPAL,
                USR.nome USR_NOME,
                USR.cpf USR_CPF,
                USR.rg USR_RG,
                USR.endereco USR_ENDERECO,
                USR.email USR_EMAIL,
                USR.senha USR_SENHA,
                USR.status USR_STATUS
                FROM '.$connTable_admUsuario.' USR
                WHERE USR.id = :Id AND USR.perfil = :Perfil AND USR.email = :Email';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Id', $_payload->uid);
        $stmt->bindValue(':Perfil', $_payload->perfilid);
        $stmt->bindValue(':Email', $_payload->email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $retorno = $stmt->fetch(\PDO::FETCH_ASSOC);
            $private_key = md5($retorno['USR_EMAIL'].$retorno['USR_SENHA']);//conposicao chave privada
        } else {
            throw new \Exception(self::$fail_loginnaoautorizado);
        }
        
        $datahorabdados = Auth::dataHoraBdados();
        $datahorabdados_timestamp = strtotime($datahorabdados) ;
        //Conferir Assinatura
        $valid = hash_hmac('sha256', $header.".".$payload, $private_key, true);
        $valid_ = self::authBase64Encode($valid);
        //Conferir se o token ainda esta valido (validacao definido de forma temporal)
        $dadoundecoder = self::authBase64Decode($payload);
        $_dadoundecoder = json_decode($dadoundecoder);//converte os dados json para array PHP

        if($sign !== $valid_){//se chave nao for valida
            return false;
        }else if($_dadoundecoder->exp === "0" && $_dadoundecoder->nbf === "0") {//se o padrao for 0 não ha validacao temporal
            return true;
        }else if($datahorabdados_timestamp > $_dadoundecoder->exp) {//se a hora nao for valida
            return 'expired';
        }else{
            return true;
        }
        //default
        return false;
    }

        
    
    
    
    /**
     * define padrao authBase64Encode para o jwt.io
     */
    private static function authBase64Encode($data)
    {
        // Codificar $data para string Base64
        $b64 = base64_encode($data);
        // Verificar se o resultado e válido, caso contrário, retornar FALSO
        if ($b64 === false) {return false;}
        // Converta Base64 em Base64URL substituindo “+” por “-” e “/” por “_”
        $url = strtr($b64, '+/', '-_');
        // Remova o caractere de preenchimento do final da linha e retorne o resultado Base64URL
        return rtrim($url, '=');
    }

    
    /**
     * define padrao authBase64Decode para o JWT.io
     */
    private static function authBase64Decode($data)
    {
        // Decodificar $data para string Base64
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
        //return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
    }

    

      
   /**
     * autenticar login
     */
    public static function checktokenuuuuuuuuuuuuuuu($data)
    {
        $sgmtoken = explode('.', $data['sgmtoken']);
        $header = $sgmtoken[0];
        $payload = $sgmtoken[1];
        $_payload = json_decode(base64_decode(str_pad(strtr($payload, '-_', '+/'), strlen($payload) % 4, '=', STR_PAD_RIGHT)));
        $sign = $sgmtoken[2];
                  
        /*
         * Buscar dados do usuario criado para anexar dados na mensageria
         */
        $connPdo = Database::connect();
        $connTable_admUsuario =  Database::admUsuario();
        $connTable_admPerfil =  Database::admPerfil();
        $sql = 'SELECT
                USR.id USR_ID, 
                USR.perfil USR_PERFILID,
                (SELECT titulo FROM '.$connTable_admPerfil.' WHERE id = USR.perfil) USR_PERFILNOME,
                USR.entmunicipal USR_ENTMUNICIPAL,
                USR.nome USR_NOME,
                USR.cpf USR_CPF,
                USR.rg USR_RG,
                USR.endereco USR_ENDERECO,
                USR.email USR_EMAIL,
                USR.senha USR_SENHA,
                USR.status USR_STATUS
                FROM '.$connTable_admUsuario.' USR
                WHERE USR.id = :Id AND USR.perfil = :Perfil AND USR.email = :Email';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Id', $_payload->uid);
        $stmt->bindValue(':Perfil', $_payload->perfilid);
        $stmt->bindValue(':Email', $_payload->email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $retorno = $stmt->fetch(\PDO::FETCH_ASSOC);
            $private_key = md5($retorno['USR_EMAIL'].$retorno['USR_SENHA']);//conposicao chave privada
            
        } else {
            throw new \Exception(self::$fail_loginnaoautorizado);
        }
        
        $datahorabdados = Auth::dataHoraBdados();
        $datahorabdados_timestamp = strtotime($datahorabdados) ;
        //Conferir Assinatura
        $valid = hash_hmac('sha256', $header.".".$payload, $private_key, true);
        $valid_ = self::authBase64Encode($valid);
        //Conferir se o token ainda esta valido (validacao definido de forma temporal)
        $dadoundecoder = self::authBase64Decode($payload);
        $_dadoundecoder = json_decode($dadoundecoder);//converte os dados json para array PHP

        if($sign !== $valid_){//se chave nao for valida
            return false;
        }else if($_dadoundecoder->exp === "0" && $_dadoundecoder->nbf === "0") {//se o padrao for 0 não ha validacao temporal
            //return true;
            return $dadoundecoder;
        }else if($datahorabdados_timestamp > $_dadoundecoder->exp) {//se a hora nao for valida
            return 'expired';
        }else{
            //return true;
            return $dadoundecoder;
        }

    }

    
    
    
    
    
    
    
    /**
     * retorna dados do usuario decodificado - base64
     */
    public static function keydecodeBbbbbbbbbbbbbbbbbb($data)
    {
        $_data = explode('.', $data['sgmtoken']);
        $data_ = $_data[1];
        $dadoundecoder = base64_decode(str_pad(strtr($data_, '-_', '+/'), strlen($data_) % 4, '=', STR_PAD_RIGHT));
        return $dadoundecoder;
    }

    
    
    
    

    
    
    /**
     * retorna dados do usuario decodificado - base64
     */
    public static function keydecoderrrrrrr($data)
    {
        
        
        
        
        
        $sgmtoken = explode('.', $data['sgmtoken']);
        $header = $sgmtoken[0];
        $payload = $sgmtoken[1];
//        $_payload = json_decode(base64_decode(str_pad(strtr($payload, '-_', '+/'), strlen($payload) % 4, '=', STR_PAD_RIGHT)));
        $sign = $sgmtoken[2];

        
        
        //Conferir se o token ainda esta valido (validacao definido de forma temporal)
        $dadoundecoder = self::authBase64Decode($payload);
//        $_dadoundecoder = json_decode($dadoundecoder);//converte os dados json para array PHP
//
//        if($sign !== $valid_){//se chave nao for valida
//            return false;
//        }else if($_dadoundecoder->exp === "0" && $_dadoundecoder->nbf === "0") {//se o padrao for 0 não ha validacao temporal
//            //return true;
//            return $dadoundecoder;
//        }else if($datahorabdados_timestamp > $_dadoundecoder->exp) {//se a hora nao for valida
//            return 'expired';
//        }else{
            //return true;
            return $dadoundecoder;
//        }        
        
        
        
        
        
        
        
        
        
    }

    
    
    
    
    
    
    
    
    
    

}


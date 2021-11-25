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
use App\DAO\Database;
use RdKafka;//classe Apache Kakfa

class Cidadao
{
    
    private static $fail_logininvalido = "Email ou senha inválido!";
    private static $fail_loginnaovalidado = "Usuário não validado!";
    private static $fail_loginnaoautorizado = "Usuário não autorizado para acesso!";
    private static $fail_loginbloqueado = "Usuário bloqueado!";
    private static $success_criarusuario = "Cadastro de usuario realizado com sucesso!";
    private static $fail_criarusuario = "Falha ao realizar o cadastro do usuário!";
    private static $fail_usuarionaoencontrado = "Usuário não encontrado!";
    
    //LISTENER_INSIDE trafego interno na rede Docker
    //LISTENER_DOCKER trafego da maquina Docker-host (localhost)
    //LISTENER_OUTSIDE trafego externo, alcancando o host Docker no ip ${IP_SERVER}
    //private static $topicBrokerList = "{kafka1:19091, kafka2:29091, kafka3:39091}"; //LISTENER_INSIDE
    //private static $topicBrokerList = "{localhost:19092, localhost:29092, localhost:39092}"; //LISTENER_DOCKER
    private static $topic_brokerList = "{192.168.0.18:19093, 192.168.0.18:29093, 192.168.0.18:39093}"; //LISTENER_OUTSIDE
   
    
    
    public static function select($id)
    {
        //instancia a conexao e a tabela
        $connPdo = Database::connect();
        $connTable =  Database::admUsuario();
        //consulta
        $sql = 'SELECT * FROM '.$connTable.' WHERE id = :id';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(self::$fail_usuarionaoencontrado);
        }
    }

    
    
    
   
    /**
     * retorna solicitacao a partir do identificador
     */
    public static function selectAll()
    {
        //instancia a conexao e a tabela
        $connPdo = Database::connect();
        $connTable_admCidadao =  Database::admCidadao();
        $connTable_admPerfil =  Database::admPerfil();
        $sql = "SELECT
                USR.id USR_ID, 
                USR.perfil USR_PERFILID,
                (SELECT titulo FROM `$connTable_admPerfil` WHERE id = USR.perfil) USR_PERFILNOME,
                USR.entmunicipal USR_ENTMUNICIPAL,
                USR.nome USR_NOME,
                USR.cpf USR_CPF,
                USR.rg USR_RG,
                USR.endereco USR_ENDERECO,
                USR.telefone USR_TELEFONE,
                USR.email USR_EMAIL,
                USR.senha USR_SENHA,
                USR.status USR_STATUS,
                USR.criadata USR_CRIADATA
                FROM `$connTable_admCidadao` USR ";

        $stmt = $connPdo->query($sql);
        $row = 0;//numero de linhas - inicio
        $retornoconsulta = array();//instancia o array
        //gera array com indice
        while ($dadoconsulta = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $retornoconsulta[$row]['USR_ID'] = $dadoconsulta['USR_ID'];
            $retornoconsulta[$row]['USR_PERFILID'] = $dadoconsulta['USR_PERFILID'];
            $retornoconsulta[$row]['USR_PERFILNOME'] = $dadoconsulta['USR_PERFILNOME'];
            $retornoconsulta[$row]['USR_ENTMUNICIPAL'] = $dadoconsulta['USR_ENTMUNICIPAL'];
            $retornoconsulta[$row]['USR_NOME'] = $dadoconsulta['USR_NOME'];
            $retornoconsulta[$row]['USR_CPF'] = $dadoconsulta['USR_CPF'];
            $retornoconsulta[$row]['USR_RG'] = $dadoconsulta['USR_RG'];
            $retornoconsulta[$row]['USR_ENDERECO'] = json_decode($dadoconsulta['USR_ENDERECO']);
            $retornoconsulta[$row]['USR_TELEFONE'] = json_decode($dadoconsulta['USR_TELEFONE']);
            $retornoconsulta[$row]['USR_EMAIL'] = $dadoconsulta['USR_EMAIL'];
            $retornoconsulta[$row]['USR_SENHA'] = $dadoconsulta['USR_SENHA'];
            $retornoconsulta[$row]['USR_STATUS'] = $dadoconsulta['USR_STATUS'];
            $retornoconsulta[$row]['USR_CRIADATA'] = $dadoconsulta['USR_CRIADATA'];
            $row++;
        }
        //Retorno JSON COM INDICE
        if ($stmt->rowCount() > 0) {
            return $retornoconsulta;
        }else{
            return false;
        }
        
    }

    
    /*
     * MENSAGERIA KAFKA
     * CONSUMIDOR
     * Consome a mensagem gerado pelo recurso de mensageria
     */
    //            User::mensagemusercreate_consumer();
    public static function usercreate_consumerB()
    {
        /*
         * Consumir mensageria
         */
        $topicBrokerList = self::$topic_brokerList;
        /*Definicao das variaveis*/
        $topicGroupid = 'kafka23';
        $topicName = 'usercreate';
        $i=0;//inicio contador

        /*Instancia as configuracoes do rdkafka*/
        $conf = new RdKafka\Conf();
        $conf->set('group.id', $topicGroupid);
        //$conf->set('log_level', (string) LOG_DEBUG);
        //$conf->set('debug', 'all');
        //$conf->set('metadata.broker.list', $topicBrokerList);

        /*Instancia o consumer do rdkafka*/
        $rk = new RdKafka\Consumer($conf);
        $rk->addBrokers($topicBrokerList);

        /*Instancia as configuracoes do topico*/
        $topicConf = new RdKafka\TopicConf();
        $topicConf->set('auto.commit.interval.ms', 100);
        $topicConf->set('offset.store.method', 'broker');//Definicao do metodo de armazenamento
        $topicConf->set('auto.offset.reset', 'earliest');
        $topic = $rk->newTopic($topicName, $topicConf);

        // O primeiro argumento é a partição a partir da qual consumir. 
        // O segundo argumento é o deslocamento no qual iniciar o consumo. Os valores válidos 
        // são: RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED.

        /*Consome as mensagens*/
        //$topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
        $topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);

        //Recuperar as mensagens consumidas:
        while (true) {
            $message = $topic->consume(0, 120*10000);
            //erros-> https://github.com/kwn/php-rdkafka-stubs/blob/master/stubs/constants.php
            //const RD_KAFKA_RESP_ERR_NO_ERROR = 0;
            //const RD_KAFKA_RESP_ERR__PARTITION_EOF = -191;
            //const RD_KAFKA_RESP_ERR__TIMED_OUT = -185;
            if ($message->err === 0){
                $usercreatecidadao = Cidadao::usercreatecidadao($message->key, $message->topic_name, $message->timestamp, $message->headers, $message->payload);
                if ($usercreatecidadao === false){
                    $cadastroexiste = 'Cadastro de cidadão já executado!';
                    return $cadastroexiste;
                }
            }else if ($message->err === -185){
                $timeout = 'Demora excessiva de rsposta do servidor (TIMED_OUT)';
                return $timeout;
            }else if($message->err === -191){
                $mensagemsucesso = 'O grupo '.$topicGroupid.' consumiu  '.$i.' itens referente ao topico '.$message->topic_name.' com sucesso !';
                return $mensagemsucesso;
            }
            $i++;
        }
        //---------------------------------------------------
        //object(RdKafka\Message)#5 (9) { 
        //["err"]=> int(-191) 
        //["topic_name"]=> string(16) "criacao_usuario5" 
        //["timestamp"]=> int(-1) 
        //["partition"]=> int(0) 
        //["payload"]=> string(24) "Broker: No more messages" 
        //["len"]=> int(24) 
        //["key"]=> NULL ["offset"]=> int(3) 
        //["headers"]=> NULL } 
        //---------------------------------------------------
    }


    
    
  
    /*
     * MENSAGERIA KAFKA
     * CONSUMIDOR
     * Consome a mensagem gerado pelo recurso de mensageria
     */
    //            User::mensagemusercreate_consumer();
    public static function usercreate_consumer($topicGroupid)
    {
        /*
         * Consumir mensageria
         */
        $topicBrokerList = self::$topic_brokerList;
        /*Definicao das variaveis*/
        //$topicGroupid = 'kafka23';
        $topicName = 'usercreate';
        $i=0;//inicio contador

        /*Instancia as configuracoes do rdkafka*/
        $conf = new RdKafka\Conf();
        $conf->set('group.id', $topicGroupid);
        //$conf->set('log_level', (string) LOG_DEBUG);
        //$conf->set('debug', 'all');
        //$conf->set('metadata.broker.list', $topicBrokerList);

        /*Instancia o consumer do rdkafka*/
        $rk = new RdKafka\Consumer($conf);
        $rk->addBrokers($topicBrokerList);

        /*Instancia as configuracoes do topico*/
        $topicConf = new RdKafka\TopicConf();
        $topicConf->set('auto.commit.interval.ms', 100);
        $topicConf->set('offset.store.method', 'broker');//Definicao do metodo de armazenamento
        $topicConf->set('auto.offset.reset', 'earliest');
        $topic = $rk->newTopic($topicName, $topicConf);

        // O primeiro argumento é a partição a partir da qual consumir. 
        // O segundo argumento é o deslocamento no qual iniciar o consumo. Os valores válidos 
        // são: RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED.

        /*Consome as mensagens*/
        //$topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
        $topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);

        //Recuperar as mensagens consumidas:
        while (true) {
            $message = $topic->consume(0, 120*10000);
            //erros-> https://github.com/kwn/php-rdkafka-stubs/blob/master/stubs/constants.php
            //const RD_KAFKA_RESP_ERR_NO_ERROR = 0;
            //const RD_KAFKA_RESP_ERR__PARTITION_EOF = -191;
            //const RD_KAFKA_RESP_ERR__TIMED_OUT = -185;
            if ($message->err === 0){
                $usercreatecidadao = Cidadao::usercreatecidadao($message->key, $message->topic_name, $message->timestamp, $message->headers, $message->payload);
                if ($usercreatecidadao === false){
                    $cadastroexiste = 'Cadastro de cidadão já executado!';
                    return $cadastroexiste;
                }
            }else if ($message->err === -185){
                $timeout = 'Demora excessiva de rsposta do servidor (TIMED_OUT)';
                return $timeout;
            }else if($message->err === -191){
                if ($i === 0){
                    //$mensagemsucesso = 'O grupo '.$topicGroupid.' ja consumiu todos os itens referente ao topico '.$message->topic_name.' com sucesso !';
                    $mensagemsucesso = 'tudo_consumido';
                    return $mensagemsucesso;
                }else{
                    $mensagemsucesso = 'O grupo '.$topicGroupid.' consumiu  '.$i.' itens referente ao topico '.$message->topic_name.' com sucesso !';
                    return $mensagemsucesso;
                }
            }
            $i++;
        }
        //---------------------------------------------------
        //object(RdKafka\Message)#5 (9) { 
        //["err"]=> int(-191) 
        //["topic_name"]=> string(16) "criacao_usuario5" 
        //["timestamp"]=> int(-1) 
        //["partition"]=> int(0) 
        //["payload"]=> string(24) "Broker: No more messages" 
        //["len"]=> int(24) 
        //["key"]=> NULL ["offset"]=> int(3) 
        //["headers"]=> NULL } 
        //---------------------------------------------------
    }


    
    
    
    
    
   /**
     * criacao do usuario Cidadao consumindo mensagem
     */
    public static function usercreatecidadao($key, $topic_name, $timestamp, $headers, $payload)
    {
        $_payload = json_decode($payload);
        $consumer = [
            'key' => $key,
            'topic_name' => $topic_name,
            'timestamp' => $timestamp,
            'headers' => $headers
        ];
        $_consumer = json_encode($consumer);
        
        $connPdo = Database::connect();
        $connTable_admCidadao = Database::admCidadao();
        $sql = 'INSERT INTO '.$connTable_admCidadao.' (id, perfil, entmunicipal, nome, cpf, rg, endereco, telefone, email, senha, status, criadata, statusconsumer) VALUES
                (:Id, :Perfil, :Entmunicipal, :Nome, :Cpf, :Rg, :Endereco, :Telefone, :Email, :Senha, :Status, :Criadata, :Consumer)';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Id', $_payload->usr_id);
        $stmt->bindValue(':Perfil', $_payload->usr_perfilid);
        $stmt->bindValue(':Entmunicipal', $_payload->usr_entmunicipal);
        $stmt->bindValue(':Nome', $_payload->usr_nome);
        $stmt->bindValue(':Cpf', $_payload->usr_cpf);
        $stmt->bindValue(':Rg', $_payload->usr_rg);
        $stmt->bindValue(':Endereco', $_payload->usr_endereco);
        $stmt->bindValue(':Telefone', $_payload->usr_telefone);
        $stmt->bindValue(':Email', $_payload->usr_email);
        $stmt->bindValue(':Senha', $_payload->usr_senha);
        $stmt->bindValue(':Status', $_payload->usr_status);
        $stmt->bindValue(':Criadata', $_payload->usr_criadata);
        $stmt->bindValue(':Consumer', $_consumer);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
        
    }
    
    
    
    
    
    

    public static function insert($data)
    {

//        //instancia a conexao e a tabela
//        $connPdo =  Database::connect();
//        $connTable =  Database::preUsuario();
//        //consulta
//        $sql = 'INSERT INTO '.$connTable.' (id, email, senha, cria_data, cria_ip, cria_dispositivo, status)VALUES (:id, :ema, :sen, :crd, :crp, :cri, :cri)';
//        $stmt = $connPdo->prepare($sql);
//        $stmt->bindValue(':id', $data['id']);
//        $stmt->bindValue(':ema', $data['email']);
//        $stmt->bindValue(':sen', $data['senha']);
//        $stmt->bindValue(':crd', $data['cria_data']);
//        $stmt->bindValue(':crp', $data['cria_ip']);
//        $stmt->bindValue(':cri', $data['cria_dispositivo']);
//        $stmt->bindValue(':cri', $data['status']);
//        $stmt->execute();
//
//        if ($stmt->rowCount() > 0) {
//            //return 'Usuário(a) inserido com sucesso!';
//            throw new \Exception(self::$success_usuarioinseridocomsucesso);
//        } else {
//            throw new \Exception(self::$fail_usuariofalhaaoinserir);
//        }
//        
        
        
    }
    
   
    
    
}






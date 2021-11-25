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
 * cadastro do cliente solicitacao
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

class Solicitacao
{
    private static $fail_solicitacaoinexistente = "Solicitação não existe!";
    private static $fail_criarsolicitacao = "Falha ao realizar o cadastro da solicitação!";
    private static $success_criarsolicitacao = "Cadastro de solicitação realizada com sucesso!";
    
    //LISTENER_INSIDE trafego interno na rede Docker
    //LISTENER_DOCKER trafego da maquina Docker-host (localhost)
    //LISTENER_OUTSIDE trafego externo, alcancando o host Docker no ip ${IP_SERVER}
    //private static $men_topic_brokerList = "{kafka1:19091, kafka2:29091, kafka3:39091}"; //LISTENER_INSIDE
    //private static $men_topic_brokerList = "{localhost:19092, localhost:29092, localhost:39092}"; //LISTENER_DOCKER
    private static $men_topic_brokerList = "{192.168.0.18:19093, 192.168.0.18:29093, 192.168.0.18:39093}"; //LISTENER_OUTSIDE
    private static $men_topic_header = ['Sistema' => 'SGM-Sistema de Gestao Municipal', 'API' => 'Mensageria', 'Descricao' => 'TCC Puc Minas', 'Desenvolvimento' => 'Augusto Arruda'];
    private static $men_topic_name = "solicitacaocreate";
    private static $men_topic_client = "rdkafka";
    private static $men_topic_tipo = "solicitacao_criada";
    
    

    public static function select($id)
    {
        //instancia a conexao e a tabela
        $connPdo = Database::connect();
        $connTable = Database::admSolicitacao();
        //consulta
        $sql = 'SELECT * FROM '.$connTable.' WHERE id = :id';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(self::$fail_solicitacaoinexistente);
        }
    }

    
 
    
    public static function selectSolicitante($solicitanteid)
    {
        //instancia a conexao e a tabela
        $connPdo =  Database::connect();
        $connTable_admSolicitacao =  Database::admSolicitacao();
        //consulta
        $sql = 'SELECT * FROM '.$connTable_admSolicitacao.' WHERE solicitante LIKE "%'.$solicitanteid.'%"';
        $stmt = $connPdo->prepare($sql);
        //$stmt->bindValue(':Solicitante', "%'.$solicitanteid.'%");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(self::$fail_solicitacaoinexistente);
        }
    }
    
    
       
    
    
    
    public static function selectAll_B() 
    {
        //instancia a conexao e a tabela
        $connPdo =  Database::connect();
        $connTable_admSolicitacao =  Database::admSolicitacao();
        //consulta
        $sql = 'SELECT * FROM '.$connTable_admSolicitacao;
        $stmt = $connPdo->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(self::$fail_solicitacaoinexistente);
        }
    }
    
    
    
    
    
    /**
     * retorna solicitacao a partir do identificador
     */
    public static function selectAll()
    {
        //instancia a conexao e a tabela
        $connPdo = Database::connect();
        $connTable_admSolicitacao =  Database::admSolicitacao();
        $sql = "SELECT
                id,
                processo,
                tiposervico,
                descricao,
                data_solicitacao DATA_SOLICTACAO_EN,
                DATE_FORMAT(data_solicitacao, '%d/%m/%Y %H:%i') DATA_SOLICTACAO_PTBR,
                solicitante,
                data_finalizacao,
                responsavel,
                status,
                (CASE WHEN status = '0' THEN 'Instanciado' WHEN status = '1' THEN 'Em andamento' WHEN status = '2' THEN 'Finalizado' ELSE 'Sem Definição' END) STATUS_NOME,
                statusconsumer
                FROM `$connTable_admSolicitacao` ";
        $stmt = $connPdo->query($sql);
        $row = 0;//numero de linhas - inicio
        $retornoconsulta = array();//instancia o array
        //gera array com indice
        while ($dadoconsulta = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $retornoconsulta[$row]['id'] = $dadoconsulta['id'];
            $retornoconsulta[$row]['processo'] = $dadoconsulta['processo'];
            $retornoconsulta[$row]['tiposervico'] = $dadoconsulta['tiposervico'];
            $retornoconsulta[$row]['descricao'] = $dadoconsulta['descricao'];
            $retornoconsulta[$row]['data_solicitacao'] = $dadoconsulta['DATA_SOLICTACAO_PTBR'];
            $retornoconsulta[$row]['solicitante'] = json_decode($dadoconsulta['solicitante']);
            $retornoconsulta[$row]['data_finalizacao'] = $dadoconsulta['data_finalizacao'];
            $retornoconsulta[$row]['responsavel'] = $dadoconsulta['responsavel'];
            $retornoconsulta[$row]['status'] = $dadoconsulta['STATUS_NOME'];
            $retornoconsulta[$row]['statusconsumer'] = $dadoconsulta['statusconsumer'];
            $row++;
        }
        //Retorno JSON COM INDICE
        if ($stmt->rowCount() > 0) {
            return $retornoconsulta;
        }else{
            throw new \Exception(self::$fail_solicitacaoinexistente);
        }
        
    }

    
    
    
    
    
    
       
 
    /**
     * criar nova solicitacao
     */
    public static function create($data)
    {
        $status = 0;
        $connPdo = Database::connect();
        $connTable_admSolicitacao = Database::admSolicitacao();
        $sql = 'INSERT INTO '.$connTable_admSolicitacao.' (id, processo, tiposervico, descricao, data_solicitacao, solicitante, data_finalizacao, responsavel, status) VALUES
                (:Id, :Processo, :Tiposervico, :Descricao, sysdate(), :Solicitante, :Data_finalizacao, :Responsavel, :Status)';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Id', NULL);
        $stmt->bindValue(':Processo', $data['novasolicitacao_processo']);
        $stmt->bindValue(':Tiposervico', $data['novasolicitacao_tiposervico']);
        $stmt->bindValue(':Descricao', $data['novasolicitacao_descricao']);
        //$stmt->bindValue(':Data_solicitacao', $data['nome']);
        $stmt->bindValue(':Solicitante', $data['novasolicitacao_solicitante']);
        $stmt->bindValue(':Data_finalizacao', NULL);
        $stmt->bindValue(':Responsavel', NULL);
        $stmt->bindValue(':Status', $status);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            //return self::$success_criarsolicitacao;
            //Gerar mensageria
            return Solicitacao::mensagemsolicitacaocreate($data['novasolicitacao_processo'], $data['novasolicitacao_tiposervico'], $data['novasolicitacao_solicitante']);
        } else {
            throw new \Exception(self::$fail_criarsolicitacao);
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
    
    
    
        
    /**
     * define padrao base64UrlDecode para o JWT.io
     */
    private static function solicitacaoBase64Decode($data)
    {
        // Decodificar $data para string Base64
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
        //return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
    }
    

 
    /**
     * Decodifica a chave encripitada no padrao JWT.io e retorna os dados do payload
     */
    public static function keydecode($data)
    {
        $sgmtoken = explode('.', $data['sgmtoken']);
        //$header = $sgmtoken[0];
        $payload = $sgmtoken[1];
        //$sign = $sgmtoken[2];
        $dadoundecoder = self::solicitacaoBase64Decode($payload);
        return $dadoundecoder;
    }

    
    
    
    
    
    
    
    
    
    
    
    
    

  
    /*
     * MENSAGERIA KAFKA
     * CONSUMIDOR
     * Consome a mensagem gerado pelo recurso de mensageria
     */
    //            User::mensagemusercreate_consumer();
    public static function solicitacaocreate_consumer($topicGroupid)
    {
        
        /*
         * Consumir mensageria
         */
        $topicBrokerList = self::$men_topic_brokerList;
        /*Definicao das variaveis*/
        //$topicGroupid = 'kafka23';
        $topicName = self::$men_topic_name;
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
                $usercreatecidadao = Solicitacao::solicitacaocreate($message->key, $message->topic_name, $message->timestamp, $message->headers, $message->payload);
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
                    
//                    $mensagemsucesso = 'isso ai';
                    
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
    public static function solicitacaocreate($key, $topic_name, $timestamp, $headers, $payload)
    {
        $_payload = json_decode($payload);
        $consumer = [
            'key' => $key,
            'topic_name' => $topic_name,
            'timestamp' => $timestamp,
            'headers' => $headers
        ];
        $_consumer = json_encode($consumer);
        
        $status = 1; //status 0 - instanciado, 1- em processo, 2 - finalizado
        
        $connPdo = Database::connect();
        $connTable_admSolicitacao = Database::admSolicitacao();
        $sql = 'INSERT INTO '.$connTable_admSolicitacao.' (id, processo, tiposervico, descricao, data_solicitacao, solicitante, data_finalizacao, responsavel, status, statusconsumer) VALUES
                (:Id, :Processo, :Tiposervico, :Descricao, :Data_solicitacao, :Solicitante, :Data_finalizacao, :Responsavel, :Status, :Statusconsumer)';
        
        

        
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':Id', $_payload->id);
        $stmt->bindValue(':Processo', $_payload->processo);
        $stmt->bindValue(':Tiposervico', $_payload->tiposervico);
        $stmt->bindValue(':Descricao', $_payload->descricao);
        $stmt->bindValue(':Data_solicitacao', $_payload->data_solicitacao);
        $stmt->bindValue(':Solicitante', $_payload->solicitante);
        $stmt->bindValue(':Data_finalizacao', $_payload->data_finalizacao);
        $stmt->bindValue(':Responsavel', $_payload->responsavel);
        $stmt->bindValue(':Status', $status);// status alterado para 1 - em processo
        $stmt->bindValue(':Statusconsumer', $_consumer);

        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}


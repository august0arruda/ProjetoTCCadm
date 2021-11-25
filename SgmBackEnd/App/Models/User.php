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

class User
{
    
    private static $fail_logininvalido = "Email ou senha inválido!";
    private static $fail_loginnaovalidado = "Usuário não validado!";
    private static $fail_loginnaoautorizado = "Usuário não autorizado para acesso!";
    private static $fail_loginbloqueado = "Usuário bloqueado!";
    private static $success_criarusuario = "Cadastro de usuario realizado com sucesso!";
    private static $fail_criarusuario = "Falha ao realizar o cadastro do usuário!";
    

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

    public static function selectAll() 
    {
        //instancia a conexao e a tabela
        $connPdo =  Database::connect();
        $connTable =  Database::admUsuario();
        //consulta
        $sql = 'SELECT * FROM '.$connTable;
        $stmt = $connPdo->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(self::$fail_usuarionaoencontrado);
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
    private static function userBase64Decode($data)
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
        $dadoundecoder = self::userBase64Decode($payload);
        return $dadoundecoder;
    }

    
    
}

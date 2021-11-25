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
namespace App\Controllers;
/* alias/import */
use App\Models\Auth;

//checa autenticacao
class AuthController 
{

    private static $fail_verbo = "Verbo não implementado!";
    private static $fail_metodo = "Método não implementado!";
    private static $fail_login = "Email ou senha vazio!";
    private static $fail_keydecode = "Chave vazia!";
    
//    private static $fail_keydecode = "Chave vazia!";
    private static $fail_criarusuario = "Falha ao realizar o cadastro do usuário!";

    //Captura o metodo detectado pela variavel global GET
    public function get()
    {
        return self::$fail_verbo;
    }

    //Captura o metodo detectado pela variavel global POST
    //ex: http://localhost/area_teste/api_rest_2021-master/public_html/api/auth/login
    public function post($metodo) 
    {
        //JWTs são credenciais que podem conceder acesso a recursos, toda a validação e depuração é feita no lado do cliente.
        //https://jwt.io/
        if ($metodo === 'login'){//login da api
            $data = $_POST;//captura formulario enviado via POST
            if($data['email']!=="" && $data['senha']!==""){ // verifica se esta vazio
                return Auth::login($data);    
            }else{
                throw new \Exception(self::$fail_login);
            }

        }else if ($metodo === 'checktoken'){//retorna chave para decodificar dados do usuario
            $data = $_POST;//captura formulario enviado via POST
            if($data['sgmtoken']!==""){ // verifica se esta vazio
                return Auth::checktoken($data);    
            }else{
                throw new \Exception(self::$fail_keydecode);
            }
            
            
        }else if ($metodo === 'usercreate'){//retorna chave para decodificar dados do usuario
            $data = $_POST;//captura formulario enviado via POST
            if($data['gerarcidadaonovo']!=="" && $data['nome']!=="" && $data['email']!=="" && $data['senha']!==""){ // verifica se esta vazio
                return Auth::usercreate($data);    
            }else{
                throw new \Exception(self::$fail_criarusuario);
            }
            
        }else{
            return self::$fail_metodo;
        }

    }

    //Captura o metodo detectado pela variavel global PUT
    public function put() 
    {
        return self::$fail_verbo;
    }

    //Captura o metodo detectado pela variavel global DELETE
    public function delete() 
    {
        return self::$fail_verbo;
    }

    //Captura o metodo detectado pela variavel global OPTIONS
    public function options() 
    {
        return self::$fail_verbo;
    }

    //Captura o metodo detectado pela variavel global PATCH
    public function patch() 
    {
        return self::$fail_verbo;
    }


}

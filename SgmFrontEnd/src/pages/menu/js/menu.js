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
* login do cliente cidadao
*
* Desenvolvimento:
* Augusto Arruda 
* Email: augusto.rr.arruda@gmail.com
* Cel: (092) 991848979
* Manaus, 04 de abril de 2021.
*/


function logout(){
    //https://github.com/js-cookie/js-cookie
    Cookies.remove('sgmToken');//Deleta o token
    location.href="./login";//redireciona ao login
};


function getUserData(){
    //retorna cookie instanciado
    var SgmToken, userdata, userdataPriNome, userdataPerfil;
    SgmToken = Cookies.get('sgmToken');//retorna os dados dos Cookies
    
    $.ajax({
        url: "http://192.168.0.18:8081/ProjetoTCCadm/SgmBackEnd/public_html/api/user/keydecode",
        method: 'POST',
        headers: {//Recepciona os dados do token no Web Storage da API
            //'Access-Control-Allow-Origin': '*',//liberar acesso a qualquer dominio inclusive o local 
            //'Content-Type': 'application/json; application/x-www-form-urlencoded',//padrao header tipo JSON
            'Authorization': 'Bearer ' + SgmToken,
            'Aplicacao': 'Sistema de Gestão Municipal',
            'Modulo': 'Administrador',
            'POC_TCC': 'Puc Minas',
            'Autor': 'Augusto Arruda'
        },
        data: {
            sgmtoken: SgmToken
        }
    })
    .done(function(msg){
        //retorno > sucess ou error
        if(msg.status === 'sucess'){//Seta os dados do token no Web Storage da API
            //trata dados
            userdata = JSON.parse(msg.data);
            userdataPriNome = userdata['name'].split(' ');//retorna o primeiro nome do usuario
            userdataPerfil = userdata['perfilnome'];
            // seta o log na barra de menu
            $('.topo_user_name').html(userdataPriNome[0]);
            $('.userperfil').html(userdataPerfil);

        }else if(msg.status === 'error'){
            alert(msg.status);
            logout();
        }else {
            alert('Erro ao acessar token: '+JSON.stringify(msg));
            logout();
        }

    })
    .fail(function(msg){//retorno apos falha
        //close animacao de processamento
        alert('Falha ao acessar token: '+JSON.stringify(msg));
        logout();
    }); 
};



$(document).ready(function(){//sobe as funcoes ao carregar a pagina
    //Se o token for indefinido executar o logout
    if(Cookies.get('sgmToken') === undefined){
        logout();
    }else{
        /*Carga de log do usuario*/
        getUserData();
    }
    //hint com mouse over ou tootltip
    $('[data-toggle="tooltip"]').tooltip();
});
   
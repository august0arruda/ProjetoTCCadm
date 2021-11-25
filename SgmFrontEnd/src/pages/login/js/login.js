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

//instancia cookies
var Cookies;

//Requisao assincrona - carreaga dados da tela candidatonovo na div div_candidatonovo da modal
function loginacesso(){//envia post que sobe os arquivos a serem anexados pelo calouro

    if ($('#emailacesso').val().length===0) {//Verifica vazio
        alert("email vazio!");
        $('#emailacesso').focus();
        return false;
    }else if ($('#senhaacesso').val().length===0){//Verifica se vazio ou se esta fora do padrao de email
        alert("Senha vazia");
        $('#senhaacesso').focus();
        return false;
    }else{
        //start animacao de processamento
        //$('#loading_carga').css({display: 'block'});
        $.ajax({
            url: "http://192.168.0.18:8081/ProjetoTCCadm/SgmBackEnd/public_html/api/auth/login",//acesso API
            type : 'post',
            data: {
                email: $('#emailacesso').val(),
                senha: $('#senhaacesso').val()
            }
        })
        .done(function(msg){//retorno apos sucesso
    
            if(msg.status === 'sucess'){
                //sobe dados do token
                Cookies.set('sgmToken', msg.data);
                //location.href="./index.html";//redireciona
                location.href="./index";//redireciona
            }else{
                alert('Sem status definido!');
                window.location.reload();//atualiza a pagina atual
            }
            
        })
        .fail(function(msg){//retorno apos falha
    
            //close animacao de processamento
            //$('#loading_carga').css({display: 'none'});
            var tipoerro = msg.responseJSON;
            alert(tipoerro['data']);
            window.location.reload();//atualiza a pagina atual
            
        }); 
    }
  
};




//novo cadastro
function cadastrocidadao(){//envia post que sobe os arquivos a serem anexados pelo calouro
//    $('#loading_carga').css({display: 'block'});
    $.ajax({
        url : "./src/pages/login/cadastro.html",
        type : 'post',
        data : {
            cadastronovo: 'cadastro_novo'
        }
    })
    .done(function(msg){//retorno apos sucesso
        //close animacao de processamento
//        $('#loading_carga').css({display: 'none'});
        $('#div_cidadaonovo').html(msg);//recepciona o valores encontrados em html
        $("#modal_cidadaonovo").modal('show');//sobe o modal

    })
    .fail(function( msg){//retorno apos falha
        alert(msg);
    }); 
};



$(document).ready(function(){//sobe as funcoes ao carregar a pagina
    //Deleta o token
    Cookies.remove('sgmToken');
    //hint com mouse over ou tootltip
    $('[data-toggle="tooltip"]').tooltip();
});
   
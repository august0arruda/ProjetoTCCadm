/**
 * -------------------------------------------------------
 * PROJETO: Sapopemba
 * DESCRICAO: Sistema de Telessaude do estado do Amazonas
 * Cliente: Governo do Estado do Amazonas - SUS - UEA
 * Solicitante: Governo do Estado do Amazonas - SUS - UEA
 * -------------------------------------------------------
 * Descricao do arquivo:
 * login de acesso para modulo de cadastro
 * -------------------------------------------------------
 * Desenvolvimento:
 * NAP UEA
 * Augusto Arruda 
 * Analista TI - Desenvolvedor Full Stack
 * Email: augustoarruda@uea.edu.br
 * Cel: (092) 991848979
 * Manaus, 24 de março de 2020.
 */



function testecookie(){
    //testa dados dos Cookies
    alert(Cookies.get('sgmToken'));        
};



//lista todos os expedidores de rg
function projetonovo(){
    $('#modal_projetonovo').modal('show');
};



//nova solicitacao
function viewcidadao(){//envia post que sobe os arquivos a serem anexados pelo calouro
    $.ajax({
        url : "./src/pages/index/viewcidadao.html",
        type : 'post',
        data : {
            viewcidadao: 'view_cidadao'
        }
    })
    .done(function(msg){//retorno apos sucesso
        //close animacao de processamento
        $('#div_viewcidadao').html(msg);//recepciona o valores encontrados em html
        $("#modal_viewcidadao").modal('show');//sobe o modal
    })
    .fail(function( msg){//retorno apos falha
        alert(msg);
    }); 
};



//nova solicitacao
function viewsolicitacao(){//envia post que sobe os arquivos a serem anexados pelo calouro
    $.ajax({
        url : "./src/pages/index/viewsolicitacao.html",
        type : 'post',
        data : {
            viewsolicitacao: 'view_solicitacao'
        }
    })
    .done(function(msg){//retorno apos sucesso
        //close animacao de processamento
        $('#div_viewsolicitacao').html(msg);//recepciona o valores encontrados em html
        $("#modal_viewsolicitacao").modal('show');//sobe o modal
    })
    .fail(function( msg){//retorno apos falha
        alert(msg);
    }); 
};



$(document).ready(function(){//sobe as funcoes ao carregar a pagina

    /*Carga da barra de menus*/
    var urlmenu = "./src/pages/menu/menu.html";
    $("#div_urlmenu").load(urlmenu); 
    
    //hint com mouse over ou tootltip
    $('[data-toggle="tooltip"]').tooltip();

});
   
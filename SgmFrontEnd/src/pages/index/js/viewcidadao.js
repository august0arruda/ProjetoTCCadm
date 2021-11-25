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






//funcao de animação ao processar
function anima_tabelacidadao(k){//a- ativa i- inativa
    if(k === 'a'){
        //start animacao de processamento
        $('.loading_tabelacidadao').css({display: 'block'});
    }else{
        //close animacao de processamento
        $('.loading_tabelacidadao').css({display: 'none'});
    }
};



function usercreate_consumer(){
    //retorna cookie instanciado
    var SgmToken, perfilConsumer;
    SgmToken = Cookies.get('sgmToken');
    perfilConsumer = 'administrador';

    $.ajax({
        url: "http://192.168.0.18:8081/ProjetoTCCadm/SgmBackEnd/public_html/api/cidadao/usercreate_consumer="+perfilConsumer,// consumer com identificador do topicGroupid 
        method: 'POST',
        headers: {//Recepciona os dados do token no Web Storage da API
            //'Access-Control-Allow-Origin': '*',//liberar acesso a qualquer dominio inclusive o local 
            //'Content-Type': 'application/json; application/x-www-form-urlencoded',//padrao header tipo JSON
            'Authorization': 'Bearer ' + SgmToken,
            'Aplicacao': 'Sistema de Gestao Municipal',
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
        if(msg.status === 'sucess' && msg.data !== 'tudo_consumido'){//Seta os dados do token no Web Storage da API
            alert(msg.data);
            readerallcidadao();
        }else if(msg.status === 'sucess' && msg.data === 'tudo_consumido'){//Seta os dados do token no Web Storage da API
            readerallcidadao();
        }else if(msg.status === 'error'){
             //readerallcidadao();
        }else {
            alert('Falha no retorno');
        }     

    })
    .fail(function(msg){//retorno apos falha
        //informes do erro    
        alert('Falha ao acessar token: '+JSON.stringify(msg));
        //atualiza pagina
        location.reload();
    }); 

}




function readerallcidadao(){
    //retorna cookie instanciado
    var SgmToken = Cookies.get('sgmToken');
    //start animacao de processamento
    anima_tabelacidadao('a'); //ativa animacao

    $.ajax({
        url: "http://192.168.0.18:8081/ProjetoTCCadm/SgmBackEnd/public_html/api/cidadao/reader",
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
            var new_row, cols, deftime; 
            //loop dos dados
            $.each(msg.data, function(index, array_buscacadastrousuario){
                new_row = $('<tr>');
                //cols += '<td style="color: #808080;">'+index+'</td>'; //referencia cinza
                cols += '<td class="coluna_tbody ctb_0">'+array_buscacadastrousuario['USR_ID']+'</td>'; //referencia cinza
                cols += '<td class="coluna_tbody ctb_1">'+array_buscacadastrousuario['USR_PERFILNOME']+'</td>';
                cols += '<td class="coluna_tbody ctb_1">'+array_buscacadastrousuario['USR_NOME']+'</td>';        
                cols += '<td class="coluna_tbody ctb_1">'+array_buscacadastrousuario['USR_CPF']+'</td>';        
                cols += '<td class="coluna_tbody ctb_1">'+array_buscacadastrousuario['USR_EMAIL']+'</td>'; 
                cols += '<td class="coluna_tbody ctb_1">'+array_buscacadastrousuario['USR_STATUS']+'</td>'; 
                cols += ('</tr>');
                new_row.append(cols);
                $('#tabelacidadao').append(new_row);   
            });

            instanciar_DataTable_tabelacidadao();
        }else if(msg.status === 'error'){
            alert(msg.status);
        }else {
            alert('Falha no retorno');
        }     
 
    })
    .fail(function(msg){//retorno apos falha
        //informes do erro    
        alert('Falha ao acessar token: '+JSON.stringify(msg));
        //atualiza pagina
        location.reload();
    }); 
       
    //start animacao de processamento
    anima_tabelacidadao('i'); //ativa animacao

}


//instancia o plugin de tabelas DataTable
function instanciar_DataTable_tabelacidadao(){
    //caracteres webdings
    //https://graphemica.com/%E2%8C%95
    //&#128269;  
    //&#8981;
    $('#tabelacidadao').DataTable({
        //Idioma - portugues-Brasil by Augusto
        language: { 
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "Visualizar _MENU_ resultados",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "         &#128270;",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
        },
        "searching": true, //ativa-desativa busca
        "lengthChange": true, //ativza-desativa numeros de linhas a serem apresentadas
        "paging": true, //ativa-desativa paginacao
        "info": true, //ativa-desativa informacao de linhas
        "responsive": true, //ativa-desativa Responsividade
        "destroy": true, //destroi a tabela que entrou anteriormente
        "order": [ 0, "desc" ], // define a ordenacao tipo default da tabela
        //"order": [[ 1, "asc" ], [ 10, "desc" ], [ 5, "desc" ]], // define a ordenacao tipo default da tabela
        //"order": [[ 10, "desc" ], [ 5, "desc" ]], // define a ordenacao tipo default da tabela
        'columnDefs': [// definicoes de clunas é usado para definir aparência e comportamento da primeira coluna 
            {
                'targets': 0,//define se o alvo do clique
                'visible': true// define se o alvo (id) e visivel ou nao
            }
    //        {
    //            'targets': [11,12,13],//define a coluna que vai ficar os checkboxes
    //            'data': 0,
    //            'checkboxes': false
    //        },
    //        {
    //          'render': function (data, type, full, meta){
    //             return '<input type="checkbox" name="id[]" value="' + $('<div/>').text('ssss' ).html() + '">';
    //        }
    //        }
            ]
    });

};



//ajuste responsivel do plugin datatables
$(window).resize(function(){
    /*recarregar a instancia*/
    //recarrega os dados toda vez que e redimencionado usando recurso "destroy: true" no plugin DataTable
    readerallcidadao();
});


$(document).ready(function(){//sobe as funcoes ao carregar a pagina
    /*Carga de parametros anexos*/
    usercreate_consumer();
    //hint com mouse over ou tootltip
    $('[data-toggle="tooltip"]').tooltip();
    //detecta o fechamento do modal e atualiza a pagina
    $('#modal_viewcidadao').on('hidden.bs.modal', function () {
        location.reload();
    });
});
      
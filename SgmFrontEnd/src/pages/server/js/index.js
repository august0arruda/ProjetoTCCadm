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






function usercreate_consumer(){
    //retorna cookie instanciado
    var SgmToken = Cookies.get('sgmToken');

    $.ajax({
        url: "http://192.168.0.18:8081/ProjetoTCCadm/SgmBackEnd/public_html/api/cidadao/usercreate_consumer=carabujiam",// consumer com identificador do topicGroupid 
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
        if(msg.status === 'sucess'){//Seta os dados do token no Web Storage da API
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

            instanciar_DataTable();
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

}





$(document).ready(function(){//sobe as funcoes ao carregar a pagina

    var timerpadrao, timerstartcontagem, timerinativo, timeratualizacao, timeoutRestart;
    
    //recepciona valor de tempo
    timerpadrao = 0.5 * 60;//Tempo em segundos - 10min
    timerstartcontagem = timerpadrao;
    timerinativo = 0;//Tempo de delay para iniciar a contagem - 0 segundos
    timeratualizacao = 1000;//Tempo em milisegundos - 1 segundo
    timeoutRestart = setTimeout(timerRegressivo, timerinativo);/*seta timeout regressivo inicial e atribui o valor para restart*/

    function timerRegressivo(){
        var timeoutRegressivo, min, seg, horaImprimivel;
        timeoutRegressivo = setTimeout(timerRegressivo, timeratualizacao);/*seta time regressivo em nova chamada*/
        if((timerstartcontagem - 1) >= 0){ //se tempo nao zerar
            min = parseInt(timerstartcontagem/60);//parte int dos minutos
            seg = timerstartcontagem%60;// resto dos segundos
            if(min < 10){// formata no numero menor que 10 sequencial
                min = "0" + min;
                min = min.substr(0, 2);
            }
            if(seg <=9){// padrao de numeros com 1 digito
                seg = "0" + seg;
            }
            horaImprimivel = "Atualização em: 00:" + min + ":" + seg; //variável de estilo hora/cronômetro

            $("#viewtimer").html(horaImprimivel); //seta valor para desktop
            
            timeoutRegressivo; //loop de execucao da funcao 1 seg
            timerstartcontagem--; //decrementar tempo
        }else{ //quando zerar a contagem

            //atualiza pagina
            location.reload();
        };
    };

    //hint com mouse over ou tootltip
    $('[data-toggle="tooltip"]').tooltip();

});
   
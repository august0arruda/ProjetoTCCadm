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
  

  
//valida CPF
function testacpf_novocadastro(strCPF){
    var Soma;
    var Resto;
    Soma = 0;
    if (strCPF === "00000000000") return false;
    if (strCPF === "11111111111") return false;
    if (strCPF === "22222222222") return false;
    if (strCPF === "33333333333") return false;
    if (strCPF === "44444444444") return false;
    if (strCPF === "55555555555") return false;        
    if (strCPF === "66666666666") return false;    
    if (strCPF === "77777777777") return false;    
    if (strCPF === "88888888888") return false;    
    if (strCPF === "99999999999") return false;    
    for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
    Resto = (Soma * 10) % 11;
    if ((Resto === 10) || (Resto === 11))  Resto = 0;
    if (Resto !== parseInt(strCPF.substring(9, 10)) ) return false;
    Soma = 0;
    for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
    Resto = (Soma * 10) % 11;
    if ((Resto === 10) || (Resto === 11))  Resto = 0;
    if (Resto !== parseInt(strCPF.substring(10, 11) ) ) return false;
    return true;
};






//verifica se o cpf já possui registro - link do FUNCOES DE MASCARA
function buscacadastrocpf(cpf_usuario){
    $.ajax({
        url : "./protected/models/cadastrosubmit.php",
        type : 'post',
        data : {
            buscacadastrocpf: 'buscacadastro_cpf',
            cpfusuario: cpf_usuario
        }
    })
    .done(function(msg){//retorno apos sucesso
        if(msg === '1'){
            alert('O CPF '+cpf_usuario+' já possui um registro');
            $("#cpf").val('');
        }
    })
    .fail(function( msg){//retorno apos falha
        alert(msg);
    }); 
};



//FUNCOES DE MASCARA - padrao  oninput="mascara(this, 'data')"
function mascara(i,t){
    var regraData = /^(0[1-9]|[1,2][0-9]|3[0,1])\/(0[1-9]|1[0,1,2])\/((1[9]|2[0])[0-9][0-9])$/;//periodo de data valido 01/01/1900 a 31/12/2099
    //var regraEmail = /^([0-9a-zA-Z]+([_.-]?[0-9a-zA-Z]+)*@[0-9a-zA-Z]+[0-9,a-z,A-Z,.,-]*(.){1}[a-zA-Z]{2,4})+$/; //padrao email
    var v = i.value;
   
    if(isNaN(v[v.length-1])){
        i.value = v.substring(0, v.length-1);
        return;
    }
   
    //valida CPF
    function testacpf(strCPF){
        var Soma;
        var Resto;
        Soma = 0;
        if (strCPF === "00000000000") return false;
        if (strCPF === "11111111111") return false;
        if (strCPF === "22222222222") return false;
        if (strCPF === "33333333333") return false;
        if (strCPF === "44444444444") return false;
        if (strCPF === "55555555555") return false;        
        if (strCPF === "66666666666") return false;    
        if (strCPF === "77777777777") return false;    
        if (strCPF === "88888888888") return false;    
        if (strCPF === "99999999999") return false;    
        for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
        Resto = (Soma * 10) % 11;
        if ((Resto === 10) || (Resto === 11))  Resto = 0;
        if (Resto !== parseInt(strCPF.substring(9, 10)) ) return false;
        Soma = 0;
        for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
        Resto = (Soma * 10) % 11;
        if ((Resto === 10) || (Resto === 11))  Resto = 0;
        if (Resto !== parseInt(strCPF.substring(10, 11) ) ) return false;
        return true;
    };
   
    if(t == "data"){
         i.setAttribute("maxlength", "10");
         if (v.length == 2 || v.length == 5) i.value += "/";
         if (i.value.length === 10 && i.value.search(regraData)) {i.value = ""; alert("Data inválida")}
         return false;
    }

    if(t == "cpf"){//com mascara
         i.setAttribute("maxlength", "14");
         if (v.length == 3 || v.length == 7) i.value += ".";
         if (v.length == 11) i.value += "-";
    }
    
    if(t == "cpf_num"){//somente numeros
        //i.setAttribute("maxlength", "11");
        if (v.length === 11){
            if (testacpf(v) === false){
                alert("Cpf inválido");
            }
        }
    }
    
    if(t == "cpf_cad_num"){//somente numeros
        //i.setAttribute("maxlength", "11");
        if (v.length === 11){
            if (testacpf(v) === false){
                alert("Cpf inválido");
            }else{
//                buscacadastrocpf(v);//verifica se cpf ja possui cadastro
//                alert('busca dados no banco');
            }
        }
    }

    if(t == "cnpj"){
         i.setAttribute("maxlength", "18");
         if (v.length == 2 || v.length == 6) i.value += ".";
         if (v.length == 10) i.value += "/";
         if (v.length == 15) i.value += "-";
    }

    if(t == "cep"){
         i.setAttribute("maxlength", "9");
         if (v.length == 5) i.value += "-";
    }
    
    if(t == "tel"){
        i.setAttribute("maxlength", "15");
        //console.log (v);
        var k = i.value;
        if (v.length === 1){var k = i.value; i.value="("+k;}
        if (v.length === 4) i.value += ") ";
        if (v.length === 10) i.value += "-";
        return false;
    }
    
    if(t == "cel"){
        i.setAttribute("maxlength", "16");
        //console.log (v);
        var k = i.value;
        if (v.length === 1){var k = i.value; i.value="("+k;}
        if (v.length === 4) i.value += ") ";
        if (v.length === 11) i.value += "-";
        return false;
    }
   

}



//*Busca externa ViaCEP - Webservice CEP e IBGE
//limpa cep
function limpa_formulário_cep(){
    $('#enderecologradouro').val("");
    $('#enderecocomplemento').val("");
    $('#enderecobairro').val("");
    $('#enderecocidade').val("");
    $('#enderecoestadouf').val("");
    //$('#ibge').val("");
};
//retorna dados do app
function retorno_server(conteudo){
    if(!("erro"in conteudo)){
        $('#enderecologradouro').val(conteudo.logradouro);
        $('#enderecocomplemento').val(conteudo.complemento);
        $('#enderecobairro').val(conteudo.bairro);
        $('#enderecocidade').val(conteudo.localidade);
        $('#enderecoestadouf').val(conteudo.uf);
    }else{
        limpa_formulário_cep();
        alert("CEP não encontrado!");
    }
};
//pesquisa o cep
function pesquisacep(valor){
    var cep=valor.replace(/\D/g,'');
    if(cep!==""){
        var validacep=/^[0-9]{8}$/;
        if(validacep.test(cep)){
            $('#enderecologradouro').val("...");
            $('#enderecocomplemento').val("...");
            $('#enderecobairro').val("...");
            $('#enderecocidade').val("...");
            $('#enderecoestadouf').val("...");
            //$('#ibge').val("...");
            var script=document.createElement('script');
            script.src='https://viacep.com.br/ws/'+cep+'/json/?callback=retorno_server';
            document.body.appendChild(script);
        }else{
            limpa_formulário_cep();
            alert("Formato de CEP inválido!");
        }
    }else{
        limpa_formulário_cep();
    }
};




//funcao de animação ao processar
function anima_cidadaonovo(k){//a- ativa i- inativa
    if(k === 'a'){
        //start animacao de processamento
        $('.loading_cidadaonovo').css({display: 'block'});
    }else{
        //close animacao de processamento
        $('.loading_cidadaonovo').css({display: 'none'});
    }
   
};





//submissao de novo cadastro
function cidadaonovo(){
    var sexo_, categoriacadastro_, decisao, formdata, regraData, regrasenha, regraEmail;
    regraData = /^(0[1-9]|[1,2][0-9]|3[0,1])\/(0[1-9]|1[0,1,2])\/((1[9]|2[0])[0-9][0-9])$/;//periodo de data valido 01/01/1900 a 31/12/2099
    regrasenha = /(?=^.{6,12}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[a-z-A-Z]).*$/; //letras e numeros de 6 a 12 caracteres
    regraEmail = /^([0-9a-zA-Z]+([_.-]?[0-9a-zA-Z]+)*@[0-9a-zA-Z]+[0-9,a-z,A-Z,.,-]*(.){1}[a-zA-Z]{2,4})+$/; //padrao email

    if ($('#nome').val().length===0) {//Verifica vazio
        alert("Nome vazio!");
        $('#nome').focus();
        return false;
 
    //endereco
    }else if ($('#enderecocep').val().length===0) {//Verifica vazio
        alert("Cep vazio!");
        $('#enderecocep').focus();
        return false;
    }else if ($('#enderecologradouro').val().length===0 ){//Verifica vazio
        alert("Logradouro vazio!");
        $('#enderecologradouro').focus();
        return false;
    }else if ($('#endereconum').val().length===0 ){//Verifica vazio
        alert("Número vazio!");
        $('#endereconum').focus();
        return false;
    }else if ($('#enderecobairro').val().length===0 ){//Verifica vazio
        alert("Bairro vazio!");
        $('#enderecobairro').focus();
        return false;
    }else if ($('#enderecocidade').val().length===0 ){//Verifica vazio
        alert("Cidade vazio!");
        $('#enderecocidade').focus();
        return false;
    }else if ($('#enderecoestadouf').val().length===0 ){//Verifica vazio
        alert("Estado vazio!");
        $('#enderecoestadouf').focus();
        return false;
    
    }else if ( $('#rgnum').val().length===0 ) {//Verifica vazio
        alert("Número de rg vazio!");
        $('#rgnum').focus();
        return false;
    }else if ( $('#rgexpedidor').val().length===0 ) {//Verifica vazio
        alert("Orgão expedidor de rg vazio!");
        $('#rgexpedidor').focus();
        return false;
     }else if ( $('#rgdtexpedicao').val().length===0 ) {//Verifica vazio
        alert("Data de expedição do rg vazio!");
        $('#rgdtexpedicao').focus();
        return false;
    }else if ( $('#rgdtexpedicao').val().search(regraData) ) {//Verifica vazio
        alert("Data de expedição do rg inválida!");
        $('#rgdtexpedicao').focus();
        return false;
    }else if ( $('#cpf').val().length===0 ) {//Verifica vazio
        alert("Cpf vazio!");
        $('#cpf').focus();
        return false;
    }else if ( testacpf_novocadastro($('#cpf').val()) !== true ){//Testa se cpf é valido
        alert("Cpf inválido");
        $('#cpf').focus();
        return false;

    //acesso e contato
    
    }else if ($('#celular').val().length===0 ){//Verifica vazio
        alert("Celular vazio!");
        $('#celular').focus();
        return false; 
    }else if ($('#email').val().length===0){//Verifica se vazio ou se esta fora do padrao de email
        alert("E-mail vazio!");
        $('#email').focus();
        return false;
    }else if ($('#email').val().search(regraEmail)){//Verifica se vazio ou se esta fora do padrao de email
        alert("E-mail inválido!");
        $('#email').focus();
        return false;
//    }else if ($('#email').attr('utilizado')==='1'){//Verifica se ja possui registro
//        alert('O E-mail '+$('#email').val()+' já possui registro');
//        $('#email').focus();
//        return false;    
    }else if ($('#repemail').val().length===0){//Verifica se vazio ou se esta fora do padrao de email
        alert("A confirmação de E-mail vazia!");
        $('#repemail').focus();
        return false;
    }else if ($('#repemail').val().search(regraEmail)){//Verifica se vazio ou se esta fora do padrao de email
        alert("A confirmação de E-mail inválida!");
        $('#repemail').focus();
        return false;
    }else if ($('#email').val() !== $('#repemail').val()){//Verifica se vazio ou se esta fora do padrao de email
        alert("A confirmação de E-mail é inválida!");
        $('#repemail').focus();
        return false;
    }else if ($('#senha').val().length===0){//Verifica se vazio ou se esta fora do padrao de email
        alert("Senha vazia");
        $('#senha').focus();
        return false;
    }else if ($('#senha').val().search(regrasenha)){//Verifica se vazio ou se esta fora do padrao de email
        alert("Senha inválida! Obrigatório a combinação de letras e números");
        $('#senha').focus();
        return false;
    }else if ($('#repsenha').val().length===0){//Verifica se vazio ou se esta fora do padrao de email
        alert("A confirmação de senha esta vazia!");
        $('#repsenha').focus();
        return false;
    }else if ($('#senha').val() !== $('#repsenha').val()){//Verifica se vazio ou se esta fora do padrao de email
        alert("A confirmação de Senha é inválida!");
        $('#repsenha').focus();
        return false;
    
    }else{
        
        decisao = confirm('Confirmar cadastro?');
        if (decisao){
            //start animacao de processamento
            anima_cidadaonovo('a'); //ativa animacao
            //executa a submissao
            //incorpora todas as div's do form - retorna valor a partir do name
            //padrao de envio de formulario sempre com a chave de array[0]
            formdata = new FormData($('#form_cidadaonovo')[0]);
            //adiciona novo topico ao formulario
            formdata.append('endereco_consolidado', 'Logradouro:'+$('#enderecologradouro').val()+', Complemento:'+$('#enderecocomplemento').val()+', N°:'+$('#enderecocep').val()+', Cep:'+$('#enderecocep').val()+', Bairro:'+$('#enderecobairro').val()+', Cidade:'+$('#enderecocidade').val()+'-'+$('#enderecoestadouf').val());
            formdata.append('rg_consolidado', 'N°:'+$('#rgnum').val()+', Emissor:'+$('#rgexpedidor').val()+', Data:'+$('#rgdtexpedicao').val());
            formdata.append('telefone_consolidado', 'Telefone:'+$('#telefone').val()+', Celular:'+$('#celular').val());
            
            $.ajax({//https://api.jquery.com/jquery.ajax/
                url: "http://192.168.0.18:8081/ProjetoTCCadm/SgmBackEnd/public_html/api/auth/usercreate",//acesso API
                type: 'post',
                data: formdata,
                async: true,//ao ativar assincrono o sistema processa sem travar
                cache: false, //false so funciona para solicitações HEAD e GET.
                contentType: false, //não definir nenhum cabeçalho de tipo de conteúdo
                //enctype default: application/x-www-form-urlencoded; charset=UTF-8
                enctype: 'multipart/form-data',//form com arquivo anexo
                processData: false //DOMDocument, ou outros dados não-processados
            })
            .done(function(msg){//retorno apos sucesso
                //fecha animacao de processamento
                anima_cidadaonovo('i');//inativa animacao
                //retorna a mensagem do processamento
                if(msg.status === 'sucess'){//Seta os dados do token no Web Storage da API
                    alert(msg.data);
                }else{
                    alert(msg.mensagem);
                }
                //fecha o modal
                $('#modal_cidadaonovo').modal('hide');
                //atualiza a pagina
                window.location.reload();
            })
            .fail(function( msg){//retorno apos falha
                alert(JSON.stringify(msg));
            });
            
        }else{
            return false;
        }
    }
};






$(document).ready(function(){//sobe as funcoes ao carregar a pagina

    //hint com mouse over ou tootltip
    $('[data-toggle="tooltip"]').tooltip();

    //detecta o fechamento do modal e atualiza a pagina
    $('#modal_cadastronovo').on('hidden.bs.modal', function () {
        location.reload();
    });

    //verifica se o email já possui registro
    $('#email').change(function(){
        var regraEmail = /^([0-9a-zA-Z]+([_.-]?[0-9a-zA-Z]+)*@[0-9a-zA-Z]+[0-9,a-z,A-Z,.,-]*(.){1}[a-zA-Z]{2,4})+$/; //padrao email
        if ($('#email').val().search(regraEmail)){//Verifica se vazio ou se esta fora do padrao de email
            alert("E-mail inválido!");
            $('#email').val('');
            $('#email').focus();
        }
    });  


    //verifica se o email já possui registro
    $('#repemail').change(function(){
        if ($('#email').val() !== $('#repemail').val()){//Verifica se vazio ou se esta fora do padrao de email
            alert("A confirmação de E-mail é inválida!");
            $('#repemail').focus();
        }    
    });
    

    //verifica se o email já possui registro
    $('#senha').change(function(){
        var regrasenha = /(?=^.{6,12}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[a-z-A-Z]).*$/; //letras e numeros de 6 a 12 caracteres
        if ($('#senha').val().search(regrasenha)){//Verifica se vazio ou se esta fora do padrao de email
            alert("Senha inválida! Obrigatório a combinação usando letras e números de 6 a 12 caracteres ");
            $('#senha').val('');
            $('#senha').focus();
        }
    });  


    //verifica se o email já possui registro
    $('#repsenha').change(function(){
        if ($('#senha').val() !== $('#repsenha').val()){//Verifica se vazio ou se esta fora do padrao de email
            alert("A confirmação da senha é inválida!");
            $('#repsenha').val('');
            $('#repsenha').focus();
        }
    });  

  
    /*Requisao assincrona list box dependente - busca cidade(s) do estado*/
    $('#enderecocep').change(function(){
        var valor = this.value;
        var cep = valor.replace(/\D/g,'');
        if(cep!==""){
            var validacep = /^[0-9]{8}$/;
            if(validacep.test(cep)){
                $('#enderecologradouro').val("...");
                $('#enderecocomplemento').val("...");
                $('#enderecobairro').val("...");
                $('#enderecocidade').val("...");
                $('#enderecoestadouf').val("...");
                //$('#ibge').val("...");
                var script = document.createElement('script');
                script.src = 'https://viacep.com.br/ws/'+cep+'/json/?callback=retorno_server';
                document.body.appendChild(script);
            }else{
                limpa_formulário_cep();
                alert("Formato de CEP inválido!");
            }
        }else{
            limpa_formulário_cep();
        }
    });  

    
    /*Algoritmo auxiliar para evitar que os cpf teham um valor menopr que 11*/
    $('#cpf').change(function(){
        if ( $('#cpf').val().length<11 ) {//Verifica vazio
            alert("Cpf inválido!");
            $('#cpf').val("");
            return false;
        }
    });
    

});
      
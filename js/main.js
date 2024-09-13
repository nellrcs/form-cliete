$(document).ready(function (e) {
    $("#novo").submit(function(e){
        
        var postData = $(this).serializeArray();
        var formURL = $(this).attr("action");
            $.ajax({
                url : formURL,
                type: "POST",
                data : postData,
                dataType: "json",
                complete: function(){
                    listaClientes();
                },
                success:function(data){           
                    console.log("data");
                }
            });
            e.preventDefault();
            inputsData(null);
        });
    listaClientes();
    document.getElementById('cancelar').addEventListener('click',function(){
        inputsData(null);
    });
});


function listaClientes(){
    $.ajax({
        url : "api/clientes",
        type: "GET",
        success:function(data){
            var rec = document.getElementById("listaClientes");
            rec.innerHTML = "";
            data.forEach(el => {
                var li = document.createElement('li');

                var button = document.createElement('button');
                button.addEventListener('click',function(){
                    document.getElementById('titulo').textContent = "Editar Cadastro";
                    document.getElementById('novo').action = "api/editar"
                    inputsData(el);
                });
                button.classList.add("btn");
                button.classList.add("btn-primary");
                button.classList.add("btn-sm");
                button.textContent = 'visualizar';
                
                var button2 = document.createElement('button');
                button2.addEventListener('click',function(){
                    apagar(el);
                });
                button2.classList.add("btn");
                button2.classList.add("btn-danger");
                button2.classList.add("btn-sm");
                button2.textContent = 'Apagar';


                li.textContent = el.nome;
                li.appendChild(button2);
                li.appendChild(button); 
                rec.appendChild(li);
            });
        }
    });
}


function inputsData(data){
    if(data){
        $("#id").val(data.id);
        $("#nome").val(data.nome);
        $("#email").val(data.email);
        $("#senha").val(data.senha);
        return;
    }
    $("#id").val("");
    document.getElementById('titulo').textContent = "Novo Cadastro";
    document.getElementById('novo').action = "api/novo"
    document.getElementById('novo').reset();
}

function apagar(obj){
    if(confirm("Desja realmente apagar este item")){
        $.ajax({
            url : 'api/apagar',
            type: "DELETE",
            data : obj,
            dataType: "json",
            complete: function(){
                listaClientes();
                inputsData(null)
            },
            success:function(data){           
                console.log("data");
            }
        });
    } 
};



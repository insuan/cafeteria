document.addEventListener("DOMContentLoaded", mostrarAlertaDocumento)

        const email = {
            email:"",
            asunto:"",
            mensaje:""
        }

        const inputEmail = document.querySelector("#email");
        const inputAsunto = document.querySelector("#asunto");
        const inputMensaje = document.querySelector("#mensaje");
        const formulario = document.querySelector("#formulario");
        const documento = document.querySelector("body h1");
        const btnSubmit = document.querySelector("#formulario button[type='submit']");
        const btnReset = document.querySelector("#formulario button[type='reset']");
        const sppiner = document.querySelector("#sppiner");

        inputEmail.addEventListener("input", revisar);
        inputAsunto.addEventListener("input",revisar);
        inputMensaje.addEventListener("input",revisar);

        formulario.addEventListener("submit", enviarEmail);

        btnReset.addEventListener("click",function(e){
            e.preventDefault();
            email.email = "";
            email.asunto = "";
            email.mensaje = "";
            formulario.reset();
            comprobarEmail(); 
        })

        function enviarEmail(e){
            e.preventDefault();

            sppiner.classList.add("flex");
            sppiner.classList.remove("hidden");

            setTimeout(() => {
                sppiner.classList.add("hidden");
                sppiner.classList.remove("flex");
                email.email = "";
                email.asunto = "";
                email.mensaje = "";
                formulario.reset();
                comprobarEmail(); 
            }, 3000);

            const alertaExito = documento.createElement("P");
            alertaExito.classList.add("bg-green-500","text-white","p-2","text-center","rounded-lg","mt-10","font-bold","text-sm","uppercase");
            alertaExito.textContent="Mensaje enviado correctamente";
            formulario.appendChild(alertaExito);
            setTimeout(() => {
                alertaExito.remove();
            }, 3000);
        }

        function revisar(e){
            if(e.target.value === ""){
                mostrarAlerta(`El campo ${e.target.id} no puede estar vacio`,e.target.parentElement);
                email[e.target.name]="";
                comprobarEmail();
                return;
            }
                
            limpiarAlerta(e.target.parentElement);

            if(e.target.id === "email" && !validarEmail(e.target.value)){
                mostrarAlerta(`No es un ${e.target.id} valido`,e.target.parentElement);
                email[e.target.name]="";
                comprobarEmail();
                return;
            }

            email[e.target.name] = e.target.value.trim().toLowerCase();

            comprobarEmail();


            }

        function mostrarAlerta(mensaje,referencia){
            alerta = referencia.querySelector(".bg-red-600");
            if(alerta){
                alerta.remove();
            }
            const error = document.createElement("P");
            error.textContent = mensaje;
            error.classList.add("bg-red-600","text-white","p-2","text-center");

            referencia.appendChild(error);
        }


        function mostrarAlertaDocumento(){
            alerta = document.querySelector(".bg-green-500");

            const error = document.createElement("P");
            error.textContent="Documento Cargado";
            error.classList.add("bg-green-500","text-white","p-2","text-center");

            //Inyectar HTML
            documento.appendChild(error);

            setTimeout(() => {
                error.remove();
            }, 3000);
        
        }

        function limpiarAlerta(referencia){
            alerta = referencia.querySelector(".bg-red-600");

            if(alerta){
                alerta.remove();
            }
        }

        function validarEmail(email){
            const regex = /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/
            resultado = regex.test(email);
            return resultado;
        }

        function comprobarEmail(){
            if(Object.values(email).includes("")){
                btnSubmit.classList.add("opacity-50");
                btnSubmit.disabled = true;
                return;
            }
                btnSubmit.classList.remove("opacity-50");
                btnSubmit.disabled = false;
        }
    

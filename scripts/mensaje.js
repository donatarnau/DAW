document.addEventListener("DOMContentLoaded", () => {

    const root = document.documentElement;
    const estilos = getComputedStyle(root);

    // MENSAJE
    let msgTipoBool = false;
    let msgTextoBool = false;
    const msgTipo = document.getElementById('tipoMensaje');
    const msgTexto = document.getElementById('mensaje');
    const msgBtn = document.getElementById('msg-boton');
    const mensaje = document.getElementById('formMensaje');

    if(msgBtn!==null){
        msgBtn.style.backgroundColor = "#5a5a5a"
    }

    msgTipo.addEventListener("input", (event) => {

        if(event.target.value.trim() === ''){ // MIRAMOS A VER SI ESTA VACÍO
            console.log('vacio');
            msgBtn.style.backgroundColor = "#5a5a5a"
            msgTipoBool = false;
        }else{
            console.log('lleno');
            if(msgTextoBool){
                msgBtn.style.backgroundColor = estilos.getPropertyValue("--titulos").trim();
            }
            msgTipoBool = true;
        }

    });

    msgTexto.addEventListener("input", (event) => {

        if(event.target.value.trim() === ''){ // MIRAMOS A VER SI ESTA VACÍO
            msgBtn.style.backgroundColor = "#5a5a5a"
            msgTextoBool = false;
        }else{
            if(msgTipoBool){
                msgBtn.style.backgroundColor = estilos.getPropertyValue("--titulos").trim();
            }
            msgTextoBool = true;
        }

    });

    // LISTENER DEL FORM, PARA SABER SI ENVIAR O NO

    mensaje.addEventListener("submit", (event) => {

        event.preventDefault();

        if(msgTipoBool && msgTextoBool){
            window.location.href = "./resMensaje.html";
            msgTipo.value = '';
            msgTexto.value = '';
        }else{
            console.log('No se han rellenada todos los campos');
        }
    });
});
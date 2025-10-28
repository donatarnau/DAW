document.addEventListener("DOMContentLoaded", () => {

    const root = document.documentElement;
    const estilos = getComputedStyle(root);

    // LOGIN
    let logUserBool = false;
    let logPassBool = false;
    const logUser = document.getElementById('login-user');
    const logPass = document.getElementById('login-pass');
    const logBtn = document.getElementById('login-btn');
    const login = document.getElementById('login-form');

    if(logBtn!==null){
        logBtn.style.backgroundColor = "#5a5a5a"
    }

    logUser.addEventListener("input", (event) => {

        if(event.target.value.trim() === ''){ // MIRAMOS A VER SI ESTA VACÍO
            console.log('vacio');
            logBtn.style.backgroundColor = "#5a5a5a"
            logUserBool = false;
        }else{
            console.log('lleno');
            if(logPassBool){
                logBtn.style.backgroundColor = estilos.getPropertyValue("--titulos").trim();
            }
            logUserBool = true;
        }

    });

    logPass.addEventListener("input", (event) => {

        if(event.target.value.trim() === ''){ // MIRAMOS A VER SI ESTA VACÍO
            logBtn.style.backgroundColor = "#5a5a5a"
            logPassBool = false;
        }else{
            if(logUserBool){
                logBtn.style.backgroundColor = estilos.getPropertyValue("--titulos").trim();
            }
            logPassBool = true;
        }

    });

    // LISTENER DEL FORM, PARA SABER SI ENVIAR O NO

    login.addEventListener("submit", (event) => {

        event.preventDefault();

        if(logUserBool && logPassBool){
            window.location.href = "./index_logged.php";
            logUser.value = '';
            logPass.value = '';
        }else{
            console.log('No se han rellenada todos los campos');
        }
    });

});
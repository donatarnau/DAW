document.addEventListener("DOMContentLoaded", () => {

    const root = document.documentElement;
    const estilos = getComputedStyle(root);

    // BUSQUEDA RAPIDA
    const barra = document.getElementById('fs');
    const fs = document.getElementById('fastSearch');

    fs.addEventListener("submit", (event) => {

        event.preventDefault();

        if(barra.value === ''){

        }else{
            window.location.href = "./resBuscar.html";
        }

    });

  
});
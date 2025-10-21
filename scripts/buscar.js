document.addEventListener("DOMContentLoaded", () => {

    const root = document.documentElement;
    const estilos = getComputedStyle(root);

    const anuncio = document.getElementById('param-anuncio');
    const vivienda = document.getElementById('param-vivienda');
    const ciudad = document.getElementById('param-ciudad');
    const pais = document.getElementById('param-pais');
    const minPrecio = document.getElementById('minPrecio');
    const maxPrecio = document.getElementById('maxPrecio');
    const fecha = document.getElementById('fecha_pub');
    const buscarBtn = document.getElementById('btnBuscar');
    const buscar = document.getElementById('busqueda');

    const modal = document.getElementById('avisoBuscar');
    const reglas = document.getElementById('reglas');
    const modalBtn = document.getElementById('closeModalBuscar');

    const fechaCorrecta = /^(\d{2})\/(\d{2})\/(\d{4})$/;
     const soloNumeros = /^[1-9]+$/;

    let minPok = true;
    let maxPok = true;
    let rangoMal = false;
    let fechaok = true; // LOGICA DE LA IMPLEMENTACION DE LOS PARAMETROS ESPECIALES -> FALTA POR IMPLEMENTAR
    let fechaExiste = true;
    let fechaFutura = true;

    fecha.addEventListener("input", (event) => {

        event.preventDefault();

        if(event.target.value.trim() === ''){
            fechaok = true;

        }else{
            // COMPROBAR EL FORMATO DE LA FECHA:
            const match = event.target.value.trim().match(fechaCorrecta); // para ver si el formato es adecuado
            
            if(!match){
                fechaok = false;
                fechaExiste = true;
                fechaFutura = false;
            }else{
                // SI EL FORMATO ES BUENO, VAMOS A VER SI LA FECHA EXISTE (no queremos 31 de febrero)
                const dia = parseInt(match[1], 10);
                const mes = parseInt(match[2], 10) - 1; // los meses van de 0 a 11 en JS
                const anyo = parseInt(match[3], 10);

                const duplicado = new Date(anyo, mes, dia); // crea una fecha con los parametros del usuario -> pasa automaticamente el 31/2 al 2/3
                if(duplicado.getFullYear() === anyo && duplicado.getMonth() === mes && duplicado.getDate() === dia) {
                    // LA FECHA ES POSIBLE

                    const today = new Date();

                    // Para comparar con hoy, usamos duplicado, que es la fecha introducida en formato date
                    today.setHours(0,0,0,0);
                    duplicado.setHours(0,0,0,0);

                    if(today<duplicado){
                        // No se puede introducir una fecha futura
                        fechaok = false;
                        fechaExiste = true;
                        fechaFutura = true;
                    }else{
                        fechaok = true;
                    }

                }else{
                    fechaok = false;
                    fechaExiste = false;
                }
            }
        }
    });

    minPrecio.addEventListener("input", (event) => {
        event.preventDefault();

        if(event.target.value.trim() === ''){
            minPok = true;
        }else{
            if(soloNumeros.test(event.target.value.trim())){
                // FORMATO CORRECTO
                if(parseInt(event.target.value.trim())>parseInt(maxPrecio.value)){
                    // RANGO MAL DISEÑADO
                    minPok = false;
                    rangoMal = true;
                }else{
                    minPok = true;
                    rangoMal = false;
                }
            }else{
                // FORMATO INCORRECTO
                minPok = false;
                rangoMal = false;
            }
        }
    });

    maxPrecio.addEventListener("input", (event) => {
        event.preventDefault();

        if(event.target.value.trim() === ''){
            maxPok = true;
        }else{
            if(soloNumeros.test(event.target.value.trim())){
                // FORMATO CORRECTO
                if(parseInt(event.target.value.trim())<parseInt(minPrecio.value)){
                    // RANGO MAL DISEÑADO
                    maxPok = false;
                    rangoMal = true;
                }else{
                    maxPok = true;
                    rangoMal = false;
                }
            }else{
                // FORMATO INCORRECTO
                maxPok = false;
                rangoMal = false;
            }
        }
    });

    buscar.addEventListener("submit", (event) => {

        event.preventDefault();

        reglas.innerHTML = '';

        if(anuncio.value.trim()!=='' || vivienda.value.trim()!=='' || ciudad.value.trim()!=='' || pais.value.trim()!=='' || fecha.value.trim()!=='' || minPrecio.value.trim()!=='' || maxPrecio.value.trim()!==''){
            console.log('Se puede buscar');
            // miramos las variables de datos especiales

            //FECHA
            if(!fechaok){
                if(fechaExiste){
                    if(fechaFutura){
                        reglas.innerHTML += '<p>La fecha introducida es posterior a la actualidad</p>';
                    }else{
                        reglas.innerHTML += '<p>Formato de fecha no soportado. Formato soportado: (dd/mm/aaaa)</p>';
                    }
                }else{
                    reglas.innerHTML += '<p>La fecha introducida no existe</p>';
                }
                console.log('Aqui no pasa nada');
            }else{
                console.log('La fecha vale?');
            }

            if(!minPok ||!maxPok){

                if(rangoMal){
                    reglas.innerHTML += '<p>El precio mínimo no puede superar al máximo</p>';
                }else{
                    reglas.innerHTML += '<p>Introduce números positivos para los precios</p>';
                }
            }


        }else{
            reglas.innerHTML += '<p>Introduce al menos un parámetro para buscar</p>';
        }

        if(reglas.innerHTML === ''){

            // LOGICA DE BUSQUEDA

            window.location.href = "./resBuscar.html";

        }else{
            modal.style.display = 'flex';
        }
    });

    modalBtn.addEventListener("click", (event) => {
        event.preventDefault;
        modal.style.display = 'none';
    });

    


});
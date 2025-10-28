document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    const estilos = getComputedStyle(root);


    // FAST LOGIN
    const flUser = document.getElementById('fl-user');
    const flPass = document.getElementById('fl-pass');
    const fl = document.getElementById('fastLogin');
    const flUserAd = document.getElementById('fl-user-ad');
    const flPassAd = document.getElementById('fl-pwd-ad');


    fl.addEventListener("submit", (event) => {

        event.preventDefault();

        let user = true;
        let pass = true;

        if(flUser.value.trim() === ''){
            flUserAd.style.display = 'flex';
            user=false;
        }else{
            flUserAd.style.display = 'none';
            user=true;
        }
        if(flPass.value.trim() === ''){
            flPassAd.style.display = 'flex';
            pass=false;
        }else{
            flPassAd.style.display = 'none';
            pass=true;
        }

        if(user && pass){
            window.location.href = "./index_logged.php";
            flUser.value = '';
            flPass.value = '';
        }else{
            console.log('No se han rellenada todos los campos');
        }
    });   
});
document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const message = document.getElementById('message');


    message.classList.add('hidden');
    message.innerText = '';


    if (!email.includes('@')) {
        message.classList.remove('hidden');
        message.innerText = 'Adres e-mail musi zawierać @';
        return;
    }


    const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.{8,})/;
    if (!passwordRegex.test(password)) {
        message.classList.remove('hidden');
        message.innerText = 'Hasło musi mieć minimum 8 znaków, jedną wielką literę i jeden znak specjalny';
        return;
    }


    message.classList.remove('hidden');
    message.style.color = 'green';
    message.innerText = 'Logowanie powiodło się!';
});
function loginWithGoogle() {
    window.location.href = "https://accounts.google.com/signin";
}

function loginWithApple() {
    window.location.href = "https://appleid.apple.com/";
}

function loginWithFacebook() {
    window.location.href = "https://www.facebook.com/login/";
}
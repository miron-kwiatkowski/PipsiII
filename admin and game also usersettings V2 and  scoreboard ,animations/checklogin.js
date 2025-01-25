document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const message = document.getElementById('message');

    // Resetowanie wiadomości
    message.classList.add('hidden');
    message.innerText = '';

    // Walidacja e-maila
    if (!email.includes('@')) {
        message.classList.remove('hidden');
        message.classList.add('error');
        message.innerText = 'Adres e-mail musi zawierać @';
        return;
    }

    // Walidacja hasła
    const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.{8,})/;
    if (!passwordRegex.test(password)) {
        message.classList.remove('hidden');
        message.classList.add('error');
        message.innerText = 'Hasło musi mieć minimum 8 znaków, jedną wielką literę i jeden znak specjalny';
        return;
    }

    // Wyślij request logowania
    const apiUrl = 'http://127.0.0.1:8080/api/users/login';
    const requestOptions = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password }),
    };

    fetch(apiUrl, requestOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error('Błąd logowania');
            }
            return response.json();
        })
        .then(data => {
            // Zapisanie tokenu i danych użytkownika w localStorage
            localStorage.setItem('access_token', data.token);
            localStorage.setItem('nickname', data.nickname);
            localStorage.setItem('pfpnum', data.pfpnum);

            // Sukces logowania
            message.classList.remove('hidden');
            message.classList.remove('error');
            message.classList.add('success');
            message.innerText = 'Logowanie powiodło się!';
        })
        .catch(error => {
            message.classList.remove('hidden');
            message.classList.add('error');
            message.innerText = 'Błąd: ' + error.message;
        });
});

function loginWithGoogle() {
    window.location.href = "http://127.0.0.1:8080/api/users/google/redirect";
}

function loginWithApple() {
    window.location.href = "https://appleid.apple.com/";
}

function loginWithFacebook() {
    window.location.href = "http://127.0.0.1:8080/api/users/facebook/redirect";
}

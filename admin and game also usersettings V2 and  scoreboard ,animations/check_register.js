document.getElementById('registrationForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Zapobiega przeładowaniu strony

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const message = document.getElementById('message');

    message.classList.add('hidden');
    message.innerText = '';

    // Walidacja e-maila
    if (!email.includes('@')) {
        message.classList.remove('hidden');
        message.innerText = 'Adres e-mail musi zawierać @';
        return;
    }

    // Walidacja hasła
    const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.{8,})/;
    if (!passwordRegex.test(password)) {
        message.classList.remove('hidden');
        message.innerText = 'Hasło musi mieć minimum 8 znaków, jedną wielką literę i jeden znak specjalny';
        return;
    }

    // Sprawdzenie zgodności haseł
    if (password !== confirmPassword) {
        message.classList.remove('hidden');
        message.innerText = 'Hasła nie są takie same!';
        return;
    }

    // Przygotowanie danych do requesta
    const apiUrl = 'http://127.0.0.1:8080/api/users/register';
    const data = {
        email: email,
        password: password,
        name: 'DefaultName' // Możesz dodać pole do formularza, jeśli wymagane
    };

    // Wysłanie requesta do backendu
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Błąd w przesyłaniu danych');
            }
            return response.json();
        })
        .then(data => {
            console.log('Rejestracja zakończona sukcesem:', data);
            message.classList.remove('hidden');
            message.innerText = 'Rejestracja zakończona sukcesem!';
            message.style.color = 'green';
        })
        .catch(error => {
            console.error('Błąd:', error);
            message.classList.remove('hidden');
            message.innerText = 'Rejestracja nie powiodła się. Spróbuj ponownie.';
            message.style.color = 'red';
        });
});


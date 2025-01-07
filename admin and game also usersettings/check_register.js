document.getElementById('registrationForm').addEventListener('submit', function(event) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const message = document.getElementById('message');


    message.classList.add('hidden');
    message.innerText = '';


    if (!email.includes('@')) {
        event.preventDefault();
        message.classList.remove('hidden');
        message.innerText = 'Adres e-mail musi zawierać @';
        return;
    }


    const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.{8,})/;
    if (!passwordRegex.test(password)) {
        event.preventDefault();
        message.classList.remove('hidden');
        message.innerText = 'Hasło musi mieć minimum 8 znaków, jedną wielką literę i jeden znak specjalny';
        return;
    }


    if (password !== confirmPassword) {
        event.preventDefault();
        message.classList.remove('hidden');
        message.innerText = 'Hasła nie są takie same!';
        return;
    }


    message.classList.add('hidden');
    message.innerText = '';
});



// ==========================================
// SHANU HAVEN - CUSTOMER AUTHENTICATION
// ==========================================

document.addEventListener('DOMContentLoaded', function () {

    // Show and hide passwords

    const toggleButtons = document.querySelectorAll(
        '.toggle-password'
    );

    toggleButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const input = document.getElementById(
                button.dataset.target
            );

            if (!input) return;

            if (input.type === 'password') {

                input.type = 'text';
                button.textContent = 'Hide';

            } else {

                input.type = 'password';
                button.textContent = 'Show';

            }

        });

    });

    // Registration form validation

    const form = document.getElementById('registerForm');

    if (!form) return;

    const password = document.getElementById('password');

    const confirmPassword = document.getElementById(
        'confirm_password'
    );

    const error = document.getElementById('formError');

    function validatePasswords() {

        if (password.value !== confirmPassword.value) {

            confirmPassword.setCustomValidity(
                'Passwords do not match.'
            );

            error.textContent = 'Passwords do not match.';

            return false;

        }

        confirmPassword.setCustomValidity('');
        error.textContent = '';

        return true;
    }

    password.addEventListener('input', validatePasswords);

    confirmPassword.addEventListener(
        'input',
        validatePasswords
    );

    form.addEventListener('submit', function (event) {

        if (password.value.length < 8) {

            event.preventDefault();

            error.textContent =
                'Password must contain at least 8 characters.';

            return;
        }

        if (!validatePasswords()) {

            event.preventDefault();
            confirmPassword.reportValidity();

        }

    });

});
// ==========================================
// SHANU HAVEN - CUSTOMER AUTHENTICATION
// ==========================================

document.addEventListener('DOMContentLoaded', function () {

    // 1. Show and Hide Password

    const toggleButtons = document.querySelectorAll(
        '.toggle-password'
    );

    toggleButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const input = document.getElementById(
                button.dataset.target
            );

            if (!input) return;

            if (input.type === 'password') {

                input.type = 'text';
                button.textContent = 'Hide';

            } else {

                input.type = 'password';
                button.textContent = 'Show';

            }

        });

    });


    // 2. Registration Form Validation

    const registerForm = document.getElementById(
        'registerForm'
    );

    if (registerForm) {

        const password = document.getElementById(
            'password'
        );

        const confirmPassword = document.getElementById(
            'confirm_password'
        );

        const error = document.getElementById(
            'formError'
        );

        function validatePasswords() {

            if (password.value !== confirmPassword.value) {

                confirmPassword.setCustomValidity(
                    'Passwords do not match.'
                );

                error.textContent =
                    'Passwords do not match.';

                return false;
            }

            confirmPassword.setCustomValidity('');
            error.textContent = '';

            return true;
        }

        password.addEventListener(
            'input',
            validatePasswords
        );

        confirmPassword.addEventListener(
            'input',
            validatePasswords
        );

        registerForm.addEventListener(
            'submit',
            function (event) {

                if (password.value.length < 8) {

                    event.preventDefault();

                    error.textContent =
                        'Password must contain at least 8 characters.';

                    return;
                }

                if (!validatePasswords()) {

                    event.preventDefault();

                    confirmPassword.reportValidity();

                }
            }
        );
    }


    // 3. Login Form Validation

    const loginForm = document.getElementById(
        'loginForm'
    );

    if (loginForm) {

        const email = document.getElementById('email');

        const password = document.getElementById(
            'password'
        );

        const error = document.getElementById(
            'loginError'
        );

        loginForm.addEventListener(
            'submit',
            function (event) {

                error.textContent = '';

                if (email.value.trim() === '') {

                    event.preventDefault();

                    error.textContent =
                        'Please enter your email address.';

                    return;
                }

                if (password.value === '') {

                    event.preventDefault();

                    error.textContent =
                        'Please enter your password.';

                }

            }
        );

        email.addEventListener('input', function () {

            error.textContent = '';

        });

        password.addEventListener('input', function () {

            error.textContent = '';

        });

    }

});
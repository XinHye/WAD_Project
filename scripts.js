function validatePassword() {
    const passwordInput = document.getElementById('password');
    const passwordError = document.getElementById('password-error');
    const password = passwordInput.value;

    // Regular expressions for password validation
    const lowerCaseLetters = /[a-z]/;
    const upperCaseLetters = /[A-Z]/;
    const numbers = /[0-9]/;
    const specialCharacters = /[!@#$%^&*()\-_=+{};:,<.>]/;

    // Validate password conditions
    const lengthValid = password.length >= 6 && password.length <= 8;
    const lowerCaseValid = lowerCaseLetters.test(password);
    const upperCaseValid = upperCaseLetters.test(password);
    const numberValid = numbers.test(password);
    const specialValid = specialCharacters.test(password);
    const noSpaces = !/\s/.test(password); // Add no-space validation

    // Update visual feedback
    document.getElementById('letter').classList.toggle("valid", lowerCaseValid);
    document.getElementById('letter').classList.toggle("invalid", !lowerCaseValid);
    
    document.getElementById('capital').classList.toggle("valid", upperCaseValid);
    document.getElementById('capital').classList.toggle("invalid", !upperCaseValid);
    
    document.getElementById('number').classList.toggle("valid", numberValid);
    document.getElementById('number').classList.toggle("invalid", !numberValid);
    
    document.getElementById('special').classList.toggle("valid", specialValid);
    document.getElementById('special').classList.toggle("invalid", !specialValid);
    
    document.getElementById('length').classList.toggle("valid", lengthValid);
    document.getElementById('length').classList.toggle("invalid", !lengthValid);

    // Validate no spaces
    document.getElementById('no-space').classList.toggle("valid", noSpaces);
    document.getElementById('no-space').classList.toggle("invalid", !noSpaces);

    // Return overall validation result
    return lowerCaseValid && upperCaseValid && numberValid && specialValid && lengthValid && noSpaces;
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                const passwordError = document.getElementById('password-error');
                passwordError.style.display = 'block';
                passwordError.textContent = 'Please meet all password requirements.';
                // Scroll to the error message
                passwordError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});

function myFunction() {
    var x = document.getElementById("password");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
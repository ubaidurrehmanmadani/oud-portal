document.querySelectorAll('.password-toggle').forEach(button => {
    const input = button.parentElement.querySelector('input');
    button.addEventListener('click', () => {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(show));
        const label = show ? document.body.dataset.hidePassword : document.body.dataset.showPassword;
        button.setAttribute('aria-label', label);
        button.textContent = label;
    });
});

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

document.querySelectorAll('[data-demo-access]').forEach(panel => {
    const toast = panel.querySelector('.demo-copy-toast');
    let timeout;
    panel.querySelectorAll('[data-copy-value]').forEach(button => {
        button.addEventListener('click', async () => {
            let copied = false;
            try {
                await navigator.clipboard.writeText(button.dataset.copyValue);
                copied = true;
            } catch {
                const field = document.createElement('textarea');
                field.value = button.dataset.copyValue;
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.append(field);
                field.select();
                try {
                    copied = document.execCommand('copy');
                } catch {
                    copied = false;
                } finally {
                    field.remove();
                    button.focus();
                }
            }
            clearTimeout(timeout);
            toast.textContent = copied ? panel.dataset.copySuccess : panel.dataset.copyFailure;
            toast.classList.add('is-visible');
            timeout = setTimeout(() => {
                toast.classList.remove('is-visible');
                toast.textContent = '';
            }, 2200);
        });
    });
});

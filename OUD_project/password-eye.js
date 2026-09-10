(function () {
  const eye = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.2 12s3.5-6 9.8-6 9.8 6 9.8 6-3.5 6-9.8 6-9.8-6-9.8-6Z"></path><circle cx="12" cy="12" r="2.6"></circle></svg>';
  document.querySelectorAll('.password-toggle').forEach((button) => {
    const input = button.parentElement.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;
    button.setAttribute('aria-label', 'Show password');
    button.innerHTML = eye;
    button.onclick = () => {
      const visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
      button.innerHTML = eye;
    };
  });
})();

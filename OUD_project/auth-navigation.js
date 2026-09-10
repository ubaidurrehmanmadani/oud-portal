(function () {
  const form = document.querySelector('[data-auth-form="forgot"]');
  if (!form) return;
  const link = document.createElement('a');
  link.className = 'back-link';
  link.href = 'user_login.html';
  link.innerHTML = '<span aria-hidden="true">←</span><span data-back-en="Back to login" data-back-ar="العودة لتسجيل الدخول"></span>';
  form.parentElement.insertBefore(link, form.parentElement.firstChild);
  function apply() { const arabic = localStorage.getItem('oud-lang') === 'ar'; link.querySelector('[data-back-en]').textContent = arabic ? link.querySelector('[data-back-en]').dataset.backAr : link.querySelector('[data-back-en]').dataset.backEn; link.querySelector('span').textContent = arabic ? '→' : '←'; }
  document.addEventListener('click', (event) => { if (event.target.closest('[data-lang]')) window.setTimeout(apply, 0); }); apply();
})();

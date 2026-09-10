(function () {
  const copy = {
    'Request access': ['طلب الوصول', 'اطلب دعوة آمنة إلى مساحة عمل عود المناسبة لمسؤولياتك.', 'دعوة الوصول', 'تتم مراجعة طلبك قبل إصدار صلاحية الدخول.', 'نظام واحد متصل', 'يستخدم الموظفون والمديرون والملاك والمديرون العامون بوابة موثوقة واحدة.'],
    'Forgot password': ['نسيت كلمة المرور', 'سنساعدك على العودة إلى مساحة عمل عود الصحيحة بأمان.', 'استعادة الوصول الآمن', '', '', ''],
    'Reset password': ['إعادة تعيين كلمة المرور', 'أنشئ كلمة مرور جديدة وتابع بأمان إلى بوابتك الخاصة بدورك.', 'طريقة مدروسة للعودة', '', '', '']
  };
  const panel = document.querySelector('.brand-panel'); if (!panel) return;
  const title = document.title.replace('OUD | ', '');
  const original = { eyebrow: panel.querySelector('.eyebrow')?.textContent, h1: panel.querySelector('h1')?.textContent, p: panel.querySelector('.brand-copy p:last-child')?.textContent, features: [...panel.querySelectorAll('.feature')].map((x) => [...x.children].map((y) => y.textContent)) };
  function apply(language) {
    if (language !== 'ar') { panel.querySelector('.eyebrow').textContent = original.eyebrow; panel.querySelector('h1').textContent = original.h1; panel.querySelector('.brand-copy p:last-child').textContent = original.p; panel.querySelectorAll('.feature').forEach((x, i) => x.innerHTML = `<strong>${original.features[i][0]}</strong><span>${original.features[i][1]}</span>`); return; }
    const data = copy[title]; if (!data) return;
    panel.querySelector('.eyebrow').textContent = 'منصة بوصلة عود للموظفين وملاك العقارات'; panel.querySelector('h1').textContent = data[2]; panel.querySelector('.brand-copy p:last-child').textContent = data[1];
    panel.querySelectorAll('.feature').forEach((x, i) => { if (data[3 + i * 2]) x.innerHTML = `<strong>${data[3 + i * 2]}</strong><span>${data[4 + i * 2]}</span>`; });
  }
  document.addEventListener('click', (event) => { const b = event.target.closest('[data-lang]'); if (b) setTimeout(() => apply(b.dataset.lang), 0); });
  apply(localStorage.getItem('oud-lang') === 'ar' ? 'ar' : 'en');
})();

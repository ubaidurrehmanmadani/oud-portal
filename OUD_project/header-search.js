(function () {
  const topbar = document.querySelector('.topbar');
  if (!topbar) return;
  const search = document.createElement('label');
  search.className = 'header-search';
  search.innerHTML = '<input type="search" placeholder="Search your workspace" aria-label="Search your workspace"><span aria-hidden="true">⌕</span>';
  topbar.insertBefore(search, topbar.querySelector('.topbar-actions'));
  document.addEventListener('click', (event) => { if (event.target.closest('[data-portal-lang]')) search.querySelector('input').placeholder = localStorage.getItem('oud-lang') === 'ar' ? 'ابحث في مساحة العمل' : 'Search your workspace'; });
  if (localStorage.getItem('oud-lang') === 'ar') search.querySelector('input').placeholder = 'ابحث في مساحة العمل';
})();

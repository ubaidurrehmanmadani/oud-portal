document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const collapsed = document.documentElement.classList.toggle('sidebar-is-collapsed');
        localStorage.setItem('oud-sidebar-collapsed', collapsed ? 'true' : 'false');
    });
});

document.querySelectorAll('.sidebar-group > summary').forEach((summary) => {
    summary.addEventListener('click', (event) => {
        const sidebarIsCollapsed = document.documentElement.classList.contains('sidebar-is-collapsed');
        const desktopSidebarIsActive = window.matchMedia('(min-width: 1024px)').matches;

        if (!sidebarIsCollapsed || !desktopSidebarIsActive) {
            return;
        }

        const group = summary.closest('.sidebar-group');
        const firstSubnavLink = group?.querySelector('.sidebar-subnav a[href]:not([href="#"])');

        if (!firstSubnavLink) {
            return;
        }

        event.preventDefault();
        window.location.href = firstSubnavLink.href;
    });
});

(() => {
    const shell = document.querySelector('[data-portal-shell]');
    const content = shell?.querySelector('[data-page-content]');
    const pageTitle = shell?.querySelector('[data-page-title]');
    const roleDialog = document.querySelector('#oud-role-dialog');
    let navigationController;
    let printDetails = [];

    window.addEventListener('beforeprint', () => {
        printDetails = Array.from(document.querySelectorAll('.reporting details:not(.picker)')).filter(details => !details.open);
        printDetails.forEach(details => details.open = true);
    });
    window.addEventListener('afterprint', () => {
        printDetails.forEach(details => details.open = false);
        printDetails = [];
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-role-open]')) roleDialog?.showModal();
        if (event.target.closest('[data-role-close]')) roleDialog?.close();
        if (event.target.closest('[data-print-report]')) window.print();
        const addRow = event.target.closest('[data-add-source-row]');
        if (addRow) {
            const section = addRow.closest('[data-source-table]');
            const rows = section.querySelector('tbody');
            const nextIndex = Math.max(-1, ...Array.from(rows.querySelectorAll('input')).map((input) => Number(input.name.match(/\[(\d+)\]/)?.[1] ?? -1))) + 1;
            if (rows.children.length >= 100) return;
            rows.insertAdjacentHTML('beforeend', section.querySelector('template').innerHTML.replaceAll('__INDEX__', String(nextIndex)));
            rows.lastElementChild.querySelector('input').focus();
        }
        const removeRow = event.target.closest('[data-remove-source-row]');
        if (removeRow) {
            const section = removeRow.closest('[data-source-table]');
            removeRow.closest('tr').remove();
            section.querySelector('[data-add-source-row]').focus();
        }
    });
    roleDialog?.addEventListener('click', (event) => {
        const bounds = roleDialog.getBoundingClientRect();
        if (event.target === roleDialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) roleDialog.close();
    });
    roleDialog?.addEventListener('close', () => document.querySelector('[data-role-open]')?.focus());

    const setupTrainingCards = () => {
        const cards = document.querySelectorAll('[data-training-video]');
        let modal = document.querySelector('.video-modal');
        if (!cards.length || modal?.dataset.bound === 'true') return;

        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'video-modal';
            modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML = '<div class="video-modal-backdrop" data-video-close></div><section class="video-modal-panel" role="dialog" aria-modal="true" aria-labelledby="video-modal-title"><button class="video-modal-close" type="button" data-video-close aria-label="Close video">&times;</button><p class="eyebrow">Oud Academy</p><h2 id="video-modal-title">Training video</h2><p class="video-modal-description"></p></section>';
            document.body.append(modal);
        }
        modal.dataset.bound = 'true';

        const close = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        };

        document.addEventListener('click', (event) => {
            const card = event.target.closest('[data-training-video]');
            if (!card) return;
            const container = card.closest('.module-card');
            modal.querySelector('#video-modal-title').textContent = container.querySelector('h3')?.textContent || 'Training video';
            modal.querySelector('.video-modal-description').textContent = container.querySelector('.muted')?.textContent || '';
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            modal.querySelector('.video-modal-close').focus();
        });

        modal.addEventListener('click', (event) => {
            if (event.target.closest('[data-video-close]')) close();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
    };

    const isInternalGet = (link) => {
        if (!link || !link.hasAttribute('href') || link.target || link.hasAttribute('download') || link.closest('form')) return false;
        const url = new URL(link.href, window.location.href);
        return url.origin === window.location.origin && url.protocol.startsWith('http') && link.pathname !== '/logout';
    };

    const updateShell = (html, url) => {
        const next = new DOMParser().parseFromString(html, 'text/html');
        const nextContent = next.querySelector('[data-page-content]');
        if (!nextContent) return false;
        if (next.documentElement.lang !== document.documentElement.lang || next.body.dataset.portalRole !== shell.dataset.portalRole) return false;
        shell.classList.toggle('reporting', next.body.classList.contains('reporting'));
        document.querySelectorAll('[data-report-style]').forEach((stylesheet) => stylesheet.media = next.body.classList.contains('reporting') ? 'all' : 'not all');
        content.className = nextContent.className;
        content.innerHTML = nextContent.innerHTML;
        if (pageTitle && next.querySelector('[data-page-title]')) pageTitle.textContent = next.querySelector('[data-page-title]').textContent;
        const nextLinks = Array.from(next.querySelectorAll('.sidebar .nav a'));
        shell.querySelectorAll('.sidebar .nav a').forEach((item) => {
            const matching = nextLinks.find((candidate) => candidate.href === item.href);
            item.classList.toggle('active', matching?.classList.contains('active') ?? false);
            if (matching?.hasAttribute('aria-current')) item.setAttribute('aria-current', matching.getAttribute('aria-current'));
            else item.removeAttribute('aria-current');
        });
        document.title = next.title;
        setupTrainingCards();
        return true;
    };

    const navigate = async (url, pushState = true) => {
        navigationController?.abort();
        const controller = new AbortController();
        navigationController = controller;
        shell.classList.add('is-navigating');
        try {
            const response = await fetch(url.href, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' } });
            if (!response.ok || response.redirected && new URL(response.url).pathname === '/login') throw new Error('navigation-fallback');
            if (!response.headers.get('content-type')?.includes('text/html')) throw new Error('navigation-fallback');
            const html = await response.text();
            if (controller.signal.aborted) return;
            if (!updateShell(html, url)) throw new Error('navigation-fallback');
            const destination = new URL(response.url);
            if (pushState) window.history.pushState({ portal: true }, '', destination.href);
            else if (destination.href !== url.href) window.history.replaceState({ portal: true }, '', destination.href);
            window.scrollTo({ top: 0, behavior: 'instant' });
        } catch (error) {
            if (error.name === 'AbortError') return;
            window.location.assign(url.href);
        } finally {
            if (navigationController === controller) shell.classList.remove('is-navigating');
        }
    };

    if (shell && content) {
        document.addEventListener('click', (event) => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const link = event.target.closest('a');
            if (!isInternalGet(link)) return;
            const url = new URL(link.href, window.location.href);
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
            if (url.pathname.includes('/download')) return;
            event.preventDefault();
            navigate(url);
        });
        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form.property-switcher');
            if (!form || form.method.toLowerCase() !== 'get') return;
            event.preventDefault();
            const url = new URL(form.action || window.location.href, window.location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            navigate(url);
        });
        window.addEventListener('popstate', () => navigate(new URL(window.location.href), false));
    }

    setupTrainingCards();
})();

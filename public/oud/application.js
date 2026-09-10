(() => {
    const shell = document.querySelector('[data-portal-shell]');
    const content = shell?.querySelector('[data-page-content]');
    const pageTitle = shell?.querySelector('[data-page-title]');

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
        if (!link || link.target || link.hasAttribute('download') || link.closest('form')) return false;
        const url = new URL(link.href, window.location.href);
        return url.origin === window.location.origin && url.protocol.startsWith('http') && link.pathname !== '/logout';
    };

    const updateShell = (html, url) => {
        const next = new DOMParser().parseFromString(html, 'text/html');
        const nextContent = next.querySelector('[data-page-content]');
        if (!nextContent) return false;
        content.className = nextContent.className;
        content.innerHTML = nextContent.innerHTML;
        if (pageTitle && next.querySelector('[data-page-title]')) pageTitle.textContent = next.querySelector('[data-page-title]').textContent;
        shell.querySelectorAll('.nav a').forEach((item) => item.classList.toggle('active', new URL(item.href, window.location.href).pathname === url.pathname && (!url.search || item.href === url.href)));
        document.title = next.title;
        setupTrainingCards();
        return true;
    };

    const navigate = async (url, pushState = true) => {
        shell.classList.add('is-navigating');
        try {
            const response = await fetch(url.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' } });
            if (!response.ok || response.redirected && new URL(response.url).pathname === '/login') throw new Error('navigation-fallback');
            const html = await response.text();
            if (!updateShell(html, url)) throw new Error('navigation-fallback');
            if (pushState) window.history.pushState({ portal: true }, '', url.href);
        } catch {
            window.location.assign(url.href);
        } finally {
            shell.classList.remove('is-navigating');
        }
    };

    if (shell && content) {
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!isInternalGet(link)) return;
            const url = new URL(link.href, window.location.href);
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

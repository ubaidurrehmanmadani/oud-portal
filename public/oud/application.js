(() => {
    const cards = document.querySelectorAll('[data-training-video]');
    if (!cards.length) return;

    const modal = document.createElement('div');
    modal.className = 'video-modal';
    modal.setAttribute('aria-hidden', 'true');
    modal.innerHTML = '<div class="video-modal-backdrop" data-video-close></div><section class="video-modal-panel" role="dialog" aria-modal="true" aria-labelledby="video-modal-title"><button class="video-modal-close" type="button" data-video-close aria-label="Close video">&times;</button><p class="eyebrow">Oud Academy</p><h2 id="video-modal-title">Training video</h2><p class="video-modal-description"></p></section>';
    document.body.append(modal);

    const close = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    cards.forEach((card) => card.addEventListener('click', () => {
        const container = card.closest('.module-card');
        modal.querySelector('#video-modal-title').textContent = container.querySelector('h3')?.textContent || 'Training video';
        modal.querySelector('.video-modal-description').textContent = container.querySelector('.muted')?.textContent || '';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        modal.querySelector('.video-modal-close').focus();
    }));

    modal.addEventListener('click', (event) => {
        if (event.target.closest('[data-video-close]')) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
})();

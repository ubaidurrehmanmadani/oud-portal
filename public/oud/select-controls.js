(() => {
    const $ = window.jQuery;
    if (!$?.fn.select2) return; // Native controls remain usable if assets fail to load.
    const arabic = document.documentElement.lang.startsWith('ar');
    const selector = 'select:not([data-native-select])';
    const init = (root = document) => {
        root.querySelectorAll(selector).forEach((select) => {
            if ($(select).hasClass('select2-hidden-accessible')) return;
            const label = select.labels?.[0];
            const labelText = label?.textContent.trim() || select.getAttribute('aria-label') || (arabic ? 'اختيار' : 'Select');
            const options = {
                width: '100%', dir: document.documentElement.dir || 'ltr',
                language: arabic ? 'ar' : 'en', closeOnSelect: !select.multiple,
                disabled: select.matches(':disabled'),
                dropdownParent: $(select.closest('dialog') || document.body),
            };
            if (select.multiple) options.placeholder = arabic ? 'اختر خيارًا أو أكثر' : 'Select one or more';
            $(select).select2(options);
            const instance = $(select).data('select2');
            const focusable = select.multiple ? instance.$selection.find('input') : instance.$selection;
            focusable.attr('aria-label', labelText);
            if (select.required) focusable.attr('aria-required', 'true');
            if (select.hasAttribute('aria-describedby')) focusable.attr('aria-describedby', select.getAttribute('aria-describedby'));
            $(select).on('select2:open.oud', () => {
                const search = instance.$dropdown.find('input.select2-search__field');
                search.attr('aria-label', arabic ? `بحث: ${labelText}` : `Search: ${labelText}`);
                search.trigger('focus');
            });
        });
    };
    const destroy = (root) => {
        root.querySelectorAll('select.select2-hidden-accessible').forEach((select) => {
            $(select).off('.oud').select2('destroy');
        });
    };
    document.addEventListener('click', (event) => {
        const label = event.target.closest('label[for]');
        const select = label && document.getElementById(label.htmlFor);
        if (select?.matches('select.select2-hidden-accessible') && !select.matches(':disabled')) {
            event.preventDefault();
            $(select).select2('open');
        }
    });
    document.addEventListener('invalid', (event) => {
        if (event.target.matches('select.select2-hidden-accessible')) {
            event.preventDefault();
            $(event.target).select2('open');
        }
    }, true);
    document.addEventListener('reset', (event) => {
        setTimeout(() => $(event.target).find('select.select2-hidden-accessible').trigger('change.select2'), 0);
    });
    window.OudSelects = { init, destroy };
    init();
})();

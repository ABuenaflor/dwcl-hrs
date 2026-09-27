import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { initCharts } from './charts';

const root = document.documentElement;

/* Browsers without cross-document View Transitions get a CSS entrance instead. */
if (!('onpagereveal' in window)) {
    root.classList.add('no-vt');
}

/* Progress bar while the next page loads (links and form posts). */
const startNavigation = () => root.classList.add('is-navigating');
const stopNavigation = () => root.classList.remove('is-navigating');

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (
        !link ||
        event.defaultPrevented ||
        event.button !== 0 ||
        event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
        link.target === '_blank' ||
        link.hasAttribute('download') ||
        link.origin !== location.origin ||
        (link.hash && link.pathname === location.pathname)
    ) {
        return;
    }
    startNavigation();
});

/* Forms: show a spinner on the submit button and block double submits. */
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (event.defaultPrevented || form.dataset.noLoading !== undefined) return;

    if (form.dataset.submitted) {
        event.preventDefault();
        return;
    }
    form.dataset.submitted = '1';

    const button = event.submitter ?? form.querySelector('[type=submit]');
    button?.setAttribute('data-loading', '');
    if (form.target !== '_blank' && !form.action.includes('.csv')) startNavigation();

    // Downloads never navigate away — re-enable the form after a moment.
    setTimeout(() => {
        delete form.dataset.submitted;
        button?.removeAttribute('data-loading');
        stopNavigation();
    }, form.action.includes('.csv') ? 1500 : 15000);
});

/* Coming back via the back/forward cache must not leave the bar running. */
window.addEventListener('pageshow', stopNavigation);

/* Global toast store used by the flash component and inline actions. */
Alpine.store('toasts', {
    items: [],
    push(message, tone = 'success') {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, tone });
        setTimeout(() => this.dismiss(id), 4500);
    },
    dismiss(id) {
        this.items = this.items.filter((t) => t.id !== id);
    },
});

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

initCharts();

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-dismiss-parent]');

    if (!trigger) {
        return;
    }

    trigger.parentElement?.remove();
});

const mobileNavToggle = document.querySelector('[data-mobile-nav-toggle]');
const mobileNav = document.querySelector('[data-mobile-nav]');
const mobileMediaQuery = window.matchMedia('(max-width: 639px)');

const syncMobileNavigation = () => {
    if (!mobileNav || !mobileNavToggle) {
        return;
    }

    if (mobileMediaQuery.matches) {
        const expanded = mobileNavToggle.getAttribute('aria-expanded') === 'true';
        mobileNav.hidden = !expanded;
    } else {
        mobileNav.hidden = false;
        mobileNavToggle.setAttribute('aria-expanded', 'false');
    }
};

if (mobileNav && mobileNavToggle) {
    mobileNavToggle.addEventListener('click', () => {
        const nextExpanded = mobileNavToggle.getAttribute('aria-expanded') !== 'true';

        mobileNavToggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
        syncMobileNavigation();
    });

    syncMobileNavigation();
    mobileMediaQuery.addEventListener('change', syncMobileNavigation);
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-dismiss-parent]');

    if (!trigger) {
        return;
    }

    trigger.parentElement?.remove();
});

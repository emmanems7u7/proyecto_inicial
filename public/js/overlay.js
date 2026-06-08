
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('overlay-spinner');

    document.addEventListener('submit', function(e) {

        const offcanvasEl = document.getElementById('offcanvasAcciones');

        if (offcanvasEl) {
            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
            offcanvas.hide();
        }

        if (overlay) {
            overlay.style.display = 'flex';
        }
    });
});

(function() {
    const overlay = document.getElementById('overlay-spinner');
    const originalFetch = window.fetch;
    let activeFetches = 0;

    window.fetch = async function(input, options = {}) {

        const showOverlay = options?.showOverlay !== false;

        if (showOverlay) {
            activeFetches++;
            overlay.style.display = 'flex';
        }

        try {
            const response = await originalFetch(input, options);
            return response;
        } catch (err) {
            console.error(err);
            throw err;
        } finally {

            if (showOverlay) {
                activeFetches--;
                if (activeFetches <= 0) {
                    overlay.style.display = 'none';
                    activeFetches = 0;
                }
            }
        }
    };
})();

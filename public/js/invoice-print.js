(() => {
    const fitInvoiceToA4 = () => {
        document.querySelectorAll('.invoice-print-page').forEach((page) => {
            const content = page.querySelector('.invoice-print-content');

            if (!content) {
                return;
            }

            page.style.removeProperty('--invoice-print-scale');

            const availableHeight = page.clientHeight - parseFloat(getComputedStyle(page).paddingTop) - parseFloat(getComputedStyle(page).paddingBottom);
            const availableWidth = page.clientWidth - parseFloat(getComputedStyle(page).paddingLeft) - parseFloat(getComputedStyle(page).paddingRight);
            const scale = Math.min(1, availableHeight / content.scrollHeight, availableWidth / content.scrollWidth);

            page.style.setProperty('--invoice-print-scale', Math.max(0.1, scale).toFixed(4));
        });
    };

    const fitAfterFontsLoad = () => Promise.resolve(document.fonts?.ready).finally(fitInvoiceToA4);

    window.addEventListener('beforeprint', fitInvoiceToA4);
    window.addEventListener('resize', fitInvoiceToA4);
    window.addEventListener('load', fitAfterFontsLoad);
    window.matchMedia('print').addEventListener('change', (event) => {
        if (event.matches) {
            fitInvoiceToA4();
        }
    });
})();

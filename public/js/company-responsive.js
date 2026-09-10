document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.table-responsive:not(.invoice-items-table-wrapper) table').forEach((table) => {
        const labels = [...table.querySelectorAll('thead th')].map((cell) => cell.textContent.trim());
        if (!labels.length) return;

        table.classList.add('mobile-card-table');
        table.querySelectorAll('tbody tr').forEach((row) => {
            [...row.children].forEach((cell, index) => {
                if (!cell.hasAttribute('data-label')) cell.dataset.label = labels[index] || '';
            });
        });
    });

    document.querySelectorAll('[data-copy-text]').forEach((button) => {
        button.addEventListener('click', async () => {
            const feedback = button.closest('.share-link-panel')?.querySelector('[data-copy-feedback]');
            try {
                await navigator.clipboard.writeText(button.dataset.copyText || '');
                button.textContent = '✓ تم النسخ';
                if (feedback) feedback.textContent = 'الرابط جاهز للمشاركة.';
            } catch (_) {
                if (feedback) feedback.textContent = 'تعذر النسخ تلقائياً؛ اضغط مطولاً على الرابط لنسخه.';
            }
        });
    });
});

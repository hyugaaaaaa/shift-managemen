
// ローディング表示
function showLoading(form) {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        const spinner = btn.querySelector('.spinner-border');
        if (spinner) spinner.classList.remove('d-none');
        btn.disabled = true;
    }
}

// Toast表示関数
function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container');
    if (!container) return;

    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'error' ? 'text-bg-danger' : 'text-bg-success';
    const icon = type === 'error' ? 'bi-exclamation-circle' : 'bi-check-circle';

    const html = `
    <div id="${toastId}" class="toast align-items-center ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <i class="bi ${icon} me-2"></i>${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();

    // 消えたらDOMから削除
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}




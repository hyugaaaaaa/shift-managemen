// ローディング表示
function showLoading(form) {
    var btn = form.querySelector('button[type="submit"]');
    if (btn) {
        var spinner = btn.querySelector('.spinner-border');
        if (spinner)
            spinner.classList.remove('d-none');
        btn.disabled = true;
    }
}
function showToast(message, type) {
    if (type === void 0) { type = 'success'; }
    var container = document.querySelector('.toast-container');
    if (!container)
        return;
    var toastId = 'toast-' + Date.now();
    var bgClass = type === 'error' ? 'text-bg-danger' : 'text-bg-success';
    var icon = type === 'error' ? 'bi-exclamation-circle' : 'bi-check-circle';
    var html = "\n    <div id=\"".concat(toastId, "\" class=\"toast align-items-center ").concat(bgClass, " border-0\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\">\n      <div class=\"d-flex\">\n        <div class=\"toast-body\">\n          <i class=\"bi ").concat(icon, " me-2\"></i>").concat(message, "\n        </div>\n        <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>\n      </div>\n    </div>\n    ");
    container.insertAdjacentHTML('beforeend', html);
    var toastEl = document.getElementById(toastId);
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        // 消えたらDOMから削除
        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }
}

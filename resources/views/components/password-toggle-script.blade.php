<script>
// Initialize once
if (!window.passwordToggleInit) {
    window.passwordToggleInit = true;

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-password-toggle]');
        if (!btn) return;
        const target = btn.dataset.target;
        if (!target) return;
        const input = document.querySelector(target);
        if (!input) return;

        const eye = btn.querySelector('.eye-icon');
        const eyeOff = btn.querySelector('.eye-off-icon');

        if (input.type === 'password') {
            input.type = 'text';
            if (eye) eye.classList.add('hidden');
            if (eyeOff) eyeOff.classList.remove('hidden');
        } else {
            input.type = 'password';
            if (eye) eye.classList.remove('hidden');
            if (eyeOff) eyeOff.classList.add('hidden');
        }
    });
}
</script>
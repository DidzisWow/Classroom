document.addEventListener('DOMContentLoaded', () => {

    // ── Theme Toggle ──────────────────────────────────────────
    const html = document.documentElement;
    const toggleBtn = document.getElementById('themeToggle');

    const savedTheme = localStorage.getItem('cn_theme') || 'dark';
    html.setAttribute('data-theme', savedTheme);

    if (toggleBtn) {
        updateThemeIcon(savedTheme);

        toggleBtn.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('cn_theme', next);
            updateThemeIcon(next);
        });
    }

    function updateThemeIcon(theme) {
        if (!toggleBtn) return;
        toggleBtn.querySelector('.theme-icon').textContent = theme === 'dark' ? '○' : '◐';
        toggleBtn.title = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
    }

    // ── User dropdown hover with delay ────────────────────────
    const userMenu = document.getElementById('userMenu');
    const userDropdown = userMenu?.querySelector('.user-dropdown');
    let closeTimer;

    if (userMenu && userDropdown) {
        userMenu.addEventListener('mouseenter', () => {
            clearTimeout(closeTimer);
            userDropdown.classList.add('open');
        });

        userMenu.addEventListener('mouseleave', () => {
            closeTimer = setTimeout(() => {
                userDropdown.classList.remove('open');
            }, 300);
        });

        userDropdown.addEventListener('mouseenter', () => {
            clearTimeout(closeTimer);
        });

        userDropdown.addEventListener('mouseleave', () => {
            closeTimer = setTimeout(() => {
                userDropdown.classList.remove('open');
            }, 300);
        });
    }

    // ── Auto-dismiss alerts ───────────────────────────────────
    document.querySelectorAll('.alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });

    // ── Modal: close on backdrop click ────────────────────────
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) backdrop.classList.remove('open');
        });
    });

    // ── Close modal on Escape ─────────────────────────────────
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
        }
    });

    // ── Avatar preview ────────────────────────────────────────
    const avatarInput = document.getElementById('avatar');
    if (avatarInput) {
        avatarInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => {
                const preview = document.querySelector('.profile-avatar, .profile-avatar-placeholder');
                if (preview) {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.className = 'profile-avatar';
                    preview.replaceWith(img);
                }
            };
            reader.readAsDataURL(file);
        });
    }

});
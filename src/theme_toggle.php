<?php
/**
 * Fragmento reutilizable: botón toggle modo claro/oscuro + script de control.
 * Incluir JUSTO ANTES de </body> en cada página protegida.
 */
?>
<!-- ─── Botón de Modo (fijo, esquina superior derecha) ─────────────── -->
<button id="rpModeToggle" title="Cambiar modo" style="position:fixed;top:14px;right:14px;z-index:9999;width:40px;height:40px;
           border-radius:10px;border:1.5px solid var(--color-card-border);
           background:var(--color-card);color:var(--color-text-muted);
           font-size:17px;cursor:pointer;display:flex;align-items:center;
           justify-content:center;transition:all 0.25s;box-shadow:0 2px 8px var(--toggle-shadow,rgba(0,0,0,.4));">
    <span id="rpModeIcon">☀️</span>
</button>
<script>
    (function () {
        var html = document.documentElement;
        var btn = document.getElementById('rpModeToggle');
        var icon = document.getElementById('rpModeIcon');

        function apply(theme) {
            html.className = theme === 'light' ? 'light-mode' : 'dark-mode';
            icon.textContent = theme === 'light' ? '🌙' : '☀️';
            localStorage.setItem('rp_theme', theme);
        }

        // Aplicar al cargar
        apply(localStorage.getItem('rp_theme') || 'dark');

        btn.addEventListener('click', function () {
            apply(html.classList.contains('light-mode') ? 'dark' : 'light');
        });

        btn.addEventListener('mouseenter', function () {
            btn.style.borderColor = 'var(--color-primary)';
            btn.style.color = 'var(--color-primary)';
        });
        btn.addEventListener('mouseleave', function () {
            btn.style.borderColor = 'var(--color-card-border)';
            btn.style.color = 'var(--color-text-muted)';
        });
    })();
</script>
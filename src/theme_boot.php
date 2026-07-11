<?php
/**
 * Fragmento reutilizable: arranca el tema (oscuro por defecto) antes del render.
 * Incluir INMEDIATAMENTE ANTES de </head> en cada página.
 */
?>
<script>
    (function () {
        var s = localStorage.getItem('rp_theme') || 'dark';
        document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode';
    })();
</script>
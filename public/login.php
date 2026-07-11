<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = isset($_POST['correo']) ? strtoupper(trim($_POST['correo'])) : '';
    $contrasena = $_POST['contrasena'] ?? '';
    try {
        $pdo = DB::getPdo(true);
        $stmt = $pdo->prepare('SELECT usuarioID, nombre, correo_electronico, contrasena, rolID FROM usuarios WHERE correo_electronico = ? LIMIT 1');
        $stmt->execute([$correo]);
        $user = $stmt->fetch();
        if ($user && password_verify($contrasena, $user['contrasena'])) {
            // Autenticado
            $_SESSION['usuarioID'] = $user['usuarioID'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rolID'] = $user['rolID'];
            header('Location: admin.php');
            exit;
        } else {
            $error = 'CREDENCIALES INVÁLIDAS';
        }
    } catch (Exception $e) {
        $error = 'ERROR DE CONEXIÓN';
    }
}
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>LOGIN - RICO POLLO</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFE66D', /* amarillo */
                        accent: '#E23E1A',  /* rojo */
                        dark: '#09090c'
                    }
                }
            }
        }
    </script>
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="css/custom.css">
    <!-- FontAwesome for beautiful icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen flex items-center justify-center p-4"
    style="background-color:var(--color-bg); color:var(--color-text);">
    <!-- Botón toggle modo (fijo esquina sup-der) -->
    <button id="modeToggle" class="mode-toggle-btn" style="position:fixed;top:16px;right:16px;z-index:50;"
        title="Cambiar modo">
        <span id="modeIcon">☀️</span>
    </button>
    <div class="w-full max-w-md mx-auto">
        <!-- Logo Header Container -->
        <div class="flex flex-col items-center mb-8">
            <div
                class="w-48 h-32 mb-2 hover:scale-105 transition-transform duration-300 bg-black/90 rounded-2xl p-4 border border-white/10 flex items-center justify-center shadow-lg">
                <?php if (file_exists(__DIR__ . '/../assets/logo.png')): ?>
                    <img src="../assets/logo.png" alt="LOGO" class="w-full h-full object-contain">
                <?php else: ?>
                    <img src="../assets/logo.svg" alt="LOGO" class="w-full h-full object-contain">
                <?php endif; ?>
            </div>
            <p class="text-[10px] uppercase tracking-widest text-[#FFE66D]/70 font-bold mt-2">SISTEMA ADMINISTRATIVO</p>
        </div>

        <!-- Glassmorphism Login Card -->
        <div class="glass-card p-8">
            <h2
                class="text-xl font-bold text-center mb-6 tracking-wide text-white uppercase border-b border-white/10 pb-4">
                <i class="fa-solid fa-lock text-[#FFE66D] mr-2"></i><?php echo strtoupper('INICIAR SESIÓN'); ?>
            </h2>

            <?php if ($error): ?>
                <div
                    class="mb-5 p-3.5 bg-red-950/40 border border-red-500/50 rounded-xl text-red-200 text-sm font-semibold flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-red-500 text-base"></i>
                    <span><?php echo strtoupper($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-5">
                <div>
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('CORREO ELECTRÓNICO'); ?></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3.5 text-gray-500">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" name="correo" required class="form-input pl-10"
                            value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>" />
                    </div>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('CONTRASEÑA'); ?></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3.5 text-gray-500">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input id="contrasena" type="password" name="contrasena" required class="form-input pl-10 pr-10"
                            placeholder="••••••••" />
                        <button type="button" id="togglePass"
                            class="absolute right-3 top-3.5 text-gray-400 hover:text-white transition-colors"
                            aria-label="Mostrar contraseña">
                            <i id="eyeIcon" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <a href="menu.php"
                        class="btn-outline px-6 py-2.5 w-full text-center text-sm flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-arrow-left"></i><?php echo strtoupper('ATRÁS'); ?>
                    </a>
                    <button type="submit"
                        class="btn-primary px-6 py-2.5 w-full justify-center text-sm flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-right-to-bracket"></i><?php echo strtoupper('INICIAR SESIÓN'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('togglePass');
        const pass = document.getElementById('contrasena');
        const eyeIcon = document.getElementById('eyeIcon');
        if (toggle) {
            toggle.addEventListener('click', () => {
                if (pass.type === 'password') {
                    pass.type = 'text';
                    eyeIcon.classList.remove('fa-regular', 'fa-eye');
                    eyeIcon.classList.add('fa-solid', 'fa-eye-slash');
                } else {
                    pass.type = 'password';
                    eyeIcon.classList.remove('fa-solid', 'fa-eye-slash');
                    eyeIcon.classList.add('fa-regular', 'fa-eye');
                }
            });
        }
    </script>
    <script>
        // ─── TEMA OSCURO / CLARO ───────────────────────────────────────
        const html2 = document.documentElement;
        const modeBtn2 = document.getElementById('modeToggle');
        const modeIcon2 = document.getElementById('modeIcon');
        function applyTheme2(theme) {
            if (theme === 'light') {
                html2.className = 'light-mode';
                modeIcon2.textContent = '🌙';
            } else {
                html2.className = 'dark-mode';
                modeIcon2.textContent = '☀️';
            }
            localStorage.setItem('rp_theme', theme);
        }
        applyTheme2(localStorage.getItem('rp_theme') || 'dark');
        modeBtn2.addEventListener('click', function () {
            applyTheme2(html2.classList.contains('light-mode') ? 'dark' : 'light');
        });
    </script>
</body>

</html>
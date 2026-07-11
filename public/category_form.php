<?php
session_start();
require_once __DIR__ . '/../src/db.php';
if (!isset($_SESSION['usuarioID'])) {
    header('Location: login.php');
    exit;
}
$pdo = DB::getPdo(true);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = '';
$cat = ['nombre' => '', 'slug' => '', 'descripcion' => '', 'activo' => 1];

if ($id) {
    $s = $pdo->prepare('SELECT * FROM categorias WHERE categoriaID = ?');
    $s->execute([$id]);
    $cat = $s->fetch() ?: $cat;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $descripcion = strtoupper(trim($_POST['descripcion'] ?? ''));
    $activo = isset($_POST['activo']) ? 1 : 0;
    // slug auto-generado desde nombre
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre));
    $slug = trim($slug, '-');

    if (!$nombre) {
        $error = 'EL NOMBRE DE LA CATEGORÍA ES OBLIGATORIO.';
    } else {
        try {
            if ($id) {
                $pdo->prepare('UPDATE categorias SET nombre=?, slug=?, descripcion=?, activo=?, fecha_modificacion=NOW() WHERE categoriaID=?')
                    ->execute([$nombre, $slug, $descripcion, $activo, $id]);
            } else {
                $pdo->prepare('INSERT INTO categorias (nombre, slug, descripcion, activo, fecha_creacion) VALUES (?,?,?,?,NOW())')
                    ->execute([$nombre, $slug, $descripcion, $activo]);
            }
            header('Location: categories.php');
            exit;
        } catch (Exception $e) {
            $error = 'ERROR AL GUARDAR: ' . strtoupper($e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>
        <?php echo $id ? 'EDITAR' : 'NUEVA'; ?> CATEGORÍA - RICO POLLO
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="background-color:var(--color-bg);color:var(--color-text);"
    class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full mx-auto">

        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <div class="w-36 h-20">
                <img src="../assets/logo.svg" alt="LOGO" class="w-full h-full object-contain">
            </div>
        </div>

        <!-- Form Card -->
        <div class="glass-card p-6 md:p-8">
            <h2 class="text-lg font-extrabold tracking-wide uppercase pb-3 border-b mb-6"
                style="border-color:var(--color-card-border)">
                <i class="fa-solid fa-tags mr-2" style="color:#FFE66D"></i>
                <?php echo $id ? 'EDITAR CATEGORÍA' : 'NUEVA CATEGORÍA'; ?>
            </h2>

            <?php if ($error): ?>
                <div class="mb-5 p-3.5 rounded-xl text-sm font-semibold flex items-center gap-3"
                    style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.4);color:#fca5a5">
                    <i class="fa-solid fa-circle-exclamation" style="color:#ef4444"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                        style="color:var(--color-text-muted)">NOMBRE DE LA CATEGORÍA *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3.5" style="color:var(--color-text-subtle)"><i
                                class="fa-solid fa-tag"></i></span>
                        <input name="nombre" required
                            value="<?php echo htmlspecialchars(strtoupper($cat['nombre'])); ?>"
                            oninput="this.value=this.value.toUpperCase()" class="form-input pl-10"
                            placeholder="EJ: POLLOS AL CARBÓN" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                        style="color:var(--color-text-muted)">DESCRIPCIÓN (OPCIONAL)</label>
                    <textarea name="descripcion" rows="2" oninput="this.value=this.value.toUpperCase()"
                        class="form-input"
                        placeholder="BREVE DESCRIPCIÓN DE ESTA CATEGORÍA..."><?php echo htmlspecialchars(strtoupper($cat['descripcion'])); ?></textarea>
                </div>

                <!-- Toggle activo -->
                <div class="pt-1 flex items-center">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="activo" value="1" class="sr-only peer" <?php echo $cat['activo'] ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-slate-200 dark:bg-gray-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 after:bg-gray-400 peer-checked:after:bg-white relative"
                            style="border:1px solid var(--color-input-border)"></div>
                        <span class="ml-3 text-xs font-bold uppercase tracking-wider"
                            style="color:var(--color-text-muted)">CATEGORÍA ACTIVA</span>
                    </label>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-between pt-6 border-t"
                    style="border-color:var(--color-card-border)">
                    <a href="categories.php" class="btn-outline text-xs">
                        <i class="fa-solid fa-xmark mr-1"></i>CANCELAR
                    </a>
                    <button type="submit" class="btn-primary text-xs">
                        <i class="fa-solid fa-floppy-disk mr-1"></i>
                        <?php echo $id ? 'GUARDAR CAMBIOS' : 'CREAR CATEGORÍA'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php require_once __DIR__ . '/../src/theme_toggle.php'; ?>
</body>

</html>
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$esAdmin = ($_SESSION['user_role'] ?? '') === 'Administrador';

if (!$esAdmin && !empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/permissions.php';
    loadUserPermissions($pdo, $_SESSION['user_id']);
}

function _moduloBloqueado($mod) {
    global $esAdmin;
    if ($esAdmin) return false;
    if (isset($_SESSION['user_permissions'][$mod]['bloqueo'])) {
        return !empty($_SESSION['user_permissions'][$mod]['bloqueo']);
    }
    $modulosBloqueables = ['servidores', 'monitoreo', 'bitacora', 'payment_gateways', 'usuarios', 'contrasenas'];
    return in_array($mod, $modulosBloqueables);
}
?>
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Credenciales</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Nav Item - Internet -->
            <li class="nav-item <?= ($current_page == 'internet.php') ? 'active' : '' ?>">
                <a class="nav-link" href="internet.php">
                    <i class="fas fa-fw fa-wifi"></i>
                    <span>Internet</span></a>
            </li>

            <?php if (!_moduloBloqueado('monitoreo')): ?>
            <!-- Nav Item - Monitoreo -->
            <li class="nav-item <?= ($current_page == 'monitoreo.php') ? 'active' : '' ?>">
                <a class="nav-link" href="monitoreo.php">
                    <i class="fas fa-fw fa-heartbeat"></i>
                    <span>Monitoreo</span></a>
            </li>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Gestión
            </div>

            <?php if (!_moduloBloqueado('servidores')): ?>
            <!-- Nav Item - Credenciales -->
            <li class="nav-item <?= ($current_page == 'credenciales.php') ? 'active' : '' ?>">
                <a class="nav-link" href="credenciales.php">
                    <i class="fas fa-fw fa-key"></i>
                    <span>Credenciales</span></a>
            </li>
            <?php endif; ?>

            <?php if (!_moduloBloqueado('contrasenas')): ?>
            <!-- Nav Item - Contraseñas -->
            <li class="nav-item <?= ($current_page == 'contrasenas.php') ? 'active' : '' ?>">
                <a class="nav-link" href="contrasenas.php">
                    <i class="fas fa-fw fa-lock"></i>
                    <span>Contraseñas</span></a>
            </li>
            <?php endif; ?>

            <?php if (!_moduloBloqueado('usuarios')): ?>
            <!-- Nav Item - Usuarios -->
            <li class="nav-item <?= ($current_page == 'usuarios.php') ? 'active' : '' ?>">
                <a class="nav-link" href="usuarios.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Usuarios</span></a>
            </li>
            <?php endif; ?>

            <!-- Nav Item - Páginas Web -->
            <li class="nav-item <?= ($current_page == 'paginas_web.php') ? 'active' : '' ?>">
                <a class="nav-link" href="paginas_web.php">
                    <i class="fas fa-fw fa-globe"></i>
                    <span>Páginas Web</span></a>
            </li>

            <!-- Nav Item - AnyDesk -->
            <li class="nav-item <?= ($current_page == 'anydesk.php') ? 'active' : '' ?>">
                <a class="nav-link" href="anydesk.php">
                    <i class="fas fa-fw fa-desktop"></i>
                    <span>AnyDesk</span></a>
            </li>

            <!-- Nav Item - Documentos -->
            <li class="nav-item <?= ($current_page == 'documentos.php') ? 'active' : '' ?>">
                <a class="nav-link" href="documentos.php">
                    <i class="fas fa-fw fa-folder-open"></i>
                    <span>Documentos</span></a>
            </li>

            <?php if (!_moduloBloqueado('payment_gateways')): ?>
            <!-- Nav Item - Pasarelas de Pago -->
            <li class="nav-item <?= ($current_page == 'payment_gateways.php') ? 'active' : '' ?>">
                <a class="nav-link" href="payment_gateways.php">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span>Pasarelas de Pago</span></a>
            </li>
            <?php endif; ?>

            <!-- Nav Item - Sistemas Internos -->
            <li class="nav-item <?= ($current_page == 'internal_systems.php') ? 'active' : '' ?>">
                <a class="nav-link" href="internal_systems.php">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>Sistemas Internos</span></a>
            </li>

            <?php if (!_moduloBloqueado('bitacora')): ?>
            <!-- Nav Item - Bitacora -->
            <li class="nav-item <?= ($current_page == 'bitacora.php') ? 'active' : '' ?>">
                <a class="nav-link" href="bitacora.php">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Bitácora</span></a>
            </li>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

<?php

function loadUserPermissions($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT modulo, ver, crear, editar, eliminar, bloqueo FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $perms = [];
    while ($row = $stmt->fetch()) {
        $perms[$row['modulo']] = [
            'ver'     => (bool) $row['ver'],
            'crear'   => (bool) $row['crear'],
            'editar'  => (bool) $row['editar'],
            'eliminar'=> (bool) $row['eliminar'],
            'bloqueo' => (bool) $row['bloqueo'],
        ];
    }
    $_SESSION['user_permissions'] = $perms;
}

function userCan($modulo, $accion) {
    if ($_SESSION['user_role'] === 'Administrador') {
        return true;
    }
    if (!empty($_SESSION['user_permissions'][$modulo]['bloqueo'])) {
        return false;
    }
    return isset($_SESSION['user_permissions'][$modulo][$accion])
        && $_SESSION['user_permissions'][$modulo][$accion];
}

function getUserPermissionsFromDb($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT modulo, ver, crear, editar, eliminar, bloqueo FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $perms = [];
    while ($row = $stmt->fetch()) {
        $perms[$row['modulo']] = [
            'ver'     => (bool) $row['ver'],
            'crear'   => (bool) $row['crear'],
            'editar'  => (bool) $row['editar'],
            'eliminar'=> (bool) $row['eliminar'],
            'bloqueo' => (bool) $row['bloqueo'],
        ];
    }
    return $perms;
}

function getAllModulos() {
    return ['servidores', 'credenciales', 'contrasenas', 'anydesk', 'documentos', 'internet', 'payment_gateways', 'internal_systems', 'usuarios', 'monitoreo', 'bitacora', 'paginas_web'];
}

function getModuloLabel($modulo) {
    $labels = [
        'servidores'       => 'Servidores',
        'credenciales'     => 'Credenciales',
        'contrasenas'      => 'Contraseñas',
        'anydesk'          => 'AnyDesk',
        'documentos'       => 'Documentos',
        'internet'         => 'Internet',
        'payment_gateways' => 'Pasarelas de Pago',
        'internal_systems' => 'Sistemas Internos',
        'usuarios'         => 'Usuarios',
        'monitoreo'        => 'Monitoreo',
        'bitacora'         => 'Bitácora',
        'paginas_web'      => 'Páginas Web',
    ];
    return $labels[$modulo] ?? $modulo;
}

function userIsBlockedForModulo($modulo) {
    if ($_SESSION['user_role'] === 'Administrador') {
        return false;
    }
    return !empty($_SESSION['user_permissions'][$modulo]['bloqueo']);
}

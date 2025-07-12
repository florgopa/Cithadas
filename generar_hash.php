<?php
$usuarios = [
    ['nombre' => 'Dueño Salon Glamour', 'email' => 'salonglamour@gmail.com', 'clave' => 'negocio456'],
    ['nombre' => 'Spa Brillitos', 'email' => 'spabrillitos@gmail.com', 'clave' => 'negocio789'],
    ['nombre' => 'Barberia PuraVida', 'email' => 'puravidax@gmail.com', 'clave' => 'negocioABC'],
    ['nombre' => 'Marian Brook', 'email' => 'marianbrook@gmail.com', 'clave' => 'cliente123'],
    ['nombre' => 'Gladys Russell', 'email' => 'gladysrussell@gmail.com', 'clave' => 'cliente456'],
];

foreach ($usuarios as $usuario) {
    $hash = password_hash($usuario['clave'], PASSWORD_DEFAULT);
    echo "<strong>{$usuario['nombre']}</strong><br>";
    echo "Email: {$usuario['email']}<br>";
    echo "Clave: {$usuario['clave']}<br>";
    echo "Hash: <code>{$hash}</code><br><br>";
}
?>

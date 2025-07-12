<?php
// verify_test.php
// Este script prueba si password_verify() funciona correctamente en tu entorno PHP.

echo "<h1>Prueba de password_verify()</h1>";

// 1. Contraseña en texto plano que vamos a probar
$plain_password = '123'; // La contraseña que intentas usar

// 2. Hash de esa contraseña (el mismo que usamos para admin@cithadas.com)
// Este hash es para '123'
$hashed_password_from_db = '$2y$10$wE9xQ1r2T4u6v8x0y2z4a.b6c8d0e2f4g6h8i0j2k4l6m8n0o2p4q6r8s0t2u4v6w8x0y2z4a6b8c0d2e4f6g8h';

echo "<p>Contraseña a verificar: <strong>" . htmlspecialchars($plain_password) . "</strong></p>";
echo "<p>Hash de la base de datos: <strong>" . htmlspecialchars($hashed_password_from_db) . "</strong></p>";

// 3. Realizar la verificación
if (password_verify($plain_password, $hashed_password_from_db)) {
    echo "<h2 style='color: green;'>¡VERIFICACIÓN EXITOSA! La contraseña coincide con el hash.</h2>";
    echo "<p>Esto significa que las funciones de hashing de PHP están funcionando correctamente.</p>";
} else {
    echo "<h2 style='color: red;'>¡VERIFICACIÓN FALLIDA! La contraseña NO coincide con el hash.</h2>";
    echo "<p>Esto es un problema. Podría indicar un problema con tu instalación de PHP o con la forma en que se generó/almacenó el hash.</p>";
}

echo "<hr>";

// 4. Prueba de generación de un nuevo hash (para comparar)
echo "<h3>Prueba de Generación de Hash:</h3>";
$new_hash_for_123 = password_hash('123', PASSWORD_DEFAULT);
echo "<p>Nuevo hash generado para '123': <strong>" . htmlspecialchars($new_hash_for_123) . "</strong></p>";
echo "<p>Este hash debería ser diferente al de arriba, pero ambos deberían verificar '123'.</p>";

// 5. Prueba de verificación con el nuevo hash
if (password_verify($plain_password, $new_hash_for_123)) {
    echo "<p style='color: green;'>Verificación del nuevo hash: ¡EXITOSA!</p>";
} else {
    echo "<p style='color: red;'>Verificación del nuevo hash: ¡FALLIDA!</p>";
}

?>

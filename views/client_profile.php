<?php
require_once 'includes/db.php';


// Proteger acceso directo si no hay sesión activa
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['user_role'])) !== 'cliente') {
    header('Location: index.php?page=login');
    exit;
}

$id_usuario = $_SESSION['user_id'];

$sql = "SELECT nombre, email FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
?>

<section class="profile-container">
    <div class="profile-card">
        <h2 class="profile-title">Editar Perfil</h2>
        <p class="profile-subtitle">Actualizá tu información personal.</p>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert success-message">✅ Tus datos se actualizaron correctamente.</div>
        <?php endif; ?>

        <form action="backend/auth/update_profile.php" method="POST" class="profile-form">
            <div class="form-group">
                <label for="nombre">Nombre <span class="required-field">*</span></label>
                <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico <span class="required-field">*</span></label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            </div>
<style>
body {
  background-color: yellow !important;
}
</style>

            <button type="submit" class="btn-primary">Guardar Cambios</button>
        </form>
    </div>
</section>

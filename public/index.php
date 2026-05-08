<?php
session_start();

require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../routes/Routes.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/NoteController.php';
require_once __DIR__ . '/../controllers/LabelController.php';
require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../controllers/ShareController.php';

//define('BASE_URL', '');

function redirect(string $path): void {
    header('Location: ' . BASE_URL . $path);
    exit();
}

function auth(): void {
    if (!isset($_SESSION['user_id'])) {
        redirect('/login');
    }
}

function guest(): void {
    if (isset($_SESSION['user_id'])) {
        redirect('/notes');
    }
}

$router = new Router();

// ================= AUTH ROUTES =================
$router->get('/login', function() {
    guest();
    require __DIR__ . '/../views/auth/login.php';
});

$router->get('/register', function() {
    guest();
    require __DIR__ . '/../views/auth/register.php';
});

$router->get('/forgot-password', function() {
    guest();
    require __DIR__ . '/../views/auth/forgot-password.php';
});

$router->get('/reset-password', function() {
    guest();
    require __DIR__ . '/../views/auth/reset-password.php';
});

$router->post('/register', function() {
    (new AuthController())->register();
});

$router->post('/login', function() {
    (new AuthController())->login();
});

$router->get('/logout', function() {
    (new AuthController())->logout();
});

$router->get('/activate', function() {
    (new AuthController())->activate();
});

$router->post('/forgot-password', function() {
    (new AuthController())->forgotPassword();
});

$router->post('/verify-otp', function() {
    (new AuthController())->verifyOtp();
});

$router->post('/reset-password', function() {
    (new AuthController())->resetPassword();
});

// ================= NOTE ROUTES =================
$router->get('/notes', function() {
    auth();
    (new NoteController())->index();
});

$router->get('/notes/create', function() {
    auth();
    (new NoteController())->create();
});

$router->get('/notes/search', function() {
    auth();
    (new NoteController())->search();
});

$router->get('/notes/{id}', function($id) {
    auth();
    (new NoteController())->edit((int)$id);
});

$router->post('/notes/autosave', function() {
    auth();
    (new NoteController())->autoSave();
});

$router->post('/notes/{id}/delete', function($id) {
    auth();
    (new NoteController())->delete((int)$id);
});

$router->post('/notes/{id}/pin', function($id) {
    auth();
    (new NoteController())->togglePin((int)$id);
});

$router->post('/notes/{id}/password', function($id) {
    auth();
    (new NoteController())->setPassword((int)$id);
});

$router->post('/notes/{id}/verify-password', function($id) {
    auth();
    (new NoteController())->verifyPassword((int)$id);
});

$router->post('/notes/{id}/upload-image', function($id) {
    auth();
    (new NoteController())->uploadImage((int)$id);
});

$router->post('/notes/{id}/delete-image', function($id) {
    auth();
    (new NoteController())->deleteImage((int)$id);
});

// ================= LABEL ROUTES =================
$router->get('/labels', function() {
    auth();
    (new LabelController())->index();
});

$router->post('/labels/store', function() {
    auth();
    (new LabelController())->store();
});

$router->post('/labels/{id}/update', function($id) {
    auth();
    (new LabelController())->update((int)$id);
});

$router->post('/labels/{id}/delete', function($id) {
    auth();
    (new LabelController())->delete((int)$id);
});

$router->post('/notes/{id}/labels/attach', function($id) {
    auth();
    (new LabelController())->attachToNote();
});

$router->post('/notes/{id}/labels/detach', function($id) {
    auth();
    (new LabelController())->detachFromNote();
});

$router->get('/notes/{id}/labels', function($id) {
    auth();
    (new LabelController())->getNoteLabels((int)$id);
});

// ================= SHARE ROUTES =================
$router->get('/shared', function() {
    auth();
    (new ShareController())->index();
});

$router->post('/notes/{id}/share', function($id) {
    auth();
    (new ShareController())->share((int)$id);
});

$router->post('/shares/{id}/revoke', function($id) {
    auth();
    (new ShareController())->revoke((int)$id);
});

$router->post('/shares/{id}/update-permission', function($id) {
    auth();
    (new ShareController())->updatePermission((int)$id);
});

$router->get('/shared/{id}/view', function($id) {
    auth();
    (new ShareController())->viewShared((int)$id);
});

$router->get('/shared/{id}/edit', function($id) {
    auth();
    (new ShareController())->editShared((int)$id);
});

// ================= PROFILE ROUTES =================
$router->get('/profile', function() {
    auth();
    (new ProfileController())->index();
});

$router->post('/profile/update', function() {
    auth();
    (new ProfileController())->update();
});

$router->post('/profile/avatar', function() {
    auth();
    (new ProfileController())->updateAvatar();
});

$router->post('/profile/change-password', function() {
    auth();
    (new ProfileController())->changePassword();
});

$router->get('/preferences', function() {
    auth();
    (new ProfileController())->preferences();
});

$router->post('/preferences/update', function() {
    auth();
    (new ProfileController())->updatePreferences();
});

// ================= LANDING PAGE =================
$router->get('/', function() {
    if (isset($_SESSION['user_id'])) {
        redirect('/notes');
    }
    require __DIR__ . '/../views/home.php';
});
// DEBUG TẠM
error_log("=== REQUEST: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
error_log("=== SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
// ================= RUN =================
$router->resolve();
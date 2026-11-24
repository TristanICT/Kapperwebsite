<?php
// Conceptversie; controleer de actieve snippet en de pagina-slugs.
defined('ABSPATH') || exit;

add_action('init', 'kapper_process_login');
function kapper_process_login() {
    if (!isset($_POST['kapper_login_submit']) || is_user_logged_in()) {
        return;
    }
    $nonce = $_POST['login_nonce'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!is_string($nonce) || !wp_verify_nonce(wp_unslash($nonce), 'kapper_login') ||
        !is_string($username) || !is_string($password)) {
        wp_safe_redirect(home_url('/inloggen/?login_status=failed'));
        exit;
    }
    $user = wp_signon([
        'user_login' => sanitize_text_field(wp_unslash($username)),
        'user_password' => wp_unslash($password),
        'remember' => false
    ], is_ssl());
    if (is_wp_error($user)) {
        wp_safe_redirect(home_url('/inloggen/?login_status=failed'));
        exit;
    }
    wp_safe_redirect(home_url('/hoofdpagina/'));
    exit;
}

add_shortcode('custom_login', 'kapper_login_form');
function kapper_login_form() {
    if (is_user_logged_in()) {
        return '<p>Je bent al ingelogd.</p>';
    }
    ob_start();
    ?>
    <form method="post">
        <h2>Inloggen</h2>
        <?php if (isset($_GET['login_status']) && $_GET['login_status'] === 'failed') : ?>
            <p role="alert">De inloggegevens zijn ongeldig.</p>
        <?php endif; ?>
        <?php wp_nonce_field('kapper_login', 'login_nonce'); ?>
        <p><label>Gebruikersnaam of e-mailadres <input name="username" autocomplete="username" required></label></p>
        <p><label>Wachtwoord <input type="password" name="password" autocomplete="current-password" required></label></p>
        <button type="submit" name="kapper_login_submit" value="1">Inloggen</button>
        <p><a href="<?php echo esc_url(home_url('/registerformulier/')); ?>">Account aanmaken</a></p>
    </form>
    <?php
    return ob_get_clean();
}

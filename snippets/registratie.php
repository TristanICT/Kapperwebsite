<?php
// Conceptversie op basis van de eerder beschreven functionaliteit.
// Vergelijk met de actieve Code Snippet voordat deze wordt gebruikt.
defined('ABSPATH') || exit;

add_action('init', 'kapper_register_role');
function kapper_register_role() {
    if (!get_role('kapper_klant')) {
        add_role('kapper_klant', 'Kapper klant', ['read' => true]);
    }
}

add_shortcode('custom_register', 'kapper_registration_form');
function kapper_registration_form() {
    if (is_user_logged_in()) {
        return '<p>Je bent al ingelogd.</p>';
    }
    $message = '';
    if (isset($_POST['kapper_register_submit'])) {
        $nonce = $_POST['register_nonce'] ?? '';
        $raw_username = $_POST['username'] ?? '';
        $raw_email = $_POST['email'] ?? '';
        $raw_password = $_POST['password'] ?? '';
        if (!is_string($nonce) || !wp_verify_nonce(wp_unslash($nonce), 'custom_register') ||
            !is_string($raw_username) || !is_string($raw_email) || !is_string($raw_password)) {
            $message = 'Ongeldige aanvraag.';
        } else {
            $username = sanitize_user(wp_unslash($raw_username), true);
            $email = sanitize_email(wp_unslash($raw_email));
            $password = wp_unslash($raw_password);
            if (!validate_username($username) || !is_email($email) || strlen($password) < 8) {
                $message = 'Vul geldige gegevens en een wachtwoord van minimaal 8 tekens in.';
            } elseif (username_exists($username) || email_exists($email)) {
                $message = 'Er kan geen account met deze gegevens worden aangemaakt.';
            } elseif (!get_role('kapper_klant')) {
                $message = 'Registratie is tijdelijk niet beschikbaar.';
            } else {
                $id = wp_insert_user([
                    'user_login' => $username,
                    'user_email' => $email,
                    'user_pass' => $password,
                    'role' => 'kapper_klant'
                ]);
                $message = is_wp_error($id) ? 'Registratie is mislukt.' : 'Je account is aangemaakt. Je kunt nu inloggen.';
            }
        }
    }
    ob_start();
    ?>
    <form method="post">
        <h2>Account aanmaken</h2>
        <?php if ($message !== '') : ?><p role="status"><?php echo esc_html($message); ?></p><?php endif; ?>
        <?php wp_nonce_field('custom_register', 'register_nonce'); ?>
        <p><label>Gebruikersnaam <input name="username" autocomplete="username" required></label></p>
        <p><label>E-mailadres <input type="email" name="email" autocomplete="email" required></label></p>
        <p><label>Wachtwoord <input type="password" name="password" minlength="8" autocomplete="new-password" required></label></p>
        <button type="submit" name="kapper_register_submit" value="1">Registreren</button>
        <p><a href="<?php echo esc_url(home_url('/inloggen/')); ?>">Inloggen</a></p>
    </form>
    <?php
    return ob_get_clean();
}

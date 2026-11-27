<?php
// Conceptversie gebaseerd op de beschreven WordPress-beheerpagina.
defined('ABSPATH') || exit;

// Geef uitsluitend de eigenaar en beheerder toegang tot afspraken.
add_action('init', 'kapper_dashboard_permissions', 20);
function kapper_dashboard_permissions() {
    $owner = get_role('kapper_eigenaar');
    if (!$owner) {
        $owner = add_role('kapper_eigenaar', 'Kapper eigenaar', ['read' => true]);
    }
    foreach ([$owner, get_role('administrator')] as $role) {
        if ($role && !$role->has_cap('manage_kapper_appointments')) {
            $role->add_cap('manage_kapper_appointments');
        }
    }
    $customer = get_role('kapper_klant');
    if ($customer && $customer->has_cap('manage_kapper_appointments')) {
        $customer->remove_cap('manage_kapper_appointments');
    }
}

add_action('admin_menu', 'kapper_admin_menu');
function kapper_admin_menu() {
    add_menu_page(
        'Kapper Reserveringen', 'Kapper Afspraken', 'manage_kapper_appointments',
        'kapper-reserveringen', 'kapper_admin_dashboard',
        'dashicons-calendar-alt', 26
    );
}

function kapper_admin_dashboard() {
    if (!current_user_can('manage_kapper_appointments')) {
        wp_die('Geen toegang.', '', ['response' => 403]);
    }
    global $wpdb;
    $table = $wpdb->prefix . 'kapper_appointments';
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
    if ($exists !== $table) {
        echo '<div class="wrap"><h1>Kapper Afspraken</h1><p>De reserveringstabel is nog niet beschikbaar.</p></div>';
        return;
    }
    // De tabelnaam komt uitsluitend uit de WordPress-configuratie en een vaste suffix.
    $rows = $wpdb->get_results("SELECT * FROM `$table` ORDER BY appointment_date ASC, appointment_time ASC LIMIT 500");
    $total = $wpdb->get_var("SELECT COUNT(*) FROM `$table`");
    ?>
    <div class="wrap">
        <h1>Kapper Afspraken</h1>
        <p>Totaal: <?php echo esc_html((string) $total); ?> afspraken. Maximaal 500 weergegeven.</p>
        <table class="widefat striped">
            <thead><tr><th>Datum</th><th>Tijd</th><th>Klantnaam</th><th>Behandeling</th><th>Telefoon</th><th>E-mail</th><th>Opmerking</th></tr></thead>
            <tbody>
            <?php foreach ($rows ?: [] as $row) : ?>
                <tr>
                    <td><?php echo esc_html($row->appointment_date); ?></td>
                    <td><?php echo esc_html($row->appointment_time); ?></td>
                    <td><?php echo esc_html($row->customer_name); ?></td>
                    <td><?php echo esc_html($row->service); ?></td>
                    <td><?php echo esc_html($row->customer_phone); ?></td>
                    <td><a href="<?php echo esc_url('mailto:' . $row->customer_email); ?>"><?php echo esc_html($row->customer_email); ?></a></td>
                    <td><?php echo esc_html($row->notes); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

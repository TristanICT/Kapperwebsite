<?php
add_action('init', 'kapper_v2_create_database');

function kapper_v2_create_database() {

    global $wpdb;

    $table = $wpdb->prefix . 'kapper_appointments';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        customer_name varchar(150) NOT NULL,
        customer_email varchar(150) NOT NULL,
        customer_phone varchar(50) NOT NULL,
        service varchar(100) NOT NULL,
        appointment_date date NOT NULL,
        appointment_time time NOT NULL,
        notes text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY appointment_slot (appointment_date, appointment_time)
    ) $charset;";

    dbDelta($sql);
}


/* =========================================
   2. BESCHIKBARE BEHANDELINGEN EN TIJDEN
========================================= */

function kapper_v2_services() {

    return [
        'Heren knippen',
        'Skin fade',
        'Baard trimmen',
        'Knippen + baard',
        'Kinderen knippen'
    ];
}


function kapper_v2_times() {

    $times = [];

    for ($hour = 9; $hour <= 17; $hour++) {

        foreach (['00', '30'] as $minute) {

            if ($hour === 17 && $minute === '30') {
                continue;
            }

            $times[] = sprintf('%02d:%s', $hour, $minute);
        }
    }

    return $times;
}


/* =========================================
   3. FORMULIER VERWERKEN
========================================= */

// Voor ingelogde en niet-ingelogde bezoekers
add_action('admin_post_nopriv_kapper_booking', 'kapper_v2_process_booking');
add_action('admin_post_kapper_booking', 'kapper_v2_process_booking');


function kapper_v2_process_booking() {

    global $wpdb;

    $table = $wpdb->prefix . 'kapper_appointments';

    // Formuliervelden moeten tekstwaarden zijn, geen arrays.
    foreach (['return_url', 'kapper_nonce', 'website', 'name', 'email', 'phone', 'service', 'date', 'time', 'notes'] as $field) {
        if (isset($_POST[$field]) && !is_string($_POST[$field])) {
            wp_safe_redirect(add_query_arg('kapper_status', 'invalid', home_url('/')));
            exit;
        }
    }

    // Terugkeeradres controleren
    $return_url = isset($_POST['return_url'])
        ? esc_url_raw(wp_unslash($_POST['return_url']))
        : home_url('/');

    $return_url = wp_validate_redirect(
        $return_url,
        home_url('/')
    );

    $redirect = function($status) use ($return_url) {

        wp_safe_redirect(
            add_query_arg('kapper_status', $status, $return_url)
        );

        exit;
    };

    // Beveiliging controleren
    if (
        !isset($_POST['kapper_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['kapper_nonce'])),
            'kapper_booking'
        )
    ) {
        $redirect('invalid');
    }

    // Spamveld controleren
    if (!empty($_POST['website'])) {
        $redirect('invalid');
    }

    // Gegevens ophalen en beveiligen
    $name = sanitize_text_field(
        wp_unslash($_POST['name'] ?? '')
    );

    $email = sanitize_email(
        wp_unslash($_POST['email'] ?? '')
    );

    $phone = sanitize_text_field(
        wp_unslash($_POST['phone'] ?? '')
    );

    $service = sanitize_text_field(
        wp_unslash($_POST['service'] ?? '')
    );

    $date = sanitize_text_field(
        wp_unslash($_POST['date'] ?? '')
    );

    $time = sanitize_text_field(
        wp_unslash($_POST['time'] ?? '')
    );

    $notes = sanitize_textarea_field(
        wp_unslash($_POST['notes'] ?? '')
    );

    // Datum valideren
    $date_object = DateTime::createFromFormat('!Y-m-d', $date);

    $valid_date = $date_object &&
        $date_object->format('Y-m-d') === $date &&
        $date >= current_time('Y-m-d');

    // Tijd controleren
    $valid_time = in_array(
        $time,
        kapper_v2_times(),
        true
    );

    // Alle gegevens controleren
    if (
        empty($name) ||
        !is_email($email) ||
        empty($phone) ||
        !in_array($service, kapper_v2_services(), true) ||
        !$valid_date ||
        !$valid_time
    ) {
        $redirect('invalid');
    }

    // Geen afspraken in het verleden
    if (
        $date === current_time('Y-m-d') &&
        $time <= current_time('H:i')
    ) {
        $redirect('past');
    }

    // =====================================
    // 4. RESERVERING OPSLAAN
    // =====================================

    $saved = $wpdb->insert(
        $table,
        [
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'service' => $service,
            'appointment_date' => $date,
            'appointment_time' => $time . ':00',
            'notes' => $notes
        ],
        [
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ]
    );

    // Als het tijdstip al bezet is of opslaan mislukt
    if (!$saved) {

        $redirect('unavailable');
    }

    // =====================================
    // 5. E-MAIL NAAR DE KAPPER
    // =====================================

    $admin_email = get_option('admin_email');

    $subject = 'Nieuwe kappersreservering';

    $message = "Er is een nieuwe reservering ontvangen.\n\n";
    $message .= "Naam: $name\n";
    $message .= "E-mail: $email\n";
    $message .= "Telefoon: $phone\n";
    $message .= "Behandeling: $service\n";
    $message .= "Datum: $date\n";
    $message .= "Tijd: $time\n";
    $message .= "Opmerking: $notes\n";

    wp_mail(
        $admin_email,
        $subject,
        $message
    );

    // =====================================
    // 6. BEVESTIGING NAAR DE KLANT
    // =====================================

    $customer_message = "Beste $name,\n\n";
    $customer_message .= "Bedankt voor je reservering!\n\n";
    $customer_message .= "Behandeling: $service\n";
    $customer_message .= "Datum: $date\n";
    $customer_message .= "Tijd: $time\n\n";
    $customer_message .= "We kijken uit naar je bezoek!\n";

    wp_mail(
        $email,
        'Bevestiging van je kappersafspraak',
        $customer_message
    );

    // Terug naar reserveringspagina
    $redirect('success');
}


/* =========================================
   7. RESERVERINGSFORMULIER TONEN
========================================= */

add_shortcode('kapper_reserveren', 'kapper_v2_booking_form');

function kapper_v2_booking_form() {

    $services = kapper_v2_services();
    $times = kapper_v2_times();

    $status = isset($_GET['kapper_status']) && is_string($_GET['kapper_status'])
        ? sanitize_key(wp_unslash($_GET['kapper_status']))
        : '';

    ob_start();

    ?>

    <style>

    .kapper-booking {
        max-width: 760px;
        margin: 50px auto;
        padding: 45px;
        background: #ffffff;
        color: #222;
        border-radius: 12px;
        box-shadow: 0 15px 50px rgba(0,0,0,.07);
        font-family: inherit;
    }

    .kapper-booking * {
        box-sizing: border-box;
    }

    .kapper-booking-heading {
        text-align: center;
        margin-bottom: 35px;
    }

    .kapper-booking-heading span {
        color: #c9a66b;
        font-size: 11px;
        letter-spacing: 4px;
        font-weight: bold;
    }

    .kapper-booking-heading h2 {
        font-size: 36px;
        color: #171717;
        margin: 12px 0;
    }

    .kapper-booking-heading p {
        color: #888;
        font-size: 14px;
    }

    .kapper-booking-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .kapper-booking-field {
        display: flex;
        flex-direction: column;
        gap: 9px;
        margin-bottom: 18px;
    }

    .kapper-booking-full {
        grid-column: 1 / -1;
    }

    .kapper-booking label {
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .kapper-booking input,
    .kapper-booking select,
    .kapper-booking textarea {
        width: 100%;
        min-height: 52px;
        padding: 14px;
        border: 1px solid #e5e5e5;
        border-radius: 5px;
        background: #fafafa;
        font-size: 14px;
        color: #222;
        font-family: inherit;
    }

    .kapper-booking input:focus,
    .kapper-booking select:focus,
    .kapper-booking textarea:focus {
        outline: none;
        border-color: #c9a66b;
        background: white;
    }

    .kapper-booking textarea {
        min-height: 100px;
        resize: vertical;
    }

    .kapper-booking button {
        width: 100%;
        min-height: 56px;
        border: none;
        border-radius: 5px;
        background: #171717;
        color: white;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
        cursor: pointer;
        transition: .3s;
    }

    .kapper-booking button:hover {
        background: #c9a66b;
        color: #171717;
    }

    .kapper-booking-message {
        padding: 16px;
        margin-bottom: 25px;
        border-radius: 5px;
        font-size: 14px;
        line-height: 1.6;
    }

    .kapper-booking-success {
        background: #e8f6ec;
        color: #236b38;
    }

    .kapper-booking-error {
        background: #fff0ef;
        color: #a52e28;
    }

    .kapper-booking-footer {
        text-align: center;
        margin-top: 20px;
        font-size: 12px;
        color: #999;
    }

    /* Verborgen spamveld */
    .kapper-honeypot {
        position: absolute !important;
        left: -9999px !important;
    }

    @media(max-width: 600px) {

        .kapper-booking {
            padding: 25px 18px;
            margin: 20px 10px;
        }

        .kapper-booking-grid {
            grid-template-columns: 1fr;
        }

        .kapper-booking-heading h2 {
            font-size: 28px;
        }

        .kapper-booking-full {
            grid-column: auto;
        }

    }

    </style>


    <div class="kapper-booking">

        <div class="kapper-booking-heading">

            <span>YOUR STYLE. YOUR MOMENT.</span>

            <h2>Maak een afspraak</h2>

            <p>
                Boek eenvoudig jouw volgende kappersbezoek.
            </p>

        </div>


        <?php

        // Bevestigingsmeldingen tonen

        if ($status === 'success') {

            echo '<div class="kapper-booking-message kapper-booking-success">
                Bedankt! Je reservering is succesvol ontvangen.
            </div>';

        } elseif ($status === 'unavailable') {

            echo '<div class="kapper-booking-message kapper-booking-error">
                Dit tijdstip is al bezet of er ging iets mis. Kies een ander tijdstip.
            </div>';

        } elseif ($status === 'invalid') {

            echo '<div class="kapper-booking-message kapper-booking-error">
                Controleer je gegevens en probeer opnieuw.
            </div>';

        } elseif ($status === 'past') {

            echo '<div class="kapper-booking-message kapper-booking-error">
                Je kunt geen afspraak in het verleden reserveren.
            </div>';

        }

        ?>


        <form
            method="post"
            action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
        >

            <input
                type="hidden"
                name="action"
                value="kapper_booking"
            >

            <input
                type="hidden"
                name="return_url"
                value="<?php echo esc_url(get_permalink()); ?>"
            >

            <?php
            wp_nonce_field(
                'kapper_booking',
                'kapper_nonce'
            );
            ?>


            <div class="kapper-booking-grid">

                <div class="kapper-booking-field kapper-booking-full">

                    <label>Behandeling</label>

                    <select name="service" required>

                        <option value="">
                            Kies een behandeling
                        </option>

                        <?php foreach ($services as $service): ?>

                            <option value="<?php echo esc_attr($service); ?>">
                                <?php echo esc_html($service); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="kapper-booking-field">

                    <label>Naam</label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Voor- en achternaam"
                        maxlength="150"
                        required
                    >

                </div>


                <div class="kapper-booking-field">

                    <label>Telefoonnummer</label>

                    <input
                        type="tel"
                        name="phone"
                        placeholder="06 12345678"
                        maxlength="50"
                        required
                    >

                </div>


                <div class="kapper-booking-field kapper-booking-full">

                    <label>E-mailadres</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="naam@email.nl"
                        maxlength="150"
                        required
                    >

                </div>


                <div class="kapper-booking-field">

                    <label>Datum</label>

                    <input
                        type="date"
                        name="date"
                        min="<?php echo esc_attr(current_time('Y-m-d')); ?>"
                        required
                    >

                </div>


                <div class="kapper-booking-field">

                    <label>Tijdstip</label>

                    <select name="time" required>

                        <option value="">
                            Kies een tijdstip
                        </option>

                        <?php foreach ($times as $time): ?>

                            <option value="<?php echo esc_attr($time); ?>">
                                <?php echo esc_html($time); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="kapper-booking-field kapper-booking-full">

                    <label>Opmerking (optioneel)</label>

                    <textarea
                        name="notes"
                        placeholder="Heb je nog speciale wensen?"
                    ></textarea>

                </div>


                <div class="kapper-honeypot" aria-hidden="true">

                    <label>Laat dit veld leeg</label>

                    <input
                        type="text"
                        name="website"
                        tabindex="-1"
                        autocomplete="off"
                    >

                </div>


                <div class="kapper-booking-field kapper-booking-full">

                    <button type="submit">
                        Reserveer mijn afspraak →
                    </button>

                </div>

            </div>

        </form>


        <div class="kapper-booking-footer">

            We kijken ernaar uit je te verwelkomen.

        </div>

    </div>

    <?php

    return ob_get_clean();
}

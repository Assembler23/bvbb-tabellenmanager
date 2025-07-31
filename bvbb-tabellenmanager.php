<?php
/**
 * Plugin Name: BVBB Tabellenmanager
 * Description: Verwalte Badminton-Ligen inkl. Sortierung, farbiger Markierung und Rückzugsanzeige.
 * Version: 1.6.0
 * Author: Christian Plunze | Assembler23
 * 
 * GitHub Plugin URI: https://github.com/Assembler23/bvbb-tabellenmanager.git
 * GitHub Branch: main
 */


if (!defined('ABSPATH')) exit;

// Cronjob aktivieren
register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('bvbb_update_all_tables')) {
        wp_schedule_event(time(), 'hourly', 'bvbb_update_all_tables');
    }
});
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('bvbb_update_all_tables');
});
add_action('bvbb_update_all_tables', 'bvbb_fetch_all_tables');

// Admin-Menü
add_action('admin_menu', function () {
    add_menu_page('BVBB Ligen', 'BVBB Ligen', 'manage_options', 'bvbb-ligen', 'bvbb_admin_ligen_page');
});

function bvbb_admin_ligen_page() {
    $ligen = get_option('bvbb_ligen', []);

    if (isset($_POST['bvbb_add_league'])) {
        $name = sanitize_text_field($_POST['league_name']);
        $url = esc_url_raw($_POST['league_url']);
        $slug = sanitize_title($name);
        $order = count($ligen);

        $ligen[$slug] = ['name' => $name, 'url' => $url, 'order' => $order];
        update_option('bvbb_ligen', $ligen);
        echo '<div class="updated"><p>Neue Liga gespeichert.</p></div>';
    }

    if (isset($_POST['bvbb_delete_league'])) {
        $slug = sanitize_text_field($_POST['league_slug']);
        unset($ligen[$slug]);
        update_option('bvbb_ligen', $ligen);
        delete_option('bvbb_table_' . $slug);
        delete_option('bvbb_table_UPDATED_' . $slug);
        echo '<div class="updated"><p>Liga gelöscht.</p></div>';
    }

    if (isset($_POST['bvbb_manual_update'])) {
        $slug = sanitize_text_field($_POST['league_slug']);
        bvbb_fetch_single_table($slug);
        echo '<div class="updated"><p>' . esc_html($slug) . ' wurde aktualisiert.</p></div>';
    }

    if (isset($_POST['bvbb_manual_update_all'])) {
        bvbb_fetch_all_tables();
        echo '<div class="updated"><p>Alle Tabellen wurden aktualisiert.</p></div>';
    }

    if (isset($_POST['bvbb_move_league']) && isset($_POST['direction'])) {
        $slug = sanitize_text_field($_POST['league_slug']);
        $direction = $_POST['direction'];

        uasort($ligen, fn($a, $b) => $a['order'] <=> $b['order']);
        $keys = array_keys($ligen);
        $index = array_search($slug, $keys);
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if (isset($keys[$swapWith])) {
            $slug2 = $keys[$swapWith];
            $temp = $ligen[$slug]['order'];
            $ligen[$slug]['order'] = $ligen[$slug2]['order'];
            $ligen[$slug2]['order'] = $temp;
            update_option('bvbb_ligen', $ligen);
            echo '<div class="updated"><p>Reihenfolge geändert.</p></div>';
        }
    }

    uasort($ligen, fn($a, $b) => $a['order'] <=> $b['order']);
    ?>
    <div class="wrap">
        <h1>BVBB Ligen verwalten</h1>

        <h2>Neue Liga hinzufügen</h2>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="league_name">Name</label></th>
                    <td><input type="text" name="league_name" required></td>
                </tr>
                <tr>
                    <th><label for="league_url">Liga.nu URL</label></th>
                    <td><input type="url" name="league_url" required></td>
                </tr>
            </table>
            <?php submit_button('Liga hinzufügen', 'primary', 'bvbb_add_league'); ?>
        </form>

        <h2>Bestehende Ligen</h2>
        <form method="post">
            <?php submit_button('Alle Ligen jetzt aktualisieren', 'primary', 'bvbb_manual_update_all'); ?>
        </form>

        <table class="widefat">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Shortcode</th>
                    <th>Letzte Aktualisierung</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ligen as $slug => $liga):
                    $updated = get_option('bvbb_table_UPDATED_' . $slug);
                    $zeit = $updated ? date_i18n('d.m.Y H:i', $updated) : '–';
                ?>
                    <tr>
                        <td>
                            <?php echo esc_html($liga['name']); ?><br>
                            <small><a href="<?php echo esc_url($liga['url']); ?>" target="_blank">URL öffnen</a></small>
                        </td>
                        <td><code>[bvbb_tabelle id="<?php echo esc_attr($slug); ?>"]</code></td>
                        <td><?php echo esc_html($zeit); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="league_slug" value="<?php echo esc_attr($slug); ?>">
                                <?php submit_button('Jetzt aktualisieren', 'secondary', 'bvbb_manual_update', false); ?>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="league_slug" value="<?php echo esc_attr($slug); ?>">
                                <?php submit_button('Löschen', 'delete', 'bvbb_delete_league', false); ?>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="league_slug" value="<?php echo esc_attr($slug); ?>">
                                <input type="hidden" name="direction" value="up">
                                <?php submit_button('▲', 'secondary', 'bvbb_move_league', false); ?>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="league_slug" value="<?php echo esc_attr($slug); ?>">
                                <input type="hidden" name="direction" value="down">
                                <?php submit_button('▼', 'secondary', 'bvbb_move_league', false); ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

add_shortcode('bvbb_tabelle', function ($atts) {
    $atts = shortcode_atts(['id' => ''], $atts);
    $slug = sanitize_title($atts['id']);

    $ligen = get_option('bvbb_ligen', []);
    if (!isset($ligen[$slug])) return '<p>Unbekannte Liga.</p>';
    $liganame = esc_html($ligen[$slug]['name']);

    $table_html = get_option('bvbb_table_' . $slug);
    $timestamp = get_option('bvbb_table_UPDATED_' . $slug);
    if (!$table_html) return '<p>Keine Tabelle verfügbar.</p>';

    $datum = $timestamp ? date_i18n('d.m.Y, H:i \U\h\r', $timestamp) : '';
	$tz_string = get_option('timezone_string') ?: 'lokale Zeit';
    $caption = "<caption class='bvbb-caption'>$liganame <span class='bvbb-caption-update'>Letztes Update: $datum</span></caption>";


    $table_html = preg_replace('/<table([^>]*)>/', '<table$1>' . $caption, $table_html, 1);

    return $table_html;
});

function bvbb_fetch_all_tables() {
    $ligen = get_option('bvbb_ligen', []);
    uasort($ligen, fn($a, $b) => $a['order'] <=> $b['order']);
    foreach ($ligen as $slug => $liga) {
        bvbb_fetch_single_table($slug);
    }
}

function bvbb_fetch_single_table($slug) {
    $ligen = get_option('bvbb_ligen', []);
    if (!isset($ligen[$slug])) return;

    $url = $ligen[$slug]['url'];
    $response = wp_remote_get($url);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) return;

    $html = mb_convert_encoding(wp_remote_retrieve_body($response), 'HTML-ENTITIES', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    $table = $xpath->query('//table[contains(@class, "result-set")]')->item(0);
    if (!$table) return;

    $rows = $table->getElementsByTagName('tr');
    $output = '<div class="bvbb-tabelle-wrapper"><table class="bvbb-tabelle">';
    $output .= '<tr><th>Rang</th><th>Mannschaft</th><th>Begegnungen</th><th>Punkte</th><th>Spiele</th><th>Sätze</th></tr>';

    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');

        // Prüfen, ob Rückzug vorliegt
// Prüfen auf Rückzug unabhängig von Spaltenanzahl
$ist_rueckzug = false;
$rueckzug_text = '';
foreach ($cols as $col) {
    if ($col->hasAttribute('colspan') && stripos($col->textContent, 'zurückgezogen') !== false) {
        $ist_rueckzug = true;
        $rueckzug_text = trim($col->textContent);
        break;
    }
}

if ($ist_rueckzug) {
    $rank = $cols->item(1) ? trim($cols->item(1)->textContent) : '–';
    $team = $cols->item(2) ? trim($cols->item(2)->textContent) : 'Zurückgezogenes Team';

    $output .= "<tr class='zurueckgezogen'>
        <td>$rank</td>
        <td>$team</td>
        <td colspan='4'><em>$rueckzug_text</em></td>
    </tr>";
    continue;
}



        

        // Nur verarbeiten, wenn mindestens 10 Spalten da sind
        if ($cols->length < 10) continue;

        $imgHTML = $dom->saveHTML($cols->item(0));
        $class = '';
        if (strpos($imgHTML, 'alt="Aufsteiger"') !== false) {
            $class = 'aufsteiger';
        } elseif (strpos($imgHTML, 'alt="Absteiger"') !== false) {
            $class = 'absteiger';
        }

        $rank = trim($cols->item(1)->textContent);
        $team = trim($cols->item(2)->textContent);
        $matches = trim($cols->item(3)->textContent);
        $points = trim($cols->item(7)->textContent);
        $games = trim($cols->item(8)->textContent);
        $sets = trim($cols->item(9)->textContent);

        if (!is_numeric($rank)) continue;

        $output .= "<tr class='$class'>
            <td>$rank</td>
            <td>$team</td>
            <td>$matches</td>
            <td>$points</td>
            <td>$games</td>
            <td>$sets</td>
        </tr>";
    }

    $output .= '</table></div>';
    update_option('bvbb_table_' . $slug, $output);
    update_option('bvbb_table_UPDATED_' . $slug, time());
}


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bvbb_tabelle_style', plugin_dir_url(__FILE__) . 'css/style.css');
});

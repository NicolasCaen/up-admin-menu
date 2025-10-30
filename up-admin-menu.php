<?php
/*
Plugin Name: Upcoder Admin Menu
Description: Plugin permettant d'ajouter un bouton pour afficher/masquer les menus administratifs selon les préférences de l'utilisateur
Version: 1.4.1
Author: GEHIN Nicolas
*/


function ajouter_toggle_admin_bar() {
    global $wp_admin_bar;
    
    $user_id = get_current_user_id();
    $current_config = get_user_meta($user_id, 'menu_simplified', true);
    $configurations = get_menu_configurations();
    $switch_icon_url = esc_url(plugins_url('assets/icons/switch.svg', __FILE__));
    $settings_icon_url = esc_url(plugins_url('assets/icons/settings.svg', __FILE__));

    // Texte du bouton
    $button_text = ($current_config === '') ? 'Menu: Standard' :  $configurations[$current_config]['name'];

    // Ajouter un nœud parent pour le conteneur du select
    $wp_admin_bar->add_node(array(
        'id'    => 'toggle-admin-menu',
        'title' => $button_text,
        'href'  => '#',
        'meta'  => array(
            'html' => '<div id="toggle-admin-menu-select-container" style="display: none;">
                            <select id="toggle-admin-menu-select"></select>
                            <button id="toggle-admin-menu-apply" style ="display:flex; align-items: center; justify-content: center; height:100%">
                                <img src="' . $switch_icon_url . '" alt="Changer de configuration" width="20" height="20" />
                            </button>
                            <a id="toggle-admin-menu-settings" href="' . admin_url('options-general.php?page=toggle-menu-settings') . '" style="display:flex; align-items: center; justify-content: center; height:100%">
                                <img src="' . $settings_icon_url . '" alt="Réglages" width="20" height="20" />
                            </a>
                        </div>', // Conteneur pour le select
        ),
    ));
}
add_action('admin_bar_menu', 'ajouter_toggle_admin_bar', 999);


// Gère l'affichage des menus
function gerer_affichage_menus() {
    // Vérifie si l'on est sur la page "options-general.php?page=toggle-menu-settings"
    if (isset($_GET['page']) && $_GET['page'] === 'toggle-menu-settings') {
        return; // Ne pas modifier les menus
    }

    $user_id = get_current_user_id();
    $current_config = get_user_meta($user_id, 'menu_simplified', true);

    if ($current_config !== '') {
        $configurations = get_menu_configurations();
        if (isset($configurations[$current_config])) {
            foreach ($configurations[$current_config]['menus'] as $menu) {
                remove_menu_page($menu);
            }
        }
    }
}
add_action('admin_menu', 'gerer_affichage_menus', 999);


// Enregistre et charge le script JavaScript
function enregistrer_toggle_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('toggle-admin-menu-styles', plugins_url('style.css', __FILE__), array(), '1.4.1');
    
    wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            // Charger les configurations disponibles
            var configurations = ' . json_encode(get_menu_configurations()) . ';
            var currentConfig = "' . get_user_meta(get_current_user_id(), 'menu_simplified', true) . '";
            
            // Remplir le sélecteur avec les options
            var select = $("#toggle-admin-menu-select");
            select.append($("<option>", {
                value: "",
                text: "Standard",
                selected: (currentConfig === "")
            }));
            
            $.each(configurations, function(index, config) {
                select.append($("<option>", {
                    value: index,
                    text: config.name,
                    selected: (currentConfig == index)
                }));
            });

            // Afficher le conteneur de sélection
            $("#toggle-admin-menu-select-container").show();

            // Gestion du clic sur le bouton "Appliquer"
            $("#toggle-admin-menu-apply").on("click", function(e) {
                e.preventDefault();
                
                // Récupérer la valeur sélectionnée
                var selectedIndex = select.val();
                
                // Envoyer la requête AJAX
                $.post(ajaxurl, {
                    action: "toggle_admin_menu",
                    nonce: "' . wp_create_nonce('toggle_admin_menu_nonce') . '",
                    config_index: selectedIndex
                }, function(response) {
                    if (response.success) {
                        console.log("Configuration mise à jour :", response.new_config);
                        window.location.reload(); // Recharger la page pour appliquer la configuration
                    } else {
                        console.error("Erreur lors de la mise à jour de la configuration.");
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Erreur AJAX :", textStatus, errorThrown);
                });
            });
        });
    ');
}

add_action('admin_enqueue_scripts', 'enregistrer_toggle_script');

// Gère l'action AJAX
function handle_toggle_ajax() {
    check_ajax_referer('toggle_admin_menu_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    $new_config = isset($_POST['config_index']) ? sanitize_text_field($_POST['config_index']) : '';
    
    if ($new_config !== '') {
        // Mettez à jour la méta utilisateur avec la nouvelle configuration sélectionnée
        update_user_meta($user_id, 'menu_simplified', $new_config);
    } else {
        // Si aucune configuration n'est sélectionnée, on réinitialise à "Standard"
        delete_user_meta($user_id, 'menu_simplified');
    }
    
    wp_send_json_success(array(
        'message' => 'Configuration mise à jour.',
        'new_config' => $new_config
    ));
}
add_action('wp_ajax_toggle_admin_menu', 'handle_toggle_ajax');


// Ajoute la page d'options
function ajouter_menu_options() {
    add_options_page(
        'Configuration Menu Toggle',
        'Menu Toggle',
        'manage_options',
        'toggle-menu-settings',
        'afficher_page_options'
    );
}
add_action('admin_menu', 'ajouter_menu_options');

// Fonction pour récupérer tous les menus admin
function get_all_admin_menus() {
    global $menu;
    
    // Assurez-vous que la variable $menu est disponible
    if (!$menu) {
        require_once(ABSPATH . 'wp-admin/includes/admin.php');
    }
    
    $all_menus = array();
    
    foreach ($menu as $menu_item) {
        if (!empty($menu_item[0]) && !empty($menu_item[2])) {
            // Nettoie le nom du menu des balises HTML
            $menu_name = strip_tags($menu_item[0]);
            $menu_slug = $menu_item[2];
            
            $all_menus[$menu_slug] = $menu_name;
        }
    }
    
    return $all_menus;
}

// Modification de la structure de stockage des options
function get_menu_configurations() {
    return get_option('toggle_menu_configurations', array(
        array(
            'name' => 'Simple',
            'menus' => array(
                'options-general.php',
                'tools.php',
                'users.php',
                'plugins.php',
                'themes.php',
                'upload.php'
            )
        )
    ));
}

// Modification de la fonction de gestion d'affichage
function afficher_page_options() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['submit'])) {
        check_admin_referer('toggle_menu_options');
        
        $configurations = array();
        foreach ($_POST['config'] as $index => $config) {
            if (!empty($config['name'])) {
                $configurations[] = array(
                    'name' => sanitize_text_field($config['name']),
                    'menus' => isset($config['menus']) ? $config['menus'] : array()
                );
            }
        }
        
        update_option('toggle_menu_configurations', $configurations);
        echo '<div class="notice notice-success"><p>Paramètres sauvegardés.</p></div>';
    }

    $configurations = get_menu_configurations();
    $available_menus = get_all_admin_menus();
    ?>
    <div class="wrap">
        <h1>Configuration Menu Toggle</h1>
        <form method="post" action="">
            <?php wp_nonce_field('toggle_menu_options'); ?>
            <div id="menu-configurations">
                <?php foreach ($configurations as $index => $config): ?>
                <div class="menu-config" style="margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ccc;">
                    <h3>Configuration <?php echo $index + 1; ?></h3>
                    <p>
                        <label>Nom de la configuration:</label>
                        <input type="text" name="config[<?php echo $index; ?>][name]" value="<?php echo esc_attr($config['name']); ?>" required>
                    </p>
                    <div class="menus-list">
                        <?php foreach ($available_menus as $menu_slug => $menu_name) : ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" 
                                       name="config[<?php echo $index; ?>][menus][]" 
                                       value="<?php echo esc_attr($menu_slug); ?>"
                                       <?php checked(in_array($menu_slug, $config['menus'])); ?>>
                                <?php echo esc_html($menu_name); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="button" onclick="addNewConfiguration()">Ajouter une configuration</button>
            <?php submit_button('Enregistrer les modifications'); ?>
        </form>
    </div>

    <script>
    function addNewConfiguration() {
        const container = document.getElementById('menu-configurations');
        const configCount = container.children.length;
        const template = container.children[0].cloneNode(true);
        
        // Mise à jour des indices
        template.querySelector('h3').textContent = `Configuration ${configCount + 1}`;
        const inputs = template.querySelectorAll('input');
        inputs.forEach(input => {
            input.name = input.name.replace(/config\[\d+\]/, `config[${configCount}]`);
            if (input.type === 'text') {
                input.value = '';
            } else if (input.type === 'checkbox') {
                input.checked = false;
            }
        });
        
        container.appendChild(template);
    }
    </script>
    <?php
}

// Ajoute un lien vers les paramètres dans la liste des plugins
function ajouter_lien_configuration($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=toggle-menu-settings') . '">Paramètres</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ajouter_lien_configuration');
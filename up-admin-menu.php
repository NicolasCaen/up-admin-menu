<?php
/*
Plugin Name: Upcoder Admin Menu
Description: Plugin permettant d'ajouter un bouton pour afficher/masquer les menus administratifs selon les préférences de l'utilisateur
Version: 1.0
Author: GEHIN Nicolas
*/


function ajouter_toggle_admin_bar() {
    global $wp_admin_bar;
    
    $user_id = get_current_user_id();
    $current_config = get_user_meta($user_id, 'menu_simplified', true);
    $configurations = get_menu_configurations();
    
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
                            <svg width="24px" height="24px" viewBox="0 0 512 512" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <style type="text/css">
                                    .st0{fill:white;}
                                    .st1{fill:none;stroke:white;stroke-width:32;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}
                                </style>
                                <g id="Layer_1"/>
                                <g id="Layer_2">
                                <g>
                                <g>
                                <path class="st0" d="M364.74,333.3c16.19,0,29.37-13.18,29.37-29.37V76.58c0-16.19-13.18-29.37-29.37-29.37H245.68     c-16.19,0-29.37,13.18-29.37,29.37v102.11h-69.05c-16.19,0-29.37,13.18-29.37,29.37v227.36c0,16.19,13.18,29.37,29.37,29.37     h119.06c16.19,0,29.37-13.18,29.37-29.37V333.3H364.74z M248.31,79.21h113.8V301.3h-66.42v-93.24     c0-16.19-13.18-29.37-29.37-29.37h-18.01V79.21z M263.69,432.79h-113.8v-222.1h82.42h31.38V317.3V432.79z"/>
                                </g>
                                <g>
                                <path class="st0" d="M462.54,360.63c-0.02-0.23-0.06-0.45-0.1-0.67c-0.04-0.29-0.08-0.58-0.13-0.87     c-0.05-0.26-0.12-0.52-0.19-0.77c-0.06-0.25-0.12-0.5-0.19-0.75c-0.08-0.25-0.17-0.5-0.26-0.75c-0.09-0.24-0.17-0.49-0.27-0.73     c-0.1-0.23-0.21-0.46-0.32-0.68c-0.12-0.25-0.23-0.49-0.35-0.74c-0.12-0.22-0.25-0.43-0.38-0.64c-0.14-0.24-0.27-0.47-0.43-0.71     c-0.16-0.24-0.33-0.46-0.5-0.68c-0.14-0.19-0.27-0.39-0.43-0.57c-0.67-0.82-1.42-1.56-2.23-2.23c-0.19-0.15-0.38-0.28-0.57-0.43     c-0.23-0.17-0.45-0.35-0.69-0.5c-0.23-0.15-0.47-0.29-0.7-0.43c-0.21-0.13-0.42-0.26-0.64-0.38c-0.24-0.13-0.49-0.24-0.73-0.35     c-0.23-0.11-0.45-0.22-0.69-0.32c-0.24-0.1-0.48-0.18-0.72-0.26c-0.25-0.09-0.5-0.19-0.75-0.26c-0.24-0.07-0.49-0.13-0.73-0.19     c-0.26-0.07-0.52-0.14-0.78-0.19c-0.28-0.06-0.57-0.09-0.85-0.13c-0.23-0.03-0.46-0.08-0.69-0.1c-0.53-0.05-1.05-0.08-1.58-0.08     h-52.89c-8.84,0-16,7.16-16,16s7.16,16,16,16H408l-78.48,78.48c-6.25,6.25-6.25,16.38,0,22.63c3.12,3.12,7.22,4.69,11.31,4.69     s8.19-1.56,11.31-4.69l78.48-78.48v14.27c0,8.84,7.16,16,16,16s16-7.16,16-16v-52.89C462.62,361.68,462.59,361.15,462.54,360.63z     "/>
                                </g>
                                <g>
                                <path class="st0" d="M56.49,163.1c0.23,0.15,0.46,0.28,0.7,0.42c0.22,0.13,0.43,0.26,0.65,0.38c0.24,0.13,0.48,0.23,0.73,0.35     c0.23,0.11,0.46,0.22,0.69,0.32c0.24,0.1,0.48,0.18,0.72,0.26c0.25,0.09,0.5,0.19,0.76,0.27c0.24,0.07,0.48,0.13,0.72,0.19     c0.26,0.07,0.53,0.14,0.79,0.19c0.28,0.06,0.56,0.09,0.84,0.13c0.24,0.03,0.47,0.08,0.71,0.1c0.52,0.05,1.05,0.08,1.58,0.08h52.9     c8.84,0,16-7.16,16-16s-7.16-16-16-16H104l78.48-78.48c6.25-6.25,6.25-16.38,0-22.63c-6.25-6.25-16.38-6.25-22.63,0l-78.48,78.48     V96.9c0-8.84-7.16-16-16-16s-16,7.16-16,16v52.89c0,0.53,0.03,1.06,0.08,1.59c0.02,0.23,0.06,0.45,0.1,0.67     c0.04,0.29,0.08,0.58,0.13,0.87c0.05,0.26,0.12,0.52,0.19,0.77c0.06,0.25,0.12,0.5,0.19,0.75c0.08,0.25,0.17,0.5,0.26,0.75     c0.09,0.24,0.17,0.49,0.27,0.73c0.1,0.23,0.21,0.46,0.32,0.68c0.12,0.25,0.23,0.49,0.35,0.74c0.12,0.22,0.25,0.43,0.38,0.64     c0.14,0.24,0.27,0.47,0.43,0.71c0.16,0.24,0.33,0.46,0.5,0.68c0.14,0.19,0.27,0.39,0.43,0.57c0.67,0.82,1.42,1.56,2.23,2.23     c0.18,0.15,0.38,0.28,0.57,0.43C56.03,162.76,56.26,162.94,56.49,163.1z"/>
                                </g>
                                </g>
                                </g>
                                </svg>
                            </button>
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


// Ajoute du CSS pour le bouton
function ajouter_toggle_styles() {
    ?>
    <style>
        #wp-admin-bar-toggle-admin-menu{
            display: flex !important;
            justify-content: center;
        }

        #wp-admin-bar-toggle-admin-menu .ab-item {
          display: none !important;
        }
#toggle-admin-menu-select-container{
    display: flex;
    align-items: center;
    background: #1d2327;
}

        #toggle-admin-menu-select {
            background:#444444;
            border:0;
            padding: 0 1rem;
            color: white;
            box-sizing: border-box;
            
        
        }

        #toggle-admin-menu-select option{
    
            padding: 0.25rem 0.5rem;    
        }
        #toggle-admin-menu-select:focus {
            outline: none;
            border-color: #2271b1;
        }
        /* Style pour le bouton */
        #toggle-admin-menu-apply {
            background: #2271b1;
            color: white;
            border: none;
            padding : 0 .25rem;
            cursor: pointer;
            margin-left: 0;
        }
        #toggle-admin-menu-apply:hover {
            background: #135e96;
        }
    </style>
    <?php
}
add_action('admin_head', 'ajouter_toggle_styles');

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
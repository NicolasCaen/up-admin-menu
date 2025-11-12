<?php
/*
Plugin Name: Upcoder Admin Menu
Description: Plugin permettant d'ajouter un bouton pour afficher/masquer les menus administratifs selon les préférences de l'utilisateur
Version: 1.5.0
Author: GEHIN Nicolas
*/


function ajouter_toggle_admin_bar() {
    global $wp_admin_bar;
    
    $user_id = get_current_user_id();
    $current_config = get_user_meta($user_id, 'menu_simplified', true);
    $configurations = get_menu_configurations();
    $switch_icon_url = esc_url(plugins_url('assets/icons/switch.svg', __FILE__));
    $settings_icon_url = esc_url(plugins_url('assets/icons/settings.svg', __FILE__));
    $settings_link_html = '';
    if (current_user_can('manage_options')) {
        $settings_link_html = '<a id="toggle-admin-menu-settings" href="' . admin_url('options-general.php?page=toggle-menu-settings') . '" style="display:flex; align-items: center; justify-content: center; height:100%">'
                            . '    <img src="' . $settings_icon_url . '" alt="Réglages" width="20" height="20" />'
                            . '</a>';
    }

    // Texte du bouton
    $button_text = 'Menu: Standard';
    if ($current_config !== '' && $current_config !== '__standard__' && isset($configurations[$current_config])) {
        $button_text = $configurations[$current_config]['name'];
        $button_text = 'Menu: ' . $button_text;
    }

    // Construire le conteneur en dehors de l'ancre via meta html
    $container_html = '<div id="toggle-admin-menu-select-container" style="display:inline-flex; align-items:center; gap:6px; margin-left:8px;">'
                    . '    <select id="toggle-admin-menu-select" style="max-width:180px;"></select>'
                    . '    <button id="toggle-admin-menu-apply" style ="display:inline-flex; align-items:center; justify-content:center; height:24px; width:24px; padding:0; border:none; background:transparent; cursor:pointer">'
                    . '        <img src="' . $switch_icon_url . '" alt="Changer de configuration" width="18" height="18" />'
                    . '    </button>'
                    .          $settings_link_html .
                    '</div>';

    // Ajouter un nœud avec titre simple + HTML additionnel cliquable en dehors de l'ancre
    $wp_admin_bar->add_node(array(
        'id'    => 'toggle-admin-menu',
        'title' => esc_html($button_text),
        'href'  => '#',
        'meta'  => array(
            'html' => $container_html,
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

    $configurations = get_menu_configurations();

    // Déterminer la configuration applicable
    $config_to_apply = null;

    // 1) Priorité: sélection personnelle (métadonnée utilisateur)
    if ($current_config !== '' && isset($configurations[$current_config])) {
        $maybe_cfg = $configurations[$current_config];
        $scope = isset($maybe_cfg['scope']) ? $maybe_cfg['scope'] : 'user';
        $owner = isset($maybe_cfg['owner']) ? intval($maybe_cfg['owner']) : 0;
        if (!($scope === 'user' && $owner !== $user_id)) {
            $config_to_apply = $maybe_cfg;
        }
    } else {
        // 2) Ensuite: configuration par rôle
        $user = wp_get_current_user();
        $roles = is_array($user->roles) ? $user->roles : array();
        foreach ($configurations as $cfg) {
            $scope = isset($cfg['scope']) ? $cfg['scope'] : 'user';
            if ($scope === 'role') {
                $role_key = isset($cfg['role']) ? $cfg['role'] : '';
                if ($role_key && in_array($role_key, $roles, true)) {
                    $config_to_apply = $cfg;
                    break;
                }
            }
        }

        // 3) Ensuite: configuration marquée par défaut
        if ($config_to_apply === null) {
            foreach ($configurations as $cfg) {
                if (!empty($cfg['default'])) {
                    $config_to_apply = $cfg;
                    break;
                }
            }
        }

        // 4) Enfin: configuration pour tous les utilisateurs
        if ($config_to_apply === null) {
            foreach ($configurations as $cfg) {
                $scope = isset($cfg['scope']) ? $cfg['scope'] : 'user';
                if ($scope === 'all') {
                    $config_to_apply = $cfg;
                    break;
                }
            }
        }
    }

    if ($config_to_apply && !empty($config_to_apply['menus'])) {
        foreach ($config_to_apply['menus'] as $menu) {
            remove_menu_page($menu);
        }
    }
}
add_action('admin_menu', 'gerer_affichage_menus', 999);


// Enregistre et charge le script JavaScript
function enregistrer_toggle_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('toggle-admin-menu-styles', plugins_url('style.css', __FILE__), array(), '1.5.0');
    
    wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            // Charger les configurations disponibles
            var configurations = ' . json_encode(get_menu_configurations()) . ';
            var currentUserId = ' . intval(get_current_user_id()) . ';
            var currentConfig = "' . get_user_meta(get_current_user_id(), 'menu_simplified', true) . '";
            if (currentConfig === "__standard__") { currentConfig = ""; }
            
            // Remplir le sélecteur avec les options
            var select = $("#toggle-admin-menu-select");
            select.empty();
            select.append($("<option>", {
                value: "",
                text: "Standard"
            }));
            
            $.each(configurations, function(index, config) {
                // Filtrer: les configs user ne sont visibles que par leur propriétaire
                var scope = config.scope || "user";
                var owner = parseInt(config.owner || 0, 10);
                if (scope === "user" && owner !== currentUserId) {
                    return; // skip
                }
                select.append($("<option>", {
                    value: index,
                    text: config.name
                }));
            });

            // Appliquer la valeur sélectionnée une seule fois
            if (currentConfig === undefined || currentConfig === null) { currentConfig = ""; }
            select.val(currentConfig);

            // Afficher par défaut le conteneur
            $("#toggle-admin-menu-select-container").show();

            // Gestion du clic sur le bouton "Appliquer"
            $("#toggle-admin-menu-apply").on("click", function(e) {
                e.preventDefault();
                
                // Récupérer la valeur sélectionnée
                var selectedIndex = select.val();
                if (selectedIndex === null || selectedIndex === undefined) { selectedIndex = ""; }
                
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
    
    if ($new_config === '') {
        // Standard explicite: on stocke un sentinelle pour ignorer toute config globale/rôle/défaut
        update_user_meta($user_id, 'menu_simplified', '__standard__');
    } else {
        // Configuration sélectionnée par l'utilisateur, avec contrôle de propriété si scope=user
        $configs = get_menu_configurations();
        if (!isset($configs[$new_config])) {
            wp_send_json_error(array('message' => 'Configuration invalide.'));
        }
        $cfg = $configs[$new_config];
        $scope = isset($cfg['scope']) ? $cfg['scope'] : 'user';
        $owner = isset($cfg['owner']) ? intval($cfg['owner']) : 0;
        if ($scope === 'user' && $owner !== $user_id) {
            wp_send_json_error(array('message' => 'Configuration non autorisée.'));
        }
        update_user_meta($user_id, 'menu_simplified', $new_config);
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
    $configs = get_option('toggle_menu_configurations', array(
        array(
            'name' => 'Simple',
            'menus' => array(
                'options-general.php',
                'tools.php',
                'users.php',
                'plugins.php',
                'themes.php',
                'upload.php'
            ),
            // Par défaut: s'applique uniquement à l'utilisateur (aucun effet global)
            'scope' => 'user', // valeurs possibles: user | all | role
            'role'  => '',
            'default' => false,
            'owner' => 0
        )
    ));

    // Rétrocompatibilité: s'assurer que chaque config a les nouvelles clés
    foreach ($configs as &$cfg) {
        if (!isset($cfg['scope'])) { $cfg['scope'] = 'user'; }
        if (!isset($cfg['role'])) { $cfg['role'] = ''; }
        if (!isset($cfg['menus'])) { $cfg['menus'] = array(); }
        if (!isset($cfg['default'])) { $cfg['default'] = false; }
        if (!isset($cfg['owner'])) { $cfg['owner'] = 0; }
    }
    unset($cfg);

    return $configs;
}

// Modification de la fonction de gestion d'affichage
function afficher_page_options() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['submit'])) {
        check_admin_referer('toggle_menu_options');
        
        $configurations = array();
        $default_index = isset($_POST['default_config']) ? intval($_POST['default_config']) : -1;
        foreach ($_POST['config'] as $index => $config) {
            if (!empty($config['name'])) {
                $scope_val = isset($config['scope']) ? $config['scope'] : 'user';
                $scope_val = in_array($scope_val, array('user','all','role'), true) ? $scope_val : 'user';
                $role_val = isset($config['role']) ? sanitize_text_field($config['role']) : '';
                $owner_val = ($scope_val === 'user') ? get_current_user_id() : 0;
                $configurations[] = array(
                    'name' => sanitize_text_field($config['name']),
                    'menus' => isset($config['menus']) ? array_map('sanitize_text_field', (array) $config['menus']) : array(),
                    'scope' => $scope_val,
                    'role'  => ($scope_val === 'role') ? $role_val : '',
                    'default' => ($default_index === intval($index)),
                    'owner' => intval($owner_val)
                );
            }
        }
        
        update_option('toggle_menu_configurations', $configurations);
        echo '<div class="notice notice-success"><p>Paramètres sauvegardés.</p></div>';
    }

    $configurations = get_menu_configurations();
    $available_menus = get_all_admin_menus();
    // Rôles disponibles pour la sélection de portée par rôle
    if (!function_exists('wp_roles')) {
        require_once ABSPATH . 'wp-includes/capabilities.php';
    }
    $wp_roles = wp_roles();
    $roles_list = is_object($wp_roles) ? $wp_roles->roles : array();
    ?>
    <div class="wrap">
        <h1>Configuration Menu Toggle</h1>
        <form method="post" action="">
            <?php wp_nonce_field('toggle_menu_options'); ?>
            <div id="menu-configurations">
                <?php foreach ($configurations as $index => $config): ?>
                <div class="menu-config" draggable="true" style="margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ccc; cursor: move;" data-index="<?php echo $index; ?>">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <h3 style="margin:0;">Configuration <?php echo $index + 1; ?></h3>
                        <button type="button" class="button button-link-delete remove-config">Supprimer</button>
                    </div>
                    <p>
                        <label>Nom de la configuration:</label>
                        <input type="text" name="config[<?php echo $index; ?>][name]" value="<?php echo esc_attr($config['name']); ?>" required>
                    </p>
                    <p>
                        <label>
                            <input type="radio" name="default_config" value="<?php echo $index; ?>" <?php checked(!empty($config['default'])); ?>>
                            Définir comme configuration par défaut
                        </label>
                    </p>
                    <fieldset style="margin:10px 0;">
                        <legend>Portée d'application</legend>
                        <?php $scope = isset($config['scope']) ? $config['scope'] : 'user'; ?>
                        <label style="margin-right:10px;">
                            <input type="radio" name="config[<?php echo $index; ?>][scope]" value="user" <?php checked($scope === 'user'); ?>>
                            Moi uniquement (par défaut)
                        </label>
                        <label style="margin-right:10px;">
                            <input type="radio" name="config[<?php echo $index; ?>][scope]" value="all" <?php checked($scope === 'all'); ?>>
                            Tous les utilisateurs
                        </label>
                        <label>
                            <input type="radio" name="config[<?php echo $index; ?>][scope]" value="role" <?php checked($scope === 'role'); ?>>
                            Un rôle spécifique
                        </label>
                        <div style="margin-top:6px;">
                            <label>Rôle:&nbsp;
                                <select name="config[<?php echo $index; ?>][role]">
                                    <option value="">— Sélectionner un rôle —</option>
                                    <?php $sel_role = isset($config['role']) ? $config['role'] : ''; ?>
                                    <?php foreach ($roles_list as $role_key => $role_obj): ?>
                                        <option value="<?php echo esc_attr($role_key); ?>" <?php selected($sel_role === $role_key); ?>><?php echo esc_html(translate_user_role($role_obj['name'])); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </fieldset>
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
        const configCount = container.querySelectorAll('.menu-config').length;
        const base = container.querySelector('.menu-config');
        const template = base.cloneNode(true);

        template.setAttribute('data-index', String(configCount));
        template.setAttribute('draggable', 'true');
        template.querySelector('h3').textContent = `Configuration ${configCount + 1}`;

        const inputs = template.querySelectorAll('input');
        inputs.forEach(input => {
            if (input.name === 'default_config') {
                input.value = String(configCount);
                input.checked = false;
                return;
            }
            input.name = input.name.replace(/config\[\d+\]/, `config[${configCount}]`);
            if (input.type === 'text') {
                input.value = '';
            } else if (input.type === 'checkbox') {
                input.checked = false;
            } else if (input.type === 'radio') {
                input.checked = (input.value === 'user');
            }
        });

        // Décoche toutes les cases des menus
        const checkboxes = template.querySelectorAll('input[type="checkbox"][name*="[menus]"]');
        checkboxes.forEach(cb => cb.checked = false);

        container.appendChild(template);
        bindConfigItem(template);
    }

    function renumberConfigurations() {
        const container = document.getElementById('menu-configurations');
        const items = Array.from(container.querySelectorAll('.menu-config'));
        items.forEach((item, idx) => {
            item.setAttribute('data-index', String(idx));
            const title = item.querySelector('h3');
            if (title) title.textContent = `Configuration ${idx + 1}`;
            const inputs = item.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.name === 'default_config') {
                    input.value = String(idx);
                    return;
                }
                input.name = input.name.replace(/config\[\d+\]/, `config[${idx}]`);
            });
        });
    }

    function bindConfigItem(el) {
        // Supprimer
        const btn = el.querySelector('.remove-config');
        if (btn) {
            btn.onclick = function() {
                el.parentNode.removeChild(el);
                renumberConfigurations();
            };
        }

        // Drag & drop
        el.addEventListener('dragstart', (e) => {
            el.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        el.addEventListener('dragend', () => {
            el.classList.remove('dragging');
        });
    }

    // Activer DnD sur le conteneur
    (function(){
        const container = document.getElementById('menu-configurations');
        container.addEventListener('dragover', (e) => {
            e.preventDefault();
            const dragging = container.querySelector('.dragging');
            if (!dragging) return;
            const afterElement = getDragAfterElement(container, e.clientY);
            if (afterElement == null) {
                container.appendChild(dragging);
            } else {
                container.insertBefore(dragging, afterElement);
            }
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.menu-config:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Lier existants
        container.querySelectorAll('.menu-config').forEach(bindConfigItem);

        // Renuméroter avant soumission
        const form = container.closest('form');
        if (form) {
            form.addEventListener('submit', function(){
                renumberConfigurations();
            });
        }
    })();
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
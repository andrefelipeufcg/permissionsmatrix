<?php
// O nome das funções DEVE conter o nome exato da pasta do plugin (plugin_permissionsmatrix)

/**
 * Função principal de inicialização do plugin
 */
function plugin_init_permissionsmatrix() {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS["csrf_compliant"]["permissionsmatrix"] = true;
    $PLUGIN_HOOKS['menu_toadd']['permissionsmatrix'] = ['tools' => 'GlpiPlugin\Permissionsmatrix\Matriz'];
    Plugin::registerClass('GlpiPlugin\Permissionsmatrix\Profile', ['addtabon' => 'Profile']);
}

/**
 * Define a versão, autor e requisitos do plugin
 */
function plugin_version_permissionsmatrix() {
    return [
        'name'           => __('Permissions Matrix', 'permissionsmatrix'),
        'version'        => '1.1.4',
        'author'         => 'andrefelipeufcg',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://github.com/andrefelipeufcg/permissionsmatrix',
        'minGlpiVersion' => '10.0.0' // Funciona para a v10 e v11
    ];
}

/**
 * Verifica os pré-requisitos antes de deixar o usuário clicar em "Instalar"
 */
function plugin_permissionsmatrix_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '10.0.0', '<')) {
        echo __("This plugin requires GLPI 10.0.0 or higher.", "permissionsmatrix");
        return false;
    }
    return true;
}

/**
 * Verifica se a configuração inicial está correta
 */
function plugin_permissionsmatrix_check_config() {
    return true;
}
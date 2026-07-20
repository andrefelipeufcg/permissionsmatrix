<?php

/**
 * Rotina de instalação
 * Aqui você cria tabelas no banco de dados quando necessário.
 */
function plugin_permissionsmatrix_install() {
    global $DB;
    
    // Concede acesso apenas ao perfil Super-Admin (requisito de segurança do Marketplace)
    // Compatibilidade: getSuperAdminProfilesId retorna um array no GLPI 11 e não existe no GLPI 10.
    $superadmin_ids = [];
    if (method_exists('\Profile', 'getSuperAdminProfilesId')) {
        $superadmin_ids = \Profile::getSuperAdminProfilesId();
    } else {
        $superadmin_ids = [4]; // Perfil Super-Admin padrão no GLPI 10
    }
    
    foreach ((array)$superadmin_ids as $superadmin_id) {
        $iterator = $DB->request([
            'SELECT' => 'id',
            'FROM'   => 'glpi_profilerights',
            'WHERE'  => [
                'profiles_id' => $superadmin_id,
                'name'        => 'plugin_permissionsmatrix'
            ]
        ]);
        
        if (count($iterator) == 0) {
            $DB->insert('glpi_profilerights', [
                'profiles_id' => $superadmin_id,
                'name'        => 'plugin_permissionsmatrix',
                'rights'      => 1
            ]);
        }
    }
    
    return true; 
}

/**
 * Rotina de desinstalação
 * Aqui você limparia as tabelas criadas na instalação.
 */
function plugin_permissionsmatrix_uninstall() {
    global $DB;
    $DB->delete('glpi_profilerights', ['name' => 'plugin_permissionsmatrix']);
    return true;
}
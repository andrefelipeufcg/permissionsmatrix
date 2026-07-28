<?php

$inc = __DIR__ . '/../../../inc/includes.php';
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/inc/includes.php'; }
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../inc/includes.php'; }
include $inc;

// Configurações de segurança: apenas Super-Admin deve ter acesso a esta página
Session::checkRight("config", UPDATE);

$plugin = new Plugin();
if (!$plugin->isActivated('permissionsmatrix')) {
    Html::displayNotFoundError();
}

global $DB;

// Processar formulário
if (isset($_POST['update'])) {
    
    $allow_all = $_POST['allow_all'] ?? 0;
    
    // Obter IDs dos super admins
    $superadmin_ids = [];
    if (method_exists('\Profile', 'getSuperAdminProfilesId')) {
        $superadmin_ids = \Profile::getSuperAdminProfilesId();
    } else {
        $superadmin_ids = [4]; // Perfil Super-Admin padrão no GLPI 10
    }
    
    if ($allow_all == 1) {
        // Sim: conceder para todos
        // Delete existing rights for the plugin first
        $DB->delete('glpi_profilerights', ['name' => 'plugin_permissionsmatrix']);
        
        $iterator = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_profiles']);
        foreach ($iterator as $data) {
            $DB->insert('glpi_profilerights', [
                'profiles_id' => $data['id'],
                'name'        => 'plugin_permissionsmatrix',
                'rights'      => 1
            ]);
        }
        
        Session::addMessageAfterRedirect(__('Permissions granted to all profiles.', 'permissionsmatrix'), true, INFO);
        
    } else {
        // Não: conceder APENAS para super admins
        
        // Remove de todos primeiro
        $DB->delete('glpi_profilerights', ['name' => 'plugin_permissionsmatrix']);
        
        // Concede apenas aos super admins
        foreach ((array)$superadmin_ids as $superadmin_id) {
            $DB->insert('glpi_profilerights', [
                'profiles_id' => $superadmin_id,
                'name'        => 'plugin_permissionsmatrix',
                'rights'      => 1
            ]);
        }
        
        Session::addMessageAfterRedirect(__('Permissions removed from all profiles (except Super-Admin).', 'permissionsmatrix'), true, INFO);
    }
    
    Html::redirect('config.form.php');
}

// Obter IDs dos super admins para verificar o estado atual
$superadmin_ids = [];
if (method_exists('\Profile', 'getSuperAdminProfilesId')) {
    $superadmin_ids = \Profile::getSuperAdminProfilesId();
} else {
    $superadmin_ids = [4];
}

// Verifica se existe algum perfil que não é super-admin com a permissão
$allow_all_state = 0;
$query = [
    'COUNT' => 'cpt',
    'FROM'  => 'glpi_profilerights',
    'WHERE' => [
        'name'   => 'plugin_permissionsmatrix',
        'rights' => 1
    ]
];
$result = $DB->request($query)->current();
if ($result['cpt'] > count((array)$superadmin_ids)) {
    $allow_all_state = 1;
}

Html::header(__('Permissions Matrix', 'permissionsmatrix'), 'config.form.php', "config", "plugins");

\Glpi\Application\View\TemplateRenderer::getInstance()->display('@permissionsmatrix/config_form.html.twig', [
    'action_url' => 'config.form.php',
    'csrf_token' => Session::getNewCSRFToken(),
    'allow_all'  => $allow_all_state
]);

Html::footer();

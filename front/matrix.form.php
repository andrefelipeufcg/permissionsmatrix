<?php
// 1. Inicializa o ambiente do GLPI
$inc = __DIR__ . '/../../../inc/includes.php';
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/inc/includes.php'; }
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../inc/includes.php'; }
include $inc;

// 2. Trava de segurança: Garante que quem tem permissão pode acessar
Session::checkRight('plugin_permissionsmatrix', READ);

use Glpi\Application\View\TemplateRenderer;



// 4. Renderiza o cabeçalho nativo do GLPI
Html::header(__('Permissions Matrix', 'permissionsmatrix'), $_SERVER['PHP_SELF'], "tools", \GlpiPlugin\Permissionsmatrix\Matriz::class);

ob_start();
Entity::dropdown([
    'name' => 'entities_id_profiles', 
    'id'   => 'dropdown_entities_profiles'
]);
$dropdown_profiles = ob_get_clean();

ob_start();
Entity::dropdown([
    'name' => 'entities_id_groups', 
    'id'   => 'dropdown_entities_groups'
]);
$dropdown_groups = ob_get_clean();

TemplateRenderer::getInstance()->display('@permissionsmatrix/matrix_form.html.twig', [
    'dropdown_profiles' => $dropdown_profiles,
    'dropdown_groups'   => $dropdown_groups,
    'csrf_token'        => Session::getNewCSRFToken(),
    'inline_js'         => file_get_contents(GLPI_ROOT . '/plugins/permissionsmatrix/js/permissionsmatrix.js')
]);

Html::footer();
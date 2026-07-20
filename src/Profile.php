<?php
namespace GlpiPlugin\Permissionsmatrix;

if (!defined('GLPI_ROOT')) { die("Acesso negado"); }

class Profile extends \CommonGLPI {

    static function getTypeName($nb = 0) {
        return __('Permissions Matrix', 'permissionsmatrix');
    }

    static function getIcon() {
        return "fas fa-table";
    }

    function getTabNameForItem(\CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == 'Profile') {
            // Usa o construtor oficial do GLPI para alinhar o ícone e o texto perfeitamente
            return self::createTabEntry(self::getTypeName(), 0, self::getIcon());
        }
        return '';
    }

    static function displayTabContentForItem(\CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $DB;
        $profile_id = $item->getID();

        // Busca o status atual no banco
        $current_right = 0;
        $res = $DB->request('glpi_profilerights', ['profiles_id' => $profile_id, 'name' => 'plugin_permissionsmatrix']);
        if ($row = $res->current()) {
            $current_right = $row['rights'];
        }

        // Desenha o Formulário
        $url_form = \Plugin::getWebDir('permissionsmatrix') . '/front/profile.form.php';
        
        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@permissionsmatrix/profile_form.html.twig', [
            'url_form'      => $url_form,
            'csrf_token'    => \Session::getNewCSRFToken(),
            'profile_id'    => $profile_id,
            'current_right' => $current_right
        ]);

        return true;
    }
}
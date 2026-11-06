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
            if (version_compare(GLPI_VERSION, '11.0.0', '>=')) {
                return self::createTabEntry(self::getTypeName(), 0, null, self::getIcon());
            }
            return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(\CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $DB;
        $profile_id = $item->getID();

        // Busca o status atual no banco
        $current_right = 0;
        $res = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => ['profiles_id' => $profile_id, 'name' => 'plugin_permissionsmatrix']
        ]);
        if ($row = $res->current()) {
            $current_right = $row['rights'];
        }

        $plugin_dir = Matriz::getPluginURL();
        $url_form = $plugin_dir . '/front/profile.form.php';
        
        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@permissionsmatrix/profile_form.html.twig', [
            'url_form'      => $url_form,
            'plugin_url'    => $plugin_dir,
            'csrf_token'    => Matriz::getCSRFToken(),
            'profile_id'    => $profile_id,
            'current_right' => $current_right,
            'inline_css'    => file_get_contents(__DIR__ . '/../css/permissionsmatrix.css'),
            'inline_js'     => file_get_contents(__DIR__ . '/../js/permissionsmatrix.js')
        ]);

        return true;
    }
}
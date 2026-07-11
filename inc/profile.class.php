<?php
if (!defined('GLPI_ROOT')) { die("Acesso negado"); }

class PluginPermissionsmatrixProfile extends CommonGLPI {

    static function getTypeName($nb = 0) {
        return __('Permissions Matrix', 'permissionsmatrix');
    }

    static function getIcon() {
        return "fas fa-table";
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == 'Profile') {
            // Usa o construtor oficial do GLPI para alinhar o ícone e o texto perfeitamente
            return self::createTabEntry(self::getTypeName(), 0, self::getIcon());
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $DB;
        $profile_id = $item->getID();

        // Busca o status atual no banco
        $current_right = 0;
        $res = $DB->request('glpi_profilerights', ['profiles_id' => $profile_id, 'name' => 'plugin_permissionsmatrix']);
        if ($row = $res->current()) {
            $current_right = $row['rights'];
        }

        // Desenha o Formulário
        $url_form = Plugin::getWebDir('permissionsmatrix') . '/front/profile.form.php';
        
        echo "<form method='post' action='$url_form'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo Html::hidden('profiles_id', ['value' => $profile_id]);
        echo Html::hidden('update_matriz_right', ['value' => 1]);
        
        echo "<div class='center'><table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'><th colspan='2'>" . __('Plugin Access Configuration', 'permissionsmatrix') . "</th></tr>";
        echo "<tr class='tab_bg_2'>";
        echo "<td class='center' style='width: 50%;'>" . __('Can view the permissions matrix?', 'permissionsmatrix') . "</td>";
        echo "<td class='center'>";
        echo "<label style='margin-right: 20px; cursor: pointer;'>";
        echo "<input type='radio' name='matriz_read' value='1' " . ($current_right ? "checked" : "") . "> " . __('Yes', 'permissionsmatrix');
        echo "</label>";
        echo "<label style='cursor: pointer;'>";
        echo "<input type='radio' name='matriz_read' value='0' " . (!$current_right ? "checked" : "") . "> " . __('No', 'permissionsmatrix');
        echo "</label>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr class='tab_bg_2'><td colspan='2' class='center'>";
        echo Html::submit(__('Save', 'permissionsmatrix'), ['name' => 'update', 'class' => 'submit']);
        echo "</td></tr>";
        echo "</table></div>";
        Html::closeForm();

        return true;
    }
}
<?php

namespace GlpiPlugin\Permissionsmatrix;

// Trava de segurança padrão do GLPI para impedir acesso direto ao arquivo
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

// GLPI 12 exige $rightname tipada (string); GLPI 10/11 proíbe o tipo.
if ((new \ReflectionProperty(\CommonGLPI::class, 'rightname'))->hasType()) {
    abstract class MatrizBase extends \CommonGLPI {
        public static string $rightname = 'plugin_permissionsmatrix';
    }
} else {
    abstract class MatrizBase extends \CommonGLPI {
        public static $rightname = 'plugin_permissionsmatrix';
    }
}

/**
 * Classe principal do plugin.
 */
class Matriz extends MatrizBase {

    /**
     * Define o nome que vai aparecer no menu do GLPI
     */
    static function getMenuName() {
        return __('Permissions Matrix', 'permissionsmatrix');
    }

    /**
     * Define o nome interno do tipo (padrão do framework)
     */
    static function getTypeName($nb = 0) {
        return __('Permissions Matrix', 'permissionsmatrix');
    }

    /**
     * Define o ícone que vai aparecer ao lado do nome no menu (opcional na v10+)
     */
    static function getIcon() {
        return "fas fa-table"; // Ícone de tabela do FontAwesome
    }

    /**
     * Constrói o conteúdo do menu, indicando para qual página ele aponta
     */
    static function getMenuContent() {
        // Se o perfil logado NÃO tiver permissão, aborta a criação do botão
        if (!self::canView()) {
            return false;
        }
        
        $dir = method_exists('\Plugin', 'getWebDir') ? \Plugin::getWebDir('permissionsmatrix', false) : \Plugin::getPhpDir('permissionsmatrix', false);
        return [
            'title' => self::getMenuName(),
            'page'  => $dir . '/front/matrix.form.php',
            'icon'  => self::getIcon()
        ];
    }

    /**
     * Controle de Acesso (Segurança)
     * Define quem pode ver este botão no menu.
     * CORREÇÃO: Adicionado o ": bool" exigido pelo PHP 8 / GLPI 11
     */
    #[\ReturnTypeWillChange]
    public static function canView(): bool {
        // Agora o GLPI só exibe o menu se o perfil tiver a permissão 1 (READ)
        return \Session::haveRight('plugin_permissionsmatrix', READ);
    }

    /**
     * Sobrescreve a resolução de URL padrão do GLPI, já que o nome do arquivo mudou para matrix.form.php
     */
    public static function getSearchURL($full = true) {
        $dir = method_exists('\Plugin', 'getWebDir') ? \Plugin::getWebDir('permissionsmatrix', $full) : \Plugin::getPhpDir('permissionsmatrix', false);
        return $dir . '/front/matrix.form.php';
    }

    public static function getFormURL($full = true) {
        $dir = method_exists('\Plugin', 'getWebDir') ? \Plugin::getWebDir('permissionsmatrix', $full) : \Plugin::getPhpDir('permissionsmatrix', false);
        return $dir . '/front/matrix.form.php';
    }

    public static function getFormURLWithID($id = 0, $full = true) {
        $dir = method_exists('\Plugin', 'getWebDir') ? \Plugin::getWebDir('permissionsmatrix', $full) : \Plugin::getPhpDir('permissionsmatrix', false);
        return $dir . '/front/matrix.form.php?id=' . $id;
    }
}
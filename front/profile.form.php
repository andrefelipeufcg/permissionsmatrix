<?php
$inc = __DIR__ . '/../../../inc/includes.php';
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/inc/includes.php'; }
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../inc/includes.php'; }
include $inc;

// Verifica se está logado e se tem permissão de gerenciar perfis
Session::checkRight("profile", UPDATE);

global $DB;

if (isset($_POST['update_matriz_right'])) {
    $profile_id = intval($_POST['profiles_id']);
    
    $profile = new Profile();
    if (!$profile->getFromDB($profile_id)) {
        Session::addMessageAfterRedirect(__('Invalid profile', 'permissionsmatrix'), false, ERROR);
        Html::back();
    }
    
    $right = (isset($_POST['matriz_read']) && $_POST['matriz_read'] == '1') ? 1 : 0;
    
    // Remove a permissão antiga
    $DB->delete('glpi_profilerights', [
        'profiles_id' => $profile_id,
        'name'        => 'plugin_permissionsmatrix'
    ]);
    
    // Insere o status atualizado (Garante que o 0 fique salvo se desmarcado)
    $DB->insert('glpi_profilerights', [
        'profiles_id' => $profile_id,
        'name'        => 'plugin_permissionsmatrix',
        'rights'      => $right
    ]);
    
    Html::back();
}
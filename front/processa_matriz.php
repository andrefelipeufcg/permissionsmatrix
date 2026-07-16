<?php
// Aumenta o limite de memória temporariamente para suportar entidades com milhares de usuários (Evita erro 500 no CSV)
ini_set('memory_limit', '512M');

include ("../../../inc/includes.php");
// Verifica se tem permissão (Security Fix)
Session::checkRight('plugin_permissionsmatrix', READ);

// Verifica se o formulário original, o botão de exportar ou a paginação foram acionados
if (!isset($_POST['gerar_matriz']) && !isset($_POST['exportar_csv'])) {
    Html::redirect("matriz.php");
    exit;
}

$is_export = isset($_POST['exportar_csv']);

// Filtro de Entidades: Previne Data Leak (Security Fix)
// O POST pode enviar uma string (ID único) ou um array, então forçamos para array
$post_perfis = (array)($_POST['entities_id_profiles'] ?? []);
$entidade_perfis = [];
foreach ($post_perfis as $id) {
    if (Session::haveAccessToEntity($id)) {
        $entidade_perfis[] = $id;
    }
}

$post_grupos = (array)($_POST['entities_id_groups'] ?? []);
$entidade_grupos = [];
foreach ($post_grupos as $id) {
    if (Session::haveAccessToEntity($id)) {
        $entidade_grupos[] = $id;
    }
}

// Se o usuário tentar forjar entidades às quais não tem acesso, o array fica vazio.
// Nesse caso, o GLPI geraria erro de "Empty IN". Vamos abortar e voltar.
if (empty($entidade_perfis) || empty($entidade_grupos)) {
    Session::addMessageAfterRedirect(__('You do not have access to the requested entities.', 'permissionsmatrix'), false, ERROR);
    Html::redirect("matriz.php");
    exit;
}

// Controle de Paginação
$pagina_atual = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$limite_por_pagina = 100;

global $DB;

// =========================================================
// 1. BUSCA DE GRUPOS VÁLIDOS
// =========================================================
$iterator_grupos = $DB->request([
    'SELECT' => ['id', 'name'],
    'FROM'   => 'glpi_groups',
    'WHERE'  => ['entities_id' => $entidade_grupos]
]);

$dicionario_grupos = [];
$nomes_grupos = [];
foreach ($iterator_grupos as $linha) {
    $dicionario_grupos[$linha['id']] = $linha['name'];
    $nomes_grupos[] = $linha['name'];
}
sort($nomes_grupos);

// =========================================================
// 2. BUSCA DE PERFIS
// =========================================================
$iterator_perfis = $DB->request([
    'SELECT'     => ['pu.users_id', 'p.name AS profile_name'],
    'FROM'       => 'glpi_profiles_users AS pu',
    'INNER JOIN' => [
        'glpi_profiles AS p' => ['ON' => ['pu' => 'profiles_id', 'p' => 'id']]
    ],
    'WHERE'      => ['pu.entities_id' => $entidade_perfis]
]);

$mapa_usuarios = [];
$nomes_perfis = [];
foreach ($iterator_perfis as $linha) {
    $uid = $linha['users_id'];
    $nome_perfil = $linha['profile_name'];
    
    if (!isset($mapa_usuarios[$uid])) {
        $mapa_usuarios[$uid] = ['perfis' => [], 'grupos' => []];
    }
    $mapa_usuarios[$uid]['perfis'][$nome_perfil] = true;
    
    if (!in_array($nome_perfil, $nomes_perfis)) {
        $nomes_perfis[] = $nome_perfil;
    }
}
sort($nomes_perfis);

// =========================================================
// 3. BUSCA DE VÍNCULOS DE GRUPOS
// =========================================================
if (!empty($mapa_usuarios) && !empty($dicionario_grupos)) {
    $iterator_vinculos_grupos = $DB->request([
        'SELECT' => ['users_id', 'groups_id'],
        'FROM'   => 'glpi_groups_users',
        'WHERE'  => [
            'users_id'  => array_keys($mapa_usuarios),
            'groups_id' => array_keys($dicionario_grupos)
        ]
    ]);
    foreach ($iterator_vinculos_grupos as $linha) {
        $nome_curto_grupo = $dicionario_grupos[$linha['groups_id']];
        $mapa_usuarios[$linha['users_id']]['grupos'][$nome_curto_grupo] = true;
    }
}

// =========================================================
// 4. BUSCA DE DADOS CADASTRAIS
// =========================================================
if (!empty($mapa_usuarios)) {
    $iterator_users = $DB->request([
        'SELECT' => ['id', 'name AS login', 'firstname', 'realname', 'is_active'],
        'FROM'   => 'glpi_users',
        'WHERE'  => ['id' => array_keys($mapa_usuarios)]
    ]);
    foreach ($iterator_users as $linha) {
        $uid = $linha['id'];
        $mapa_usuarios[$uid]['login']     = $linha['login'];
        $mapa_usuarios[$uid]['firstname'] = $linha['firstname'];
        $mapa_usuarios[$uid]['realname']  = $linha['realname'];
        $mapa_usuarios[$uid]['ativo']     = $linha['is_active'] ? __('Yes', 'permissionsmatrix') : __('No', 'permissionsmatrix');
    }
}

// Ordenar os usuários em ordem alfabética ignorando acentos (Nome + Sobrenome)
uasort($mapa_usuarios, function($a, $b) {
    // Tabela de conversão para ignorar acentos na hora da ordenação
    $acentos = [
        'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
        'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e',
        'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i',
        'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o',
        'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u',
        'ç'=>'c', 'ñ'=>'n'
    ];

    $nomeA = mb_strtolower(trim(($a['firstname'] ?? '') . ' ' . ($a['realname'] ?? '')), 'UTF-8');
    $nomeB = mb_strtolower(trim(($b['firstname'] ?? '') . ' ' . ($b['realname'] ?? '')), 'UTF-8');

    $nomeA_limpo = strtr($nomeA, $acentos);
    $nomeB_limpo = strtr($nomeB, $acentos);

    return strcmp($nomeA_limpo, $nomeB_limpo);
});

// Cálculos de Paginação
$total_usuarios = count($mapa_usuarios);
$total_paginas = ceil($total_usuarios / $limite_por_pagina);

// =========================================================
// 5. MODO EXPORTAÇÃO (Se o botão de Download foi clicado)
// =========================================================
if ($is_export) {
    // O PHP lê os filtros que o JS enviou e descarta as colunas não selecionadas
    if (isset($_POST['perfis_ativos']) && $_POST['perfis_ativos'] !== '') {
        $perfis_ativos = json_decode($_POST['perfis_ativos'], true);
        if (is_array($perfis_ativos)) {
            $nomes_perfis = array_intersect($nomes_perfis, $perfis_ativos);
        }
    }
    if (isset($_POST['grupos_ativos']) && $_POST['grupos_ativos'] !== '') {
        $grupos_ativos = json_decode($_POST['grupos_ativos'], true);
        if (is_array($grupos_ativos)) {
            $nomes_grupos = array_intersect($nomes_grupos, $grupos_ativos);
        }
    }

    $nome_arquivo = "matriz_permissoes_" . date("Ymd_His") . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 

    // Cabeçalho do CSV
    $cabecalho = array_merge([__('Active', 'permissionsmatrix'), __('User', 'permissionsmatrix'), __('First name', 'permissionsmatrix'), __('Last name', 'permissionsmatrix')], $nomes_perfis, $nomes_grupos);
    fputcsv($output, $cabecalho, ';'); 

    // O CSV continua exportando 100% da lista ($mapa_usuarios inteiro)
    foreach ($mapa_usuarios as $uid => $dados) {
        // Verifica se o usuário tem X em ALGUMA das colunas que sobraram ativas no filtro
        $tem_x = false;
        foreach ($nomes_perfis as $p) {
            if (isset($dados['perfis'][$p])) { $tem_x = true; break; }
        }
        if (!$tem_x) {
            foreach ($nomes_grupos as $g) {
                if (isset($dados['grupos'][$g])) { $tem_x = true; break; }
            }
        }

        // Se a pessoa não tem 'X' nas colunas selecionadas, a linha não vai para o CSV
        if ($tem_x) {
            // Função local para prevenir CSV Formula Injection
            $escape_csv = function($val) {
                if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@'])) {
                    return "'" . $val;
                }
                return $val;
            };
            
            $linha = [
                $dados['ativo'] ?? __('No', 'permissionsmatrix'), 
                $escape_csv($dados['login'] ?? ''), 
                $escape_csv($dados['firstname'] ?? ''), 
                $escape_csv($dados['realname'] ?? '')
            ];
            foreach ($nomes_perfis as $p) $linha[] = isset($dados['perfis'][$p]) ? 'X' : '';
            foreach ($nomes_grupos as $g) $linha[] = isset($dados['grupos'][$g]) ? 'X' : '';
            fputcsv($output, $linha, ';');
        }
    }
    fclose($output);
    exit;
}

// =========================================================
// 6. MODO VISUALIZAÇÃO (Tela HTML do GLPI)
// =========================================================
use Glpi\Application\View\TemplateRenderer;

Html::header(__('Permissions Matrix', 'permissionsmatrix'), $_SERVER['PHP_SELF'], "tools", \GlpiPlugin\Permissionsmatrix\Matriz::class);

// Fatiamento da tela (Paginação)
$usuarios_pagina = array_slice($mapa_usuarios, ($pagina_atual - 1) * $limite_por_pagina, $limite_por_pagina, true);

// Cálculos para exibir a posição atual (Exibindo X - Y de Z usuários)
$inicio_exibicao = (($pagina_atual - 1) * $limite_por_pagina) + 1;
$fim_exibicao = min($pagina_atual * $limite_por_pagina, $total_usuarios);
$texto_exibindo = sprintf(
    __('Showing %1$s - %2$s of %3$s users', 'permissionsmatrix'), 
    "<b>$inicio_exibicao</b>", 
    "<b>$fim_exibicao</b>", 
    "<b>$total_usuarios</b>"
);

TemplateRenderer::getInstance()->display('@permissionsmatrix/matriz_result.html.twig', [
    'total_usuarios'   => $total_usuarios,
    'nomes_perfis'     => $nomes_perfis,
    'nomes_grupos'     => $nomes_grupos,
    'entidade_perfis'  => $entidade_perfis,
    'entidade_grupos'  => $entidade_grupos,
    'usuarios_pagina'  => $usuarios_pagina,
    'pagina_atual'     => $pagina_atual,
    'total_paginas'    => $total_paginas,
    'texto_exibindo'   => $texto_exibindo,
    'csrf_token'       => Session::getNewCSRFToken()
]);

Html::footer();
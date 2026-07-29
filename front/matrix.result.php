<?php
// Limite de memória removido conforme revisão de segurança (Unbounded memory_limit override)

$inc = __DIR__ . '/../../../inc/includes.php';
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/inc/includes.php'; }
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../inc/includes.php'; }
include $inc;
// Verifica se tem permissão (Security Fix)
Session::checkRight('plugin_permissionsmatrix', READ);

// Verifica se o formulário original, o botão de exportar ou a paginação foram acionados
if (!isset($_POST['gerar_matriz']) && !isset($_POST['exportar_csv'])) {
    Html::redirect("matrix.form.php");
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
    Html::redirect("matrix.form.php");
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

// =========================================================
// 5. FILTRO DE COLUNAS (Server-Side)
// =========================================================
// Usa arrays nativos do PHP (name='perfis_ativos[]') em vez de JSON
// para evitar problemas de sanitização do GLPI 10
$filtro_perfis_ativos = $nomes_perfis; // Padrão: todos os perfis ativos
$filtro_grupos_ativos = $nomes_grupos; // Padrão: todos os grupos ativos
$filtro_ativo = isset($_POST['filtro_ativo']) && $_POST['filtro_ativo'] === '1';

if ($filtro_ativo) {
    // Quando o filtro está ativo, lê os arrays do POST
    // Se o array não existir no POST, significa que o usuário desmarcou todos
    $post_perfis = isset($_POST['perfis_ativos']) ? (array)$_POST['perfis_ativos'] : [];
    $post_grupos = isset($_POST['grupos_ativos']) ? (array)$_POST['grupos_ativos'] : [];

    // Limpa valores sanitizados pelo GLPI 10 (stripslashes + html_entity_decode)
    $limpar = function($v) {
        $v = is_string($v) ? stripslashes($v) : (string)$v;
        return html_entity_decode($v, ENT_QUOTES, 'UTF-8');
    };
    $post_perfis = array_map($limpar, $post_perfis);
    $post_grupos = array_map($limpar, $post_grupos);

    // Intersecta com os nomes reais do banco para segurança
    $filtro_perfis_ativos = array_values(array_intersect($nomes_perfis, $post_perfis));
    $filtro_grupos_ativos = array_values(array_intersect($nomes_grupos, $post_grupos));
}

// Aplica o filtro: remove usuários que não tem nenhum X nas colunas selecionadas
if ($filtro_ativo) {
    $mapa_usuarios_filtrado = [];
    foreach ($mapa_usuarios as $uid => $dados) {
        $tem_x = false;
        foreach ($filtro_perfis_ativos as $p) {
            if (isset($dados['perfis'][$p])) { $tem_x = true; break; }
        }
        if (!$tem_x) {
            foreach ($filtro_grupos_ativos as $g) {
                if (isset($dados['grupos'][$g])) { $tem_x = true; break; }
            }
        }
        if ($tem_x) {
            $mapa_usuarios_filtrado[$uid] = $dados;
        }
    }
    $mapa_usuarios = $mapa_usuarios_filtrado;
}

// Cálculos de Paginação (agora sobre os dados já filtrados)
$total_usuarios = count($mapa_usuarios);
$total_paginas = max(1, ceil($total_usuarios / $limite_por_pagina));
if ($pagina_atual > $total_paginas) { $pagina_atual = $total_paginas; }

// Fatiamento da tela (Paginação)
$usuarios_pagina = array_slice($mapa_usuarios, ($pagina_atual - 1) * $limite_por_pagina, $limite_por_pagina, true);

// Cálculos para exibir a posição atual (Exibindo X - Y de Z usuários)
$inicio_exibicao = (($pagina_atual - 1) * $limite_por_pagina) + 1;
$fim_exibicao = min($pagina_atual * $limite_por_pagina, $total_usuarios);
if ($total_usuarios == 0) { $inicio_exibicao = 0; $fim_exibicao = 0; }
$texto_exibindo = sprintf(
    __('Showing %1$s - %2$s of %3$s users', 'permissionsmatrix'), 
    "<b>$inicio_exibicao</b>", 
    "<b>$fim_exibicao</b>", 
    "<b>$total_usuarios</b>"
);

use Glpi\Application\View\TemplateRenderer;

$dados_tabela = [
    'filtro_perfis_ativos' => $filtro_perfis_ativos,
    'filtro_grupos_ativos' => $filtro_grupos_ativos,
    'usuarios_pagina'      => $usuarios_pagina,
    'pagina_atual'         => $pagina_atual,
    'total_paginas'        => $total_paginas,
    'texto_exibindo'       => $texto_exibindo,
];

// =========================================================
// 6. MODO EXPORTAÇÃO (Se o botão de Download foi clicado)
// =========================================================
if ($is_export) {
    // Na exportação, usa os filtros já aplicados acima
    $nomes_perfis_csv = $filtro_perfis_ativos;
    $nomes_grupos_csv = $filtro_grupos_ativos;

    $nome_arquivo = "matriz_permissoes_" . date("Ymd_His") . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 

    // Função local para prevenir CSV Formula Injection
    $escape_csv = function($val) {
        $val = preg_replace('/^[\s\x00-\x1F]+/', '', (string)$val);
        if (strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $val;
        }
        return $val;
    };

    // Cabeçalho do CSV
    $cabecalho = array_merge([__('Active', 'permissionsmatrix'), __('User', 'permissionsmatrix'), __('First name', 'permissionsmatrix'), __('Last name', 'permissionsmatrix')], $nomes_perfis_csv, $nomes_grupos_csv);
    $cabecalho = array_map($escape_csv, $cabecalho);
    fputcsv($output, $cabecalho, ';'); 

    // O CSV exporta 100% dos usuários filtrados (sem paginação)
    foreach ($mapa_usuarios as $uid => $dados) {
        $linha = [
            $escape_csv($dados['ativo'] ?? __('No', 'permissionsmatrix')), 
            $escape_csv($dados['login'] ?? ''), 
            $escape_csv($dados['firstname'] ?? ''), 
            $escape_csv($dados['realname'] ?? '')
        ];
        foreach ($nomes_perfis_csv as $p) $linha[] = isset($dados['perfis'][$p]) ? 'X' : '';
        foreach ($nomes_grupos_csv as $g) $linha[] = isset($dados['grupos'][$g]) ? 'X' : '';
        fputcsv($output, $linha, ';');
    }
    fclose($output);
    exit;
}

// =========================================================
// 7. MODO AJAX (Requisição assíncrona do JavaScript)
// =========================================================
$is_ajax = !empty($_POST['pm_ajax']);

if ($is_ajax) {
    // Limpa qualquer saída anterior do GLPI
    while (ob_get_level() > 0) { ob_end_clean(); }

    ob_start();
    TemplateRenderer::getInstance()->display('@permissionsmatrix/matrix_result_table.html.twig', $dados_tabela);
    $html = ob_get_clean();

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'html'            => $html,
        'total_usuarios'  => $total_usuarios,
    ]);
    exit;
}

// =========================================================
// 8. MODO VISUALIZAÇÃO (Tela HTML do GLPI - Carga inicial)
// =========================================================
Html::header(__('Permissions Matrix', 'permissionsmatrix'), $_SERVER['PHP_SELF'], "tools", \GlpiPlugin\Permissionsmatrix\Matriz::class);

TemplateRenderer::getInstance()->display('@permissionsmatrix/matrix_result.html.twig', array_merge($dados_tabela, [
    'total_usuarios'      => $total_usuarios,
    'nomes_perfis'        => $nomes_perfis,
    'nomes_grupos'        => $nomes_grupos,
    'entidade_perfis'     => $entidade_perfis,
    'entidade_grupos'     => $entidade_grupos,
    'csrf_token'          => Session::getNewCSRFToken(),
    'inline_css'          => file_get_contents(__DIR__ . '/../css/permissionsmatrix.css'),
    'inline_js'           => file_get_contents(__DIR__ . '/../js/permissionsmatrix.js')
]));

Html::footer();
/**
 * Plugin Permissions Matrix - Global Scripts
 */

$(document).ready(function() {

    // ==========================================
    // 1. Requisição AJAX (Filtro + Paginação)
    // ==========================================
    var ajaxEmAndamento = null; // Referência para cancelar requisição anterior

    function carregarResultadoAjax(pagina) {
        // Cancela requisição anterior se ainda estiver em andamento
        if (ajaxEmAndamento && ajaxEmAndamento.readyState !== 4) {
            ajaxEmAndamento.abort();
        }

        $('#pm-loading-overlay').css('display', 'flex');

        var $dados = $('#pm-dados-ajax');
        if ($dados.length === 0) return;

        var url = $dados.data('url');
        var entidadesPerfis = String($dados.data('entidades-perfis')).split(',');
        var entidadesGrupos = String($dados.data('entidades-grupos')).split(',');

        // Coleta filtros marcados
        var perfis = [];
        $('#caixa-perfis .col-filter:checked').each(function() {
            perfis.push($(this).val());
        });
        var grupos = [];
        $('#caixa-grupos .col-filter:checked').each(function() {
            grupos.push($(this).val());
        });

        // Monta os dados do POST como array de {name, value}
        // para que o jQuery serialize corretamente como arrays PHP
        var postData = [];
        postData.push({name: 'gerar_matriz', value: '1'});
        
        // Coleta o token CSRF da página (essencial para o GLPI não bloquear o POST AJAX)
        var csrfToken = $('input[name="_glpi_csrf_token"]').val();
        if (csrfToken) {
            postData.push({name: '_glpi_csrf_token', value: csrfToken});
        }
        postData.push({name: 'pm_ajax', value: '1'});
        postData.push({name: 'filtro_ativo', value: '1'});
        postData.push({name: 'pagina', value: pagina});

        for (var i = 0; i < entidadesPerfis.length; i++) {
            if (entidadesPerfis[i] !== '') {
                postData.push({name: 'entities_id_profiles[]', value: entidadesPerfis[i]});
            }
        }
        for (var i = 0; i < entidadesGrupos.length; i++) {
            if (entidadesGrupos[i] !== '') {
                postData.push({name: 'entities_id_groups[]', value: entidadesGrupos[i]});
            }
        }
        for (var i = 0; i < perfis.length; i++) {
            postData.push({name: 'perfis_ativos[]', value: perfis[i]});
        }
        for (var i = 0; i < grupos.length; i++) {
            postData.push({name: 'grupos_ativos[]', value: grupos[i]});
        }

        ajaxEmAndamento = $.ajax({
            url: url,
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                // Substitui a tabela e paginação
                $('#pm-resultado').html(response.html);

                // Atualiza o contador de usuários
                $('#pm-total-usuarios').text(response.total_usuarios);

                // Atualiza os inputs do formulário de exportação
                atualizarFormExportacao(perfis, grupos);

                // Recalcula colunas congeladas e altura do layout
                recalcularPosicoesFixas();
                ajustarAlturaMatriz();

                $('#pm-loading-overlay').css('display', 'none');
            },
            error: function(xhr, status) {
                if (status !== 'abort') {
                    $('#pm-loading-overlay').css('display', 'none');
                    console.error("PermissionsMatrix AJAX Error:", status, xhr.responseText);
                }
            }
        });
    }

    // Atualiza os inputs hidden do formulário de exportação CSV
    function atualizarFormExportacao(perfis, grupos) {
        var $container = $('#pm-export-filtros');
        $container.empty();
        for (var i = 0; i < perfis.length; i++) {
            $('<input>').attr({type: 'hidden', name: 'perfis_ativos[]', value: perfis[i]}).appendTo($container);
        }
        for (var i = 0; i < grupos.length; i++) {
            $('<input>').attr({type: 'hidden', name: 'grupos_ativos[]', value: grupos[i]}).appendTo($container);
        }
    }

    // ==========================================
    // 2. Recálculo de Colunas Congeladas
    // ==========================================
    function recalcularPosicoesFixas() {
        var leftPositions = [];
        var currentLeft = 0;
        
        $('.pm-headerRow th.pm-freeze-col').each(function() {
            leftPositions.push(currentLeft);
            currentLeft += $(this).outerWidth();
        });

        $('.pm-freeze-col').each(function() {
            var index = $(this).data('colindex');
            if(leftPositions[index] !== undefined) {
                $(this).css('left', leftPositions[index] + 'px');
            }
        });
    }

    if($('.pm-freeze-col').length > 0) {
        recalcularPosicoesFixas();
    }

    // ==========================================
    // 3. Filtro de Colunas (AJAX assíncrono)
    // ==========================================
    // Ao marcar/desmarcar qualquer checkbox, faz AJAX (volta à página 1)
    $('.col-filter').on('change', function() {
        carregarResultadoAjax(1);
    });

    // Toggle do painel de filtros
    $('#btn-toggle-filtro').on('click', function() {
        $('#conteudo-filtro').slideToggle('fast');
    });

    // Marcar/Desmarcar todos (Perfis)
    $('.acao-massa-perfil').on('click', function(e) {
        e.preventDefault();
        var marcar = $(this).data('acao') === 'marcar';
        $('#caixa-perfis .col-filter').prop('checked', marcar);
        carregarResultadoAjax(1);
    });

    // Marcar/Desmarcar todos (Grupos)
    $('.acao-massa-grupo').on('click', function(e) {
        e.preventDefault();
        var marcar = $(this).data('acao') === 'marcar';
        $('#caixa-grupos .col-filter').prop('checked', marcar);
        carregarResultadoAjax(1);
    });

    // ==========================================
    // 4. Paginação (AJAX com Event Delegation)
    // ==========================================
    // Usa delegação de eventos porque os botões são recriados a cada AJAX
    $('#pm-resultado').on('click', '[data-page-action="go"]', function() {
        var p = $(this).data('page');
        if (p) carregarResultadoAjax(p);
    });

    $('#pm-resultado').on('change', '[data-page-action="jump"]', function() {
        var valor = parseInt($(this).val());
        var maximo = parseInt($(this).data('max-page'));
        if (isNaN(valor) || valor < 1) valor = 1;
        if (valor > maximo) valor = maximo;
        carregarResultadoAjax(valor);
    });

    // ==========================================
    // 5. Feedback nos Botões de Salvar (Config & Profile Forms)
    // ==========================================
    $('.pm-save-form').on('submit', function() {
        var btn = $(this).find('button[type="submit"], input[type="submit"]');
        var savingText = btn.data('saving-text') || 'Saving...';
        
        setTimeout(function() {
            if(btn.is('input')) {
                btn.val(savingText);
            } else {
                btn.text(savingText);
            }
            btn.css('pointer-events', 'none');
            btn.css('opacity', '0.7');
        }, 10);
    });

    // ==========================================
    // 6. Sincronização de Select2 (Formulário Gerador)
    // ==========================================
    var $selectPerfil = $('select[name="entities_id_profiles"]');
    var $selectGrupo = $('select[name="entities_id_groups"]');

    if($selectPerfil.length > 0 && $selectGrupo.length > 0) {
        $selectPerfil.on('change', function() {
            var valorSelecionado = $(this).val();
            
            // Tratamento caso seja select múltiplo ou não
            if(Array.isArray(valorSelecionado) && valorSelecionado.length > 0) {
                valorSelecionado = valorSelecionado[0];
            }

            var dadosSelect2 = $(this).select2('data');
            var textoSelecionado = (dadosSelect2 && dadosSelect2.length > 0) ? dadosSelect2[0].text : '';
            
            if (valorSelecionado && textoSelecionado !== '') {
                if ($selectGrupo.find("option[value='" + valorSelecionado + "']").length === 0) {
                    var novaOpcao = new Option(textoSelecionado, valorSelecionado, true, true);
                    $selectGrupo.append(novaOpcao);
                }
                $selectGrupo.val(valorSelecionado).trigger('change');
            }
        });
    }

    // ==========================================
    // 7. Altura Dinâmica do Layout (Correção Cross-version)
    // ==========================================
    function ajustarAlturaMatriz() {
        var $wrapper = $('.pm-app-wrapper');
        if ($wrapper.length > 0) {
            var topOffset = $wrapper.offset().top;
            // Calcula o espaço restante com precisão
            var alturaDisponivel = window.innerHeight - topOffset - 25; // 25px de margem inferior
            if (alturaDisponivel < 400) { alturaDisponivel = 400; } // Altura mínima de segurança
            $wrapper.css('height', alturaDisponivel + 'px');
        }
    }

    if ($('.pm-app-wrapper').length > 0) {
        ajustarAlturaMatriz();
        $(window).on('resize', ajustarAlturaMatriz);
        // Ocasionalmente o GLPI carrega imagens ou menus com delay, então recalcula após 500ms
        setTimeout(ajustarAlturaMatriz, 500); 
    }

});

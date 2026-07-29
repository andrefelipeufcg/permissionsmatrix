/**
 * Plugin Permissions Matrix - Global Scripts
 */

$(document).ready(function() {
    
    // ==========================================
    // 1. Pagination Logic (Matriz Result)
    // ==========================================
    window.irParaPagina = function(p) {
        $('.pm-btn-paginacao').prop('disabled', true).addClass('pm-btn-paginacao-disabled');
        $('input[type=number]').prop('disabled', true);
        var inputPagina = document.getElementById('input_pagina');
        if(inputPagina) {
            inputPagina.value = p;
            document.getElementById('form-paginacao').submit();
        }
    };

    window.pularParaPagina = function(valor, maximo) {
        var p = parseInt(valor);
        if (isNaN(p) || p < 1) { p = 1; }
        if (p > maximo) { p = maximo; }
        irParaPagina(p);
    };

    // Replace onclick attributes dynamically if needed, or rely on global scope for existing HTML
    $('[data-page-action="go"]').on('click', function() {
        var p = $(this).data('page');
        if(p) irParaPagina(p);
    });
    
    $('[data-page-action="jump"]').on('change', function() {
        var valor = $(this).val();
        var maximo = $(this).data('max-page');
        if(valor && maximo) pularParaPagina(valor, maximo);
    });

    // ==========================================
    // 2. Frozen Columns Recalculation
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
    // 3. Visual Filters (Hide/Show Columns)
    // ==========================================
    function atualizaInputsExportacao() {
        var perfis = [];
        $('#caixa-perfis .col-filter:checked').each(function() {
            perfis.push($(this).val());
        });
        $('#input_perfis_ativos').val(JSON.stringify(perfis));

        var grupos = [];
        $('#caixa-grupos .col-filter:checked').each(function() {
            grupos.push($(this).val());
        });
        $('#input_grupos_ativos').val(JSON.stringify(grupos));
    }

    if($('#caixa-perfis').length > 0) {
        atualizaInputsExportacao();
    }

    $('.col-filter').on('change', function() {
        var colIndex = $(this).data('colindex');
        var isVisible = $(this).is(':checked');
        var nth = colIndex + 1; 
        
        if (isVisible) {
            $('.tab_cadre_fixehov tr').find('th:nth-child(' + nth + '), td:nth-child(' + nth + ')').show();
        } else {
            $('.tab_cadre_fixehov tr').find('th:nth-child(' + nth + '), td:nth-child(' + nth + ')').hide();
        }

        var colunasVisiveis = [];
        $('.col-filter:checked').each(function() {
            colunasVisiveis.push($(this).data('colindex') + 1);
        });

        $('.tab_cadre_fixehov tr.tab_bg_1').each(function() {
            var tr_linha = $(this);
            var linhaTemX = false;

            if (colunasVisiveis.length > 0) {
                for (var i = 0; i < colunasVisiveis.length; i++) {
                    var textoCelula = tr_linha.find('td:nth-child(' + colunasVisiveis[i] + ')').text().trim();
                    if (textoCelula === 'X') {
                        linhaTemX = true;
                        break; 
                    }
                }
            }

            if (linhaTemX) {
                tr_linha.show();
            } else {
                tr_linha.hide();
            }
        });

        atualizaInputsExportacao();

        setTimeout(function() {
            recalcularPosicoesFixas();
        }, 50);
    });

    $('#btn-toggle-filtro').on('click', function() {
        $('#conteudo-filtro').slideToggle('fast');
    });

    $('.acao-massa-perfil').on('click', function(e) {
        e.preventDefault(); 
        var marcar = $(this).data('acao') === 'marcar';
        $('#caixa-perfis .col-filter').prop('checked', marcar).trigger('change');
    });

    $('.acao-massa-grupo').on('click', function(e) {
        e.preventDefault(); 
        var marcar = $(this).data('acao') === 'marcar';
        $('#caixa-grupos .col-filter').prop('checked', marcar).trigger('change');
    });

    // ==========================================
    // 4. Save Buttons Feedback (Config & Profile Forms)
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
    // 5. Select2 Sync (Generator Form)
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
    // 6. Altura Dinâmica do Layout (Correção Cross-version)
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

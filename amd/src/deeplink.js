/**
 * AMD Module for H5P Chapter Deeplink (Double Inception Architecture)
 *
 * @module local_h5pchapter/deeplink
 */
define(['jquery'], function ($) {
    return {
        // --- CÓDIGO DA PÁGINA PAI (CAMADA 0) ---
        initParent: function (params) {
            console.log('👑 [Pai] Aguardando o iframe pedir instruções...');

            window.addEventListener('message', function(event) {
                if (event.data && event.data.app === 'h5pchapter' && event.data.action === 'ready') {
                    console.log('👑 [Pai] Iframe avisou que está pronto. Enviando ordens!');
                    
                    event.source.postMessage({
                        app: 'h5pchapter',
                        action: 'execute',
                        target: params.chapter_target,
                        block: params.block_navigation
                    }, '*');
                }
            });
        },

// --- CÓDIGO DENTRO DO EMBED.PHP (CAMADA 1) ---
        initIframe: function () {
            console.log('👷 [Iframe] Acordei! Avisando o chefe que estou pronto...');

            window.parent.postMessage({
                app: 'h5pchapter',
                action: 'ready'
            }, '*');

            window.addEventListener('message', function(event) {
                if (event.data && event.data.app === 'h5pchapter' && event.data.action === 'execute') {
                    console.log('👷 [Iframe] Recebi as ordens: Cap ' + event.data.target + ' | Bloqueio: ' + event.data.block);
                    
                    var targetChapterNum = parseInt(event.data.target, 10);
                    var blockNavigation = event.data.block;
                    var attempts = 0;

                    var checkDOM = function() {
                        attempts++;
                        var targetDoc = document;
                        
                        var $innerIframe = $('iframe.h5p-iframe');
                        if ($innerIframe.length > 0) {
                            try {
                                targetDoc = $innerIframe[0].contentDocument || $innerIframe[0].contentWindow.document;
                            } catch (e) {
                                console.warn('👷 [Iframe] Erro ao acessar Camada 2.');
                            }
                        }

                        var $menuContainer = $(targetDoc).find('.h5p-interactive-book-navigation');
                        
                        if ($menuContainer.length > 0) {
                            var $chapters = $(targetDoc).find('.h5p-interactive-book-navigation-chapter');
                            
                            if ($chapters.length > 0) {
                                
                                // 1. O SALTO (DEEPLINK COM CLIQUE NATIVO)
                                if (!isNaN(targetChapterNum) && targetChapterNum > 0) {
                                    var targetIndex = targetChapterNum - 1;
                                    
                                    if ($chapters.length > targetIndex) {
                                        var $targetLi = $chapters.eq(targetIndex);
                                        var $clickable = $targetLi.find('[role="button"], a, button').first();
                                        
                                        // Pega o elemento DOM cru (sem o wrapper do jQuery)
                                        var domElement = $clickable.length > 0 ? $clickable[0] : $targetLi[0];
                                        
                                        // Cria um evento de mouse nativo simulando um clique humano real
                                        var clickEvent = new MouseEvent('click', {
                                            view: targetDoc.defaultView,
                                            bubbles: true,
                                            cancelable: true
                                        });
                                        
                                        // Dispara o evento
                                        domElement.dispatchEvent(clickEvent);
                                        
                                        console.log('🎯 [Iframe] SUCESSO: Simulou um clique humano no capítulo ' + targetChapterNum);
                                    }
                                }

                                // 2. A GUILHOTINA (BLOQUEIO VISUAL)
                                if (blockNavigation) {
                                    var cssRule = '<style>' +
                                        '.h5p-interactive-book-navigation { display: none !important; }' +
                                        '.h5p-interactive-book-main { width: 100% !important; float: none !important; left: 0 !important; }' +
                                        '.h5p-interactive-book-status { display: none !important; }' +
                                        '.h5p-interactive-book-cover { display: none !important; }' + 
                                        '</style>';
                                        
                                    $(targetDoc).find('head').append(cssRule);
                                    console.log('🔒 [Iframe] SUCESSO: Navegação bloqueada.');
                                }
                                
                                return; // Missão cumprida, encerra o loop!
                            }
                        }

                        if (attempts % 10 === 0) {
                            console.log('👷 [Iframe] Aguardando H5P renderizar botões... (' + attempts + ')');
                        }

                        setTimeout(checkDOM, 300);
                    };

                    checkDOM();
                }
            });
        }
    };
});
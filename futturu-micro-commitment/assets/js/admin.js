/**
 * Admin JavaScript for Futturu Micro-Commitment Plugin
 * Handles question/CTA management and data visualization
 */

(function($) {
    'use strict';
    
    var FMCAdmin = {
        
        init: function() {
            this.bindEvents();
            this.loadResponses();
        },
        
        bindEvents: function() {
            // Tab switching
            $(document).on('click', '.fmc-tab-btn', $.proxy(this.switchTab, this));
            
            // Add question
            $(document).on('click', '#fmc-add-question', $.proxy(this.addQuestion, this));
            
            // Remove question
            $(document).on('click', '.fmc-remove-question', $.proxy(this.removeQuestion, this));
            
            // Add answer
            $(document).on('click', '.fmc-add-answer', $.proxy(this.addAnswer, this));
            
            // Remove answer
            $(document).on('click', '.fmc-remove-answer', $.proxy(this.removeAnswer, this));
            
            // Save questions
            $(document).on('click', '#fmc-save-questions', $.proxy(this.saveQuestions, this));
            
            // Add CTA
            $(document).on('click', '#fmc-add-cta', $.proxy(this.addCTA, this));
            
            // Remove CTA
            $(document).on('click', '.fmc-remove-cta', $.proxy(this.removeCTA, this));
            
            // Save CTAs
            $(document).on('click', '#fmc-save-ctas', $.proxy(this.saveCTAs, this));
            
            // Refresh responses
            $(document).on('click', '#fmc-refresh-responses', $.proxy(this.loadResponses, this));
        },
        
        switchTab: function(e) {
            e.preventDefault();
            
            var $btn = $(e.currentTarget);
            var tabId = $btn.data('tab');
            
            // Update active button
            $('.fmc-tab-btn').removeClass('active');
            $btn.addClass('active');
            
            // Update active content
            $('.fmc-tab-content').removeClass('active');
            $('#fmc-tab-' + tabId).addClass('active');
            
            // Load responses if responses tab
            if (tabId === 'responses') {
                this.loadResponses();
            }
        },
        
        addQuestion: function() {
            var questionId = 'q' + new Date().getTime();
            var html = '<div class="fmc-question-item" data-id="' + questionId + '">' +
                '<div class="fmc-question-header">' +
                    '<span class="fmc-question-title">Nova Pergunta</span>' +
                    '<button class="fmc-remove-question button button-small">Remover</button>' +
                '</div>' +
                '<div class="fmc-question-body">' +
                    '<label>ID da Pergunta:</label>' +
                    '<input type="text" class="fmc-question-id" value="' + questionId + '" readonly>' +
                    '<label>Texto da Pergunta:</label>' +
                    '<input type="text" class="fmc-question-text" value="Sua pergunta aqui">' +
                    '<h4>Opções de Resposta:</h4>' +
                    '<div class="fmc-answers-container">' +
                        '<div class="fmc-answer-item">' +
                            '<input type="text" class="fmc-answer-text" placeholder="Texto da resposta">' +
                            '<select class="fmc-answer-next">' +
                                '<option value="">-- Selecione --</option>' +
                            '</select>' +
                            '<button class="fmc-remove-answer button button-small">X</button>' +
                        '</div>' +
                    '</div>' +
                    '<button class="fmc-add-answer button">+ Adicionar Resposta</button>' +
                '</div>' +
                '</div>';
            
            $('#fmc-questions-container').append(html);
            this.updateAnswerOptions();
        },
        
        removeQuestion: function(e) {
            if (confirm(fmcAdmin.strings.confirmDelete)) {
                $(e.currentTarget).closest('.fmc-question-item').remove();
                this.updateAnswerOptions();
            }
        },
        
        addAnswer: function(e) {
            var $container = $(e.currentTarget).siblings('.fmc-answers-container');
            var html = '<div class="fmc-answer-item">' +
                '<input type="text" class="fmc-answer-text" placeholder="Texto da resposta">' +
                '<select class="fmc-answer-next">' +
                    '<option value="">-- Selecione --</option>' +
                '</select>' +
                '<button class="fmc-remove-answer button button-small">X</button>' +
                '</div>';
            
            $container.append(html);
            this.updateAnswerOptions();
        },
        
        removeAnswer: function(e) {
            $(e.currentTarget).closest('.fmc-answer-item').remove();
        },
        
        updateAnswerOptions: function() {
            var questionsHTML = '';
            var ctasHTML = '';
            
            // Gather questions
            $('#fmc-questions-container .fmc-question-item').each(function() {
                var qId = $(this).find('.fmc-question-id').val();
                var qText = $(this).find('.fmc-question-text').val();
                questionsHTML += '<option value="' + qId + '">' + qText + '</option>';
            });
            
            // Gather CTAs
            $('#fmc-ctas-container .fmc-cta-item').each(function() {
                var ctaId = $(this).find('.fmc-cta-id').val();
                var ctaTitle = $(this).find('.fmc-cta-title-input').val();
                ctasHTML += '<option value="' + ctaId + '">' + ctaTitle + '</option>';
            });
            
            // Update all answer selects
            $('.fmc-answer-next').each(function() {
                var currentValue = $(this).val();
                $(this).html(
                    '<option value="">-- Selecione --</option>' +
                    '<optgroup label="Outras Perguntas">' + questionsHTML + '</optgroup>' +
                    '<optgroup label="CTAs Finais">' + ctasHTML + '</optgroup>'
                );
                $(this).val(currentValue);
            });
        },
        
        saveQuestions: function() {
            var questions = [];
            
            $('#fmc-questions-container .fmc-question-item').each(function() {
                var $item = $(this);
                var answers = [];
                
                $item.find('.fmc-answer-item').each(function() {
                    var $answer = $(this);
                    var answerData = {
                        text: $answer.find('.fmc-answer-text').val(),
                        next: $answer.find('.fmc-answer-next').val()
                    };
                    
                    // Check if it's a CTA
                    if (answerData.next && answerData.next.indexOf('cta_') === 0) {
                        answerData.cta = answerData.next;
                        delete answerData.next;
                    }
                    
                    answers.push(answerData);
                });
                
                questions.push({
                    id: $item.find('.fmc-question-id').val(),
                    question: $item.find('.fmc-question-text').val(),
                    answers: answers
                });
            });
            
            $.ajax({
                url: fmcAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmc_save_questions',
                    nonce: fmcAdmin.nonce,
                    questions: questions
                },
                success: function(response) {
                    if (response.success) {
                        alert(fmcAdmin.strings.saveSuccess);
                        FMCAdmin.updateAnswerOptions();
                    } else {
                        alert(fmcAdmin.strings.saveError);
                    }
                },
                error: function() {
                    alert(fmcAdmin.strings.saveError);
                }
            });
        },
        
        addCTA: function() {
            var ctaId = 'cta_' + new Date().getTime();
            var html = '<div class="fmc-cta-item" data-id="' + ctaId + '">' +
                '<div class="fmc-cta-header">' +
                    '<span class="fmc-cta-title">Novo CTA</span>' +
                    '<button class="fmc-remove-cta button button-small">Remover</button>' +
                '</div>' +
                '<div class="fmc-cta-body">' +
                    '<label>ID do CTA:</label>' +
                    '<input type="text" class="fmc-cta-id" value="' + ctaId + '">' +
                    '<label>Título:</label>' +
                    '<input type="text" class="fmc-cta-title-input" value="Seu título aqui">' +
                    '<label>Descrição:</label>' +
                    '<textarea class="fmc-cta-description">Sua descrição aqui</textarea>' +
                    '<label>Texto do Botão:</label>' +
                    '<input type="text" class="fmc-cta-button-text" value="Clique aqui">' +
                    '<label>Link de Destino:</label>' +
                    '<input type="text" class="fmc-cta-link" value="#">' +
                    '<label>Tipo:</label>' +
                    '<select class="fmc-cta-type">' +
                        '<option value="link">Link Externo</option>' +
                        '<option value="modal">Modal/Formulário</option>' +
                    '</select>' +
                '</div>' +
                '</div>';
            
            $('#fmc-ctas-container').append(html);
            this.updateAnswerOptions();
        },
        
        removeCTA: function(e) {
            if (confirm(fmcAdmin.strings.confirmDelete)) {
                $(e.currentTarget).closest('.fmc-cta-item').remove();
                this.updateAnswerOptions();
            }
        },
        
        saveCTAs: function() {
            var ctas = [];
            
            $('#fmc-ctas-container .fmc-cta-item').each(function() {
                var $item = $(this);
                ctas.push({
                    id: $item.find('.fmc-cta-id').val(),
                    title: $item.find('.fmc-cta-title-input').val(),
                    description: $item.find('.fmc-cta-description').val(),
                    button_text: $item.find('.fmc-cta-button-text').val(),
                    link: $item.find('.fmc-cta-link').val(),
                    type: $item.find('.fmc-cta-type').val()
                });
            });
            
            $.ajax({
                url: fmcAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmc_save_ctas',
                    nonce: fmcAdmin.nonce,
                    ctas: ctas
                },
                success: function(response) {
                    if (response.success) {
                        alert(fmcAdmin.strings.saveSuccess);
                        FMCAdmin.updateAnswerOptions();
                    } else {
                        alert(fmcAdmin.strings.saveError);
                    }
                },
                error: function() {
                    alert(fmcAdmin.strings.saveError);
                }
            });
        },
        
        loadResponses: function() {
            $.ajax({
                url: fmcAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmc_get_responses',
                    nonce: fmcAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FMCAdmin.renderResponses(response.data.responses);
                    }
                }
            });
        },
        
        renderResponses: function(responses) {
            var html = '';
            
            if (responses.length === 0) {
                html = '<tr><td colspan="5">Nenhuma resposta registrada ainda.</td></tr>';
            } else {
                $.each(responses, function(index, response) {
                    html += '<tr>' +
                        '<td>' + response.created_at + '</td>' +
                        '<td>' + response.session_id.substring(0, 8) + '...</td>' +
                        '<td>' + response.question_id + '</td>' +
                        '<td>' + response.answer + '</td>' +
                        '<td>' + (response.user_ip || 'N/A') + '</td>' +
                        '</tr>';
                });
            }
            
            $('#fmc-responses-body').html(html);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.wrap h1').text().indexOf('Micro-Engajamento Futturu') !== -1) {
            FMCAdmin.init();
        }
    });
    
})(jQuery);

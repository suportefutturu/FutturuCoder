/**
 * Frontend JavaScript for Futturu Micro-Commitment Plugin
 * Handles question navigation, answer submission, and CTA display
 */

(function($) {
    'use strict';
    
    var FMC = {
        sessionId: '',
        currentQuestionId: '',
        pathTaken: '',
        totalQuestions: 0,
        currentIndex: 0,
        
        init: function() {
            this.bindEvents();
            this.loadFirstQuestion();
        },
        
        bindEvents: function() {
            $(document).on('click', '.fmc-answer-btn', $.proxy(this.handleAnswer, this));
            $(document).on('click', '.fmc-cta-button', $.proxy(this.handleCTAClick, this));
        },
        
        loadFirstQuestion: function() {
            var $widget = $('.fmc-widget');
            $widget.find('.fmc-content').html(this.getLoadingHTML());
            
            $.ajax({
                url: fmcFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmc_get_question',
                    nonce: fmcFrontend.nonce,
                    question_id: '',
                    session_id: this.sessionId
                },
                success: $.proxy(this.handleQuestionResponse, this),
                error: $.proxy(this.handleError, this)
            });
        },
        
        loadQuestion: function(questionId) {
            var $widget = $('.fmc-widget');
            $widget.find('.fmc-question-wrapper').html(this.getLoadingHTML());
            
            $.ajax({
                url: fmcFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmc_get_question',
                    nonce: fmcFrontend.nonce,
                    question_id: questionId,
                    session_id: this.sessionId
                },
                success: $.proxy(this.handleQuestionResponse, this),
                error: $.proxy(this.handleError, this)
            });
        },
        
        handleQuestionResponse: function(response) {
            if (response.success) {
                var data = response.data;
                
                this.sessionId = data.session_id;
                this.currentQuestionId = data.question_id;
                this.totalQuestions = data.total_questions;
                this.currentIndex = data.current_index;
                
                this.renderQuestion(data);
                this.updateProgressBar();
            } else {
                this.showError(fmcFrontend.strings.error);
            }
        },
        
        renderQuestion: function(data) {
            var $widget = $('.fmc-widget');
            var $content = $widget.find('.fmc-content');
            
            var answersHTML = '';
            $.each(data.answers, function(index, answer) {
                answersHTML += '<button class="fmc-answer-btn" data-answer="' + 
                    FMC.escapeHtml(answer.text) + '">' + 
                    FMC.escapeHtml(answer.text) + '</button>';
            });
            
            var html = '<div class="fmc-question-wrapper">' +
                '<div class="fmc-question-text">' + this.escapeHtml(data.question_text) + '</div>' +
                '<div class="fmc-answers-container">' + answersHTML + '</div>' +
                '</div>';
            
            $content.html(html);
        },
        
        handleAnswer: function(e) {
            e.preventDefault();
            
            var $btn = $(e.currentTarget);
            var answerText = $btn.data('answer');
            
            // Disable all buttons to prevent multiple clicks
            $('.fmc-answer-btn').prop('disabled', true);
            
            $.ajax({
                url: fmcFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmc_submit_answer',
                    nonce: fmcFrontend.nonce,
                    session_id: this.sessionId,
                    question_id: this.currentQuestionId,
                    answer_text: answerText,
                    path_taken: this.pathTaken
                },
                success: $.proxy(this.handleAnswerResponse, this),
                error: $.proxy(this.handleAnswerError, this)
            });
        },
        
        handleAnswerResponse: function(response) {
            if (response.success) {
                var data = response.data;
                this.pathTaken = data.path_taken;
                
                if (data.next_step.type === 'question') {
                    this.loadQuestion(data.next_step.question_id);
                } else if (data.next_step.type === 'cta') {
                    this.renderCTA(data.next_step.cta);
                }
            } else {
                $('.fmc-answer-btn').prop('disabled', false);
                this.showError(response.data.message || fmcFrontend.strings.error);
            }
        },
        
        handleAnswerError: function() {
            $('.fmc-answer-btn').prop('disabled', false);
            this.showError(fmcFrontend.strings.error);
        },
        
        renderCTA: function(cta) {
            var $widget = $('.fmc-widget');
            var $content = $widget.find('.fmc-content');
            
            // Hide progress bar when showing CTA
            $widget.find('.fmc-progress-bar').hide();
            
            var html = '<div class="fmc-cta-wrapper">' +
                '<h3 class="fmc-cta-title">' + this.escapeHtml(cta.title) + '</h3>' +
                '<p class="fmc-cta-description">' + this.escapeHtml(cta.description) + '</p>' +
                '<a href="' + this.escapeHtml(cta.link) + '" class="fmc-cta-button" target="' + 
                (cta.type === 'link' ? '_self' : '_self') + '">' + 
                this.escapeHtml(cta.button_text) + '</a>' +
                '</div>';
            
            $content.html(html);
            
            // Update progress bar to 100%
            this.updateProgressBar(100);
        },
        
        handleCTAClick: function(e) {
            // Track CTA click if needed
            console.log('CTA clicked:', $(e.currentTarget).text());
        },
        
        updateProgressBar: function(forcePercent) {
            var $widget = $('.fmc-widget');
            var $progressBar = $widget.find('.fmc-progress-fill');
            
            if ($progressBar.length === 0) {
                return;
            }
            
            var percent;
            if (typeof forcePercent !== 'undefined') {
                percent = forcePercent;
            } else if (this.totalQuestions > 0) {
                percent = ((this.currentIndex + 1) / this.totalQuestions) * 100;
            } else {
                percent = 0;
            }
            
            $progressBar.css('width', percent + '%');
        },
        
        showError: function(message) {
            var $widget = $('.fmc-widget');
            var $content = $widget.find('.fmc-content');
            
            var html = '<div class="fmc-error">' + this.escapeHtml(message) + '</div>';
            $content.html(html);
        },
        
        getLoadingHTML: function() {
            return '<div class="fmc-loading">' +
                '<div class="fmc-spinner"></div>' +
                '<p>' + fmcFrontend.strings.loading + '</p>' +
                '</div>';
        },
        
        escapeHtml: function(text) {
            if (!text) return '';
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.fmc-widget').length > 0) {
            FMC.init();
        }
    });
    
})(jQuery);

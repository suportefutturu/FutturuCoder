/**
 * JavaScript comum para todas as ferramentas
 * Funções utilitárias e interações do usuário
 */

// Função para copiar texto para clipboard
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copiado com sucesso!', 'success');
        return true;
    } catch (err) {
        // Fallback para navegadores antigos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showToast('Copiado com sucesso!', 'success');
            return true;
        } catch (err) {
            showToast('Erro ao copiar', 'danger');
            return false;
        }
        document.body.removeChild(textArea);
    }
}

// Função para mostrar toast/notification
function showToast(message, type = 'info') {
    // Cria elemento toast se não existir
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '11';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove após fechar
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Função para limpar formulário
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        // Limpa também inputs de número
        const minInput = document.getElementById('min-val');
        const maxInput = document.getElementById('max-val');
        if (minInput) minInput.value = '1';
        if (maxInput) maxInput.value = '100';
    }
}

// Função para contar caracteres em tempo real (opcional para algumas ferramentas)
function setupRealTimeCounter(inputId, counterId) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    
    if (input && counter) {
        input.addEventListener('input', function() {
            const text = this.value;
            const count = text.length;
            counter.textContent = `${count} caractere(s)`;
        });
    }
}

// Detecta se é mobile
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Adiciona analytics event tracking (placeholder para Google Analytics)
function trackEvent(category, action, label) {
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            'event_category': category,
            'event_label': label
        });
    }
    // Para outros analytics, adicione aqui
}

// Lazy loading para imagens (performance)
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// Service Worker registration para PWA (opcional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // navigator.serviceWorker.register('/sw.js').then(registration => {
        //     console.log('SW registered:', registration);
        // }).catch(error => {
        //     console.log('SW registration failed:', error);
        // });
    });
}

// Export functions for global use
window.copyToClipboard = copyToClipboard;
window.showToast = showToast;
window.clearForm = clearForm;
window.setupRealTimeCounter = setupRealTimeCounter;
window.trackEvent = trackEvent;

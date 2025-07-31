/**
 * Utilitários para o frontend
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

/**
 * Formata valor monetário
 */
export function formatCurrency(value, currency = 'BRL', locale = 'pt-BR') {
    if (!value || isNaN(value)) return 'R$ 0,00';
    
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(value);
}

/**
 * Formata data
 */
export function formatDate(date, options = {}) {
    if (!date) return '';
    
    const defaultOptions = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    };
    
    const dateObj = new Date(date);
    return dateObj.toLocaleDateString('pt-BR', { ...defaultOptions, ...options });
}

/**
 * Formata data relativa
 */
export function formatRelativeDate(date) {
    if (!date) return '';
    
    const now = new Date();
    const dateObj = new Date(date);
    const diffTime = Math.abs(now - dateObj);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Hoje';
    if (diffDays === 1) return 'Ontem';
    if (diffDays < 7) return `${diffDays} dias atrás`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} semanas atrás`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} meses atrás`;
    
    return `${Math.floor(diffDays / 365)} anos atrás`;
}

/**
 * Calcula tempo restante
 */
export function getTimeRemaining(expiryDate) {
    if (!expiryDate) return null;
    
    const now = new Date();
    const expiry = new Date(expiryDate);
    const diff = expiry - now;
    
    if (diff <= 0) return null;
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    if (days > 0) {
        return `${days} ${days === 1 ? 'dia' : 'dias'}`;
    } else if (hours > 0) {
        return `${hours} ${hours === 1 ? 'hora' : 'horas'}`;
    } else if (minutes > 0) {
        return `${minutes} ${minutes === 1 ? 'minuto' : 'minutos'}`;
    } else {
        return 'Menos de 1 minuto';
    }
}

/**
 * Verifica se data está expirada
 */
export function isExpired(date) {
    if (!date) return false;
    return new Date(date) < new Date();
}

/**
 * Debounce function
 */
export function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

/**
 * Throttle function
 */
export function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Sanitiza string
 */
export function sanitizeString(str) {
    if (!str) return '';
    return str.replace(/[<>]/g, '');
}

/**
 * Valida email
 */
export function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Gera ID único
 */
export function generateId() {
    return Math.random().toString(36).substr(2, 9);
}

/**
 * Copia texto para clipboard
 */
export async function copyToClipboard(text) {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return true;
        } else {
            // Fallback para navegadores mais antigos
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            const result = document.execCommand('copy');
            textArea.remove();
            return result;
        }
    } catch (error) {
        console.error('Erro ao copiar para clipboard:', error);
        return false;
    }
}

/**
 * Detecta dispositivo móvel
 */
export function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Detecta se é touch device
 */
export function isTouchDevice() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
}

/**
 * Obtém parâmetros da URL
 */
export function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const result = {};
    for (const [key, value] of params) {
        result[key] = value;
    }
    return result;
}

/**
 * Atualiza parâmetros da URL
 */
export function updateUrlParams(params) {
    const url = new URL(window.location);
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    window.history.replaceState({}, '', url);
}

/**
 * Localiza elemento no DOM
 */
export function findElement(selector, parent = document) {
    return parent.querySelector(selector);
}

/**
 * Localiza elementos no DOM
 */
export function findElements(selector, parent = document) {
    return parent.querySelectorAll(selector);
}

/**
 * Adiciona classe CSS
 */
export function addClass(element, className) {
    if (element && element.classList) {
        element.classList.add(className);
    }
}

/**
 * Remove classe CSS
 */
export function removeClass(element, className) {
    if (element && element.classList) {
        element.classList.remove(className);
    }
}

/**
 * Toggle classe CSS
 */
export function toggleClass(element, className) {
    if (element && element.classList) {
        element.classList.toggle(className);
    }
}

/**
 * Verifica se elemento tem classe
 */
export function hasClass(element, className) {
    return element && element.classList && element.classList.contains(className);
}

/**
 * Mostra notificação
 */
export function showNotification(message, type = 'info', duration = 3000) {
    // Cria elemento de notificação
    const notification = document.createElement('div');
    notification.className = `cupompromo-notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" aria-label="Fechar notificação">×</button>
        </div>
    `;
    
    // Adiciona ao DOM
    document.body.appendChild(notification);
    
    // Anima entrada
    setTimeout(() => {
        addClass(notification, 'show');
    }, 10);
    
    // Remove após duração
    setTimeout(() => {
        removeClass(notification, 'show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
    
    // Fecha ao clicar no X
    const closeBtn = notification.querySelector('.notification-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            removeClass(notification, 'show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
    }
}

/**
 * Loga para console apenas em desenvolvimento
 */
export function debugLog(...args) {
    if (window.cupompromo_debug) {
        console.log('[Cupompromo Debug]', ...args);
    }
}

/**
 * Trata erros de forma consistente
 */
export function handleError(error, context = '') {
    console.error(`[Cupompromo Error] ${context}:`, error);
    
    // Mostra notificação de erro para o usuário
    showNotification(
        'Ocorreu um erro. Tente novamente.',
        'error',
        5000
    );
} 
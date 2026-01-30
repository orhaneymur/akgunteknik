/**
 * Toast notification helper
 * Usage:
 *   import toast from '../../utils/toast';
 *   toast.success('İşlem başarılı!');
 *   toast.error('Bir hata oluştu!');
 *   toast.info('Bilgilendirme mesajı');
 *   toast.warning('Uyarı mesajı');
 */

const toast = {
    show(message, type = 'info', duration = 5000) {
        // Emit custom DOM event to Toast component
        const event = new CustomEvent('toast', {
            detail: { message, type, duration }
        });
        window.dispatchEvent(event);
    },
    success(message, duration = 5000) {
        this.show(message, 'success', duration);
    },
    error(message, duration = 5000) {
        this.show(message, 'error', duration);
    },
    info(message, duration = 5000) {
        this.show(message, 'info', duration);
    },
    warning(message, duration = 5000) {
        this.show(message, 'warning', duration);
    }
};

export default toast;

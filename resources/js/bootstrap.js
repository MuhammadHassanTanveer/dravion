import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set up CSRF token
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Handle 403 Forbidden responses
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 403) {
            // Don't redirect if we're already on the dashboard or trying to navigate
            // Only show error message, don't force redirect
            if (window.toast) {
                window.toast.error('You do not have permission to perform this action.');
            } else {
                alert('You do not have permission to perform this action.');
            }
            // Don't force redirect - let the component handle navigation
        }
        return Promise.reject(error);
    }
);

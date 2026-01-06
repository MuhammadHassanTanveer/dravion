import './bootstrap';
import { createApp } from 'vue';
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard.vue';

// Simple routing based on current page
document.addEventListener('DOMContentLoaded', () => {
    const loginElement = document.getElementById('login-app');
    const dashboardElement = document.getElementById('dashboard-app');
    
    if (loginElement) {
        const app = createApp(Login);
        app.mount('#login-app');
    }
    
    if (dashboardElement) {
        const app = createApp(Dashboard);
        app.mount('#dashboard-app');
    }
});

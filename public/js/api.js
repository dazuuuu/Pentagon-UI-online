// Base configuration for API — derived from this module's own resolved URL
// so it still finds /api correctly whether the site lives at a true domain
// root or is nested under a subfolder (e.g. Pentagon Quest UI).
const API_BASE_URL = new URL('../api', import.meta.url).pathname;

export const ApiService = {
    async get(endpoint) {
        const response = await fetch(`${API_BASE_URL}${endpoint}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    },

    async post(endpoint, data) {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    },

    packages(status = 'published') {
        return this.get(`/packages.php?status=${encodeURIComponent(status)}`);
    },

    travels() {
        return this.get('/travels.php');
    },

    contact(payload) {
        return this.post('/contact.php', payload);
    },

    newsletter(payload) {
        return this.post('/newsletter.php', payload);
    },

    track(code) {
        return this.get(`/track.php?code=${encodeURIComponent(code)}`);
    },
};

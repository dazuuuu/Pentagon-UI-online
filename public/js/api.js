// Base configuration for API — respects subfolder installs (Pentagon Quest UI)
function pqBasePath() {
    if (typeof window !== 'undefined' && window.PQ_BASE_PATH != null) {
        return String(window.PQ_BASE_PATH).replace(/\/$/, '');
    }
    const path = window.location.pathname || '';
    const markers = ['/admin/', '/client/', '/api/', '/devs/'];
    for (const marker of markers) {
        const idx = path.indexOf(marker);
        if (idx >= 0) return path.slice(0, idx);
    }
    // Fallback: project lives in /Pentagon Quest UI/
    const m = path.match(/^(\/Pentagon(?:%20| )Quest(?:%20| )UI)/i);
    if (m) return decodeURIComponent(m[1]);
    return '/Pentagon Quest UI';
}

const API_BASE_URL = `${pqBasePath()}/api`;

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

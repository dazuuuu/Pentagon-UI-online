import { ApiService } from './api.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('Pentagon Quest App Initialized');
    
    // Example: Fetch packages if there's a container for it
    const packagesContainer = document.getElementById('packages-container');
    if (packagesContainer) {
        loadPackages();
    }
});

async function loadPackages() {
    try {
        // Placeholder for API call
        // const packages = await ApiService.get('/packages');
        console.log('Loading packages...');
    } catch (error) {
        console.error('Failed to load packages:', error);
    }
}

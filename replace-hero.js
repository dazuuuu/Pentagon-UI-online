const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf-8');

const startMarker = '<header data-elementor-type="header"';
const endMarker = '<div class="elementor-element elementor-element-d69e4e9 e-flex e-con-boxed e-con e-parent"';

const startIndex = html.indexOf(startMarker);
const endIndex = html.indexOf(endMarker);

if (startIndex !== -1 && endIndex !== -1) {
    const newHero = `
<!-- BEGIN NEW CUSTOM HERO -->
<link rel="stylesheet" href="css/custom-hero.css">
<header class="custom-hero">
    <div class="hero-header">
        <div class="hero-search-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
        <nav class="hero-nav">
            <a href="#">gallery</a>
            <a href="#">reviews</a>
            <div class="hero-logo">
                <span style="color: #D4AF37;">🦁</span> Pentagon Quest
            </div>
            <a href="#">tours</a>
            <a href="#">destinations</a>
            <a href="#">services</a>
        </nav>
        <div class="hero-contact">+31 📞</div>
    </div>
    
    <div class="hero-center">
        <h1 class="hero-title">SAFARI TOUR</h1>
    </div>
    
    <div class="hero-bottom">
        <div class="hero-search-bar">
            <div class="search-field">
                <label>Destination</label>
                <select>
                    <option>Select Destination</option>
                    <option>Maasai Mara</option>
                    <option>Serengeti</option>
                    <option>Tsavo National Park</option>
                    <option>Amboseli</option>
                </select>
            </div>
            <div class="search-field">
                <label>Date</label>
                <input type="date" />
            </div>
            <div class="search-field">
                <label>Adventure type</label>
                <select>
                    <option>All Types</option>
                    <option>Luxury Safari</option>
                    <option>Budget Tour</option>
                    <option>Family Package</option>
                </select>
            </div>
            <button class="search-btn">Search Tours</button>
        </div>
    </div>
</header>
<div data-elementor-type="wp-page" data-elementor-id="7621" class="elementor elementor-7621" data-elementor-post-type="page">
<!-- END NEW CUSTOM HERO -->
`;

    const newHtml = html.substring(0, startIndex) + newHero + html.substring(endIndex);
    fs.writeFileSync('index.html', newHtml);
    console.log("Successfully replaced hero section.");
} else {
    console.error("Could not find markers.", { startIndex, endIndex });
}

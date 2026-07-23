const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf-8');

let startSection = html.indexOf('<div class="elementor-element elementor-element-28cdc5da');
let endSection = html.indexOf('<div class="elementor-element elementor-element-8a226e4 e-con-full e-flex e-con e-child" data-id="8a226e4"');

if (endSection === -1) {
    endSection = html.indexOf('<div class="elementor-element elementor-element-114c4aa6');
    endSection = html.lastIndexOf('<div class="elementor-element', endSection);
}

const newSection = `
<!-- BEGIN NEW POPULAR DESTINATIONS 3-CARD LAYOUT -->
<style>
.destination-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}
.destination-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #fff;
    border: 1px solid #f0f0f0;
    text-align: center;
    padding-bottom: 20px;
}
.destination-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}
.destination-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
}
.destination-card h4 {
    margin: 20px 0 10px;
    font-size: 22px;
    color: #111;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
}
.destination-card p {
    color: #555;
    padding: 0 20px;
    font-size: 14px;
    line-height: 1.6;
}
.destination-card .btn {
    display: inline-block;
    margin-top: 15px;
    background-color: #D4AF37;
    color: white;
    padding: 10px 25px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: background 0.3s;
}
.destination-card .btn:hover {
    background-color: #b8972e;
}
@media (max-width: 900px) {
    .destination-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 600px) {
    .destination-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<div class="destination-grid">
    <!-- Card 1: Dubai -->
    <div class="destination-card">
        <img src="wp-content/uploads/2026/02/dubai-1.jpg" onerror="this.src='https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&q=80&w=800'" alt="Dubai">
        <h4>Dubai</h4>
        <p>Futuristic skyline architecture, sweeping desert dunes, and world-class shopping define this luxurious city destination for an unforgettable getaway.</p>
        <a href="#" class="btn">Explore</a>
    </div>

    <!-- Card 2: Nakuru -->
    <div class="destination-card">
        <img src="wp-content/uploads/2026/02/nakuru.jpg" onerror="this.src='https://images.unsplash.com/photo-1549472346-630b427b0eb8?auto=format&fit=crop&q=80&w=800'" alt="Nakuru">
        <h4>Nakuru</h4>
        <p>A renowned rhino sanctuary, diverse wildlife, and stunning Great Rift Valley scenery offer a remarkable setting for a weekend safari.</p>
        <a href="#" class="btn">Explore</a>
    </div>

    <!-- Card 3: Maasai Mara -->
    <div class="destination-card">
        <img src="wp-content/uploads/2026/02/mara.jpg" onerror="this.src='https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&q=80&w=800'" alt="Maasai Mara">
        <h4>Maasai Mara</h4>
        <p>Experience the ultimate African safari with breathtaking savannahs, the Great Migration, and majestic wildlife encounters in Kenya's crown jewel.</p>
        <a href="#" class="btn">Explore</a>
    </div>
</div>
<!-- END NEW POPULAR DESTINATIONS 3-CARD LAYOUT -->
`;

if (startSection !== -1 && endSection !== -1) {
    let newHtml = html.substring(0, startSection) + newSection + html.substring(endSection);
    fs.writeFileSync('index.html', newHtml);
    console.log("Successfully replaced popular destinations.");
} else {
    console.log("Could not find blocks. Start:", startSection, "End:", endSection);
}

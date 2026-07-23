const fs = require('fs');

let html = fs.readFileSync('about-us/index.html', 'utf-8');
let snippet = fs.readFileSync('director-section.html', 'utf-8');

// Insert the snippet right before the footer
let footerIndex = html.indexOf('<footer');

if (footerIndex !== -1) {
    let newHtml = html.substring(0, footerIndex) + snippet + '\n' + html.substring(footerIndex);
    fs.writeFileSync('about-us/index.html', newHtml);
    console.log("Successfully inserted director section.");
} else {
    console.error("Could not find <footer tag.");
}

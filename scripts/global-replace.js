const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

let changedCount = 0;

walkDir('.', function(filePath) {
    if (filePath.endsWith('.html')) {
        let content = fs.readFileSync(filePath, 'utf-8');
        let regex = /Bonfire Adventures/gi;
        
        if (regex.test(content)) {
            let newContent = content.replace(regex, 'Pentagon Quest');
            // Also handle "bonfireadventures" in text but avoid changing actual links
            let regex2 = />([^<]*?)bonfireadventures([^>]*?)</gi;
            if (regex2.test(newContent)) {
               newContent = newContent.replace(regex2, '>$1Pentagon Quest$2<');
            }

            fs.writeFileSync(filePath, newContent);
            changedCount++;
        }
    }
});

console.log(`Replaced text in ${changedCount} files.`);

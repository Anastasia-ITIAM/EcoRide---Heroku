const express = require('express');
const path = require('path');
const app = express();
const port = process.env.PORT || 3000;

// Serve static files from the current directory
app.use(express.static('.'));

// Handle all routes by serving the appropriate HTML file
app.get('*', (req, res) => {
    const filePath = path.join(__dirname, req.path);
    const htmlPath = filePath.endsWith('.html') ? filePath : filePath + '.html';
    
    // Check if the HTML file exists
    if (require('fs').existsSync(htmlPath)) {
        res.sendFile(htmlPath);
    } else {
        // If no HTML file exists, serve index.html (for SPA behavior)
        res.sendFile(path.join(__dirname, 'index.html'));
    }
});

app.listen(port, () => {
    console.log(`Server running on port ${port}`);
});

#!/usr/bin/env node

const fs = require('fs');

try {
    const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
    console.log(packageJson.version);
} catch (error) {
    console.error('Error reading package.json:', error.message);
    process.exit(1);
} 
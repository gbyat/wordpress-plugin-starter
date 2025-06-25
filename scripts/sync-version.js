#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

/**
 * WordPress Plugin Starter - Version Sync Script
 * 
 * This script synchronizes the version number between package.json and the main PHP file.
 * It also updates the CHANGELOG.md with a new entry.
 */

// Configuration
const CONFIG = {
    phpFile: 'plugin-name.php',
    changelogFile: 'CHANGELOG.md',
    versionPattern: /(Version:\s*)(['"]?)([^'"]+)(['"]?)/,
    constantPattern: /(define\s*\(\s*['"]WPS_VERSION['"]\s*,\s*['"])([^'"]+)(['"]\s*\);)/,
    changelogHeader: '# Changelog\n\nAll notable changes to this project will be documented in this file.\n\n'
};

/**
 * Get version from package.json
 */
function getPackageVersion() {
    try {
        const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
        return packageJson.version;
    } catch (error) {
        console.error('❌ Error reading package.json:', error.message);
        process.exit(1);
    }
}

/**
 * Update version in PHP file
 */
function updatePhpVersion(version) {
    try {
        let phpContent = fs.readFileSync(CONFIG.phpFile, 'utf8');
        let updated = false;

        // Update plugin header version
        if (CONFIG.versionPattern.test(phpContent)) {
            phpContent = phpContent.replace(CONFIG.versionPattern, `$1$2${version}$4`);
            updated = true;
            console.log(`✅ Updated plugin header version to ${version}`);
        }

        // Update WPS_VERSION constant
        if (CONFIG.constantPattern.test(phpContent)) {
            phpContent = phpContent.replace(CONFIG.constantPattern, `$1${version}$3`);
            updated = true;
            console.log(`✅ Updated WPS_VERSION constant to ${version}`);
        }

        if (updated) {
            fs.writeFileSync(CONFIG.phpFile, phpContent, 'utf8');
            console.log(`✅ Plugin-Version auf ${version} synchronisiert.`);
        } else {
            console.log('⚠️  No version patterns found in PHP file');
        }

        return updated;
    } catch (error) {
        console.error('❌ Error updating PHP file:', error.message);
        return false;
    }
}

/**
 * Update CHANGELOG.md
 */
function updateChangelog(version) {
    try {
        const timestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
        const changelogEntry = `## [${version}] - ${timestamp}\n\n### Added\n- Version ${version} release\n\n`;

        let changelogContent = '';

        // Check if changelog file exists
        if (fs.existsSync(CONFIG.changelogFile)) {
            changelogContent = fs.readFileSync(CONFIG.changelogFile, 'utf8');
        } else {
            changelogContent = CONFIG.changelogHeader;
        }

        // Add new entry at the beginning (after header)
        const lines = changelogContent.split('\n');
        const headerEndIndex = lines.findIndex(line => line.startsWith('## [')) || 0;

        if (headerEndIndex === 0) {
            // No existing entries, add after header
            lines.splice(2, 0, changelogEntry);
        } else {
            // Add after existing header
            lines.splice(headerEndIndex, 0, changelogEntry);
        }

        const updatedChangelog = lines.join('\n');
        fs.writeFileSync(CONFIG.changelogFile, updatedChangelog, 'utf8');

        console.log(`✅ Changelog updated with ${timestamp} entry`);
        console.log(`📝 Added 1 changed files to changelog`);

        return true;
    } catch (error) {
        console.error('❌ Error updating changelog:', error.message);
        return false;
    }
}

/**
 * Main function
 */
function main() {
    console.log('🔄 WordPress Plugin Starter - Version Sync');
    console.log('==========================================\n');

    // Get version from package.json
    const version = getPackageVersion();
    console.log(`📦 Package version: ${version}\n`);

    // Update PHP file
    const phpUpdated = updatePhpVersion(version);

    // Update changelog
    const changelogUpdated = updateChangelog(version);

    console.log('\n✅ Version synchronization completed!');

    if (!phpUpdated || !changelogUpdated) {
        console.log('⚠️  Some updates may have failed. Please check the output above.');
        process.exit(1);
    }
}

// Run the script
if (require.main === module) {
    main();
}

module.exports = {
    getPackageVersion,
    updatePhpVersion,
    updateChangelog,
    main
}; 
#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const readline = require('readline');

/**
 * WordPress Plugin Starter - Setup Script
 * 
 * This script helps customize the plugin starter template for your specific project.
 */

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

function question(prompt) {
    return new Promise((resolve) => {
        rl.question(prompt, resolve);
    });
}

async function setup() {
    console.log('🚀 WordPress Plugin Starter - Setup');
    console.log('====================================\n');

    try {
        // Get plugin information
        const pluginName = await question('Plugin Name (e.g., My Awesome Plugin): ');
        const pluginSlug = await question('Plugin Slug (e.g., my-awesome-plugin): ');
        const pluginDescription = await question('Plugin Description: ');
        const authorName = await question('Author Name: ');
        const authorUrl = await question('Author URL (optional): ');
        const githubUsername = await question('GitHub Username: ');
        const githubRepo = await question('GitHub Repository Name: ');

        console.log('\n📝 Updating files...\n');

        // Update package.json
        const packageJsonPath = 'package.json';
        const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));

        packageJson.name = pluginSlug;
        packageJson.description = pluginDescription;
        packageJson.author = authorName;
        packageJson.repository.url = `https://github.com/${githubUsername}/${githubRepo}.git`;
        packageJson.bugs.url = `https://github.com/${githubUsername}/${githubRepo}/issues`;
        packageJson.homepage = `https://github.com/${githubUsername}/${githubRepo}#readme`;

        fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2) + '\n');
        console.log('✅ Updated package.json');

        // Update main PHP file
        const phpPath = 'plugin-name.php';
        let phpContent = fs.readFileSync(phpPath, 'utf8');

        // Update plugin header
        phpContent = phpContent.replace(
            /Plugin Name: WordPress Plugin Starter/,
            `Plugin Name: ${pluginName}`
        );
        phpContent = phpContent.replace(
            /Plugin URI: https:\/\/github.com\/gbyat\/wordpress-plugin-starter/,
            `Plugin URI: https://github.com/${githubUsername}/${githubRepo}`
        );
        phpContent = phpContent.replace(
            /Description: Ein Starter-Template für WordPress-Plugins mit automatischem GitHub-Update-System/,
            `Description: ${pluginDescription}`
        );
        phpContent = phpContent.replace(
            /Author: Your Name/,
            `Author: ${authorName}`
        );
        phpContent = phpContent.replace(
            /Text Domain: wp-plugin-starter/,
            `Text Domain: ${pluginSlug}`
        );

        // Update GitHub repository constant
        phpContent = phpContent.replace(
            /define\('WPS_GITHUB_REPO', 'gbyat\/wordpress-plugin-starter'\);/,
            `define('WPS_GITHUB_REPO', '${githubUsername}/${githubRepo}');`
        );

        // Update class name and constants
        const classPrefix = pluginSlug.replace(/-/g, '_').toUpperCase();
        phpContent = phpContent.replace(/WordPressPluginStarter/g, pluginName.replace(/\s+/g, ''));
        phpContent = phpContent.replace(/WPS_VERSION/g, `${classPrefix}_VERSION`);
        phpContent = phpContent.replace(/WPS_PLUGIN_DIR/g, `${classPrefix}_PLUGIN_DIR`);
        phpContent = phpContent.replace(/WPS_PLUGIN_URL/g, `${classPrefix}_PLUGIN_URL`);
        phpContent = phpContent.replace(/WPS_GITHUB_REPO/g, `${classPrefix}_GITHUB_REPO`);

        // Update text domain
        phpContent = phpContent.replace(/wp-plugin-starter/g, pluginSlug);

        fs.writeFileSync(phpPath, phpContent);
        console.log('✅ Updated plugin-name.php');

        // Rename PHP file
        const newPhpPath = `${pluginSlug}.php`;
        fs.renameSync(phpPath, newPhpPath);
        console.log(`✅ Renamed plugin-name.php to ${newPhpPath}`);

        // Update block.json
        const blockJsonPath = 'src/block.json';
        const blockJson = JSON.parse(fs.readFileSync(blockJsonPath, 'utf8'));

        blockJson.name = `${pluginSlug}/example-block`;
        blockJson.textdomain = pluginSlug;

        fs.writeFileSync(blockJsonPath, JSON.stringify(blockJson, null, 2) + '\n');
        console.log('✅ Updated src/block.json');

        // Update sync-version.js
        const syncScriptPath = 'scripts/sync-version.js';
        let syncScript = fs.readFileSync(syncScriptPath, 'utf8');

        syncScript = syncScript.replace(
            /phpFile: 'plugin-name\.php'/,
            `phpFile: '${newPhpPath}'`
        );
        syncScript = syncScript.replace(
            /constantPattern: \/\(define\\s*\(\\s*\['"\]WPS_VERSION\['"\]\\s*,\\s*\['"\]\)\([^'"]+\)\(\['"\]\\s*\);\)/,
            `constantPattern: /(define\\s*\\(\\s*['"]${classPrefix}_VERSION['"]\\s*,\\s*['"])([^'"]+)(['"]\\s*\\);)/`
        );

        fs.writeFileSync(syncScriptPath, syncScript);
        console.log('✅ Updated scripts/sync-version.js');

        // Update GitHub Actions workflow
        const workflowPath = '.github/workflows/release.yml';
        let workflow = fs.readFileSync(workflowPath, 'utf8');

        workflow = workflow.replace(
            /cp plugin-name\.php release\//,
            `cp ${newPhpPath} release/`
        );
        workflow = workflow.replace(
            /zip -r \.\.\/wordpress-plugin-starter\.zip \./,
            `zip -r ../${pluginSlug}.zip .`
        );
        workflow = workflow.replace(
            /select\(\.name == "wordpress-plugin-starter\.zip"\)/,
            `select(.name == "${pluginSlug}.zip")`
        );

        fs.writeFileSync(workflowPath, workflow);
        console.log('✅ Updated .github/workflows/release.yml');

        // Update README.md
        const readmePath = 'README.md';
        let readme = fs.readFileSync(readmePath, 'utf8');

        readme = readme.replace(/WordPress Plugin Starter/g, pluginName);
        readme = readme.replace(/wordpress-plugin-starter/g, pluginSlug);
        readme = readme.replace(/gbyat\/wordpress-plugin-starter/g, `${githubUsername}/${githubRepo}`);
        readme = readme.replace(/Your Name/g, authorName);

        fs.writeFileSync(readmePath, readme);
        console.log('✅ Updated README.md');

        console.log('\n🎉 Setup completed successfully!');
        console.log('\n📋 Next steps:');
        console.log('1. Review and customize the generated files');
        console.log('2. Run: npm install');
        console.log('3. Run: npm run build');
        console.log('4. Create a GitHub repository and push your code');
        console.log('5. Set up GitHub token in plugin settings');
        console.log('6. Test the update system');

        console.log('\n📁 Files updated:');
        console.log(`- ${newPhpPath} (main plugin file)`);
        console.log('- package.json');
        console.log('- src/block.json');
        console.log('- scripts/sync-version.js');
        console.log('- .github/workflows/release.yml');
        console.log('- README.md');

    } catch (error) {
        console.error('❌ Error during setup:', error.message);
        process.exit(1);
    } finally {
        rl.close();
    }
}

// Run the setup
if (require.main === module) {
    setup();
}

module.exports = { setup }; 
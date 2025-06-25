# Quick Start Guide

Get your WordPress plugin up and running in minutes with this starter template!

## 🚀 5-Minute Setup

### 1. Clone and Setup

```bash
# Clone the starter template
git clone https://github.com/gbyat/wordpress-plugin-starter.git my-plugin
cd my-plugin

# Run the interactive setup
node setup.js
```

### 2. Install Dependencies

```bash
npm install
```

### 3. Build the Plugin

```bash
npm run build
```

### 4. Test Locally

- Copy the plugin folder to your WordPress `wp-content/plugins/` directory
- Activate the plugin in WordPress admin
- Go to Settings → [Your Plugin Name] to configure

### 5. Deploy to GitHub

```bash
# Create a new repository on GitHub
# Then push your code
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/your-username/your-repo.git
git push -u origin main
```

## 🔧 Configuration

### GitHub Token Setup

1. Go to [GitHub Settings → Tokens](https://github.com/settings/tokens)
2. Create a new token with `repo` and `workflow` permissions
3. Copy the token
4. Go to WordPress Admin → Settings → [Your Plugin Name]
5. Paste the token and save

### First Release

```bash
# Create your first release
npm run release:patch
```

This will:

- Bump the version number
- Sync versions between files
- Commit and push changes
- Create a GitHub release automatically

## 📁 Project Structure

```
my-plugin/
├── .github/workflows/release.yml  # CI/CD pipeline
├── src/
│   ├── block.json                 # Block configuration
│   ├── index.js                   # Block editor code
│   ├── index.css                  # Editor styles
│   └── style.css                  # Frontend styles
├── scripts/
│   └── sync-version.js            # Version sync script
├── my-plugin.php                  # Main plugin file
├── package.json                   # Dependencies & scripts
├── setup.js                       # Setup script
└── README.md                      # Documentation
```

## 🛠️ Development Commands

```bash
# Development build with watch mode
npm run start

# Production build
npm run build

# Version management
npm run release:patch   # 1.0.0 → 1.0.1
npm run release:minor   # 1.0.0 → 1.1.0
npm run release:major   # 1.0.0 → 2.0.0
```

## 🎯 Customization

### Adding Your Own Blocks

1. Edit `src/block.json` to define your block
2. Modify `src/index.js` to add your block logic
3. Update styles in `src/index.css` and `src/style.css`
4. Add PHP rendering in `my-plugin.php`

### Adding Admin Features

1. Add new tabs in the admin page
2. Create new settings sections
3. Add custom functionality to the main plugin class

### Update System

The update system is already configured and will:

- Check for new releases on GitHub
- Show update notifications in WordPress admin
- Allow one-click updates
- Display changelog information

## 🔍 Troubleshooting

### Build Issues

```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Update System Not Working

1. Check GitHub token is set correctly
2. Verify repository URL in plugin settings
3. Clear WordPress cache
4. Check GitHub release has the correct ZIP asset

### Plugin Not Loading

1. Check PHP error logs
2. Verify file permissions
3. Ensure all required files are present
4. Check WordPress version compatibility

## 📞 Support

- **Documentation**: See [README.md](README.md) for detailed information
- **Issues**: Report bugs on [GitHub Issues](https://github.com/gbyat/wordpress-plugin-starter/issues)
- **Examples**: Check the example block in `src/index.js` for reference

## 🎉 You're Ready!

Your WordPress plugin now has:

- ✅ Modern development environment
- ✅ Automatic GitHub updates
- ✅ CI/CD pipeline
- ✅ Version management
- ✅ Professional admin interface
- ✅ Block editor integration

Start building your awesome WordPress plugin! 🚀

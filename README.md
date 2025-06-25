# WordPress Plugin Starter Template

Ein professionelles Starter-Template für WordPress-Plugins mit automatischem GitHub-Update-System, CI/CD-Pipeline und moderner Entwicklungsumgebung.

## 🚀 Features

### ✅ **Automatisches Update-System**

- GitHub Releases Integration
- WordPress Dashboard Update-Benachrichtigungen
- Sichere Token-basierte Authentifizierung
- Automatische Version-Erkennung

### ✅ **CI/CD Pipeline**

- GitHub Actions für automatische Releases
- Automatisches Build und Packaging
- Version-Synchronisation zwischen package.json und PHP
- ZIP-Asset Upload zu GitHub Releases

### ✅ **Entwicklungstools**

- Webpack-basiertes Build-System
- Hot-Reload für Block-Entwicklung
- Automatische Changelog-Generierung
- Version-Management mit npm scripts

### ✅ **Admin Interface**

- Tab-basierte Einstellungsseite
- Debug-Informationen
- Cache-Management
- GitHub API-Tests

## 📦 Installation

### 1. Repository klonen

```bash
git clone https://github.com/gbyat/wordpress-plugin-starter.git my-plugin-name
cd my-plugin-name
```

### 2. Dependencies installieren

```bash
npm install
```

### 3. Plugin anpassen

- `plugin-name.php` umbenennen und anpassen
- `package.json` anpassen (Name, Version, etc.)
- GitHub Repository in `plugin-name.php` aktualisieren
- Plugin-spezifische Funktionalität hinzufügen

### 4. GitHub Repository erstellen

- Neues Repository auf GitHub erstellen
- Remote URL anpassen:

```bash
git remote set-url origin https://github.com/your-username/your-plugin-repo.git
```

## 🔧 Konfiguration

### GitHub Token einrichten

1. Gehe zu [GitHub Settings → Tokens](https://github.com/settings/tokens)
2. Erstelle einen neuen Token mit `repo` und `workflow` Berechtigungen
3. Kopiere den Token
4. Gehe zu WordPress Admin → Einstellungen → [Plugin Name]
5. Füge den Token ein und speichere

### Plugin-spezifische Anpassungen

1. **Plugin Header** in `plugin-name.php` anpassen:

```php
/**
 * Plugin Name: Your Plugin Name
 * Description: Your plugin description
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */
```

2. **GitHub Repository** in `plugin-name.php` aktualisieren:

```php
define('CFB_GITHUB_REPO', 'your-username/your-plugin-repo');
```

3. **Block-Namespace** in `src/block.json` anpassen:

```json
{
  "name": "your-namespace/your-block-name"
}
```

## 🚀 Entwicklung

### Build-System

```bash
# Development Build
npm run build

# Production Build
npm run build:prod

# Watch Mode
npm run start
```

### Version Management

```bash
# Patch Version (1.0.0 → 1.0.1)
npm run release:patch

# Minor Version (1.0.0 → 1.1.0)
npm run release:minor

# Major Version (1.0.0 → 2.0.0)
npm run release:major
```

### Release erstellen

1. Änderungen committen
2. Version bumpen: `npm run release:patch`
3. GitHub Actions erstellt automatisch ein Release
4. WordPress erkennt das Update automatisch

## 📁 Projektstruktur

```
wordpress-plugin-starter/
├── .github/
│   └── workflows/
│       └── release.yml          # GitHub Actions für Releases
├── src/
│   ├── block.json              # Block-Konfiguration
│   ├── index.js                # Block-Editor Code
│   ├── index.css               # Editor Styles
│   └── style.css               # Frontend Styles
├── scripts/
│   └── sync-version.js         # Version-Synchronisation
├── languages/                  # Übersetzungen
├── plugin-name.php             # Haupt-Plugin-Datei
├── package.json                # Dependencies und Scripts
├── webpack.config.js           # Build-Konfiguration
├── CHANGELOG.md                # Automatisch generiert
└── README.md                   # Diese Datei
```

## 🔍 Admin Interface

Das Plugin bietet eine tab-basierte Admin-Oberfläche:

### 📋 **Custom Fields Manager**

- Scan für Custom Fields
- Cache-Management
- Field-Übersicht mit Copy-Funktion

### ⚙️ **Settings**

- GitHub Token Konfiguration
- Plugin-spezifische Einstellungen

### 🐛 **Debug Info**

- Update-System Status
- GitHub API Tests
- Cache-Status
- System-Informationen

## 🛠️ Anpassungen für dein Plugin

### 1. **Plugin-Namen ändern**

- `plugin-name.php` umbenennen
- Plugin Header anpassen
- GitHub Repository URL aktualisieren

### 2. **Block-Funktionalität hinzufügen**

- `src/index.js` anpassen
- `src/block.json` konfigurieren
- Styles in `src/index.css` und `src/style.css`

### 3. **Admin-Interface erweitern**

- Neue Tabs in `plugin-name.php` hinzufügen
- Plugin-spezifische Einstellungen
- Custom Debug-Informationen

### 4. **Update-Logik anpassen**

- GitHub Repository in `plugin-name.php` ändern
- Plugin-spezifische Update-Logik hinzufügen

## 📝 Changelog

Das Changelog wird automatisch über Git Hooks generiert. Jeder Commit wird automatisch dokumentiert.

## 🤝 Beitragen

1. Fork das Repository
2. Erstelle einen Feature Branch
3. Committe deine Änderungen
4. Push zum Branch
5. Erstelle einen Pull Request

## 📄 Lizenz

GPL v2 oder später - siehe [LICENSE](LICENSE) Datei für Details.

## 🙏 Credits

Dieses Template basiert auf bewährten Praktiken für WordPress-Plugin-Entwicklung und automatische Update-Systeme.

---

**Hinweis:** Dies ist ein Template. Passe alle Plugin-spezifischen Inhalte an dein Projekt an!

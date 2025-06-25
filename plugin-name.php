<?php

/**
 * Plugin Name: WordPress Plugin Starter
 * Plugin URI: https://github.com/gbyat/wordpress-plugin-starter
 * Description: Ein Starter-Template für WordPress-Plugins mit automatischem GitHub-Update-System
 * Version: 1.0.3
 * Author: your name
 * License: GPL v2 or later
 * Text Domain: wordpress-plugin-starter
 * Domain Path: /languages
 * Update URI: https://github.com/your-github-username/wordpress-plugin-starter/releases/latest/download/wordspress-plugin-starter.zip
 * */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPS_VERSION', '1.0.3');
define('WPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPS_GITHUB_REPO', 'gbyat/wordpress-plugin-starter'); // Change this to your repo

// Initialize the plugin
new WordPressPluginStarter();

/**
 * Main plugin class
 */
class WordPressPluginStarter
{
    public function __init__()
    {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'upgrader_post_install'), 10, 3);

        // Admin settings - remove old menu first, then add new one
        add_action('admin_menu', array($this, 'remove_old_menu'), 5);
        add_action('admin_menu', array($this, 'add_admin_menu'), 10);
        add_action('admin_init', array($this, 'init_settings'));

        // Cache management
        add_action('save_post', array($this, 'clear_cache'));
        add_action('deleted_post', array($this, 'clear_cache'));
        add_action('updated_post_meta', array($this, 'clear_cache'));
        add_action('added_post_meta', array($this, 'clear_cache'));
        add_action('deleted_post_meta', array($this, 'clear_cache'));
    }

    /**
     * Remove old admin menu to prevent conflicts
     */
    public function remove_old_menu()
    {
        remove_submenu_page('options-general.php', 'wp-plugin-starter-settings');
    }

    /**
     * Check for plugin updates
     */
    public function check_for_updates($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugin_slug = basename(dirname(__FILE__));
        $plugin_file = basename(__FILE__);
        $plugin_path = $plugin_slug . '/' . $plugin_file;

        // Get latest release info from GitHub
        $latest_release = $this->get_latest_release();

        if ($latest_release && version_compare($latest_release['version'], WPS_VERSION, '>')) {
            $transient->response[$plugin_path] = (object) array(
                'slug' => $plugin_slug,
                'new_version' => $latest_release['version'],
                'url' => 'https://github.com/' . WPS_GITHUB_REPO,
                'package' => $latest_release['download_url'],
                'requires' => '5.0',
                'requires_php' => '7.4',
                'tested' => '6.4',
                'last_updated' => $latest_release['published_at'],
                'sections' => array(
                    'description' => $latest_release['description'],
                    'changelog' => $latest_release['changelog']
                )
            );
        }

        return $transient;
    }

    /**
     * Get plugin information for update screen
     */
    public function plugin_info($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        $plugin_slug = basename(dirname(__FILE__));

        if ($args->slug !== $plugin_slug) {
            return $result;
        }

        $latest_release = $this->get_latest_release();

        if (!$latest_release) {
            return $result;
        }

        return (object) array(
            'name' => 'WordPress Plugin Starter',
            'slug' => $plugin_slug,
            'version' => $latest_release['version'],
            'author' => 'Your Name',
            'author_profile' => 'https://github.com/gbyat',
            'last_updated' => $latest_release['published_at'],
            'requires' => '5.0',
            'requires_php' => '7.4',
            'tested' => '6.4',
            'download_link' => $latest_release['download_url'],
            'sections' => array(
                'description' => $latest_release['description'],
                'changelog' => $latest_release['changelog'],
                'installation' => 'Upload the plugin files to the /wp-content/plugins/wordpress-plugin-starter directory, or install the plugin through the WordPress plugins screen directly.',
                'screenshots' => ''
            )
        );
    }

    /**
     * Get latest release from GitHub
     */
    private function get_latest_release()
    {
        $cache_key = 'wps_latest_release';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $api_url = 'https://api.github.com/repos/' . WPS_GITHUB_REPO . '/releases/latest';

        $headers = array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version'),
            'Accept' => 'application/vnd.github.v3+json'
        );

        // Token aus den Plugin-Optionen holen
        $github_token = get_option('wps_github_token', '');
        if (!empty($github_token)) {
            $headers['Authorization'] = 'token ' . $github_token;
        }

        $response = wp_remote_get($api_url, array(
            'headers' => $headers,
            'timeout' => 15
        ));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $release = json_decode($body, true);

        if (!$release) {
            return false;
        }

        // Find the plugin zip file
        $download_url = '';
        foreach ($release['assets'] as $asset) {
            if ($asset['name'] === 'wordpress-plugin-starter.zip') {
                $download_url = $asset['browser_download_url'];
                break;
            }
        }

        $release_data = array(
            'version' => ltrim($release['tag_name'], 'v'),
            'download_url' => $download_url,
            'published_at' => $release['published_at'],
            'description' => $release['body'],
            'changelog' => $release['body']
        );

        // Cache for 12 hours
        set_transient($cache_key, $release_data, 12 * 3600);

        return $release_data;
    }

    /**
     * Handle post-installation
     */
    public function upgrader_post_install($response, $hook_extra, $result)
    {
        if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === basename(__FILE__)) {
            // Clear update cache
            delete_transient('wps_latest_release');
        }

        return $response;
    }

    /**
     * Clear update cache manually
     */
    public function clear_update_cache()
    {
        delete_transient('wps_latest_release');
        delete_site_transient('update_plugins');
        delete_site_transient('update_themes');
        delete_site_transient('update_core');

        // Force WordPress to check for updates immediately
        wp_schedule_single_event(time(), 'wp_version_check');
        wp_schedule_single_event(time(), 'wp_update_plugins');
        wp_schedule_single_event(time(), 'wp_update_themes');

        // Clear any cached plugin data
        wp_cache_flush();
    }

    /**
     * Initialize the plugin
     */
    public function init()
    {
        // Debug: Check if build directory exists
        if (!file_exists(WPS_PLUGIN_DIR . 'build/block.json')) {
            error_log('WordPress Plugin Starter: build/block.json not found!');
            return;
        }

        // Register block
        $block_result = register_block_type(WPS_PLUGIN_DIR . 'build', array(
            'render_callback' => array($this, 'render_block'),
        ));

        // Debug: Check if block registration was successful
        if (!$block_result) {
            error_log('WordPress Plugin Starter: Failed to register block!');
        } else {
            error_log('WordPress Plugin Starter: Block registered successfully!');
        }

        // Load text domain
        load_plugin_textdomain('wp-plugin-starter', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets()
    {
        wp_enqueue_script(
            'wp-plugin-starter-editor',
            WPS_PLUGIN_URL . 'build/index.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            WPS_VERSION
        );

        wp_enqueue_style(
            'wp-plugin-starter-editor',
            WPS_PLUGIN_URL . 'build/index.css',
            array('wp-edit-blocks'),
            WPS_VERSION
        );

        // Localize script with data
        wp_localize_script('wp-plugin-starter-editor', 'wpsData', array(
            'nonce' => wp_create_nonce('wps_nonce'),
        ));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets()
    {
        wp_enqueue_style(
            'wp-plugin-starter-frontend',
            WPS_PLUGIN_URL . 'build/style.css',
            array(),
            WPS_VERSION
        );
    }

    /**
     * Clear cache
     */
    public function clear_cache()
    {
        // Clear any plugin-specific caches here
        delete_transient('wps_cache');
    }

    /**
     * Render the block
     */
    public function render_block($attributes, $content)
    {
        // This is a template - customize this for your specific block functionality
        $text = isset($attributes['text']) ? $attributes['text'] : 'Hello from WordPress Plugin Starter!';
        $display_type = isset($attributes['displayType']) ? $attributes['displayType'] : 'paragraph';
        $heading_level = isset($attributes['headingLevel']) ? intval($attributes['headingLevel']) : 2;
        $typography = isset($attributes['typography']) ? $attributes['typography'] : array();
        $colors = isset($attributes['colors']) ? $attributes['colors'] : array();
        $spacing = isset($attributes['spacing']) ? $attributes['spacing'] : array();
        $alignment = isset($attributes['alignment']) ? $attributes['alignment'] : '';

        // Build inline styles
        $styles = array();

        // Typography styles
        if (!empty($typography)) {
            if (!empty($typography['fontSize'])) {
                $styles[] = 'font-size: ' . $typography['fontSize'] . 'px';
            }
            if (!empty($typography['fontWeight'])) {
                $styles[] = 'font-weight: ' . $typography['fontWeight'];
            }
            if (!empty($typography['lineHeight'])) {
                $styles[] = 'line-height: ' . $typography['lineHeight'];
            }
            if (!empty($typography['letterSpacing'])) {
                $styles[] = 'letter-spacing: ' . $typography['letterSpacing'] . 'px';
            }
        }

        // Color styles
        if (!empty($colors)) {
            if (!empty($colors['textColor'])) {
                $styles[] = 'color: ' . $colors['textColor'];
            }
            if (!empty($colors['backgroundColor'])) {
                $styles[] = 'background-color: ' . $colors['backgroundColor'];
            }
        }

        // Spacing styles
        if (!empty($spacing)) {
            if (!empty($spacing['marginTop'])) {
                $styles[] = 'margin-top: ' . $spacing['marginTop'] . 'px';
            }
            if (!empty($spacing['marginBottom'])) {
                $styles[] = 'margin-bottom: ' . $spacing['marginBottom'] . 'px';
            }
            if (!empty($spacing['paddingTop'])) {
                $styles[] = 'padding-top: ' . $spacing['paddingTop'] . 'px';
            }
            if (!empty($spacing['paddingBottom'])) {
                $styles[] = 'padding-bottom: ' . $spacing['paddingBottom'] . 'px';
            }
        }

        $style_attr = !empty($styles) ? ' style="' . esc_attr(implode('; ', $styles)) . '"' : '';

        // Build classes
        $classes = array('wps-block');
        if (!empty($alignment)) {
            $classes[] = 'has-text-align-' . $alignment;
        }
        if (!empty($colors['textColor'])) {
            $classes[] = 'has-text-color';
        }
        if (!empty($colors['backgroundColor'])) {
            $classes[] = 'has-background';
        }

        $class_attr = ' class="' . esc_attr(implode(' ', $classes)) . '"';

        // Render based on display type
        switch ($display_type) {
            case 'heading':
                // Validate heading level (1-6)
                $heading_level = max(1, min(6, $heading_level));
                $tag = 'h' . $heading_level;
                return '<' . $tag . $class_attr . $style_attr . '>' . esc_html($text) . '</' . $tag . '>';

            case 'paragraph':
            default:
                return '<p' . $class_attr . $style_attr . '>' . esc_html($text) . '</p>';
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_options_page(
            'WordPress Plugin Starter',
            'Plugin Starter',
            'manage_options',
            'wp-plugin-starter',
            array($this, 'admin_page')
        );
    }

    /**
     * Initialize settings
     */
    public function init_settings()
    {
        register_setting('wps_settings', 'wps_github_token');
        register_setting('wps_settings', 'wps_cache', array(
            'type' => 'array',
            'default' => array()
        ));

        add_settings_section(
            'wps_github_section',
            'GitHub Update Settings',
            array($this, 'github_section_callback'),
            'wps_settings'
        );

        add_settings_field(
            'wps_github_token',
            'GitHub Personal Access Token',
            array($this, 'github_token_callback'),
            'wps_settings',
            'wps_github_section'
        );
    }

    /**
     * GitHub section callback
     */
    public function github_section_callback()
    {
        echo '<p>Configure GitHub integration for automatic plugin updates.</p>';
    }

    /**
     * GitHub token callback
     */
    public function github_token_callback()
    {
        $token = get_option('wps_github_token');
        echo '<input type="password" name="wps_github_token" value="' . esc_attr($token) . '" class="regular-text" />';
        echo '<p class="description">Enter your GitHub Personal Access Token for automatic updates.</p>';
    }

    /**
     * Main admin page with tabs
     */
    public function admin_page()
    {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';

        // Handle form submissions
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'clear_cache':
                    $this->clear_cache();
                    break;
                case 'clear_update_cache':
                    $this->clear_update_cache();
                    break;
                case 'save_settings':
                    $this->save_settings();
                    break;
            }
        }
?>
        <div class="wrap">
            <h1>WordPress Plugin Starter</h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=wp-plugin-starter&tab=overview"
                    class="nav-tab <?php echo $active_tab === 'overview' ? 'nav-tab-active' : ''; ?>">
                    Overview
                </a>
                <a href="?page=wp-plugin-starter&tab=settings"
                    class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    Settings
                </a>
                <a href="?page=wp-plugin-starter&tab=debug"
                    class="nav-tab <?php echo $active_tab === 'debug' ? 'nav-tab-active' : ''; ?>">
                    Debug Info
                </a>
            </nav>

            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'overview':
                        $this->overview_tab();
                        break;
                    case 'settings':
                        $this->settings_tab();
                        break;
                    case 'debug':
                        $this->debug_tab();
                        break;
                }
                ?>
            </div>
        </div>
    <?php
    }

    /**
     * Overview tab
     */
    private function overview_tab()
    {
    ?>
        <div class="tab-pane">
            <h2>Plugin Overview</h2>

            <div class="card">
                <h3>Welcome to WordPress Plugin Starter!</h3>
                <p>This is a starter template for WordPress plugins with automatic GitHub updates, CI/CD pipeline, and modern development tools.</p>

                <h4>Features included:</h4>
                <ul>
                    <li>✅ Automatic GitHub Update System</li>
                    <li>✅ GitHub Actions CI/CD Pipeline</li>
                    <li>✅ Version Management with npm scripts</li>
                    <li>✅ Tab-based Admin Interface</li>
                    <li>✅ Debug Information and Cache Management</li>
                    <li>✅ Webpack Build System</li>
                    <li>✅ Block Editor Integration</li>
                </ul>

                <h4>Next steps:</h4>
                <ol>
                    <li>Customize the plugin name and description</li>
                    <li>Update the GitHub repository URL</li>
                    <li>Add your specific plugin functionality</li>
                    <li>Configure GitHub token for updates</li>
                    <li>Test the update system</li>
                </ol>
            </div>

            <div class="cfb-actions">
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="clear_cache">
                    <?php wp_nonce_field('wps_clear_cache', 'wps_nonce'); ?>
                    <button type="submit" class="button button-secondary">
                        🗑️ Clear Cache
                    </button>
                </form>
            </div>
        </div>
    <?php
    }

    /**
     * Settings tab
     */
    private function settings_tab()
    {
    ?>
        <div class="tab-pane">
            <h2>Settings</h2>

            <form method="post">
                <input type="hidden" name="action" value="save_settings">
                <?php wp_nonce_field('wps_save_settings', 'wps_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">GitHub Token</th>
                        <td>
                            <input type="password" name="wps_github_token"
                                value="<?php echo esc_attr(get_option('wps_github_token')); ?>"
                                class="regular-text" />
                            <p class="description">Personal Access Token for automatic plugin updates</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
    <?php
    }

    /**
     * Debug tab
     */
    private function debug_tab()
    {
        $latest_release = $this->get_latest_release();
        $github_token = get_option('wps_github_token');
    ?>
        <div class="tab-pane">
            <h2>Debug Information</h2>

            <h3>Update System</h3>
            <table class="form-table">
                <tr>
                    <th>Current Version</th>
                    <td><strong><?php echo WPS_VERSION; ?></strong></td>
                </tr>
                <tr>
                    <th>Latest Version</th>
                    <td>
                        <?php if ($latest_release): ?>
                            <strong><?php echo esc_html($latest_release['version']); ?></strong>
                            <?php if (version_compare($latest_release['version'], WPS_VERSION, '>')): ?>
                                <span style="color: green;">✅ Update available!</span>
                            <?php else: ?>
                                <span style="color: blue;">✅ Up to date</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: red;">❌ Could not fetch latest release</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>GitHub Token</th>
                    <td>
                        <?php if ($github_token): ?>
                            <span style="color: green;">✅ Set (<?php echo substr($github_token, 0, 8) . '...'; ?>)</span>
                        <?php else: ?>
                            <span style="color: red;">❌ Not set</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>GitHub Repository</th>
                    <td><code><?php echo WPS_GITHUB_REPO; ?></code></td>
                </tr>
            </table>

            <h3>System Information</h3>
            <table class="form-table">
                <tr>
                    <th>WordPress Version</th>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <th>PHP Version</th>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <th>Plugin Directory</th>
                    <td><code><?php echo WPS_PLUGIN_DIR; ?></code></td>
                </tr>
                <tr>
                    <th>Plugin URL</th>
                    <td><code><?php echo WPS_PLUGIN_URL; ?></code></td>
                </tr>
            </table>

            <h3>Cache Management</h3>
            <div class="cfb-actions">
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="clear_update_cache">
                    <?php wp_nonce_field('wps_clear_update_cache', 'wps_nonce'); ?>
                    <button type="submit" class="button button-secondary">
                        🗑️ Clear Update Cache
                    </button>
                </form>

                <form method="post" style="display: inline; margin-left: 10px;">
                    <input type="hidden" name="action" value="clear_cache">
                    <?php wp_nonce_field('wps_clear_cache', 'wps_nonce'); ?>
                    <button type="submit" class="button button-secondary">
                        🗑️ Clear Plugin Cache
                    </button>
                </form>

                <a href="<?php echo admin_url('update-core.php'); ?>" class="button button-primary" style="margin-left: 10px;">
                    🔄 Check for Updates
                </a>
            </div>
        </div>
<?php
    }

    /**
     * Save settings
     */
    private function save_settings()
    {
        if (!wp_verify_nonce($_POST['wps_nonce'], 'wps_save_settings')) {
            wp_die('Security check failed');
        }

        if (isset($_POST['wps_github_token'])) {
            update_option('wps_github_token', sanitize_text_field($_POST['wps_github_token']));
        }

        add_action('admin_notices', function () {
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        });
    }
}

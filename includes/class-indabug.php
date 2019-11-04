<?php
/**
 * Indabug class file
 *
 * @package Indabug
 */

namespace Wpseed\Indabug;

/**
 * Main initiation plugin class.
 *
 * @since  1.0.0
 */
final class Indabug implements Plugin {

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $basename = '';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $url = '';

	/**
	 * Debug Bar instance.
	 *
	 * @var Debug_Bar
	 * @since  1.0.0
	 */
	protected $debugbar;

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    Indabug
	 * @since  1.0.0
	 */
	protected static $single_instance = null;

	/**
	 * Sets up our plugin.
	 *
	 * @since  1.0.0
	 */
	protected function __construct() {
		$this->path     = WPSEED_INDABUG_PATH;
		$this->basename = WPSEED_INDABUG_BASENAME;
		$this->url      = WPSEED_INDABUG_URL;
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  1.0.0
	 */
	public function plugin_classes() {
		$this->debugbar = new Debug_Bar();
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return  Indabug A single instance of this class.
	 * @since   1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  1.0.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
	}

	/**
	 * Activate the plugin.
	 *
	 * @since  1.0.0
	 */
	public function activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  1.0.0
	 */
	public function deactivate() {
		// Add deactivation cleanup functionality here.
	}

	/**
	 * Init hooks
	 *
	 * @since  1.0.0
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'indabug', false, dirname( $this->basename ) . '/languages/' );

		if ( $this->should_display_toolbar() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action(
				'wp_print_footer_scripts',
				function () {
					echo wp_kses( $this->debugbar->getJavascriptRenderer()->render(), array( 'script' => array() ) );
				},
				1000
			);
		}

		// Initialize plugin classes.
		$this->plugin_classes();
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @return boolean True if requirements met, false if not.
	 * @since  1.0.0
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  1.0.0
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @return boolean True if requirements are met.
	 * @since  1.0.0
	 */
	public function meets_requirements() {
		if ( version_compare( WPSEED_INDABUG_MIN_PHP_VERSION, phpversion(), '>' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  1.0.0
	 */
	public function requirements_not_met_notice() {

		/* translators: 1: Admin Url */
		$default_message = sprintf( __( 'Indabug is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'indabug' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Check of toolbar display.
	 *
	 * @return boolean True if toolbar must be shown.
	 * @since  1.0.0
	 */
	public function should_display_toolbar() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		return get_current_user_id() && ( current_user_can( 'administrator' ) || is_super_admin( get_current_user_id() ) );
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $id Property to get.
	 *
	 * @return mixed         Value of the field.
	 * @throws \Exception     Throws an exception if the property is invalid.
	 * @since  1.0.0
	 */
	public function get( $id ) {
		switch ( $id ) {
			case 'path':
			case 'url':
				return $this->$id;
			case 'debugbar':
				return $this->debugbar;
			default:
				throw new \Exception( 'Invalid ' . __CLASS__ . ' property: ' . $id );
		}
	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'wpseed-indabug-font-awesome-min',
			WPSEED_INDABUG_URL . 'assets/vendor/font-awesome/css/font-awesome.min.css',
			array(),
			WPSEED_INDABUG_VERSION
		);

		wp_enqueue_style(
			'wpseed-indabug-highlightjs',
			WPSEED_INDABUG_URL . 'assets/vendor/highlightjs/css/github.css',
			array(),
			WPSEED_INDABUG_VERSION
		);

		wp_enqueue_style(
			'wpseed-indabug-debugbar',
			WPSEED_INDABUG_URL . 'assets/vendor/debugbar/css/debugbar.css',
			array(),
			WPSEED_INDABUG_VERSION
		);

		wp_enqueue_style(
			'wpseed-indabug-widgets',
			WPSEED_INDABUG_URL . 'assets/vendor/debugbar/css/widgets.css',
			array(),
			WPSEED_INDABUG_VERSION
		);

		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		wp_enqueue_script(
			'wpseed-indabug-highlightjs',
			WPSEED_INDABUG_URL . 'assets/vendor/highlightjs/js/highlight.pack.js',
			array( 'jquery' ),
			WPSEED_INDABUG_VERSION,
			true
		);

		wp_enqueue_script(
			'wpseed-indabug-debugbar',
			WPSEED_INDABUG_URL . 'assets/vendor/debugbar/js/debugbar.js',
			array( 'jquery' ),
			WPSEED_INDABUG_VERSION,
			true
		);

		wp_enqueue_script(
			'wpseed-indabug-widgets',
			WPSEED_INDABUG_URL . 'assets/vendor/debugbar/js/widgets.js',
			array( 'jquery' ),
			WPSEED_INDABUG_VERSION,
			true
		);
	}

	/**
	 * Push debug message to toolbar.
	 *
	 * @param string $message Debug message.
	 *
	 * @return mixed
	 */
	public function debug( $message ) {
		return $this->debugbar['messages']->debug( $message );
	}
}

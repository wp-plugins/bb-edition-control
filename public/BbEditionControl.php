<?php
/**
 * Bb Edition Control
 *
 * @package   BbEditionControl
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 *
 * @package BbEditionControl
 * @author  Bruno Barros <bruno@brunobarros.com>
 */
class BbEditionControl {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'bb-edition-control';

	/**
	 * Chave do metadado sobre edição em cada post
	 * @var string
	 */
	public $postMetaKey = '_bb_edition_control';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance os BbEditionControlDb
	 * @var object
	 */
	public $DB;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$this->DB = new BbEditionControlDb();

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_shortcode('bbec-list', array( $this, 'shortcode_editions_list' ) );
		add_shortcode('bbec-combo', array( $this, 'shortcode_editions_combo' ) );
		add_shortcode('bbec-active-name', array( $this, 'shortcode_active_name' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		// add_action( '@TODO', array( $this, 'action_method_name' ) );
		// add_filter( '@TODO', array( $this, 'filter_method_name' ) );
		
		add_action( 'init', array( $this, 'rewrite_rules' ) );

		// add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		// add_action( 'rewrite_rules_array', array( $this, 'rewrite_rules' ) );

		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		
		// altera a query principal e adiciona restrição da edição
		add_filter( 'pre_get_posts', array( $this, 'filter_pre_get_posts' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 *@return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return the class name
	 * 
	 * @return string
	 */
	public function get_class_name()
	{
		return get_class($this);
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * Create database
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		$db = new BbEditionControlDb();
		$db->createTable();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		$db = new BbEditionControlDb();
		// $db->dropTable();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( plugin_dir_path( __DIR__ ) ) .'languages/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	public function rewrite_rules($rules = array())
	{
		// var_dump( get_query_var('edition') );
		// var_dump( $_GET['edition'] );
		// 
		add_rewrite_rule(  
        '^edition/([^/]+)',  
        'index.php?pagename=$matches[1]&id=$matches[2]',  
        "top");

        add_rewrite_rule('by\-date/([0-9]{4}\-[0-9]{2}\-[0-9]{2})$', 'index.php?post_type=event&event_date=$matches[1]', 'top');
  //       $newrules = array();
		// $newrules['(edition)/([^/]+)$'] = 'index.php?pagename=$matches[1]&id=$matches[2]';
		// return $newrules + $rules;
	}

	public function query_vars($vars)
	{
		array_push($vars, 'edition');
    	return $vars;
	}

	/**
	 * Altera a query principal para filtrar os conteúdos da edição
	 * @param  object $query
	 * @return object
	 */
	public function filter_pre_get_posts($query)
	{
		if(! is_admin() && $query->is_main_query() ){
		// echo "<pre>"; 
		// var_dump( is_front_page() );
		// echo "</pre>";
		}
		$query->set( 'is_by_edition', false );
		if (! is_admin() && ! is_singular() && $query->is_main_query() && $this->checkValidTemplate($query) ) {

			$edition = $this->getEdition();			

			// $query->set( 'post_type', 'event' );
			// $query->set( 'post_status', 'publish' );
			// $query->set( 'orderby', 'meta_value' );
			// $query->set( 'order', 'ASC' );
			// $query->set( 'post_per_page', -1 );
			$query->set( 'is_by_edition', true );
			$query->set( 'meta_key', $this->postMetaKey );

			$meta_query = array(
				array(
					'key' => $this->postMetaKey,
					'value' => $edition->id, // get_query_var( 'event_date' )
					'type' => 'NUMERIC',
					'compare' => '='
					)
				);

			$query->set( 'meta_query', $meta_query );
		}

    	return $query;
	}

	/**
	 * Retorna a última edição, ou a que estiver ativa
	 * @return object Edição
	 */
	public function getEdition( $specific = null)
	{
		if( strtolower($specific) === 'latest')
		{
			$edition = $this->DB->getLatest();
		}
		else
		{
			// @todo verifica se a url tem uma edição
			// senão pega a última
			$edition = $this->DB->getLatest();			
		}
		return $edition;
	}

	/**
	 * Verifica se a query atual permite usar o filtro de edições
	 * @param  object $query 
	 * @return bool
	 */
	private function checkValidTemplate($query)
	{
		$templates = get_option('bbec-templates');

		$passed = false;
		for($x = 0; $x < count($templates); $x++)
		{
			$tmpl = $templates[$x];
			// verifica se a query valida este template
			if($query->$tmpl){
				$passed = true;
				break;
			}
		}

		return $passed;
	}

	public function template_redirect()
	{
		if( isset($_GET['edition']) )
		{
			$template_filename = TEMPLATEPATH. "/edition.php";
			if ( file_exists($template_filename) )
			{
				load_template($template_filename);
				return;
				// exit;
			}
			
		}
	}

	/**
	 * Gera lista não ordenada das edições
	 * @param  array  $atts Opções
	 * @return string
	 */
	public function shortcode_editions_list($atts)
	{
		$editions = $this->DB->getActive();

		if( count($editions) === 0 ){
			return '';
		}

		$opt = array_merge(array(
			'id' => '',
			'class' => 'bb-edition-control',
		), (array)$atts);

		$id = ($opt['id']) ? "id=\"{$opt['id']}\"" : '';

		$h = "<ul {$id} class=\"{$opt['class']}\">";

		foreach ($editions as $e):

			$h .= "<li><a href=\"{$e->slug}\">{$e->name}</a></li>";

		endforeach;

		$h .= '</ul>';
		return $h;
	}

	/**
	 * Gera combobox com as edições ativas
	 * @param  array $atts Opções
	 * @return string
	 */
	public function shortcode_editions_combo($atts)
	{
		$editions = $this->DB->getActive();

		if( count($editions) === 0 ){
			return '';
		}

		$opt = array_merge(array(
			'id' => '',
			'class' => 'bb-edition-control',
			'name' => 'edition',
		), (array)$atts);

		$id = ($opt['id']) ? "id=\"{$opt['id']}\"" : '';

		$h = "<select {$id} class=\"{$opt['class']}\" name=\"{$opt['name']}\">";

		foreach ($editions as $e):

			$h .= "<option value=\"{$e->slug}\">{$e->name}</option>";

		endforeach;

		$h .= '</select>';
		return $h;
	}

	/**
	 * Retorna o nome da edição ativa
	 * @return string
	 */
	public function shortcode_active_name()
	{
		$ed = $this->getEdition();
		return ($ed) ? $ed->name : '';
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}

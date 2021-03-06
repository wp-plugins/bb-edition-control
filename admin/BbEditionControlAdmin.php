<?php
/**
 * Plugin Name.
 *
 * @package   BbEditionControlAdmin
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 *
 * @package BbEditionControlAdmin
 * @author  Bruno Barros <bruno@brunobarros.com>
 */
class BbEditionControlAdmin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Instance os BbEditionControlDb
	 * @var object
	 */
	public $DB;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// Db object
		$this->DB = new BbEditionControlDb();

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = BbEditionControl::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// create metabox on posts
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $plugin->get_class_name() . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */

		// ao submeter form para adicionar nova edição
		add_action( 'admin_init', array( $this, 'action_form_submited' ), 10 );

		// ao salvar um post verifica a edição para salvar
		add_action('save_post', array( $this, 'action_save_post' ), 10, 2);

		// Add action to the manage post column to display the data
		add_action( 'manage_posts_custom_column' , array( $this, 'action_custom_columns' ) );

		// load custom sortable filter
		add_action( 'load-edit.php', array( $this, 'action_load_sortable_columns' ) );

		// Add a column to the edit post list
		add_action( 'admin_init', array( $this, 'add_filter_add_new_columns' ), 10 );

		// register column sortable
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'filter_register_sortable_columns' ) );


		// add_filter( '@TODO', array( $this, 'filter_method_name' ) );


	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( 'jquery-theme', plugins_url( 'assets/css/smoothness/jquery-ui-1.10.3.custom.min.css', __FILE__ ) );
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), BbEditionControl::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script('jquery-ui-datepicker');
			// jquery.slugify.js
			wp_enqueue_script( $this->plugin_slug . '-slugify-script', plugins_url( 'assets/js/jquery.slugify.js', __FILE__ ), array( 'jquery' ), BbEditionControl::VERSION );
			// admin.js
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), BbEditionControl::VERSION );
		}

	}

	/**
	 * Cria metabox
	 * @param string $postType
	 */
	public function add_meta_box($postType)
	{
		$postTypes = get_option('bbec-posttypes');
		if ( current_user_can('manage_options') &&  in_array($postType, (array)$postTypes) )
		{
			//add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );			
			add_meta_box($this->plugin_slug, __('Edition', $this->plugin_slug), array($this, "render_meta_box"), $postType, 'side', 'high');
		}
	}

	/**
	 * Monta view do metabox
	 * 
	 */
	public function render_meta_box()
	{
		global $wpdb, $post;

//		$e = $this->DB->getActive();
		$e = $this->DB->getAll();
		$pe = $this->DB->getPostEditionId($post);
		// $this->dd($pe);

		require_once('views/metabox.php');

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * Se alterar a localização no menu, não esqueçer de atualizar $this->url();
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *        
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Edition Control', $this->plugin_slug ),
			__( 'Edition Control', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
			);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		include_once('views/tabs.php');

		// mendagem para remoção de uma edição
		if(isset($_GET['delete']) && is_numeric($_GET['delete']))
		{
			echo "delete";
		}

		if( $this->getTab() == 'edit' )
		{
			$item = $this->DB->get($_GET['edit']);
			include_once( 'views/edit.php' );
		}
		else if( $this->getTab() == 'new' )
		{
			include_once( 'views/new.php' );
		}
		else if( $this->getTab() == 'options' )
		{
			include_once( 'views/options.php' );
		}
		else if( $this->getTab() == 'delete' )
		{
			$item = $this->DB->get($_GET['id']);
			include_once( 'views/delete.php' );
		}
		else
		{
			$r = $this->DB->getAllPaginated(isset($_GET['p']) ?$_GET['p']: 1);
			// $this->dd($list);
			include_once( 'views/list.php' );
		}

	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Opções', $this->plugin_slug ) . '</a>'
				),
			$links
			);

	}

	/**
	 * Verifica requisições dos formulários e executa métodos correspondentes
	 * @return void
	 */
	public function action_form_submited()
	{
		// insere nova edição
		if(isset($_POST['bb_add_new_hidden']) && $_POST['bb_add_new_hidden'] === 'Y')
		{
			try{
				$save = $this->DB->saveNewEdition($_POST);
				if( $save )
				{
					$this->message('Edition saved', 'updated');
					do_action('bbec_edition_created');
				}
			} catch (Exception $e) {
				$this->message($e->getMessage(), 'error');
			}
		}
		// editando uma edição
		else if(isset($_POST['bb_update_hidden']) && $_POST['bb_update_hidden'] === 'Y')
		{
			try{
				$save = $this->DB->updateEdition($_POST['bb_update_id'], $_POST);
				if( $save )
				{
					$this->message('Edition saved', 'updated');
					do_action('bbec_edition_updated', $_POST['bb_update_id']);
				}
			} catch (Exception $e) {
				$this->message($e->getMessage(), 'error');
			}			
		}
		// atualiza opções do plugin
		else if(isset($_POST['bb_options_hidden']) && $_POST['bb_options_hidden'] === 'Y')
		{
			// $this->dd($_POST);
			try {
				$save = $this->DB->saveOptions($_POST);
				if( $save )
				{
					$this->message('Options saved', 'updated');
					do_action('bbec_options_updated', $_POST);
				}
			} catch (Exception $e) {
				$this->message($e->getMessage(), 'error');
			}
		}
		// cria uma edição no modo rápido
		else if(isset($_GET['quickcreate']) && $_GET['quickcreate'] == 1)
		{
			try{
				$generatedData = $this->DB->createQuickNewEdition();				
				// $this->dd($generatedData);
				$save = $this->DB->saveNewEdition($generatedData);
				if( $save )
				{
					$this->message('Edition saved', 'updated');
					do_action('bbec_edition_created');
				}
			} catch (Exception $e) {
				$this->message($e->getMessage(), 'error');
			}
			wp_redirect( $this->url() );
			exit;
		}
		// exclui 
		else if(isset($_POST['bb_delete_hidden']) && $_POST['bb_delete_hidden'] === 'Y')
		{
			try{
				$del = $this->DB->removeEdition($_POST['bb_delete_id']);
				if( $del )
				{
					$this->message('Edition removed', 'updated');
					do_action('bbec_edition_deleted', $_POST['bb_delete_id']);
				}
			} catch (Exception $e) {
				$this->message($e->getMessage(), 'error');
			}
			wp_redirect( $this->url() );
			exit;
		}
		
	}


	/**
	 * Callback para savlar edição ao salvar um post
	 * @param  int $postId
	 * @param  object $post
	 * @return void
	 */
	public function action_save_post($postId, $post)
	{
		if( isset($_POST['bb_edition_control_selected']) )
		{
			try{
				$save = $this->DB->savePostEdition($postId, $_POST['bb_edition_control_selected']);
			}
			catch(Exception $e)
			{
				$this->dd($e->getMessage());
			}
		}
		// $this->dd('Salvou um post', false);
		// $this->dd( func_get_args());
		// $this->dd($post);
	}


	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

	/**
	 * Retorna o endereço do plugin da administração e adiciona parâmetros
	 * @param  string $value 
	 * @return string
	 */
	public function url($value='')
	{
		$pieces = (! is_array($value)) ? array($value) : $value;
		$vals = '&' . implode('&', $pieces);
		return admin_url( 'options-general.php?page=' . $this->plugin_slug . $vals );
	}

	/**
	 * Retorna o valor da tab ativa:
	 * - list
	 * - new
	 * - edit
	 * @return string
	 */
	public function getTab()
	{
		if(! isset($_GET['tab']) && ! isset($_GET['edit']) )
		{
			$tab = 'list';
		}
		else if( isset($_GET['edit']) )
		{
			$tab = 'edit';
		}
		else
		{
			$tab = $_GET['tab'];
		}

		return $tab;
	}


	/**
	 * Retorna o endereço atual para preencher formulário
	 * @return string
	 */
	public function form_action_url()
	{
		unset($_GET['edit']); // delete edit parameter;
		$qs = http_build_query($_GET);

		return str_replace( '%7E', '~', $_SERVER['PHP_SELF']) . "?{$qs}";
	}

	/**
	 * Monta html da mensagem
	 * @param  string $msg  [description]
	 * @param  string $type updated|error
	 * @return string
	 */
	public function message($msg = 'Edition saved', $type = 'updated')
	{
		add_action( 'admin_notices', function() use ($msg, $type){
			echo "<div id=\"message\" class=\"{$type}\"><p><strong>".__($msg, $this->plugin_slug )
				."</strong></p></div>  ";
		} );
	}
	

	/**
	 * Helper para debugar app
	 * @param  string  $value 
	 * @param  boolean $die   
	 */
	public function dd($value='', $die = true)
	{
		echo '<pre>';
		var_dump($value);
		if($die) exit;
	}

	/**
	 * Cria os filtros para coluna personalizada nos posttypes configurados
	 */
	public function add_filter_add_new_columns()
	{
		$posttypes = get_option('bbec-posttypes');

		for($x = 0; $x < count($posttypes); $x++)
		{
			$pt = $posttypes[$x];
			add_filter( 'manage_'.$pt.'_posts_columns', array( $this, 'filter_add_new_columns' ), 10, 1);			
		}
	}

	/**
	 * Add new columns to the post table
	 *
	 * @param array $columns Current columns on the list post
	 */
	public function filter_add_new_columns( $columns ) {
		global $post;

		// var_dump($post);
		

		// 
		// if(){}
		$column_meta = array( 'edition' => __('Edition', $this->plugin_slug) );
		// position
		$columns = array_slice( $columns, 0, 2, true ) + $column_meta + array_slice( $columns, 2, NULL, true );
		return $columns;
	}

	/**
	 * Add content to custom columns when listing
	 * @param  string $column Column index
	 * @return string
	 */
	public function action_custom_columns($column = '')
	{
		global $post;

		switch ( $column ) {
			case 'edition':
				$metaData = $this->DB->getPostEdition($post);
				echo ($metaData) ? $metaData->name : '-';
			break;
		}
	}


	/**
	 * Register the column as sortable
	 * @param  array $columns
	 * @return array
	 */
	public function filter_register_sortable_columns( $columns ) {
	    $columns['edition'] = 'edition';
	    return $columns;
	}

	/**
	 * Adiciona filtro para ordenação pela edição
	 * @return void
	 */
	public function action_load_sortable_columns()
	{
		add_filter( 'request', array($this, 'filter_sortable_columns') );
	}

	/**
	 * Filtro executado ao ordenar colunas oela edição.
	 * Posts que não tenham edição não serão exibidos
	 * @param  array $vars 
	 * @return array
	 */
	public function filter_sortable_columns($vars = '')
	{
		// return $vars;
		// check correct post types
		if ( isset( $vars['post_type'] ) && 'page' !== $vars['post_type'] ) {

			// check if orderby is set to 'edition'
			if ( isset( $vars['orderby'] ) && 'edition' == $vars['orderby'] ) {

				// Merge the query vars with our custom variables.
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_bb_edition_control',
						'orderby' => 'meta_value'
					)
				);
			}
		}

		return $vars;
	}

}

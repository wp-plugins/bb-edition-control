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
class BbEditionControl
{

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.3.4
     *
     * @var     string
     */
    const VERSION = '1.3.4';

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
     * Uri (slug) do post type $posyTypeId
     * @var string
     */
    public $postTypeUri = 'edition';

    /**
     * Identificador do post type de edições
     * @var string
     */
    public $postTypeId = 'edition-control';

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
    private function __construct()
    {

        $this->postTypeUri = get_option('bbec-posttype', 'edition');

        $this->DB = new BbEditionControlDb();

        // Load plugin text domain
        add_action('init', array($this, 'load_plugin_textdomain'));

        // Activate plugin when new blog is added
        add_action('wpmu_new_blog', array($this, 'activate_new_site'));

        // Load public-facing style sheet and JavaScript.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        add_shortcode('bbec-list', array($this, 'shortcode_editions_list'));
        add_shortcode('bbec-list-li', array($this, 'shortcode_editions_list_li'));
        add_shortcode('bbec-combo', array($this, 'shortcode_editions_combo'));
        add_shortcode('bbec-active-name', array($this, 'shortcode_active_name'));

        /* Define custom functionality.
         * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         */
        // add_action( '@TODO', array( $this, 'action_method_name' ) );
        // add_filter( '@TODO', array( $this, 'filter_method_name' ) );

        add_action('init', array($this, 'register_taxonomy'));
        add_action('init', array($this, 'rewrite_rules'));

        // add_action( 'template_redirect', array( $this, 'template_redirect' ) );
        // add_action( 'rewrite_rules_array', array( $this, 'rewrite_rules' ) );

        add_filter('query_vars', array($this, 'query_vars'));

        // altera a query principal e adiciona restrição da edição
        add_filter('pre_get_posts', array($this, 'filter_pre_get_posts'));

    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_plugin_slug()
    {
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
    public static function get_instance()
    {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean $network_wide True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite())
        {

            if ($network_wide)
            {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id)
                {

                    switch_to_blog($blog_id);
                    self::single_activate();
                }

                restore_current_blog();

            }
            else
            {
                self::single_activate();
            }

        }
        else
        {
            self::single_activate();
        }

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean $network_wide True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite())
        {

            if ($network_wide)
            {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id)
                {

                    switch_to_blog($blog_id);
                    self::single_deactivate();

                }

                restore_current_blog();

            }
            else
            {
                self::single_deactivate();
            }

        }
        else
        {
            self::single_deactivate();
        }

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int $blog_id ID of the new blog.
     */
    public function activate_new_site($blog_id)
    {

        if (1 !== did_action('wpmu_new_blog'))
        {
            return;
        }

        switch_to_blog($blog_id);
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
    private static function get_blog_ids()
    {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

        return $wpdb->get_col($sql);

    }

    /**
     * Fired for each blog when the plugin is activated.
     *
     * Create database
     *
     * @since    1.0.0
     */
    private static function single_activate()
    {
        $db = new BbEditionControlDb();
        $db->createTable();
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private static function single_deactivate()
    {
        $db = new BbEditionControlDb();
        // $db->dropTable();
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        $domain = $this->plugin_slug;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, trailingslashit(plugin_dir_path(__DIR__)) . 'languages/' . $domain . '-' . $locale . '.mo');

    }

    /**
     * Register and enqueue public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_slug . '-plugin-styles', plugins_url('assets/css/public.css', __FILE__), array(), self::VERSION);
    }

    /**
     * Register and enqueues public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_slug . '-plugin-script', plugins_url('assets/js/public.js', __FILE__), array('jquery'), self::VERSION);
    }

    /**
     * Cria taxonimia para poder utilizar o template archive-edition.php
     * @return void
     */
    public function register_taxonomy()
    {
        $labels = array(
            'menu_name'     => 'Editions',
            'name'          => 'Edition',
            'singular_name' => 'Edition'
        );

        $args = array(
            'labels'              => $labels,
            'description'         => '',
            'public'              => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'show_ui'             => false,
            'show_in_menu'        => true,
            'menu_icon'           => null,
            'query_var'           => true,
            'rewrite'             => array('slug' => $this->postTypeUri),
            'capability_type'     => 'page', // post|page
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 4,
            'supports'            => array('title', 'editor', 'excerpt', 'trackbacks', 'custom-fields', 'page-attributes')
        );

        register_post_type($this->postTypeId, $args);
    }

    /**
     * Retorna a url absoluta para a página personalizada de edições
     * @param  string $slug
     * @return string
     */
    public function getEditionUrl($slug = '')
    {
        return get_bloginfo('url') . '/' . $this->postTypeUri . '/' . trim($slug, '/');
    }

    /**
     * Url personalizadas [description]
     * @return void
     */
    public function rewrite_rules($rules = array())
    {
        add_rewrite_rule(
            $this->postTypeUri . '/(\S+)/?$',
            'index.php?post_type=' . $this->postTypeId . '&edition_id=$matches[1]',
            "top");

        add_rewrite_tag('%edition_id%', '(\S+)');
    }

    public function query_vars($vars)
    {
        array_push($vars, 'edition_id');

        return $vars;
    }

    /**
     * Altera a query principal para filtrar os conteúdos da edição
     * @param  object $query
     * @return object
     */
    public function filter_pre_get_posts($query)
    {

        $qs = parse_query_string();

        $pageOfEditionsFilter = (isset($qs[0]) && $qs[0] == $this->postTypeUri) ? true : false;

        $query->set('is_by_edition', false);
        if (!is_admin()
            && !is_singular()
            && $query->is_main_query()
            && ($this->checkValidTemplate($query) || $pageOfEditionsFilter)
        )
        {

            $edition = $this->getEdition();

            // $query->set( 'post_type', 'event' );
            // $query->set( 'post_status', 'publish' );
            // $query->set( 'orderby', 'meta_value' );
            // $query->set( 'order', 'ASC' );
            // $query->set( 'post_per_page', -1 );
            $query->set('is_by_edition', true);
            $query->set('meta_key', $this->postMetaKey);

            $meta_query = array(
                array(
                    'key'     => $this->postMetaKey,
                    'value'   => $edition->id, // get_query_var( 'event_date' )
                    'type'    => 'NUMERIC',
                    'compare' => '='
                )
            );

            $query->set('meta_query', $meta_query);
        }

        return $query;
    }

    /**
     * Retorna a última edição, ou a que estiver ativa
     * @return object Edição
     */
    public function getEdition($specific = null)
    {
        if (strtolower($specific) === 'latest')
        {
            $edition = $this->DB->getLatest();
        }
        else
        {
            // verifica se a url tem uma edição
            if ($editionId = get_query_var('edition_id'))
            {
                $edition = $this->DB->get($editionId);

                // para se sertificar de não haver retorno de erro com uma slug incorreta
                if (is_null($edition))
                {
                    $edition = $this->DB->getLatest();
                }
            }
            // senão pega a última
            else
            {
                $edition = $this->DB->getLatest();
            }
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

        if (empty($templates))
        {
            return false;
        }

        $passed = false;
        for ($x = 0; $x < count($templates); $x++)
        {
            $tmpl = $templates[$x];
            // verifica se a query valida este template
            if ($query->$tmpl)
            {
                $passed = true;
                break;
            }
        }

        return $passed;
    }

    public function template_redirect()
    {
        if (isset($_GET['edition']))
        {
            $template_filename = TEMPLATEPATH . "/edition.php";
            if (file_exists($template_filename))
            {
                load_template($template_filename);

                return;
                // exit;
            }

        }
    }

    /**
     * Gera lista não ordenada das edições
     * @param  array $atts Opções
     * @return string
     */
    public function shortcode_editions_list($atts)
    {
        // $editions = $this->DB->getActive();

        // if( count($editions) === 0 ){
        // 	return '';
        // }

        $opt = array_merge(array(
            'id'    => '',
            'class' => 'bb-edition-control',
        ), (array)$atts);

        $id = ($opt['id']) ? "id=\"{$opt['id']}\"" : '';

        $h = "<ul {$id} class=\"{$opt['class']}\">";

        $h .= $this->shortcode_editions_list_li();

        // foreach ($editions as $e):

        // 	$h .= "<li><a href=\"{$this->getEditionUrl($e->slug)}\">{$e->name}</a></li>";

        // endforeach;

        $h .= '</ul>';

        return $h;
    }


    public function shortcode_editions_list_li($atts = array())
    {
        $editions = $this->DB->getActive();

        if (count($editions) === 0)
        {
            return '';
        }

        $opt = array_merge(array(
            'a_class'  => '',
            'li_class' => '',
        ), (array)$atts);

        $h = "";

        foreach ($editions as $e):

            $h .= "<li class=\"{$opt['li_class']}\"><a href=\"{$this->getEditionUrl($e->slug)}\" class=\"{$opt['a_class']}\">{$e->name}</a></li>";

        endforeach;

        return $h;
    }

    /**
     * Gera combobox com as edições ativas
     * @param  array $atts Opções
     * @return string
     */
    public function shortcode_editions_combo($atts = array())
    {
        $editions = $this->DB->getActive();

        if (count($editions) === 0)
        {
            return '';
        }

        $opt = array_merge(array(
            'id'    => '',
            'class' => 'bb-edition-control',
            'name'  => 'edition',
        ), (array)$atts);

        $id = ($opt['id']) ? "id=\"{$opt['id']}\"" : '';

        $h = "<select {$id} class=\"{$opt['class']}\" name=\"{$opt['name']}\">";

        foreach ($editions as $e):

            $h .= "<option value=\"{$this->getEditionUrl($e->slug)}\">{$e->name}</option>";

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
    public function action_method_name()
    {
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
    public function filter_method_name()
    {
        // @TODO: Define your filter hook callback here
    }

}

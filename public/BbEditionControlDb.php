<?php 
/**
 * Bb Edition Control Database
 *
 * Every database job is done by it.
 *
 * @package   BbEditionControl
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */

class BbEditionControlDb {

    protected $table = 'bb_edition_control';

    public $rules = array(
        'date' => 'date|required',
        'name' => 'string|required',
        'slug' => 'string|required',
        'description' => 'string',
        'status' => 'number|required',
        );

    /**
     * Objeto da edição mais recente
     * @var [type]
     */
    static protected $latest = null;

    public function __construct()
    {
        global $wpdb;
    }

    /**
     * Return table name prefixed
     * @return string
     */
    public function getTable()
    {
        global $wpdb;
        return $wpdb->prefix . $this->table;
    }

    /**
     * Gera automaticamente os dados para uma nova edição.
     * @return array
     */
    public function createQuickNewEdition()
    {
        global $wpdb;
        // get last edition
        $l = $wpdb->get_results("SELECT * FROM {$this->getTable()} ORDER BY number DESC LIMIT 1");
        self::$latest = $l[0];
        $last = $this->getLatest();
        // get preformated name
        $format = get_option('bbec-edition-format');
        // set new attributes
        $number = (int)$last->number + 1;
        // parse edition name
        $name = Str::parseFormatEdition($format, array(
            'number' => $number,
            'day' => date("d"),
            'month' => date("m"),
            'year' => date("Y"),
            ));
        // verifica se a data não é igual a última

        // return data        
        return array(
            'date' => date("Y-m-d"),
            'name' => $name,
            'number' => $number,
            'slug' => Str::slugify($name),
            'status' => 0
            );
    }

    /**
     * Valida e salva dados de uma nova edição
     * @return string|bool String do erro, ou TRUE tudo OK
     */
    public function saveNewEdition($formData)
    {        
        if( $this->insert($formData) )
        {
            return true;        
        } else {
            throw new Exception("A nova edição não foi salva corretamente.");
        }
    }

    /**
     * Valida e atualiza dados de uma nova edição
     * @return string|bool String do erro, ou TRUE tudo OK
     */
    public function updateEdition($id, $formData)
    {        
        if( $this->update($id, $formData) )
        {
            return true;        
        } else {
            throw new Exception("A edição não foi atualizada corretamente.");
        }
    }


    /**
     * Insere dados na tabela bb_edition_cotrol
     * @param  array $array Dados da edição
     * @return bool
     */
    public function insert($array)
    {
        global $wpdb;
        $wpdb->show_errors();

        if(! is_array($array))
        {
            throw new InvalidArgumentException("O argumento deve ser array, mas \"".gettype($array)."\" foi passado.");
        }

        $date = Date::sql($array['date']);
        $name = $array['name'];
        $number = $array['number'];
        $slug = Str::slugify($array['slug']);
        $status = (isset($array['status'])) ? $array['status'] : 0;
        $desc = (isset($array['description'])) ? $array['description'] : '';

        try{
            $wpdb->query( 
                $wpdb->prepare("INSERT INTO " . $this->getTable() . " VALUES (NULL, %s, %s, %s, %s, %d, %s)", 
                    $date, $number, $name, $slug, $status, $desc)
                );

        } catch(Exception $e)
        {
            throw new Exception($e->getMessage());
            
        }

        return true;
    }

    /**
     * Atualiza um registro na tabela bb_edition_cotrol
     * @param  [type] $id    [description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function update($id, $array)
    {
        global $wpdb;
        $wpdb->show_errors();

        if(! is_array($array))
        {
            throw new InvalidArgumentException("O argumento deve ser array, mas \"".gettype($array)."\" foi passado.");
        }

        

        $date = Date::sql($array['date']);
        $name = $array['name'];
        $number = $array['number'];
        $slug = Str::slugify($array['slug']);
        $status = $array['status'];
        $desc = $array['description'];


        
        $wpdb->query( 
            $wpdb->prepare("UPDATE " . $this->getTable() . " SET date = %s, number = %s, name = %s, status = %d, description = %s  WHERE id = '{$id}'", 
                $date, $number, $name, $status, $desc)
            );

        return true;
    }

    /**
     * Remove o registro com ID passado
     * @param  int $id
     * @return bool
     */
    public function removeEdition($id = null)
    {
        if(! is_numeric($id))
        {
            throw new Exception("O ID passado não é um número válido.");
        }

        global $wpdb;

        $wpdb->query($wpdb->prepare("DELETE FROM {$this->getTable()} WHERE id = %d", $id));

        return true;
    }

    /**
     * Retorna todas as edições
     * @return object
     */
    public function getAll()
    {
        global $wpdb;

        $l = $wpdb->get_results("SELECT * FROM {$this->getTable()} ORDER BY number DESC, date DESC");

        return $l;

    }

    /**
     * Retorna as edições paginadas
     * @param  integer $page $_GET['p']
     * @return array
     */
    public function getAllPaginated($page = 1)
    {
        global $wpdb;

        $page = (! isset($page) || ! is_numeric($page)) ? 1 : $page;
        $perPage = 40;
        $start = ($page - 1) * $perPage;

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->getTable()}");
        $rows = $wpdb->get_results("SELECT * FROM {$this->getTable()} ORDER BY number DESC, date DESC LIMIT {$start},{$perPage}");

        return array(
            'total' => $total,
            'rows' => $rows,
            'page' => $page,
            'pages' => ceil($total / $perPage)
            );
    }

    /**
     * Retorna todas as edições ativas
     * @return object
     */
    public function getActive()
    {
        global $wpdb;

        $l = $wpdb->get_results("SELECT * FROM {$this->getTable()} WHERE status = '1' ORDER BY number DESC");

        return $l;

    }

    /**
     * retorna a última edição... mais recente
     * @return object
     */
    public function getLatest()
    {
        global $wpdb;

        if(self::$latest !== null){
            return self::$latest;
        }

        $l = $wpdb->get_results("SELECT * FROM {$this->getTable()} WHERE status = '1' ORDER BY number DESC LIMIT 1");
        self::$latest = $l[0];
        return self::$latest;
    }


    /**
     * Retorna a edição pelo ID
     * @param  int $idSlug
     * @return object
     */
    public function get($idSlug = null)
    {
        global $wpdb;

        if( is_numeric($idSlug) ){
            $by = "id = {$idSlug}";
        } else {
            $by = "slug = '{$idSlug}'";            
        }

        $l = $wpdb->get_results("SELECT * FROM {$this->getTable()} WHERE {$by}");

        return ($l) ? $l[0] : null;
    }


    /**
     * Retorna o ID da edição do post
     * @param  object|int $post Pode ser o objeto ou o ID
     * @return int
     */
    public function getPostEditionId($post)
    {
        if( is_object($post))
        {
            $id = $post->ID;
        }
        else
        {
            $id = $post;
        }

        $metaVal = get_post_meta( $id, '_bb_edition_control', true);

        return ( is_numeric($metaVal) ) ? $metaVal : 0;
    }

    /**
     * Retorna objeto com dados da edição
     * @param  object|int $post ID ou objeto
     * @return object
     */
    public function getPostEdition($post)
    {
        $id = $this->getPostEditionId($post);
        return $this->get($id);
    }

    /**
     * Adiciona ou atualiza o metadado sobre a edição
     * @param  int $postId
     * @param  int $editionId
     * @return bool
     */
    public function savePostEdition($postId, $editionId = null)
    {
        /* Get the meta value of the custom field key. */
        $metaVal = get_post_meta( $postId, '_bb_edition_control', true );

        if($metaVal === '')
        {
            add_post_meta( $postId, '_bb_edition_control', $editionId, true );
        }
        else
        {
            update_post_meta( $postId, '_bb_edition_control', $editionId );
        }

    }

    /**
     * Salva opções do plugin
     * @param  array $formData $_POST
     * @return bool
     */
    public function saveOptions($formData)
    {
        // post types que serão filtrados pela edição
        $posttype = (! isset($formData['posttype'])) ? 'edition' : $formData['posttype'];
        update_option('bbec-posttype', $posttype);

        // templates em que serão aplicados os filtros pela edição
        $templates = (! isset($formData['templates'])) ? array('Home') : $formData['templates'];
        update_option('bbec-templates', $templates);

        // post types que serão filtrados pela edição
        $posttypes = (! isset($formData['posttypes'])) ? array('Home') : $formData['posttypes'];
        update_option('bbec-posttypes', $posttypes);

        // modelo de string para criação de novas edições rápidas
        $pedition_format = (! isset($formData['edition_format'])) ? '' : $formData['edition_format'];
        update_option('bbec-edition-format', $pedition_format);
        
        return true;
    }

    /**
     * Gera as tabelas
     * @return void
     */
    public function createTable()
    {
        global $wpdb;
        $structure = "CREATE TABLE IF NOT EXISTS {$this->getTable()} (
            `id` int(9) NOT NULL AUTO_INCREMENT,
            `date` date NOT NULL,
            `number` int(9) NOT NULL,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `status` int(1) NOT NULL DEFAULT '0',
            `description` text NOT NULL,
            PRIMARY KEY (`number`),
            UNIQUE KEY `id` (`id`)
            );";
$wpdb->query($structure);
}

    /**
     * Remove tabela
     * @return void
     */
    public function dropTable()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE {$this->getTable()};");
    }

}
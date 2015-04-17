<?php 
/**
 * Helpers para template
 *
 *
 * @package   BB Edition Control
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */

if(! function_exists('bbec_current_edition'))
{
    /**
     * Retorna a edição atual, ou algum campo específico
     * @param  string $field Um campo da tabela
     * @return string|object
     */
    function bbec_current_edition($field = null)
    {
        $bbec = BbEditionControl::get_instance();
        $edition = $bbec->getEdition();

        if($field === null)
        {
            return $edition;
        }
        else
        {
            return (isset($edition->$field)) ? $edition->$field : null;
        }
    }
}

if(! function_exists('bbec_latest_edition'))
{
    /**
     * Retorna a edição mais recente e ativa, ou algum campo específico
     * @param  string $field Um campo da tabela
     * @return string|object
     */
    function bbec_latest_edition($field = null)
    {
        $bbec = BbEditionControl::get_instance();
        $edition = $bbec->getEdition('latest');

        if($field === null)
        {
            return $edition;
        }
        else
        {
            return (isset($edition->$field)) ? $edition->$field : null;
        }
    }
}

if(! function_exists('bbed_editions_li'))
{
    function bbed_editions_li()
    {

        $bbec = BbEditionControl::get_instance();
        $lis = $bbec->shortcode_editions_list_li();

        return $lis;
    }
}


if(! function_exists('parse_query_string'))
{   
    /**
     * Retorna um array com a query string removido as pastas de instalação do WP
     * @return array
     */
    function parse_query_string()
    {
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $aUri = explode('/', $uri);

        $bUrl = explode('/', get_bloginfo('url'));

        $fArray = array();

        foreach ($aUri as $u) {
                if(! in_array($u, $bUrl))
                {
                    $fArray[] = $u;
                }
        }

        return $fArray;
    }
}
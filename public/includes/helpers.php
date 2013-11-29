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
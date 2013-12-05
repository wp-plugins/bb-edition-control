<?php 
/**
 * String helper
 *
 *
 * @package   BB Edition Control
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */
class Str {

    static function statusLbl($value='')
    {
        $label = '';

        if($value == 1) $label = 'Publicado';
        if($value == 0) $label = 'Não publicado';

        return $label;
    }

    static function statusCombo($selected = 1)
    {
        $h = "<select name=\"status\" id=\"field_status\">";
        $h .= "<option value=\"1\" ".( ($selected == 1)?'selected':'').">Publicado</option>";
        $h .= "<option value=\"0\" ".( ($selected == 0)?'selected':'' ).">Não publicado</option>";
        $h .= '</select>';

        return $h;
    }

    static function templatesCombo($selected = array())
    {

        $options = array(
            'is_home' => 'Home',
            'is_date' => 'Date',
            'is_attachment' => 'Attachment',
            'is_archive' => 'Archive',
            'is_category' => 'Category',
            'is_search' => 'Search',
            'is_tag' => 'Tags',
        );
        
        return self::multiple('templates', $options, $selected);
    }

    static function postTypesCombo($selected = array())
    {
        $post_types = get_post_types();
        unset($post_types['revision']);
        unset($post_types['nav_menu_item']);
        unset($post_types['attachment']);
        unset($post_types['edition-control']);// já é usada pelo plugin como um template especial
        
        return self::multiple('posttypes', $post_types, $selected);
    }

    /**
     * Método primitivo para combobox multiple
     * @param  string $name     
     * @param  array  $options  
     * @param  array  $selected 
     * @return string
     */
    static function multiple($name = '', $options = array(), $selected = array())
    {
        if(! is_array($selected)){
            $selected = (array)$selected;
        }
        $h = "<select name=\"{$name}[]\" id=\"field_{$name}\" multiple=\"multiple\">";

        foreach($options as $idx => $lbl)
        {
            $h .= "<option value=\"{$idx}\" ".( (in_array($idx, $selected))?'selected':'').">{$lbl}</option>";            
        }
        $h .= '</select>';

        return $h;
    }

}
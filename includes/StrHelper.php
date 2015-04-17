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

		$bb = BbEditionControl::get_instance();

		if($value == 1) $label = __('Public', $bb->get_plugin_slug());
        if($value == 0) $label = __('Not public', $bb->get_plugin_slug());

        return $label;
    }

    static function statusCombo($selected = 1)
    {
		$bb = BbEditionControl::get_instance();

        $h = "<select name=\"status\" id=\"field_status\">";
        $h .= "<option value=\"1\" ".( ($selected == 1)?'selected':'').">".__('Public', $bb->get_plugin_slug())."</option>";
        $h .= "<option value=\"0\" ".( ($selected == 0)?'selected':'' ).">".__('Not public', $bb->get_plugin_slug())."</option>";
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

    /**
     * Recebe a string com o formato da edição e troca os curingas pelos valores informados.
     * 
     * @param  string $format 
     * @param  array  $values 
     * @return string
     */
    static public function parseFormatEdition($format = '', $values = array())
    {
        if(isset($values['number']))
        {
            $format = str_replace('%number%', $values['number'], $format);
        }
        if(isset($values['day']))
        {
            $format = str_replace('%day%', $values['day'], $format);
        }
        if(isset($values['month']))
        {
            $format = str_replace('%month%', $values['month'], $format);
        }
        if(isset($values['year']))
        {
            $format = str_replace('%year%', $values['year'], $format);
        }
        return $format;
    }


    static public function slugify($text)
    { 
    // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim
        $text = trim($text, '-');

    // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // lowercase
        $text = strtolower($text);

    // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }

}
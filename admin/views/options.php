<?php
/**
 * Opções do plugin
 *
 * @package   BB Edition Control
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */
?>

<div class="wrap">


    <form id="add-new-edition" action="<?php echo $this->form_action_url()?>" method="post">
        <input type="hidden" name="bb_options_hidden" value="Y">
        <input type="hidden" name="bb_referrer" value="<?php echo $this->form_action_url()?>">

    <table class="form-table">
 
        <tr valign="top">
            <th scope="row"><label for="field_posttype"><?php _e('Post type slug', $this->plugin_slug)?></label>
                <br>
                <small><?php _e('The URI to the personalized template to show editions content.', $this->plugin_slug)?></small>
                <br>
                <small><?php _e('Template: archive-edition-control.php.', $this->plugin_slug)?></small>
                <br>
                <small><?php _e('After change this value go to <b>Settings > Permalinks</b> and save to flush the rules.', $this->plugin_slug)?></small>
            </th>
            <td>
                <input type="text" name="posttype" id="field_posttype" class="" value="<?php echo get_option( 'bbec-posttype', 'edition' )?>">

                <span class="light-txt"><?php bloginfo('url') ?>/</span><span><?php echo get_option( 'bbec-posttype', 'edition' )?></span><span class="light-txt">/edition-slug</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="field_templates"><?php _e('Apply filter in templates', $this->plugin_slug)?></label>
                <br>
                <small><?php _e('The posts will be filtered by editions only on main loop. By default is only applied in home.', $this->plugin_slug)?></small>
            </th>
            <td>
                <?php echo Str::templatesCombo(get_option( 'bbec-templates', array('Home') )); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="field_posttypes"><?php _e('Post types', $this->plugin_slug)?></label>
                <br>
                <small><?php _e('Post types the editions will be applied.', $this->plugin_slug)?></small>
            </th>
            <td>
                <?php echo Str::postTypesCombo(get_option( 'bbec-posttypes', array('post') )); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="field_edition_format"><?php _e('Edition name format', $this->plugin_slug)?></label>
                <br>
                <small><?php _e('Preformated string to quick create.', $this->plugin_slug)?></small>
            </th>
            <td>
                <input type="text" name="edition_format" id="field_edition_format" class="input-large" value="<?php echo get_option( 'bbec-edition-format', 'Edition nº %number% - %day%/%month%/%year%' )?>">
                <br>
                <span class="light-txt">%number% %day% %month% %year%</span>
            </td>
        </tr>
          
        
        </table>

        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', $this->plugin_slug ) ?>" />

        </p>

    </form>



</div>

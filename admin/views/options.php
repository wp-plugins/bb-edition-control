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
          
        
        </table>

        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', $this->plugin_slug ) ?>" />
        </p>

    </form>



</div>

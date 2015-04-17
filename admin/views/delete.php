<?php
/**
 * Editando uma edição
 *
 * @package   BB Edition Control
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */
?>

<div class="wrap">

    <h2><?php _e('Attention', $this->plugin_slug ) ?></h2>
    <p><?php _e('You are going to delete permanently the edition', $this->plugin_slug ) ?> <?php echo $item->name?>.</p>
    <p><?php _e('All posts associated with this edition will be lost on templates that use this feature.', $this->plugin_slug ) ?></p>

    <form id="delete-edition" action="<?php echo $this->form_action_url()?>" method="post">
        <input type="hidden" name="bb_delete_hidden" value="Y">
        <input type="hidden" name="bb_delete_id" value="<?php echo $item->id?>">
        <input type="hidden" name="bb_referrer" value="<?php echo $this->form_action_url()?>">
    
    <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Delete', $this->plugin_slug ) ?>" />
        <a href="<?php echo $this->url()?>" class="button-secondary"><?php _e('Back', $this->plugin_slug ) ?></a>
    </p>

    </form>

</div>

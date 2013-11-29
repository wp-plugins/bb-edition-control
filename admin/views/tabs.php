<?php
/**
 * Abas
 *
 * @package   BB Edition Control
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */
?>

<div class="wrap">


<?php screen_icon(); ?>
<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

<h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo ($this->getTab()=='list')?'nav-tab-active':''?>" href="<?php echo $this->url('tab=list')?>">
        <?php _e('Editions', $this->plugin_slug)?>
    </a>
    <a class="nav-tab <?php echo ($this->getTab()=='new')?'nav-tab-active':''?>" href="<?php echo $this->url('tab=new')?>">
        <?php _e('New', $this->plugin_slug)?>
    </a>

    <?php if( $this->getTab()=='edit' ): ?>
    <div class="nav-tab nav-tab-active"><?php _e('Editing', $this->plugin_slug)?></div> 
    <?php endif; ?>

    <a class="nav-tab <?php echo ($this->getTab()=='options')?'nav-tab-active':''?>" href="<?php echo $this->url('tab=options')?>">
        <?php _e('Options', $this->plugin_slug)?>
    </a>
</h2>

</div>
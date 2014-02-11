<?php
/**
 * View da listagem de edições
 *
 * @package   BB Edition Control
 * @author    Bruno Barros <bruno@brunobarros.com>
 * @license   GPL-2.0+
 * @link      https://github.com/bruno-barros/BB-Edition-Control-for-Wordpress
 * @copyright 2013 Bruno Barros
 */
?>
<div class="wrap">
    
    <?php if($r['pages'] > 1): ?>
    <div class="tablenav">
    <div class='tablenav-pages'>
    <div class="pagination-links">
        <?php for($x = 1; $x <= $r['pages']; $x++): ?>
        <a href="<?php echo $this->url('tab=list&p='.$x) ?>" class="<?php echo ($x==$r['page'])?'active':'' ?>"><?php echo $x ?></a>
        <?php endfor; ?>
    </div>   
    </div>
    </div>
    <?php endif; ?>

 
    <table class="widefat">
    <thead>
        <tr>
            <th><?php _e('Date', $this->plugin_slug)?></th>
            <th><?php _e('Number', $this->plugin_slug)?></th>
            <th><?php _e('Name', $this->plugin_slug)?></th>       
            <th><?php _e('Slug', $this->plugin_slug)?></th>       
            <th><?php _e('Status', $this->plugin_slug)?></th>
            <th></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
        <th><?php _e('Date', $this->plugin_slug)?></th>
        <th><?php _e('Number', $this->plugin_slug)?></th>
        <th><?php _e('Name', $this->plugin_slug)?></th>
        <th><?php _e('Slug', $this->plugin_slug)?></th>
        <th><?php _e('Status', $this->plugin_slug)?></th>
        <th></th>
        </tr>
    </tfoot> 
    <tbody>       
    <?php

    if(isset($r['rows']) && $r['rows']):
        $i = 0;
        foreach ($r['rows'] as $k => $item):
    ?>
        <tr class="<?php echo ($i%2 == 0) ? 'alternate' : '' ?>">
         <td><?php echo Date::pt($item->date)?></td>
         <td><?php echo $item->number?></td>
         <td><?php echo $item->name?></td>
         <td><?php echo $item->slug?></td>
         <td><?php echo Str::statusLbl($item->status)?></td>
         <td>
            <a href="<?php echo $this->url('edit='.$item->id)?>"><?php _e('edit', $this->plugin_slug)?></a> | 
            <a href="<?php echo get_bloginfo('url') .'/'. get_option( 'bbec-posttype', 'edition' ) .'/'. $item->slug?>">ver</a>

        </td>
       </tr>
        

    <?php 
        $i++;
        endforeach;

    endif;
    ?>
    </tbody>
    </table>

    <?php if($r['pages'] > 1): ?>
    <div class="tablenav">
    <div class='tablenav-pages'>
    <div class="pagination-links">
        <?php for($x = 1; $x <= $r['pages']; $x++): ?>
        <a href="<?php echo $this->url('tab=list&p='.$x) ?>" class="<?php echo ($x==$r['page'])?'active':'' ?>"><?php echo $x ?></a>
        <?php endfor; ?>
    </div>   
    </div>
    </div>
    <?php endif; ?>




</div>
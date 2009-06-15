<?php // dsm($node->content);	?>	
<?php  //dsm(get_defined_vars());  ?> 
<?php //print $FIELD_NAME_rendered ?>
<?php if ($page == 0){ ?>
<div class="<?php print $classes ?>">

	<?php if($node->title){	?>	
    <h3><?php print l($node->title, 'node/'.$node->nid); ?></h3>
	<?php } ?>

	<div class="meta">
		<span class="time">
			<?php print format_date($node->created, 'custom', "j F Y") ?> 
		</span>	
		<span class="author">
			af <?php print theme('username', $node); ?>
		</span>	

		<?php if (count($taxonomy)){ ?>
		  <div class="taxonomy">
	   	  <?php print $terms ?> 
		  </div>  
		<?php } ?>
	</div>

  <?php print $content ?>
</div>
<?php }else{ 
//Content
?>
<div class="<?php print $classes ?>">
	<?php if($node->title){	?>	
	  <h1><?php print $title;?></h1>
	<?php } ?>
		
	<div class="meta">
		<span class="time">
			<?php print format_date($node->created, 'custom', "j F Y") ?> 
		</span>	
		<span class="author">
			af <?php print theme('username', $node); ?>
		</span>	

		<?php if (count($taxonomy)){ ?>
		  <div class="taxonomy">
	   	  <?php print $terms ?> 
		  </div>  
		<?php } ?>
	</div>

	<div class="content">
		<?php print $content ?>
	</div>
		
	<?php if ($links){ ?>
    <?php  print $links; ?>
	<?php } ?>
</div>
<?php } ?>
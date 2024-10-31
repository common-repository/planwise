<?php 
/*
*
* Template Name: Planwise Template
*/

           

get_header(); ?>
<div class="content-wide">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<h1 class="post-title"><?php the_title(); ?></h1>
<?php the_content() ?>

<?php
$plan = new planwise();
if($plan->options['transparent']=='yes'){
    $color = '';
 }else{
    $color = 'background-color:'.$plan->options['background_color'];
 }
?>
<div style='<?php echo $color ?>;overflow:visible;width:960px' >
    <iframe height='700' width='100%' src='<?php echo $plan->iframe_url; ?>' marginwidth='0' marginheight='0' scrolling='no' frameborder='0' >
    </iframe>
</div> 

<?php endwhile;endif; ?> </div>  
<?php get_footer(); ?>
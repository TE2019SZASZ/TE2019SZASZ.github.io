<?php
/**
 * The template for displaying the footer
 * @subpackage Private Tutor
 * @since 1.0
 * @version 0.1
 */

?>
	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="container">
			<?php get_template_part( 'template-parts/footer/footer', 'widgets' ); ?>
		</div>
		<div class="clearfix"></div>
		<div class="copyright"> 
			<div class="container">
				<?php get_template_part( 'template-parts/footer/site', 'info' ); ?>
			</div>
		</div>
	</footer>

<?php wp_footer(); ?>
<script type="text/javascript">
	jQuery("#show-member-1").click(function(){
		jQuery("#member-2").hide();
		jQuery("#member-3").hide();
		jQuery("#member-4").hide();
		jQuery("#member-5").hide();
		jQuery("#member-1").slideDown();
	});
jQuery("#show-member-2").click(function(){
		jQuery("#member-3").hide();
		jQuery("#member-4").hide();
		jQuery("#member-5").hide();
		jQuery("#member-1").hide();
		jQuery("#member-2").slideDown();
	});
	jQuery("#show-member-3").click(function(){
		jQuery("#member-4").hide();
		jQuery("#member-5").hide();
		jQuery("#member-1").hide();
		jQuery("#member-2").hide();
		jQuery("#member-3").slideDown();
	});
	jQuery("#show-member-4").click(function(){
		jQuery("#member-5").hide();
		jQuery("#member-1").hide();
		jQuery("#member-2").hide();
		jQuery("#member-3").hide();
		jQuery("#member-4").slideDown();
	});
	jQuery("#show-member-5").click(function(){
		jQuery("#member-1").hide();
		jQuery("#member-2").hide();
		jQuery("#member-3").hide();
		jQuery("#member-4").hide();
		jQuery("#member-5").slideDown();
	});

</script>
</body>
</html>
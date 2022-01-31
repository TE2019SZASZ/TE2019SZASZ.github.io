jQuery('document').ready(function($){
	 $(".cool_timeline").find("a[class^='ctl_prettyPhoto']").prettyPhoto({
	 social_tools: false,
	 show_title:false,
	});
	$(".cool_timeline_horizontal").find("a[ref^='prettyPhoto']").prettyPhoto({
	 social_tools: false,
	 show_title:false,
	}); 
var nextBtn='<div class="clt_h_nav_btn ctl-slick-next"><i class="fa fa-angle-right"></i></div>';
var preBtn='<div class="clt_h_nav_btn ctl-slick-prev"><i class="fa fa-angle-left"></i></div>';
$('.cool_timeline_horizontal').each(function(){
	$(this).siblings(".ctl-preloader-loader").hide();
	$(this).css("opacity",1);
var slidetoshow=$('ul.ctl_road_map_wrp').data('slide-to-show');
	$(this).find('ul.ctl_road_map_wrp').slick({
				dots: false,
				infinite: false,
				slidesToShow:slidetoshow,
				slidesToScroll:1,
				nextArrow:nextBtn,
				prevArrow:preBtn,
				responsive: [
				  {
					breakpoint: 600,
					settings: {
					  slidesToShow: 2,
					  slidesToScroll: 2
					}
				  },
				  {
					breakpoint: 480,
					settings: {
					  slidesToShow: 1,
					  slidesToScroll: 1
					}
				  }
				]
				});
			});
});

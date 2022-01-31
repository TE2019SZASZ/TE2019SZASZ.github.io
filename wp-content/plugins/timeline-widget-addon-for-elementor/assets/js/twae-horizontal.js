class HorizontalSliderClass extends elementorModules.frontend.handlers.Base {
  getDefaultSettings() {
      return {
          selectors: {
              swiperContainer:'.twae-horizontal.swiper-container',
              nextButton: '.twae-button-next',
              prevButton: '.twae-button-prev',
              paginationEl: '.twae-pagination',
          },
      };
  }

  getDefaultElements() {
      const selectors = this.getSettings( 'selectors' );
      return {
          $swiperContainer: this.$element.find( selectors.swiperContainer ),
          $nextButton: this.$element.find( selectors.nextButton ),
          $prevButton: this.$element.find( selectors.prevButton ),
          $paginationEl: this.$element.find( selectors.paginationEl ),      
      };
  }

  bindEvents() {  
    
    var selector = this.elements.$swiperContainer,
        slidestoshow = selector.data("slidestoshow"),
        autoplay = selector.data("autoplay"), 
        
        nextButton = this.elements.$nextButton, 
        prevButton = this.elements.$prevButton, 
        paginationEl = this.elements.$paginationEl;

        var Navigation;
        var lang_dir = selector.attr("dir");        
        if(lang_dir=='rtl'){
         var Navigation =  {
            nextEl: prevButton,
            prevEl: nextButton,
          }
        }
        else{
          Navigation =  {
            nextEl: nextButton,
            prevEl: prevButton,
          }
        }
     
        var swiper = new Swiper( selector, {
          spaceBetween: 10,
          autoplay:autoplay,
          delay: 5000,
          slidesPerView: slidestoshow,
          direction: 'horizontal',
          pagination: {
            el: paginationEl,
            type: 'progressbar',
          },
          navigation: Navigation,
          // Responsive breakpoints
          breakpoints: {
            // when window width is >= 320px
            320: {
              slidesPerView: 1,
            },
            // when window width is >= 480px
            480: {
              slidesPerView: 2,
            },
            // when window width is >= 640px
            640: {
              slidesPerView: slidestoshow,
            
            }
          },
        
        });
  }  
  
}


jQuery( window ).on( 'elementor/frontend/init', () => {

  const addHandler = ( $element ) => {
      elementorFrontend.elementsHandler.addHandler( HorizontalSliderClass, {
        $element,           
      });
  };

  elementorFrontend.hooks.addAction( 'frontend/element_ready/timeline-widget-addon.default', addHandler );

});
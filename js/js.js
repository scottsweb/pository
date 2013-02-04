$(document).ready(function () {

	var $panels = $('.panel');
	var $container = $('#slider-container');
	
	var horizontal = true;
	
	// float the panels left if we're going horizontal
	if (horizontal) {
	  $panels.css({
	    'float' : 'left',
	    'position' : 'relative' // IE fix to ensure overflow is hidden
	  });
	  
	  // calculate a new width for the container (so it holds all panels)
	  $container.css('width', $panels[0].offsetWidth * $panels.length);
	}
	
	// collect the scroll object, at the same time apply the hidden overflow
	// to remove the default scrollbars that will appear
	var $scroll = $('#slider').css('overflow', 'hidden');
	
	// handle nav selection
	function selectNav() {
	  $(this)
	    .parents('ul:first')
	      .find('a')
	        .removeClass('selected')
	      .end()
	    .end()
	    .addClass('selected');
	}
	
	$('#nav').find('a').click(selectNav);
	
	// go find the navigation link that has this target and select the nav
	function trigger(data) {
	  var el = $('#nav').find('a[href$="' + data.id + '"]').get(0);
	  selectNav.call(el);
	}
	
	if (window.location.hash) {
	  trigger({ id : window.location.hash.substr(1) });
	} else {
	  $('#nav ul a:first').click();
	}
	
	// offset is used to move to *exactly* the right place, since I'm using
	// padding on my example, I need to subtract the amount of padding to
	// the offset.  Try removing this to get a good idea of the effect
	var offset = parseInt((horizontal ? 
	  $container.css('paddingTop') : 
	  $container.css('paddingLeft')) 
	  || 0) * -1;
	
	
	var scrollOptions = {
	  target: $scroll, 
	  items: $panels,
	  hash: true,
	  navigation: '#nav',
	  axis: 'x',
	  onAfter: trigger,
	  offset: offset,
	  duration: 400
	};
	
	// apply serialScroll to the slider - we chose this plugin because it 
	// supports// the indexed next and previous scroll along with hooking 
	// in to our navigation.
	$('#slider').serialScroll(scrollOptions);
	
	// now apply localScroll to hook any other arbitrary links to trigger 
	// the effect
	$.localScroll(scrollOptions);
	
	// finally, if the URL has a hash, move the slider in to position, 
	// setting the duration to 1 because don't want it to scroll in the
	scrollOptions.duration = 1;
	$.localScroll.hash(scrollOptions);

});
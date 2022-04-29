(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function (){
		$("#back-top").hide();

		$(window).scroll(function (){
			if ($(this).scrollTop() > 350){
				$("#back-top").fadeIn();
			} else{
				$("#back-top").fadeOut();
			}
		});

		$("#back-top a").click(function (){
			$("body,html").animate({
				scrollTop:0
			}, 800);
			return false;
		});
	});

})( jQuery );

function CopyText(id) {

	var input = document.getElementById(id);

	/* Select the text field */
	input.select();
	input.setSelectionRange(0, 99999); /* For mobile devices */

	/* Copy the text inside the text field */
	document.execCommand("copy");

	alert("Скопировано: " + input.value);
}

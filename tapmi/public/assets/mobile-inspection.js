var MobileInspection = new Object();

MobileInspection.set_active_menu = function( value ) {
	$( "#master-menu > li" ).removeClass( 'm-menu__item--active' );
	$( "#master-menu > li" ).removeClass( 'm-menu__item--active-tab' );
	$( "#" + value ).addClass( 'm-menu__item--active' );
	$( "#" + value ).addClass( 'm-menu__item--active-tab' );
}
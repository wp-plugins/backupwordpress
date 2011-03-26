jQuery( document ).ready( function( $ ) {

	if ( $( '.hmbkp_running' ).size() ) {
		hmbkpRedirectOnBackupComplete();
	}

	$.get( ajaxurl, { 'action' : 'hmbkp_calculate' },
	    function( data ) {
	    	$( '.hmbkp_estimated-size .calculate' ).removeClass( 'calculating' );
	    	$( '.hmbkp_estimated-size .calculate' ).fadeOut( function() {
	    		$( this ).empty().append( data );
	    	} ).fadeIn();
	    }
	);

} );

function hmbkpRedirectOnBackupComplete() {

	jQuery.get( ajaxurl, { 'action' : 'hmbkp_is_in_progress' },
		function( data ) {
			if ( data == 0 )
				location.reload( true );
			else
				setTimeout( 'hmbkpRedirectOnBackupComplete();', 5000 );
		}
	);

}
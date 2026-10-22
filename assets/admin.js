( function () {
	const { __ } = wp.i18n;

	document.querySelectorAll( '.hdform-copy' ).forEach( ( button ) => {
		button.addEventListener( 'click', async () => {
			const code = button.closest( '.hdform-code' ).querySelector( 'code' );
			try {
				await navigator.clipboard.writeText( code.textContent );
				button.textContent = __( 'Copied', 'headless-form' );
			} catch ( error ) {
				window.getSelection().selectAllChildren( code );
				button.textContent = __( 'Press Ctrl+C to copy', 'headless-form' );
			}
			window.setTimeout( () => {
				button.textContent = __( 'Copy', 'headless-form' );
			}, 2500 );
		} );
	} );
} )();

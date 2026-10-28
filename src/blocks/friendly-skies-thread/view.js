document.querySelectorAll( '.ghostlabs-fs-thread' ).forEach( ( thread ) => {
	const toggle = thread.querySelector( '.ghostlabs-fs-thread__toggle' );
	const collapsed = thread.querySelector( '.ghostlabs-fs-thread__collapsed' );

	if ( ! toggle || ! collapsed ) return;

	if ( ! collapsed.id ) {
		collapsed.id = 'ghostlabs-fs-thread-collapsed-' + Math.random().toString( 36 ).slice( 2, 9 );
	}

	toggle.setAttribute( 'aria-expanded', 'false' );
	toggle.setAttribute( 'aria-controls', collapsed.id );

	toggle.addEventListener( 'click', () => {
		const isHidden = collapsed.hidden;
		collapsed.hidden = ! isHidden;
		toggle.textContent = isHidden ? toggle.dataset.labelLess : toggle.dataset.labelMore;
		toggle.setAttribute( 'aria-expanded', String( isHidden ) );
	} );
} );

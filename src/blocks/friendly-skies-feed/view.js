function initShowMoreToggles( feed ) {
	feed.querySelectorAll( '.ghostlabs-fs-feed-post__show-more' ).forEach( ( btn ) => {
		const post = btn.closest( '.ghostlabs-fs-feed-post' );
		if ( ! post ) return;

		const truncated = post.querySelector( '.ghostlabs-fs-feed-post__content-truncated' );
		const full = post.querySelector( '.ghostlabs-fs-feed-post__content-full' );

		if ( ! truncated || ! full ) return;

		btn.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			e.stopPropagation();

			const isExpanded = ! full.hidden;

			truncated.hidden = ! isExpanded;
			full.hidden = isExpanded;
			btn.textContent = isExpanded ? btn.dataset.labelMore : btn.dataset.labelLess;
			btn.setAttribute( 'aria-expanded', String( ! isExpanded ) );
		} );
	} );
}

document.querySelectorAll( '.ghostlabs-fs-feed' ).forEach( ( feed ) => {
	initShowMoreToggles( feed );
} );

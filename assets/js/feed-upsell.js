/**
 * The Feed block's Upsell panel, in the free edition only.
 *
 * Adds one closed panel to the Feed block's inspector, after the block's own controls, through
 * the block's `GhostLabsFriendlySkiesFeedInspectorEnd` slot (`shared/.../friendly-skies-feed/edit.js`).
 * ⚠️ Not a bare InspectorControls: that lands in registration order, above the block's panels,
 * because they mount only once a feed has loaded. The panel
 * points out what the paid edition adds and links to it; it offers nothing, and nothing it names
 * is in this build (WordPress.org Guideline 5).
 *
 * Every word and the URL come from `Service\FeedUpsell`, inlined before this file runs. Without
 * them nothing renders: there is no fallback copy and no hard-coded link.
 *
 * Closed by default: the inspector holds the controls the author came for, and an advertisement
 * must not push them down (the upsell convention's Upsell panel).
 *
 * Plain JavaScript with no build step, so what a reviewer reads is what ships.
 */
( function ( wp, copy ) {
	if ( ! wp || ! copy ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var Fill = wp.components.Fill;
	var PanelBody = wp.components.PanelBody;
	var Notice = wp.components.Notice;
	var ExternalLink = wp.components.ExternalLink;

	var BLOCK = 'ghostlabs/friendly-skies-feed';
	var SLOT = 'GhostLabsFriendlySkiesFeedInspectorEnd';

	function UpsellPanel() {
		return el(
			Fill,
			{ name: SLOT },
			el(
				PanelBody,
				{ title: copy.title, initialOpen: false, className: 'ghlbsfs-feed-upsell' },
				el( 'p', null, copy.body ),
				// Notice for its look only. `spokenMessage` empty, or the copy is read aloud each
				// time the panel opens; not dismissible, or its close button does nothing.
				el(
					Notice,
					{ status: 'info', spokenMessage: '', isDismissible: false },
					el( 'p', null, copy.keeps ),
					// ExternalLink opens a new tab and says so, so the editor keeps its unsaved state.
					el( 'p', null, el( ExternalLink, { href: copy.url }, copy.link ) )
				)
			)
		);
	}

	var withUpsellPanel = wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( props.name !== BLOCK || ! props.isSelected ) {
				return el( BlockEdit, props );
			}

			return el( Fragment, null, el( BlockEdit, props ), el( UpsellPanel ) );
		};
	}, 'withFeedUpsellPanel' );

	wp.hooks.addFilter( 'editor.BlockEdit', 'ghostlabs/friendly-skies/feed-upsell', withUpsellPanel );
} )( window.wp, window.ghlbsfsFeedUpsell );

import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { SelectControl } from '@wordpress/components';

/**
 * What this Feed block offers: its layouts, its sources and its guide.
 *
 * Each list is passed through a filter, so a plugin that adds layouts or sources can add them
 * from its own code — this block carries no layout or source it does not offer. The descriptors:
 *
 * - **layout** `{ value, label, alignable?, Controls? }` — `value` is what the `layout` attribute
 *   stores; `Controls`, if any, renders below the select as `<Controls attributes setAttributes />`.
 * - **source** `{ value, label, attribute, fieldLabel, inspectorLabel, placeholder, instructions,
 *   inspectorPlaceholder?, help?, parse?, normalise?, claims? }` — `attribute` names the block
 *   attribute that holds what the source shows. `parse( input )` turns the setup field's entry
 *   into `{ value }` or `{ error }`; `normalise( value )` tidies an inspector edit; `claims( input,
 *   current )` lets a source take a pasted value whichever source is selected.
 * - **guide** — the pages of the block's first-run guide, `{ heading, body }`.
 *
 * ⚠️ **Functions, never module-level constants.** A `__()` call at import time resolves whichever
 * locale had loaded first.
 *
 * The server renders with its own list of layouts and sources (`Service\FeedRenderer`), and the
 * two have to name the same values.
 */

const HANDLE_PATTERN = /^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)+$|^did:[a-z]+:.+$/;

function GridControls( { attributes, setAttributes } ) {
	return (
		<SelectControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ __( 'Columns', 'ghostlabs-friendly-skies' ) }
			value={ String( attributes.gridColumns ) }
			options={ [
				{ label: '2', value: '2' },
				{ label: '3', value: '3' },
				{ label: '4', value: '4' },
			] }
			onChange={ ( val ) => setAttributes( { gridColumns: Number( val ) } ) }
		/>
	);
}

/** @return {Object[]} The layouts, in the order the select lists them. */
export const layoutOptions = () =>
	applyFilters( 'ghostlabs.friendlySkies.feed.layouts', [
		{ value: 'list', label: __( 'List', 'ghostlabs-friendly-skies' ) },
		{ value: 'grid', label: __( 'Grid', 'ghostlabs-friendly-skies' ), alignable: true, Controls: GridControls },
	] );

/** @return {Object[]} The sources, in the order the select lists them. */
export const sourceOptions = () =>
	applyFilters( 'ghostlabs.friendlySkies.feed.sources', [
		{
			value: 'user',
			label: __( 'User Feed', 'ghostlabs-friendly-skies' ),
			attribute: 'actor',
			fieldLabel: __( 'Bluesky handle', 'ghostlabs-friendly-skies' ),
			inspectorLabel: __( 'Handle', 'ghostlabs-friendly-skies' ),
			placeholder: 'user.bsky.social',
			instructions: __( 'Enter a Bluesky handle to display their posts.', 'ghostlabs-friendly-skies' ),
			parse: ( input ) => {
				const handle = input.replace( /^@/, '' );

				return HANDLE_PATTERN.test( handle )
					? { value: handle }
					: { error: __( 'Please enter a valid Bluesky handle or DID.', 'ghostlabs-friendly-skies' ) };
			},
			normalise: ( value ) => value.replace( /^@/, '' ),
		},
	] );

/** @return {Object[]} The first-run guide's pages. */
export const guidePages = () =>
	applyFilters( 'ghostlabs.friendlySkies.feed.guide', [
		{
			heading: __( 'Bluesky Feed', 'ghostlabs-friendly-skies' ),
			body: __( 'Display a live Bluesky feed on your site.', 'ghostlabs-friendly-skies' ),
		},
		{
			heading: __( 'Getting Started', 'ghostlabs-friendly-skies' ),
			body: __( 'Enter a Bluesky handle, then choose Load Feed.', 'ghostlabs-friendly-skies' ),
		},
		{
			heading: __( 'Layouts & Filters', 'ghostlabs-friendly-skies' ),
			body: __( 'Show the posts as a list or a grid. Use the sidebar to configure filters, display options, and more.', 'ghostlabs-friendly-skies' ),
		},
	] );

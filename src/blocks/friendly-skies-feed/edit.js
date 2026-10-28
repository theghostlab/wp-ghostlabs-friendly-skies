import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	BlockAlignmentControl,
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	ToggleControl,
	RangeControl,
	Placeholder,
	Button,
	Slot,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import { BlockGuide } from '../_shared/guide.js';
import { SetupError, SetupInstructions, SetupTextField, useSetupError, useSetupIds } from '../_shared/setup-field.js';
import { guidePages, layoutOptions, sourceOptions } from './options';
import './editor.scss';

/**
 * A named seam after the block's own inspector panels, empty unless an edition fills it.
 *
 * ⚠️ A Fill rendered beside the block lands in registration order, and this block's panels mount
 * only once a feed has loaded, so anything added from outside would otherwise sit above Source.
 * The name is a contract: `free-only/assets/js/feed-upsell.js` fills it.
 */
export const INSPECTOR_END = 'GhostLabsFriendlySkiesFeedInspectorEnd';

const toSelectOptions = ( descriptors ) =>
	descriptors.map( ( { value, label } ) => ( { value, label } ) );

export default function Edit( { attributes, setAttributes } ) {
	const {
		align,
		sourceType,
		layout,
		theme,
		maxPosts,
		includeReplies,
		includeReposts,
		contentFilter,
		pinnedPostUri,
		showEngagement,
		showTimestamps,
		truncatePosts,
		truncateLength,
	} = attributes;

	const [ inputValue, setInputValue ] = useState( '' );
	const [ error, setError ] = useSetupError();
	const { instructionsId, errorId } = useSetupIds( 'feed' );

	const layouts = layoutOptions();
	const sources = sourceOptions();

	// What the block stores is not always something this block offers — a block written on a site
	// with more sources or layouts. It renders as the first layout, and an unknown source shows the
	// setup screen for the first source.
	const currentLayout = layouts.find( ( l ) => l.value === layout ) ?? layouts[ 0 ];
	const currentSource = sources.find( ( s ) => s.value === sourceType ) ?? null;
	const setupSource = currentSource ?? sources[ 0 ];

	const supportsAlign = !! currentLayout.alignable;
	const LayoutControls = currentLayout.Controls;

	const blockProps = useBlockProps( {
		className: supportsAlign && align ? `align${ align }` : undefined,
	} );

	const hasSource = !! currentSource && !! attributes[ currentSource.attribute ];

	const onSubmit = () => {
		const trimmed = inputValue.trim();

		if ( ! trimmed ) {
			setError( __( 'Please enter a value.', 'ghostlabs-friendly-skies' ) );
			return;
		}

		// A pasted link can name its own source, whichever one is selected.
		const target = sources.find( ( s ) => s.claims?.( trimmed, setupSource.value ) ) ?? setupSource;
		const result = target.parse ? target.parse( trimmed ) : { value: trimmed };

		if ( result.error ) {
			setError( result.error );
			return;
		}

		setAttributes( { sourceType: target.value, [ target.attribute ]: result.value } );
		setError( '' );
	};

	const onKeyDown = ( event ) => {
		if ( event.key === 'Enter' ) {
			event.preventDefault();
			onSubmit();
		}
	};

	// Changing source clears what every source showed, so a block never holds two.
	const clearedSources = () =>
		Object.fromEntries( sources.map( ( s ) => [ s.attribute, '' ] ) );

	// One source is no choice, so there is no select for it.
	const sourceSelect = ( props ) =>
		sources.length > 1 && (
			<SelectControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Source Type', 'ghostlabs-friendly-skies' ) }
				value={ setupSource.value }
				options={ toSelectOptions( sources ) }
				{ ...props }
			/>
		);

	if ( ! hasSource ) {
		return (
			<div { ...blockProps }>
				<BlockGuide
					slug="feed"
					pages={ guidePages().map( ( { heading, body } ) => ( {
						content: (
							<>
								<h1 className="ghostlabs-fs-block-guide__heading">{ heading }</h1>
								<p>{ body }</p>
							</>
						),
					} ) ) }
				/>
				<Placeholder
					className="ghostlabs-fs-feed-placeholder"
					icon="rss"
					label={ __( 'Bluesky Feed', 'ghostlabs-friendly-skies' ) }
				>
					<SetupInstructions id={ instructionsId }>{ setupSource.instructions }</SetupInstructions>
					{ sources.length > 1 && (
						<div className="ghostlabs-fs-feed-placeholder__source-select">
							{ sourceSelect( {
								hideLabelFromVision: true,
								onChange: ( val ) => {
									setAttributes( { sourceType: val } );
									setInputValue( '' );
									setError( '' );
								},
							} ) }
						</div>
					) }
					<div className="ghostlabs-fs-feed-placeholder__input-row">
						<SetupTextField
							label={ setupSource.fieldLabel }
							instructionsId={ instructionsId }
							errorId={ errorId }
							error={ error }
							placeholder={ setupSource.placeholder }
							value={ inputValue }
							onChange={ setInputValue }
							onKeyDown={ onKeyDown }
						/>
						<Button
							variant="primary"
							onClick={ onSubmit }
						>
							{ __( 'Load Feed', 'ghostlabs-friendly-skies' ) }
						</Button>
					</div>
					<SetupError id={ errorId } className="ghostlabs-fs-feed-placeholder__error">{ error }</SetupError>
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			{ supportsAlign && (
				<BlockControls>
					<BlockAlignmentControl
						value={ align }
						onChange={ ( val ) => setAttributes( { align: val } ) }
						controls={ [ 'wide', 'full' ] }
					/>
				</BlockControls>
			) }
			<InspectorControls>
				<PanelBody title={ __( 'Source', 'ghostlabs-friendly-skies' ) }>
					{ sourceSelect( {
						onChange: ( val ) => setAttributes( { sourceType: val, ...clearedSources() } ),
					} ) }
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ currentSource.inspectorLabel }
						help={ currentSource.help }
						placeholder={ currentSource.inspectorPlaceholder }
						value={ attributes[ currentSource.attribute ] }
						onChange={ ( val ) =>
							setAttributes( {
								[ currentSource.attribute ]: currentSource.normalise ? currentSource.normalise( val ) : val,
							} )
						}
					/>
				</PanelBody>

				<PanelBody title={ __( 'Layout', 'ghostlabs-friendly-skies' ) }>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Layout', 'ghostlabs-friendly-skies' ) }
						value={ currentLayout.value }
						options={ toSelectOptions( layouts ) }
						onChange={ ( val ) => {
							const attrs = { layout: val };
							if ( ! layouts.find( ( l ) => l.value === val )?.alignable ) {
								attrs.align = '';
							}
							setAttributes( attrs );
						} }
					/>
					{ LayoutControls && (
						<LayoutControls attributes={ attributes } setAttributes={ setAttributes } />
					) }
				</PanelBody>

				<PanelBody title={ __( 'Filters', 'ghostlabs-friendly-skies' ) } initialOpen={ false }>
					<RangeControl
						__nextHasNoMarginBottom
						label={ __( 'Maximum Posts', 'ghostlabs-friendly-skies' ) }
						value={ maxPosts }
						onChange={ ( val ) => setAttributes( { maxPosts: val } ) }
						min={ 1 }
						max={ 50 }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include replies', 'ghostlabs-friendly-skies' ) }
						checked={ includeReplies }
						onChange={ ( val ) => setAttributes( { includeReplies: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include reposts', 'ghostlabs-friendly-skies' ) }
						checked={ includeReposts }
						onChange={ ( val ) => setAttributes( { includeReposts: val } ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Content Filter', 'ghostlabs-friendly-skies' ) }
						value={ contentFilter }
						options={ [
							{ label: __( 'All', 'ghostlabs-friendly-skies' ), value: 'all' },
							{ label: __( 'Text only', 'ghostlabs-friendly-skies' ), value: 'text' },
							{ label: __( 'With images', 'ghostlabs-friendly-skies' ), value: 'images' },
							{ label: __( 'With video', 'ghostlabs-friendly-skies' ), value: 'video' },
							{ label: __( 'With links', 'ghostlabs-friendly-skies' ), value: 'links' },
						] }
						onChange={ ( val ) => setAttributes( { contentFilter: val } ) }
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Pinned Post URL', 'ghostlabs-friendly-skies' ) }
						help={ __( 'Paste a bsky.app post link or an AT URI.', 'ghostlabs-friendly-skies' ) }
						value={ pinnedPostUri }
						onChange={ ( val ) => setAttributes( { pinnedPostUri: val } ) }
						placeholder="https://bsky.app/profile/.../post/..."
					/>
				</PanelBody>

				<PanelBody title={ __( 'Display', 'ghostlabs-friendly-skies' ) } initialOpen={ false }>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Theme', 'ghostlabs-friendly-skies' ) }
						value={ theme }
						options={ [
							{ label: __( 'System', 'ghostlabs-friendly-skies' ), value: 'system' },
							{ label: __( 'Light', 'ghostlabs-friendly-skies' ), value: 'light' },
							{ label: __( 'Dark', 'ghostlabs-friendly-skies' ), value: 'dark' },
						] }
						onChange={ ( val ) => setAttributes( { theme: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show engagement counts', 'ghostlabs-friendly-skies' ) }
						checked={ showEngagement }
						onChange={ ( val ) => setAttributes( { showEngagement: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show timestamps', 'ghostlabs-friendly-skies' ) }
						checked={ showTimestamps }
						onChange={ ( val ) => setAttributes( { showTimestamps: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Truncate long posts', 'ghostlabs-friendly-skies' ) }
						checked={ truncatePosts }
						onChange={ ( val ) => setAttributes( { truncatePosts: val } ) }
					/>
					{ truncatePosts && (
						<RangeControl
							__nextHasNoMarginBottom
							label={ __( 'Truncate at characters', 'ghostlabs-friendly-skies' ) }
							value={ truncateLength }
							onChange={ ( val ) => setAttributes( { truncateLength: val } ) }
							min={ 100 }
							max={ 500 }
						/>
					) }
				</PanelBody>
				<Slot name={ INSPECTOR_END } />
			</InspectorControls>

			<ServerSideRender
				block="ghostlabs/friendly-skies-feed"
				attributes={ attributes }
			/>
		</div>
	);
}

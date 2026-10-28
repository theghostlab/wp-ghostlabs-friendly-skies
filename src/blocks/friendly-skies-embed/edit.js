import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	Placeholder,
	Button,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import { BlockGuide } from '../_shared/guide.js';
import { SetupError, SetupInstructions, SetupTextField, useSetupError, useSetupIds } from '../_shared/setup-field.js';
import './editor.scss';

const BSKY_URL_PATTERN = /^https?:\/\/bsky\.app\/profile\/[^/]+\/post\/[a-zA-Z0-9]+/;

export default function Edit( { attributes, setAttributes } ) {
	const { url, theme, displayMode, showEngagement, showTimestamp, clickBehavior } = attributes;
	const [ inputUrl, setInputUrl ] = useState( '' );
	const [ error, setError ] = useSetupError();
	const { instructionsId, errorId } = useSetupIds( 'embed' );
	const [ isEditing, setIsEditing ] = useState( ! url );

	const blockProps = useBlockProps();

	const onSubmit = () => {
		const trimmed = inputUrl.trim();

		if ( ! BSKY_URL_PATTERN.test( trimmed ) ) {
			setError( __( 'Please enter a valid Bluesky post URL.', 'ghostlabs-friendly-skies' ) );
			return;
		}

		setError( '' );
		setAttributes( { url: trimmed } );
		setIsEditing( false );
	};

	const onKeyDown = ( event ) => {
		if ( event.key === 'Enter' ) {
			event.preventDefault();
			onSubmit();
		}
	};

	if ( isEditing || ! url ) {
		return (
			<div { ...blockProps }>
				<BlockGuide
					slug="embed"
					pages={ [
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Bluesky Post Embed', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'Embed any Bluesky post on your site. Just copy the post URL from bsky.app and paste it here.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Customize Your Embed', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'After loading, use the sidebar to choose a theme, display mode, and toggle engagement counts and timestamps.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
					] }
				/>
				<Placeholder
					className="ghostlabs-fs-embed-placeholder"
					icon="format-status"
					label={ __( 'Bluesky Post', 'ghostlabs-friendly-skies' ) }
				>
					<SetupInstructions id={ instructionsId }>{ __( 'Paste a Bluesky post URL to embed it.', 'ghostlabs-friendly-skies' ) }</SetupInstructions>
					<div className="ghostlabs-fs-embed-placeholder__input-row">
						<SetupTextField
							label={ __( 'Bluesky post URL', 'ghostlabs-friendly-skies' ) }
							instructionsId={ instructionsId }
							errorId={ errorId }
							error={ error }
							placeholder="https://bsky.app/profile/..."
							value={ inputUrl }
							onChange={ setInputUrl }
							onKeyDown={ onKeyDown }
						/>
						<Button
							variant="primary"
							onClick={ onSubmit }
						>
							{ __( 'Embed', 'ghostlabs-friendly-skies' ) }
						</Button>
					</div>
					<SetupError id={ errorId } className="ghostlabs-fs-embed-placeholder__error">{ error }</SetupError>
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon="edit"
						label={ __( 'Edit URL', 'ghostlabs-friendly-skies' ) }
						onClick={ () => {
							setInputUrl( url );
							setIsEditing( true );
						} }
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Embed Settings', 'ghostlabs-friendly-skies' ) }>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Theme', 'ghostlabs-friendly-skies' ) }
						value={ theme }
						options={ [
							{ label: __( 'System', 'ghostlabs-friendly-skies' ), value: 'system' },
							{ label: __( 'Light', 'ghostlabs-friendly-skies' ), value: 'light' },
							{ label: __( 'Dark', 'ghostlabs-friendly-skies' ), value: 'dark' },
						] }
						onChange={ ( val ) => setAttributes( { theme: val } ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Display Mode', 'ghostlabs-friendly-skies' ) }
						value={ displayMode }
						options={ [
							{ label: __( 'Bluesky Card', 'ghostlabs-friendly-skies' ), value: 'card' },
							{ label: __( 'Theme Integrated', 'ghostlabs-friendly-skies' ), value: 'theme-integrated' },
						] }
						onChange={ ( val ) => setAttributes( { displayMode: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show engagement counts', 'ghostlabs-friendly-skies' ) }
						checked={ showEngagement }
						onChange={ ( val ) => setAttributes( { showEngagement: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show timestamp', 'ghostlabs-friendly-skies' ) }
						checked={ showTimestamp }
						onChange={ ( val ) => setAttributes( { showTimestamp: val } ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Click behavior', 'ghostlabs-friendly-skies' ) }
						value={ clickBehavior }
						options={ [
							{ label: __( 'Open on Bluesky', 'ghostlabs-friendly-skies' ), value: 'new-tab' },
							{ label: __( 'No link', 'ghostlabs-friendly-skies' ), value: 'none' },
						] }
						onChange={ ( val ) => setAttributes( { clickBehavior: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="ghostlabs/friendly-skies-embed"
				attributes={ attributes }
			/>
		</div>
	);
}

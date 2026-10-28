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

const HANDLE_PATTERN = /^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)+$|^did:[a-z]+:.+$/;

export default function Edit( { attributes, setAttributes } ) {
	const { handle, theme, cardStyle, showStats, showBio, ctaButton } = attributes;
	const [ inputHandle, setInputHandle ] = useState( '' );
	const [ error, setError ] = useSetupError();
	const { instructionsId, errorId } = useSetupIds( 'profile' );
	const [ isEditing, setIsEditing ] = useState( ! handle );

	const blockProps = useBlockProps();

	const onSubmit = () => {
		const trimmed = inputHandle.trim().replace( /^@/, '' );

		if ( ! HANDLE_PATTERN.test( trimmed ) ) {
			setError( __( 'Please enter a valid Bluesky handle or DID.', 'ghostlabs-friendly-skies' ) );
			return;
		}

		setError( '' );
		setAttributes( { handle: trimmed } );
		setIsEditing( false );
	};

	const onKeyDown = ( event ) => {
		if ( event.key === 'Enter' ) {
			event.preventDefault();
			onSubmit();
		}
	};

	if ( isEditing || ! handle ) {
		return (
			<div { ...blockProps }>
				<BlockGuide
					slug="profile"
					pages={ [
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Bluesky Profile Card', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'Display a Bluesky profile card with avatar, banner, bio, and stats. Enter any handle or DID to get started.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Card Styles', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'Choose from Full, Compact, or Minimal card styles in the sidebar. You can also toggle stats, bio, and the call-to-action button.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
					] }
				/>
				<Placeholder
					className="ghostlabs-fs-profile-placeholder"
					icon="id"
					label={ __( 'Bluesky Profile', 'ghostlabs-friendly-skies' ) }
				>
					<SetupInstructions id={ instructionsId }>{ __( 'Enter a Bluesky handle to display their profile card.', 'ghostlabs-friendly-skies' ) }</SetupInstructions>
					<div className="ghostlabs-fs-profile-placeholder__input-row">
						<SetupTextField
							label={ __( 'Bluesky handle', 'ghostlabs-friendly-skies' ) }
							instructionsId={ instructionsId }
							errorId={ errorId }
							error={ error }
							placeholder="user.bsky.social"
							value={ inputHandle }
							onChange={ setInputHandle }
							onKeyDown={ onKeyDown }
						/>
						<Button
							variant="primary"
							onClick={ onSubmit }
						>
							{ __( 'Embed', 'ghostlabs-friendly-skies' ) }
						</Button>
					</div>
					<SetupError id={ errorId } className="ghostlabs-fs-profile-placeholder__error">{ error }</SetupError>
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
						label={ __( 'Edit Handle', 'ghostlabs-friendly-skies' ) }
						onClick={ () => {
							setInputHandle( handle );
							setIsEditing( true );
						} }
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Profile Settings', 'ghostlabs-friendly-skies' ) }>
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
						label={ __( 'Card Style', 'ghostlabs-friendly-skies' ) }
						value={ cardStyle }
						options={ [
							{ label: __( 'Full', 'ghostlabs-friendly-skies' ), value: 'full' },
							{ label: __( 'Compact', 'ghostlabs-friendly-skies' ), value: 'compact' },
							{ label: __( 'Minimal', 'ghostlabs-friendly-skies' ), value: 'minimal' },
						] }
						onChange={ ( val ) => setAttributes( { cardStyle: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show stats', 'ghostlabs-friendly-skies' ) }
						checked={ showStats }
						onChange={ ( val ) => setAttributes( { showStats: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show bio', 'ghostlabs-friendly-skies' ) }
						checked={ showBio }
						onChange={ ( val ) => setAttributes( { showBio: val } ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'CTA button', 'ghostlabs-friendly-skies' ) }
						value={ ctaButton }
						options={ [
							{ label: __( 'None', 'ghostlabs-friendly-skies' ), value: 'none' },
							{ label: __( 'View Profile', 'ghostlabs-friendly-skies' ), value: 'view-profile' },
							{ label: __( 'Follow on Bluesky', 'ghostlabs-friendly-skies' ), value: 'follow' },
						] }
						onChange={ ( val ) => setAttributes( { ctaButton: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="ghostlabs/friendly-skies-profile"
				attributes={ attributes }
			/>
		</div>
	);
}

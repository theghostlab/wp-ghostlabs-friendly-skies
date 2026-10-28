import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
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
	const { handle, theme, buttonStyle, showFollowerCount, label, size, showIcon } = attributes;
	const [ inputHandle, setInputHandle ] = useState( '' );
	const [ error, setError ] = useSetupError();
	const { instructionsId, errorId } = useSetupIds( 'follow-button' );
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
					slug="follow-button"
					pages={ [
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Bluesky Follow Button', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'Add a follow button for any Bluesky profile. Enter a handle to get started.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Button Options', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'Customize the button label, style, size, and theme in the sidebar. You can also show a live follower count.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
					] }
				/>
				<Placeholder
					className="ghostlabs-fs-follow-placeholder"
					icon="heart"
					label={ __( 'Bluesky Follow Button', 'ghostlabs-friendly-skies' ) }
				>
					<SetupInstructions id={ instructionsId }>{ __( 'Enter a Bluesky handle to create a follow button.', 'ghostlabs-friendly-skies' ) }</SetupInstructions>
					<div className="ghostlabs-fs-follow-placeholder__input-row">
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
							{ __( 'Create', 'ghostlabs-friendly-skies' ) }
						</Button>
					</div>
					<SetupError id={ errorId } className="ghostlabs-fs-follow-placeholder__error">{ error }</SetupError>
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
				<PanelBody title={ __( 'Button Settings', 'ghostlabs-friendly-skies' ) }>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Label', 'ghostlabs-friendly-skies' ) }
						value={ label }
						onChange={ ( val ) => setAttributes( { label: val } ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Style', 'ghostlabs-friendly-skies' ) }
						value={ buttonStyle }
						options={ [
							{ label: __( 'Primary', 'ghostlabs-friendly-skies' ), value: 'primary' },
							{ label: __( 'Secondary', 'ghostlabs-friendly-skies' ), value: 'secondary' },
							{ label: __( 'Outline', 'ghostlabs-friendly-skies' ), value: 'outline' },
							{ label: __( 'Minimal', 'ghostlabs-friendly-skies' ), value: 'minimal' },
						] }
						onChange={ ( val ) => setAttributes( { buttonStyle: val } ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Size', 'ghostlabs-friendly-skies' ) }
						value={ size }
						options={ [
							{ label: __( 'Small', 'ghostlabs-friendly-skies' ), value: 'small' },
							{ label: __( 'Medium', 'ghostlabs-friendly-skies' ), value: 'medium' },
							{ label: __( 'Large', 'ghostlabs-friendly-skies' ), value: 'large' },
						] }
						onChange={ ( val ) => setAttributes( { size: val } ) }
					/>
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
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show Bluesky icon', 'ghostlabs-friendly-skies' ) }
						checked={ showIcon }
						onChange={ ( val ) => setAttributes( { showIcon: val } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show follower count', 'ghostlabs-friendly-skies' ) }
						checked={ showFollowerCount }
						onChange={ ( val ) => setAttributes( { showFollowerCount: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="ghostlabs/friendly-skies-follow-button"
				attributes={ attributes }
			/>
		</div>
	);
}

import { __ } from '@wordpress/i18n';
import {
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
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import { BlockGuide } from '../_shared/guide.js';
import { SetupError, SetupInstructions, SetupTextField, useSetupError, useSetupIds } from '../_shared/setup-field.js';
import './editor.scss';

const URL_PATTERN = /^https:\/\/bsky\.app\/profile\/[^/]+\/post\/[a-zA-Z0-9]+$/;

export default function Edit( { attributes, setAttributes } ) {
	const {
		url,
		theme,
		depth,
		showOnlyOpReplies,
		showEngagement,
		collapseThreshold,
		clickBehavior,
	} = attributes;

	const [ inputValue, setInputValue ] = useState( '' );
	const [ error, setError ] = useSetupError();
	const { instructionsId, errorId } = useSetupIds( 'thread' );

	const blockProps = useBlockProps();

	const onSubmit = () => {
		const trimmed = inputValue.trim();

		if ( ! trimmed ) {
			setError( __( 'Please enter a Bluesky thread URL.', 'ghostlabs-friendly-skies' ) );
			return;
		}

		if ( ! URL_PATTERN.test( trimmed ) ) {
			setError( __( 'Please enter a valid Bluesky post URL (e.g. https://bsky.app/profile/user.bsky.social/post/abc123).', 'ghostlabs-friendly-skies' ) );
			return;
		}

		setAttributes( { url: trimmed } );
		setError( '' );
	};

	const onKeyDown = ( event ) => {
		if ( event.key === 'Enter' ) {
			event.preventDefault();
			onSubmit();
		}
	};

	if ( ! url ) {
		return (
			<div { ...blockProps }>
				<BlockGuide
					slug="thread"
					pages={ [
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Bluesky Thread', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'Embed a full Bluesky thread on your site. Paste the URL of any post and its replies will be displayed.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
						{
							content: (
								<>
									<h1 className="ghostlabs-fs-block-guide__heading">{ __( 'Thread Options', 'ghostlabs-friendly-skies' ) }</h1>
									<p>{ __( 'Control the reply depth, filter to only OP replies, and set a collapse threshold in the sidebar. Choose a theme and toggle engagement counts.', 'ghostlabs-friendly-skies' ) }</p>
								</>
							),
						},
					] }
				/>
				<Placeholder
					className="ghostlabs-fs-thread-placeholder"
					icon="format-chat"
					label={ __( 'Bluesky Thread', 'ghostlabs-friendly-skies' ) }
				>
					<SetupInstructions id={ instructionsId }>{ __( 'Enter a Bluesky post URL to display its thread.', 'ghostlabs-friendly-skies' ) }</SetupInstructions>
					<div className="ghostlabs-fs-thread-placeholder__input-row">
						<SetupTextField
							label={ __( 'Bluesky post URL', 'ghostlabs-friendly-skies' ) }
							instructionsId={ instructionsId }
							errorId={ errorId }
							error={ error }
							placeholder="https://bsky.app/profile/user.bsky.social/post/abc123"
							value={ inputValue }
							onChange={ setInputValue }
							onKeyDown={ onKeyDown }
						/>
						<Button
							variant="primary"
							onClick={ onSubmit }
						>
							{ __( 'Load Thread', 'ghostlabs-friendly-skies' ) }
						</Button>
					</div>
					<SetupError id={ errorId } className="ghostlabs-fs-thread-placeholder__error">{ error }</SetupError>
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Source', 'ghostlabs-friendly-skies' ) }>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Thread URL', 'ghostlabs-friendly-skies' ) }
						value={ url }
						onChange={ ( val ) => setAttributes( { url: val } ) }
						placeholder="https://bsky.app/profile/user.bsky.social/post/abc123"
					/>
				</PanelBody>

				<PanelBody title={ __( 'Thread', 'ghostlabs-friendly-skies' ) }>
					<RangeControl
						__nextHasNoMarginBottom
						label={ __( 'Reply Depth', 'ghostlabs-friendly-skies' ) }
						value={ depth }
						onChange={ ( val ) => setAttributes( { depth: val } ) }
						min={ 1 }
						max={ 10 }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show only OP replies', 'ghostlabs-friendly-skies' ) }
						checked={ showOnlyOpReplies }
						onChange={ ( val ) => setAttributes( { showOnlyOpReplies: val } ) }
					/>
					<RangeControl
						__nextHasNoMarginBottom
						label={ __( 'Collapse after N posts', 'ghostlabs-friendly-skies' ) }
						value={ collapseThreshold }
						onChange={ ( val ) => setAttributes( { collapseThreshold: val } ) }
						min={ 5 }
						max={ 50 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Display', 'ghostlabs-friendly-skies' ) } initialOpen={ false }>
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
						label={ __( 'Show engagement counts', 'ghostlabs-friendly-skies' ) }
						checked={ showEngagement }
						onChange={ ( val ) => setAttributes( { showEngagement: val } ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Click Behavior', 'ghostlabs-friendly-skies' ) }
						value={ clickBehavior }
						options={ [
							{ label: __( 'Open on Bluesky', 'ghostlabs-friendly-skies' ), value: 'open' },
							{ label: __( 'Expand only', 'ghostlabs-friendly-skies' ), value: 'expand' },
						] }
						onChange={ ( val ) => setAttributes( { clickBehavior: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="ghostlabs/friendly-skies-thread"
				attributes={ attributes }
			/>
		</div>
	);
}

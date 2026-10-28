import { speak } from '@wordpress/a11y';
import { TextControl } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { useCallback, useState } from '@wordpress/element';

/**
 * The one text field on each block's setup screen, and what it needs to be usable without
 * sight (accessibility audit 2026-10-23, item 6; WCAG 1.3.1, 3.3.2, 4.1.3):
 *
 * - a real label, not only a placeholder, which disappears on typing and is not a name;
 * - the setup instructions tied to the field with `aria-describedby`, not merely nearby;
 * - an error that is spoken when it appears, and tied to the field while it stands.
 *
 * ⚠️ The instructions are rendered here rather than through `Placeholder`'s `instructions`
 * prop, because that element takes no id and so cannot describe the field.
 */

/**
 * The ids a block's setup screen ties together.
 *
 * @param {string} slug The block's short name, which keeps two blocks' ids apart.
 * @return {{instructionsId: string, errorId: string}} The instructions' id and the error's.
 */
export function useSetupIds( slug ) {
	const id = useInstanceId( useSetupIds, `ghostlabs-fs-${ slug }-setup` );

	return { instructionsId: `${ id }-instructions`, errorId: `${ id }-error` };
}

/**
 * State for the setup error, announced through `wp.a11y.speak()` whenever one is set. The
 * same message set twice is spoken twice: speak() makes a repeat distinct by itself.
 */
export function useSetupError() {
	const [ error, setErrorState ] = useState( '' );

	const setError = useCallback( ( message ) => {
		setErrorState( message );

		if ( message ) {
			speak( message, 'assertive' );
		}
	}, [] );

	return [ error, setError ];
}

export function SetupInstructions( { id, children } ) {
	return (
		<p id={ id } className="components-placeholder__instructions ghostlabs-fs-setup__instructions">
			{ children }
		</p>
	);
}

export function SetupTextField( { label, instructionsId, errorId, error, ...props } ) {
	const describedBy = [ instructionsId, error ? errorId : null ].filter( Boolean ).join( ' ' );

	return (
		<TextControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ label }
			hideLabelFromVision
			aria-describedby={ describedBy || undefined }
			aria-invalid={ error ? true : undefined }
			{ ...props }
		/>
	);
}

export function SetupError( { id, className, children } ) {
	if ( ! children ) {
		return null;
	}

	return (
		<p id={ id } className={ className }>
			{ children }
		</p>
	);
}

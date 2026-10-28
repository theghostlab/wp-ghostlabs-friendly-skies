import { useState } from '@wordpress/element';
import { Guide } from '@wordpress/components';

const STORAGE_PREFIX = 'ghostlabs_fs_guide_';

export function BlockGuide( { slug, pages } ) {
	const storageKey = STORAGE_PREFIX + slug;
	const [ isOpen, setIsOpen ] = useState(
		() => localStorage.getItem( storageKey ) !== '1'
	);

	if ( ! isOpen || pages.length === 0 ) {
		return null;
	}

	return (
		<Guide
			className="ghostlabs-fs-block-guide"
			onFinish={ () => {
				localStorage.setItem( storageKey, '1' );
				setIsOpen( false );
			} }
			pages={ pages }
		/>
	);
}

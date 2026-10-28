/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 373
(__unused_webpack_module, __unused_webpack___webpack_exports__, __webpack_require__) {


;// external ["wp","blocks"]
const external_wp_blocks_namespaceObject = window["wp"]["blocks"];
;// external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external ["wp","blockEditor"]
const external_wp_blockEditor_namespaceObject = window["wp"]["blockEditor"];
;// external ["wp","components"]
const external_wp_components_namespaceObject = window["wp"]["components"];
;// external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","serverSideRender"]
const external_wp_serverSideRender_namespaceObject = window["wp"]["serverSideRender"];
var external_wp_serverSideRender_default = /*#__PURE__*/__webpack_require__.n(external_wp_serverSideRender_namespaceObject);
;// external "ReactJSXRuntime"
const external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./src/blocks/_shared/guide.js



const STORAGE_PREFIX = 'ghostlabs_fs_guide_';
function BlockGuide({
  slug,
  pages
}) {
  const storageKey = STORAGE_PREFIX + slug;
  const [isOpen, setIsOpen] = (0,external_wp_element_namespaceObject.useState)(() => localStorage.getItem(storageKey) !== '1');
  if (!isOpen || pages.length === 0) {
    return null;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Guide, {
    className: "ghostlabs-fs-block-guide",
    onFinish: () => {
      localStorage.setItem(storageKey, '1');
      setIsOpen(false);
    },
    pages: pages
  });
}
;// external ["wp","a11y"]
const external_wp_a11y_namespaceObject = window["wp"]["a11y"];
;// external ["wp","compose"]
const external_wp_compose_namespaceObject = window["wp"]["compose"];
;// ./src/blocks/_shared/setup-field.js





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

function useSetupIds(slug) {
  const id = (0,external_wp_compose_namespaceObject.useInstanceId)(useSetupIds, `ghostlabs-fs-${slug}-setup`);
  return {
    instructionsId: `${id}-instructions`,
    errorId: `${id}-error`
  };
}

/**
 * State for the setup error, announced through `wp.a11y.speak()` whenever one is set. The
 * same message set twice is spoken twice: speak() makes a repeat distinct by itself.
 */
function useSetupError() {
  const [error, setErrorState] = (0,external_wp_element_namespaceObject.useState)('');
  const setError = (0,external_wp_element_namespaceObject.useCallback)(message => {
    setErrorState(message);
    if (message) {
      (0,external_wp_a11y_namespaceObject.speak)(message, 'assertive');
    }
  }, []);
  return [error, setError];
}
function SetupInstructions({
  id,
  children
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
    id: id,
    className: "components-placeholder__instructions ghostlabs-fs-setup__instructions",
    children: children
  });
}
function SetupTextField({
  label,
  instructionsId,
  errorId,
  error,
  ...props
}) {
  const describedBy = [instructionsId, error ? errorId : null].filter(Boolean).join(' ');
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TextControl, {
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true,
    label: label,
    hideLabelFromVision: true,
    "aria-describedby": describedBy || undefined,
    "aria-invalid": error ? true : undefined,
    ...props
  });
}
function SetupError({
  id,
  className,
  children
}) {
  if (!children) {
    return null;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
    id: id,
    className: className,
    children: children
  });
}
;// ./src/blocks/friendly-skies-embed/edit.js









const BSKY_URL_PATTERN = /^https?:\/\/bsky\.app\/profile\/[^/]+\/post\/[a-zA-Z0-9]+/;
function Edit({
  attributes,
  setAttributes
}) {
  const {
    url,
    theme,
    displayMode,
    showEngagement,
    showTimestamp,
    clickBehavior
  } = attributes;
  const [inputUrl, setInputUrl] = (0,external_wp_element_namespaceObject.useState)('');
  const [error, setError] = useSetupError();
  const {
    instructionsId,
    errorId
  } = useSetupIds('embed');
  const [isEditing, setIsEditing] = (0,external_wp_element_namespaceObject.useState)(!url);
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)();
  const onSubmit = () => {
    const trimmed = inputUrl.trim();
    if (!BSKY_URL_PATTERN.test(trimmed)) {
      setError((0,external_wp_i18n_namespaceObject.__)('Please enter a valid Bluesky post URL.', 'ghostlabs-friendly-skies'));
      return;
    }
    setError('');
    setAttributes({
      url: trimmed
    });
    setIsEditing(false);
  };
  const onKeyDown = event => {
    if (event.key === 'Enter') {
      event.preventDefault();
      onSubmit();
    }
  };
  if (isEditing || !url) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      ...blockProps,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(BlockGuide, {
        slug: "embed",
        pages: [{
          content: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h1", {
              className: "ghostlabs-fs-block-guide__heading",
              children: (0,external_wp_i18n_namespaceObject.__)('Bluesky Post Embed', 'ghostlabs-friendly-skies')
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
              children: (0,external_wp_i18n_namespaceObject.__)('Embed any Bluesky post on your site. Just copy the post URL from bsky.app and paste it here.', 'ghostlabs-friendly-skies')
            })]
          })
        }, {
          content: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h1", {
              className: "ghostlabs-fs-block-guide__heading",
              children: (0,external_wp_i18n_namespaceObject.__)('Customize Your Embed', 'ghostlabs-friendly-skies')
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
              children: (0,external_wp_i18n_namespaceObject.__)('After loading, use the sidebar to choose a theme, display mode, and toggle engagement counts and timestamps.', 'ghostlabs-friendly-skies')
            })]
          })
        }]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Placeholder, {
        className: "ghostlabs-fs-embed-placeholder",
        icon: "format-status",
        label: (0,external_wp_i18n_namespaceObject.__)('Bluesky Post', 'ghostlabs-friendly-skies'),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupInstructions, {
          id: instructionsId,
          children: (0,external_wp_i18n_namespaceObject.__)('Paste a Bluesky post URL to embed it.', 'ghostlabs-friendly-skies')
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "ghostlabs-fs-embed-placeholder__input-row",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupTextField, {
            label: (0,external_wp_i18n_namespaceObject.__)('Bluesky post URL', 'ghostlabs-friendly-skies'),
            instructionsId: instructionsId,
            errorId: errorId,
            error: error,
            placeholder: "https://bsky.app/profile/...",
            value: inputUrl,
            onChange: setInputUrl,
            onKeyDown: onKeyDown
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
            variant: "primary",
            onClick: onSubmit,
            children: (0,external_wp_i18n_namespaceObject.__)('Embed', 'ghostlabs-friendly-skies')
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupError, {
          id: errorId,
          className: "ghostlabs-fs-embed-placeholder__error",
          children: error
        })]
      })]
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    ...blockProps,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_blockEditor_namespaceObject.BlockControls, {
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToolbarGroup, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToolbarButton, {
          icon: "edit",
          label: (0,external_wp_i18n_namespaceObject.__)('Edit URL', 'ghostlabs-friendly-skies'),
          onClick: () => {
            setInputUrl(url);
            setIsEditing(true);
          }
        })
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_blockEditor_namespaceObject.InspectorControls, {
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
        title: (0,external_wp_i18n_namespaceObject.__)('Embed Settings', 'ghostlabs-friendly-skies'),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Theme', 'ghostlabs-friendly-skies'),
          value: theme,
          options: [{
            label: (0,external_wp_i18n_namespaceObject.__)('System', 'ghostlabs-friendly-skies'),
            value: 'system'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Light', 'ghostlabs-friendly-skies'),
            value: 'light'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Dark', 'ghostlabs-friendly-skies'),
            value: 'dark'
          }],
          onChange: val => setAttributes({
            theme: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Display Mode', 'ghostlabs-friendly-skies'),
          value: displayMode,
          options: [{
            label: (0,external_wp_i18n_namespaceObject.__)('Bluesky Card', 'ghostlabs-friendly-skies'),
            value: 'card'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Theme Integrated', 'ghostlabs-friendly-skies'),
            value: 'theme-integrated'
          }],
          onChange: val => setAttributes({
            displayMode: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Show engagement counts', 'ghostlabs-friendly-skies'),
          checked: showEngagement,
          onChange: val => setAttributes({
            showEngagement: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Show timestamp', 'ghostlabs-friendly-skies'),
          checked: showTimestamp,
          onChange: val => setAttributes({
            showTimestamp: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Click behavior', 'ghostlabs-friendly-skies'),
          value: clickBehavior,
          options: [{
            label: (0,external_wp_i18n_namespaceObject.__)('Open on Bluesky', 'ghostlabs-friendly-skies'),
            value: 'new-tab'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('No link', 'ghostlabs-friendly-skies'),
            value: 'none'
          }],
          onChange: val => setAttributes({
            clickBehavior: val
          })
        })]
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)((external_wp_serverSideRender_default()), {
      block: "ghostlabs/friendly-skies-embed",
      attributes: attributes
    })]
  });
}
;// ./src/blocks/friendly-skies-embed/block.json
const block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"ghostlabs/friendly-skies-embed"}');
;// ./src/blocks/friendly-skies-embed/index.js




(0,external_wp_blocks_namespaceObject.registerBlockType)(block_namespaceObject.UU, {
  edit: Edit,
  save: () => null
});

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		const deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			let notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				let [chunkIds, fn, priority] = deferred[i];
/******/ 				let fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					const r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			const getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter/value functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			if(Array.isArray(definition)) {
/******/ 				var i = 0;
/******/ 				while(i < definition.length) {
/******/ 					var key = definition[i++];
/******/ 					var binding = definition[i++];
/******/ 					if(!__webpack_require__.o(exports, key)) {
/******/ 						if(binding === 0) {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, value: definition[i++] });
/******/ 						} else {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, get: binding });
/******/ 						}
/******/ 					} else if(binding === 0) { i++; }
/******/ 				}
/******/ 			} else {
/******/ 				for(var key in definition) {
/******/ 					if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 						Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		const installedChunks = {
/******/ 			316: 0,
/******/ 			348: 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		const webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			let [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		const chunkLoadingGlobal = globalThis["webpackChunkfriendly_skies_blocks"] ||= [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, [348], () => (__webpack_require__(373)))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
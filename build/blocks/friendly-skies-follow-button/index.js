/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 530
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
;// ./src/blocks/friendly-skies-follow-button/edit.js









const HANDLE_PATTERN = /^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)+$|^did:[a-z]+:.+$/;
function Edit({
  attributes,
  setAttributes
}) {
  const {
    handle,
    theme,
    buttonStyle,
    showFollowerCount,
    label,
    size,
    showIcon
  } = attributes;
  const [inputHandle, setInputHandle] = (0,external_wp_element_namespaceObject.useState)('');
  const [error, setError] = useSetupError();
  const {
    instructionsId,
    errorId
  } = useSetupIds('follow-button');
  const [isEditing, setIsEditing] = (0,external_wp_element_namespaceObject.useState)(!handle);
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)();
  const onSubmit = () => {
    const trimmed = inputHandle.trim().replace(/^@/, '');
    if (!HANDLE_PATTERN.test(trimmed)) {
      setError((0,external_wp_i18n_namespaceObject.__)('Please enter a valid Bluesky handle or DID.', 'ghostlabs-friendly-skies'));
      return;
    }
    setError('');
    setAttributes({
      handle: trimmed
    });
    setIsEditing(false);
  };
  const onKeyDown = event => {
    if (event.key === 'Enter') {
      event.preventDefault();
      onSubmit();
    }
  };
  if (isEditing || !handle) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      ...blockProps,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(BlockGuide, {
        slug: "follow-button",
        pages: [{
          content: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h1", {
              className: "ghostlabs-fs-block-guide__heading",
              children: (0,external_wp_i18n_namespaceObject.__)('Bluesky Follow Button', 'ghostlabs-friendly-skies')
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
              children: (0,external_wp_i18n_namespaceObject.__)('Add a follow button for any Bluesky profile. Enter a handle to get started.', 'ghostlabs-friendly-skies')
            })]
          })
        }, {
          content: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h1", {
              className: "ghostlabs-fs-block-guide__heading",
              children: (0,external_wp_i18n_namespaceObject.__)('Button Options', 'ghostlabs-friendly-skies')
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
              children: (0,external_wp_i18n_namespaceObject.__)('Customize the button label, style, size, and theme in the sidebar. You can also show a live follower count.', 'ghostlabs-friendly-skies')
            })]
          })
        }]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Placeholder, {
        className: "ghostlabs-fs-follow-placeholder",
        icon: "heart",
        label: (0,external_wp_i18n_namespaceObject.__)('Bluesky Follow Button', 'ghostlabs-friendly-skies'),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupInstructions, {
          id: instructionsId,
          children: (0,external_wp_i18n_namespaceObject.__)('Enter a Bluesky handle to create a follow button.', 'ghostlabs-friendly-skies')
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "ghostlabs-fs-follow-placeholder__input-row",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupTextField, {
            label: (0,external_wp_i18n_namespaceObject.__)('Bluesky handle', 'ghostlabs-friendly-skies'),
            instructionsId: instructionsId,
            errorId: errorId,
            error: error,
            placeholder: "user.bsky.social",
            value: inputHandle,
            onChange: setInputHandle,
            onKeyDown: onKeyDown
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
            variant: "primary",
            onClick: onSubmit,
            children: (0,external_wp_i18n_namespaceObject.__)('Create', 'ghostlabs-friendly-skies')
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupError, {
          id: errorId,
          className: "ghostlabs-fs-follow-placeholder__error",
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
          label: (0,external_wp_i18n_namespaceObject.__)('Edit Handle', 'ghostlabs-friendly-skies'),
          onClick: () => {
            setInputHandle(handle);
            setIsEditing(true);
          }
        })
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_blockEditor_namespaceObject.InspectorControls, {
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
        title: (0,external_wp_i18n_namespaceObject.__)('Button Settings', 'ghostlabs-friendly-skies'),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TextControl, {
          __nextHasNoMarginBottom: true,
          __next40pxDefaultSize: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Label', 'ghostlabs-friendly-skies'),
          value: label,
          onChange: val => setAttributes({
            label: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Style', 'ghostlabs-friendly-skies'),
          value: buttonStyle,
          options: [{
            label: (0,external_wp_i18n_namespaceObject.__)('Primary', 'ghostlabs-friendly-skies'),
            value: 'primary'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Secondary', 'ghostlabs-friendly-skies'),
            value: 'secondary'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Outline', 'ghostlabs-friendly-skies'),
            value: 'outline'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Minimal', 'ghostlabs-friendly-skies'),
            value: 'minimal'
          }],
          onChange: val => setAttributes({
            buttonStyle: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Size', 'ghostlabs-friendly-skies'),
          value: size,
          options: [{
            label: (0,external_wp_i18n_namespaceObject.__)('Small', 'ghostlabs-friendly-skies'),
            value: 'small'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Medium', 'ghostlabs-friendly-skies'),
            value: 'medium'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Large', 'ghostlabs-friendly-skies'),
            value: 'large'
          }],
          onChange: val => setAttributes({
            size: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
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
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Show Bluesky icon', 'ghostlabs-friendly-skies'),
          checked: showIcon,
          onChange: val => setAttributes({
            showIcon: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Show follower count', 'ghostlabs-friendly-skies'),
          checked: showFollowerCount,
          onChange: val => setAttributes({
            showFollowerCount: val
          })
        })]
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)((external_wp_serverSideRender_default()), {
      block: "ghostlabs/friendly-skies-follow-button",
      attributes: attributes
    })]
  });
}
;// ./src/blocks/friendly-skies-follow-button/block.json
const block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"ghostlabs/friendly-skies-follow-button"}');
;// ./src/blocks/friendly-skies-follow-button/index.js




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
/******/ 			139: 0,
/******/ 			91: 0
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
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, [91], () => (__webpack_require__(530)))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
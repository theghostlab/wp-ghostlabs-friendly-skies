/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 492
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
;// external ["wp","hooks"]
const external_wp_hooks_namespaceObject = window["wp"]["hooks"];
;// ./src/blocks/friendly-skies-feed/options.js




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
function GridControls({
  attributes,
  setAttributes
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true,
    label: (0,external_wp_i18n_namespaceObject.__)('Columns', 'ghostlabs-friendly-skies'),
    value: String(attributes.gridColumns),
    options: [{
      label: '2',
      value: '2'
    }, {
      label: '3',
      value: '3'
    }, {
      label: '4',
      value: '4'
    }],
    onChange: val => setAttributes({
      gridColumns: Number(val)
    })
  });
}

/** @return {Object[]} The layouts, in the order the select lists them. */
const layoutOptions = () => (0,external_wp_hooks_namespaceObject.applyFilters)('ghostlabs.friendlySkies.feed.layouts', [{
  value: 'list',
  label: (0,external_wp_i18n_namespaceObject.__)('List', 'ghostlabs-friendly-skies')
}, {
  value: 'grid',
  label: (0,external_wp_i18n_namespaceObject.__)('Grid', 'ghostlabs-friendly-skies'),
  alignable: true,
  Controls: GridControls
}]);

/** @return {Object[]} The sources, in the order the select lists them. */
const sourceOptions = () => (0,external_wp_hooks_namespaceObject.applyFilters)('ghostlabs.friendlySkies.feed.sources', [{
  value: 'user',
  label: (0,external_wp_i18n_namespaceObject.__)('User Feed', 'ghostlabs-friendly-skies'),
  attribute: 'actor',
  fieldLabel: (0,external_wp_i18n_namespaceObject.__)('Bluesky handle', 'ghostlabs-friendly-skies'),
  inspectorLabel: (0,external_wp_i18n_namespaceObject.__)('Handle', 'ghostlabs-friendly-skies'),
  placeholder: 'user.bsky.social',
  instructions: (0,external_wp_i18n_namespaceObject.__)('Enter a Bluesky handle to display their posts.', 'ghostlabs-friendly-skies'),
  parse: input => {
    const handle = input.replace(/^@/, '');
    return HANDLE_PATTERN.test(handle) ? {
      value: handle
    } : {
      error: (0,external_wp_i18n_namespaceObject.__)('Please enter a valid Bluesky handle or DID.', 'ghostlabs-friendly-skies')
    };
  },
  normalise: value => value.replace(/^@/, '')
}]);

/** @return {Object[]} The first-run guide's pages. */
const guidePages = () => (0,external_wp_hooks_namespaceObject.applyFilters)('ghostlabs.friendlySkies.feed.guide', [{
  heading: (0,external_wp_i18n_namespaceObject.__)('Bluesky Feed', 'ghostlabs-friendly-skies'),
  body: (0,external_wp_i18n_namespaceObject.__)('Display a live Bluesky feed on your site.', 'ghostlabs-friendly-skies')
}, {
  heading: (0,external_wp_i18n_namespaceObject.__)('Getting Started', 'ghostlabs-friendly-skies'),
  body: (0,external_wp_i18n_namespaceObject.__)('Enter a Bluesky handle, then choose Load Feed.', 'ghostlabs-friendly-skies')
}, {
  heading: (0,external_wp_i18n_namespaceObject.__)('Layouts & Filters', 'ghostlabs-friendly-skies'),
  body: (0,external_wp_i18n_namespaceObject.__)('Show the posts as a list or a grid. Use the sidebar to configure filters, display options, and more.', 'ghostlabs-friendly-skies')
}]);
;// ./src/blocks/friendly-skies-feed/edit.js










/**
 * A named seam after the block's own inspector panels, empty unless an edition fills it.
 *
 * ⚠️ A Fill rendered beside the block lands in registration order, and this block's panels mount
 * only once a feed has loaded, so anything added from outside would otherwise sit above Source.
 * The name is a contract: `free-only/assets/js/feed-upsell.js` fills it.
 */

const INSPECTOR_END = 'GhostLabsFriendlySkiesFeedInspectorEnd';
const toSelectOptions = descriptors => descriptors.map(({
  value,
  label
}) => ({
  value,
  label
}));
function Edit({
  attributes,
  setAttributes
}) {
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
    truncateLength
  } = attributes;
  const [inputValue, setInputValue] = (0,external_wp_element_namespaceObject.useState)('');
  const [error, setError] = useSetupError();
  const {
    instructionsId,
    errorId
  } = useSetupIds('feed');
  const layouts = layoutOptions();
  const sources = sourceOptions();

  // What the block stores is not always something this block offers — a block written on a site
  // with more sources or layouts. It renders as the first layout, and an unknown source shows the
  // setup screen for the first source.
  const currentLayout = layouts.find(l => l.value === layout) ?? layouts[0];
  const currentSource = sources.find(s => s.value === sourceType) ?? null;
  const setupSource = currentSource ?? sources[0];
  const supportsAlign = !!currentLayout.alignable;
  const LayoutControls = currentLayout.Controls;
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)({
    className: supportsAlign && align ? `align${align}` : undefined
  });
  const hasSource = !!currentSource && !!attributes[currentSource.attribute];
  const onSubmit = () => {
    const trimmed = inputValue.trim();
    if (!trimmed) {
      setError((0,external_wp_i18n_namespaceObject.__)('Please enter a value.', 'ghostlabs-friendly-skies'));
      return;
    }

    // A pasted link can name its own source, whichever one is selected.
    const target = sources.find(s => s.claims?.(trimmed, setupSource.value)) ?? setupSource;
    const result = target.parse ? target.parse(trimmed) : {
      value: trimmed
    };
    if (result.error) {
      setError(result.error);
      return;
    }
    setAttributes({
      sourceType: target.value,
      [target.attribute]: result.value
    });
    setError('');
  };
  const onKeyDown = event => {
    if (event.key === 'Enter') {
      event.preventDefault();
      onSubmit();
    }
  };

  // Changing source clears what every source showed, so a block never holds two.
  const clearedSources = () => Object.fromEntries(sources.map(s => [s.attribute, '']));

  // One source is no choice, so there is no select for it.
  const sourceSelect = props => sources.length > 1 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true,
    label: (0,external_wp_i18n_namespaceObject.__)('Source Type', 'ghostlabs-friendly-skies'),
    value: setupSource.value,
    options: toSelectOptions(sources),
    ...props
  });
  if (!hasSource) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      ...blockProps,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(BlockGuide, {
        slug: "feed",
        pages: guidePages().map(({
          heading,
          body
        }) => ({
          content: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h1", {
              className: "ghostlabs-fs-block-guide__heading",
              children: heading
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
              children: body
            })]
          })
        }))
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Placeholder, {
        className: "ghostlabs-fs-feed-placeholder",
        icon: "rss",
        label: (0,external_wp_i18n_namespaceObject.__)('Bluesky Feed', 'ghostlabs-friendly-skies'),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupInstructions, {
          id: instructionsId,
          children: setupSource.instructions
        }), sources.length > 1 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          className: "ghostlabs-fs-feed-placeholder__source-select",
          children: sourceSelect({
            hideLabelFromVision: true,
            onChange: val => {
              setAttributes({
                sourceType: val
              });
              setInputValue('');
              setError('');
            }
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "ghostlabs-fs-feed-placeholder__input-row",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupTextField, {
            label: setupSource.fieldLabel,
            instructionsId: instructionsId,
            errorId: errorId,
            error: error,
            placeholder: setupSource.placeholder,
            value: inputValue,
            onChange: setInputValue,
            onKeyDown: onKeyDown
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
            variant: "primary",
            onClick: onSubmit,
            children: (0,external_wp_i18n_namespaceObject.__)('Load Feed', 'ghostlabs-friendly-skies')
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SetupError, {
          id: errorId,
          className: "ghostlabs-fs-feed-placeholder__error",
          children: error
        })]
      })]
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    ...blockProps,
    children: [supportsAlign && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_blockEditor_namespaceObject.BlockControls, {
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_blockEditor_namespaceObject.BlockAlignmentControl, {
        value: align,
        onChange: val => setAttributes({
          align: val
        }),
        controls: ['wide', 'full']
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_blockEditor_namespaceObject.InspectorControls, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
        title: (0,external_wp_i18n_namespaceObject.__)('Source', 'ghostlabs-friendly-skies'),
        children: [sourceSelect({
          onChange: val => setAttributes({
            sourceType: val,
            ...clearedSources()
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TextControl, {
          __nextHasNoMarginBottom: true,
          __next40pxDefaultSize: true,
          label: currentSource.inspectorLabel,
          help: currentSource.help,
          placeholder: currentSource.inspectorPlaceholder,
          value: attributes[currentSource.attribute],
          onChange: val => setAttributes({
            [currentSource.attribute]: currentSource.normalise ? currentSource.normalise(val) : val
          })
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
        title: (0,external_wp_i18n_namespaceObject.__)('Layout', 'ghostlabs-friendly-skies'),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          __next40pxDefaultSize: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Layout', 'ghostlabs-friendly-skies'),
          value: currentLayout.value,
          options: toSelectOptions(layouts),
          onChange: val => {
            const attrs = {
              layout: val
            };
            if (!layouts.find(l => l.value === val)?.alignable) {
              attrs.align = '';
            }
            setAttributes(attrs);
          }
        }), LayoutControls && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(LayoutControls, {
          attributes: attributes,
          setAttributes: setAttributes
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
        title: (0,external_wp_i18n_namespaceObject.__)('Filters', 'ghostlabs-friendly-skies'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.RangeControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Maximum Posts', 'ghostlabs-friendly-skies'),
          value: maxPosts,
          onChange: val => setAttributes({
            maxPosts: val
          }),
          min: 1,
          max: 50
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Include replies', 'ghostlabs-friendly-skies'),
          checked: includeReplies,
          onChange: val => setAttributes({
            includeReplies: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Include reposts', 'ghostlabs-friendly-skies'),
          checked: includeReposts,
          onChange: val => setAttributes({
            includeReposts: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          __next40pxDefaultSize: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Content Filter', 'ghostlabs-friendly-skies'),
          value: contentFilter,
          options: [{
            label: (0,external_wp_i18n_namespaceObject.__)('All', 'ghostlabs-friendly-skies'),
            value: 'all'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('Text only', 'ghostlabs-friendly-skies'),
            value: 'text'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('With images', 'ghostlabs-friendly-skies'),
            value: 'images'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('With video', 'ghostlabs-friendly-skies'),
            value: 'video'
          }, {
            label: (0,external_wp_i18n_namespaceObject.__)('With links', 'ghostlabs-friendly-skies'),
            value: 'links'
          }],
          onChange: val => setAttributes({
            contentFilter: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TextControl, {
          __nextHasNoMarginBottom: true,
          __next40pxDefaultSize: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Pinned Post URL', 'ghostlabs-friendly-skies'),
          help: (0,external_wp_i18n_namespaceObject.__)('Paste a bsky.app post link or an AT URI.', 'ghostlabs-friendly-skies'),
          value: pinnedPostUri,
          onChange: val => setAttributes({
            pinnedPostUri: val
          }),
          placeholder: "https://bsky.app/profile/.../post/..."
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
        title: (0,external_wp_i18n_namespaceObject.__)('Display', 'ghostlabs-friendly-skies'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
          __nextHasNoMarginBottom: true,
          __next40pxDefaultSize: true,
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
          label: (0,external_wp_i18n_namespaceObject.__)('Show engagement counts', 'ghostlabs-friendly-skies'),
          checked: showEngagement,
          onChange: val => setAttributes({
            showEngagement: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Show timestamps', 'ghostlabs-friendly-skies'),
          checked: showTimestamps,
          onChange: val => setAttributes({
            showTimestamps: val
          })
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Truncate long posts', 'ghostlabs-friendly-skies'),
          checked: truncatePosts,
          onChange: val => setAttributes({
            truncatePosts: val
          })
        }), truncatePosts && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.RangeControl, {
          __nextHasNoMarginBottom: true,
          label: (0,external_wp_i18n_namespaceObject.__)('Truncate at characters', 'ghostlabs-friendly-skies'),
          value: truncateLength,
          onChange: val => setAttributes({
            truncateLength: val
          }),
          min: 100,
          max: 500
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Slot, {
        name: INSPECTOR_END
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)((external_wp_serverSideRender_default()), {
      block: "ghostlabs/friendly-skies-feed",
      attributes: attributes
    })]
  });
}
;// ./src/blocks/friendly-skies-feed/block.json
const block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"ghostlabs/friendly-skies-feed"}');
;// ./src/blocks/friendly-skies-feed/index.js




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
/******/ 			679: 0,
/******/ 			879: 0
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
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, [879], () => (__webpack_require__(492)))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
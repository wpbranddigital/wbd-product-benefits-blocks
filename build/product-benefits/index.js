/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/product-benefits/benefit-icon.js"
/*!**********************************************!*\
  !*** ./src/product-benefits/benefit-icon.js ***!
  \**********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ BenefitIcon)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./icons */ "./src/product-benefits/icons.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils */ "./src/product-benefits/utils.js");
/**
 * Icon renderer shared by edit() and save().
 */




/**
 * Renders the visual for a single benefit item.
 *
 * - Checklist layout always uses a checkmark.
 * - A custom image replaces the SVG icon when set; its alt text is kept.
 * - SVG icons are decorative and hidden from assistive technology.
 *
 * @param {Object} props            Component props.
 * @param {Object} props.item       Benefit item.
 * @param {Object} props.attributes Block attributes.
 * @return {Element|null} Icon element.
 */
function BenefitIcon({
  item,
  attributes
}) {
  if (false === attributes.showIcon) {
    return null;
  }
  if ('checklist' === attributes.layout) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "cb-benefit-icon",
      "aria-hidden": "true"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_icons__WEBPACK_IMPORTED_MODULE_1__.SvgIcon, {
      name: "check"
    }));
  }
  if (item.mediaUrl) {
    const size = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.getIconPixelSize)(attributes);
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "cb-benefit-icon cb-benefit-icon--image"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: item.mediaUrl,
      alt: item.mediaAlt || '',
      width: size,
      height: size,
      loading: "lazy",
      decoding: "async"
    }));
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "cb-benefit-icon",
    "aria-hidden": "true"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_icons__WEBPACK_IMPORTED_MODULE_1__.SvgIcon, {
    name: item.icon
  }));
}

/***/ },

/***/ "./src/product-benefits/edit.js"
/*!**************************************!*\
  !*** ./src/product-benefits/edit.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _benefit_icon__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./benefit-icon */ "./src/product-benefits/benefit-icon.js");
/* harmony import */ var _inspector_controls__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./inspector-controls */ "./src/product-benefits/inspector-controls.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./utils */ "./src/product-benefits/utils.js");
/**
 * Editor component for the Product Benefits block.
 */








function Edit({
  attributes,
  setAttributes,
  isSelected
}) {
  const {
    items,
    titleTag
  } = attributes;
  const [activeIndex, setActiveIndex] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(0);
  const {
    __unstableMarkNextChangeAsNotPersistent: markNotPersistent
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_2__.useDispatch)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.store);

  // Insert translated default items once, when the block is first added.
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    if (undefined === items) {
      if (typeof markNotPersistent === 'function') {
        markNotPersistent();
      }
      setAttributes({
        items: (0,_utils__WEBPACK_IMPORTED_MODULE_7__.getDefaultItems)()
      });
    }
  }, [items, markNotPersistent, setAttributes]);
  const list = Array.isArray(items) ? items : [];
  const TitleTag = _utils__WEBPACK_IMPORTED_MODULE_7__.TITLE_TAGS.includes(titleTag) ? titleTag : 'h3';
  const setItems = next => setAttributes({
    items: next
  });
  const updateItem = (index, changes) => {
    setItems(list.map((item, i) => i === index ? {
      ...item,
      ...changes
    } : item));
  };
  const addItem = () => {
    const next = [...list, (0,_utils__WEBPACK_IMPORTED_MODULE_7__.createItem)({
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('New benefit', 'wbd-product-benefits-blocks')
    })];
    setItems(next);
    setActiveIndex(next.length - 1);
  };
  const removeItem = index => {
    const next = list.filter((item, i) => i !== index);
    setItems(next);
    setActiveIndex(Math.max(0, Math.min(index, next.length - 1)));
  };
  const duplicateItem = index => {
    const next = [...list];
    next.splice(index + 1, 0, {
      ...list[index],
      id: (0,_utils__WEBPACK_IMPORTED_MODULE_7__.createItemId)()
    });
    setItems(next);
    setActiveIndex(index + 1);
  };
  const moveItem = (index, direction) => {
    const target = index + direction;
    if (target < 0 || target >= list.length) {
      return;
    }
    const next = [...list];
    [next[index], next[target]] = [next[target], next[index]];
    setItems(next);
    setActiveIndex(target);
  };
  const itemActions = {
    updateItem,
    addItem,
    removeItem,
    duplicateItem,
    moveItem
  };
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)({
    className: (0,_utils__WEBPACK_IMPORTED_MODULE_7__.getBlockClassName)(attributes),
    style: (0,_utils__WEBPACK_IMPORTED_MODULE_7__.getStyleVars)(attributes)
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.BlockControls, {
    group: "block"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarGroup, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarButton, {
    icon: "plus-alt2",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add benefit', 'wbd-product-benefits-blocks'),
    onClick: addItem
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_inspector_controls__WEBPACK_IMPORTED_MODULE_6__["default"], {
    attributes: attributes,
    setAttributes: setAttributes,
    activeIndex: activeIndex,
    setActiveIndex: setActiveIndex,
    ...itemActions
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("ul", {
    ...blockProps,
    role: "list"
  }, list.length === 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("li", {
    className: "cb-benefit-item cb-benefit-empty"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('This block has no benefits yet.', 'wbd-product-benefits-blocks')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Button, {
    variant: "secondary",
    icon: "plus-alt2",
    onClick: addItem
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add benefit', 'wbd-product-benefits-blocks'))), list.map((item, index) => {
    const isActive = isSelected && index === activeIndex;
    const position = index + 1;
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("li", {
      key: item.id || index,
      className: (0,_utils__WEBPACK_IMPORTED_MODULE_7__.getItemClassName)(item) + (isActive ? ' is-cb-active' : ''),
      onFocus: () => setActiveIndex(index)
    }, isActive && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("div", {
      className: "cb-item-actions",
      role: "toolbar",
      "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)(/* translators: %d: benefit position. */
      (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Benefit %d actions', 'wbd-product-benefits-blocks'), position)
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Button, {
      size: "small",
      icon: "arrow-up-alt2",
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move up', 'wbd-product-benefits-blocks'),
      onClick: () => moveItem(index, -1),
      "aria-disabled": index === 0
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Button, {
      size: "small",
      icon: "arrow-down-alt2",
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move down', 'wbd-product-benefits-blocks'),
      onClick: () => moveItem(index, 1),
      "aria-disabled": index === list.length - 1
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Button, {
      size: "small",
      icon: "admin-page",
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Duplicate', 'wbd-product-benefits-blocks'),
      onClick: () => duplicateItem(index)
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Button, {
      size: "small",
      icon: "trash",
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Remove', 'wbd-product-benefits-blocks'),
      onClick: () => removeItem(index),
      isDestructive: true
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_benefit_icon__WEBPACK_IMPORTED_MODULE_5__["default"], {
      item: item,
      attributes: attributes
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("div", {
      className: "cb-benefit-content"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.RichText, {
      tagName: TitleTag,
      className: 'cb-benefit-title' + (item.url ? ' cb-is-linked' : ''),
      value: item.title || '',
      onChange: value => updateItem(index, {
        title: value
      }),
      placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Benefit title…', 'wbd-product-benefits-blocks'),
      allowedFormats: ['core/bold', 'core/italic'],
      "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)(/* translators: %d: benefit position. */
      (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Benefit %d title', 'wbd-product-benefits-blocks'), position)
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.RichText, {
      tagName: "p",
      className: "cb-benefit-description",
      value: item.description || '',
      onChange: value => updateItem(index, {
        description: value
      }),
      placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Benefit description…', 'wbd-product-benefits-blocks'),
      allowedFormats: ['core/bold', 'core/italic', 'core/link'],
      "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)(/* translators: %d: benefit position. */
      (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Benefit %d description', 'wbd-product-benefits-blocks'), position)
    })));
  })));
}

/***/ },

/***/ "./src/product-benefits/icons.js"
/*!***************************************!*\
  !*** ./src/product-benefits/icons.js ***!
  \***************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DEFAULT_ICON: () => (/* binding */ DEFAULT_ICON),
/* harmony export */   ICONS: () => (/* binding */ ICONS),
/* harmony export */   SvgIcon: () => (/* binding */ SvgIcon)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/**
 * Built-in inline SVG icon set for the Product Benefits block.
 *
 * Every icon is drawn on a 24×24 grid with a 2px round stroke so the set
 * looks consistent at any size and inherits `currentColor`.
 * Shapes are adapted from Feather Icons (MIT License, https://feathericons.com).
 */



/**
 * Icon definitions: key => { label, shapes }.
 * Each shape is [ tagName, attributes ].
 */
const ICONS = {
  truck: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Truck / shipping', 'wbd-product-benefits-blocks'),
    shapes: [['rect', {
      x: '1',
      y: '3',
      width: '15',
      height: '13'
    }], ['polygon', {
      points: '16 8 20 8 23 11 23 16 16 16 16 8'
    }], ['circle', {
      cx: '5.5',
      cy: '18.5',
      r: '2.5'
    }], ['circle', {
      cx: '18.5',
      cy: '18.5',
      r: '2.5'
    }]]
  },
  package: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Package', 'wbd-product-benefits-blocks'),
    shapes: [['path', {
      d: 'M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z'
    }], ['polyline', {
      points: '3.27 6.96 12 12.01 20.73 6.96'
    }], ['line', {
      x1: '12',
      y1: '22.08',
      x2: '12',
      y2: '12'
    }]]
  },
  shield: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Shield', 'wbd-product-benefits-blocks'),
    shapes: [['path', {
      d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'
    }], ['polyline', {
      points: '9 12 11 14 15 10'
    }]]
  },
  lock: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Lock / secure', 'wbd-product-benefits-blocks'),
    shapes: [['rect', {
      x: '3',
      y: '11',
      width: '18',
      height: '11',
      rx: '2',
      ry: '2'
    }], ['path', {
      d: 'M7 11V7a5 5 0 0 1 10 0v4'
    }]]
  },
  refresh: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Return / refresh', 'wbd-product-benefits-blocks'),
    shapes: [['polyline', {
      points: '1 4 1 10 7 10'
    }], ['path', {
      d: 'M3.51 15a9 9 0 1 0 2.13-9.36L1 10'
    }]]
  },
  headset: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Headset / support', 'wbd-product-benefits-blocks'),
    shapes: [['path', {
      d: 'M3 18v-6a9 9 0 0 1 18 0v6'
    }], ['path', {
      d: 'M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z'
    }]]
  },
  'credit-card': {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Credit card', 'wbd-product-benefits-blocks'),
    shapes: [['rect', {
      x: '1',
      y: '4',
      width: '22',
      height: '16',
      rx: '2',
      ry: '2'
    }], ['line', {
      x1: '1',
      y1: '10',
      x2: '23',
      y2: '10'
    }]]
  },
  'check-circle': {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Check circle', 'wbd-product-benefits-blocks'),
    shapes: [['path', {
      d: 'M22 11.08V12a10 10 0 1 1-5.93-9.14'
    }], ['polyline', {
      points: '22 4 12 14.01 9 11.01'
    }]]
  },
  check: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Checkmark', 'wbd-product-benefits-blocks'),
    shapes: [['polyline', {
      points: '20 6 9 17 4 12'
    }]]
  },
  award: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Award / quality', 'wbd-product-benefits-blocks'),
    shapes: [['circle', {
      cx: '12',
      cy: '8',
      r: '7'
    }], ['polyline', {
      points: '8.21 13.89 7 23 12 20 17 23 15.79 13.88'
    }]]
  },
  star: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Star', 'wbd-product-benefits-blocks'),
    shapes: [['polygon', {
      points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'
    }]]
  },
  'thumbs-up': {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Thumbs up', 'wbd-product-benefits-blocks'),
    shapes: [['path', {
      d: 'M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3'
    }]]
  },
  gift: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Gift', 'wbd-product-benefits-blocks'),
    shapes: [['polyline', {
      points: '20 12 20 22 4 22 4 12'
    }], ['rect', {
      x: '2',
      y: '7',
      width: '20',
      height: '5'
    }], ['line', {
      x1: '12',
      y1: '22',
      x2: '12',
      y2: '7'
    }], ['path', {
      d: 'M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z'
    }], ['path', {
      d: 'M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z'
    }]]
  },
  tag: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Price tag', 'wbd-product-benefits-blocks'),
    shapes: [['path', {
      d: 'M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z'
    }], ['line', {
      x1: '7',
      y1: '7',
      x2: '7.01',
      y2: '7'
    }]]
  },
  clock: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Clock', 'wbd-product-benefits-blocks'),
    shapes: [['circle', {
      cx: '12',
      cy: '12',
      r: '10'
    }], ['polyline', {
      points: '12 6 12 12 16 14'
    }]]
  },
  zap: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Lightning / fast', 'wbd-product-benefits-blocks'),
    shapes: [['polygon', {
      points: '13 2 3 14 12 14 11 22 21 10 12 10 13 2'
    }]]
  },
  heart: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Heart', 'wbd-product-benefits-blocks'),
    shapes: [['path', {
      d: 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z'
    }]]
  },
  globe: {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Globe / worldwide', 'wbd-product-benefits-blocks'),
    shapes: [['circle', {
      cx: '12',
      cy: '12',
      r: '10'
    }], ['line', {
      x1: '2',
      y1: '12',
      x2: '22',
      y2: '12'
    }], ['path', {
      d: 'M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'
    }]]
  }
};
const DEFAULT_ICON = 'check-circle';

/**
 * Renders an icon as an inline, decorative SVG element.
 * Output is deterministic so it is safe to use in save().
 *
 * @param {Object} props      Component props.
 * @param {string} props.name Icon key.
 * @return {Element} SVG element.
 */
function SvgIcon({
  name
}) {
  const icon = ICONS[name] || ICONS[DEFAULT_ICON];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round",
    "aria-hidden": "true",
    focusable: "false"
  }, icon.shapes.map(([Tag, attrs], index) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Tag, {
    key: index,
    ...attrs
  })));
}

/***/ },

/***/ "./src/product-benefits/index.js"
/*!***************************************!*\
  !*** ./src/product-benefits/index.js ***!
  \***************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./style.scss */ "./src/product-benefits/style.scss");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./editor.scss */ "./src/product-benefits/editor.scss");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./src/product-benefits/block.json");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./edit */ "./src/product-benefits/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./save */ "./src/product-benefits/save.js");
/**
 * Product Benefits block registration.
 */







const blockIcon = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  width: "24",
  height: "24",
  "aria-hidden": "true",
  focusable: "false"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "1.75",
  strokeLinejoin: "round",
  d: "M12 2.75 19.25 5.5v6c0 4.55-3.06 8.18-7.25 9.75-4.19-1.57-7.25-5.2-7.25-9.75v-6L12 2.75Z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "1.75",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  d: "m8.75 12 2.25 2.25 4.25-4.5"
}));
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  icon: blockIcon,
  edit: _edit__WEBPACK_IMPORTED_MODULE_5__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_6__["default"]
});

/***/ },

/***/ "./src/product-benefits/inspector-controls.js"
/*!****************************************************!*\
  !*** ./src/product-benefits/inspector-controls.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ BenefitsInspectorControls)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./icons */ "./src/product-benefits/icons.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./utils */ "./src/product-benefits/utils.js");
/**
 * Sidebar (Inspector) controls for the Product Benefits block.
 */







const columnOptions = list => list.map(value => ({
  label: String(value),
  value: String(value)
}));
const ALIGN_OPTIONS = [{
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Left', 'wbd-product-benefits-blocks'),
  value: 'left'
}, {
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Center', 'wbd-product-benefits-blocks'),
  value: 'center'
}, {
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Right', 'wbd-product-benefits-blocks'),
  value: 'right'
}];
const WEIGHT_OPTIONS = [{
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Default', 'wbd-product-benefits-blocks'),
  value: ''
}, ..._utils__WEBPACK_IMPORTED_MODULE_6__.FONT_WEIGHTS.map(weight => ({
  label: weight,
  value: weight
}))];
const BORDER_STYLE_LABELS = {
  none: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('None', 'wbd-product-benefits-blocks'),
  solid: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Solid', 'wbd-product-benefits-blocks'),
  dashed: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Dashed', 'wbd-product-benefits-blocks'),
  dotted: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Dotted', 'wbd-product-benefits-blocks'),
  double: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Double', 'wbd-product-benefits-blocks')
};
const stripTags = html => (html || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();

/**
 * Accessible grid of built-in icons.
 *
 * @param {Object}   props          Props.
 * @param {string}   props.value    Selected key.
 * @param {Function} props.onChange Change handler.
 * @param {string}   props.id       Control id.
 * @return {Element} Icon picker.
 */
function IconPicker({
  value,
  onChange,
  id
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.BaseControl, {
    id: id,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon', 'wbd-product-benefits-blocks'),
    __nextHasNoMarginBottom: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: id,
    className: "cb-icon-picker",
    role: "group",
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Choose an icon', 'wbd-product-benefits-blocks')
  }, Object.keys(_icons__WEBPACK_IMPORTED_MODULE_5__.ICONS).map(key => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    key: key,
    className: "cb-icon-picker-button",
    label: _icons__WEBPACK_IMPORTED_MODULE_5__.ICONS[key].label,
    showTooltip: true,
    isPressed: value === key,
    onClick: () => onChange(key)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_icons__WEBPACK_IMPORTED_MODULE_5__.SvgIcon, {
    name: key
  })))));
}

/**
 * Settings panel for one benefit item.
 *
 * @param {Object}   props               Props.
 * @param {Object}   props.item          Benefit item.
 * @param {number}   props.index         Item index.
 * @param {number}   props.total         Number of items.
 * @param {boolean}  props.opened        Whether the panel is open.
 * @param {Function} props.onToggle      Toggle handler.
 * @param {string}   props.layout        Block layout.
 * @param {Function} props.updateItem    Update handler.
 * @param {Function} props.removeItem    Remove handler.
 * @param {Function} props.duplicateItem Duplicate handler.
 * @param {Function} props.moveItem      Move handler.
 * @return {Element} Panel.
 */
function ItemPanel({
  item,
  index,
  total,
  opened,
  onToggle,
  layout,
  updateItem,
  removeItem,
  duplicateItem,
  moveItem
}) {
  const position = index + 1;
  const label = stripTags(item.title) || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('(Untitled)', 'wbd-product-benefits-blocks');
  const update = changes => updateItem(index, changes);
  const isChecklist = 'checklist' === layout;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)(/* translators: 1: benefit position, 2: benefit title. */
    (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('%1$d. %2$s', 'wbd-product-benefits-blocks'), position, label),
    opened: opened,
    onToggle: onToggle,
    className: "cb-item-panel"
  }, isChecklist ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Notice, {
    status: "info",
    isDismissible: false
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('The checklist layout always uses a checkmark icon. Switch layout to use a custom icon or image.', 'wbd-product-benefits-blocks')) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, !item.mediaUrl && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(IconPicker, {
    id: `cb-icon-picker-${item.id || index}`,
    value: item.icon,
    onChange: icon => update({
      icon
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.MediaUploadCheck, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.BaseControl, {
    id: `cb-media-${item.id || index}`,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Custom icon or image', 'wbd-product-benefits-blocks'),
    help: item.mediaUrl ? undefined : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Optional. Replaces the built-in icon. Square SVG or PNG images work best.', 'wbd-product-benefits-blocks'),
    __nextHasNoMarginBottom: true
  }, item.mediaUrl && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    className: "cb-media-preview",
    src: item.mediaUrl,
    alt: "",
    width: "48",
    height: "48"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "cb-button-row"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.MediaUpload, {
    allowedTypes: ['image'],
    value: item.mediaId,
    onSelect: media => update({
      mediaId: media.id || 0,
      mediaUrl: media.url || '',
      mediaAlt: media.alt || ''
    }),
    render: ({
      open
    }) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
      variant: "secondary",
      onClick: open
    }, item.mediaUrl ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Replace image', 'wbd-product-benefits-blocks') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Upload / select image', 'wbd-product-benefits-blocks'))
  }), item.mediaUrl && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "tertiary",
    isDestructive: true,
    onClick: () => update({
      mediaId: 0,
      mediaUrl: '',
      mediaAlt: ''
    })
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Remove image', 'wbd-product-benefits-blocks'))))), item.mediaUrl && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Image alternative text', 'wbd-product-benefits-blocks'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Describe the image if it adds meaning. Leave empty if it is purely decorative.', 'wbd-product-benefits-blocks'),
    value: item.mediaAlt || '',
    onChange: mediaAlt => update({
      mediaAlt
    }),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Link URL (optional)', 'wbd-product-benefits-blocks'),
    type: "url",
    value: item.url || '',
    placeholder: "https://",
    onChange: url => update({
      url
    }),
    onBlur: () => {
      if (item.url && item.url.trim()) {
        update({
          url: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_4__.prependHTTP)(item.url.trim())
        });
      }
    },
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('The benefit title becomes a link.', 'wbd-product-benefits-blocks'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), !!item.url && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Open link in a new tab', 'wbd-product-benefits-blocks'),
    checked: !!item.opensInNewTab,
    onChange: opensInNewTab => update({
      opensInNewTab
    }),
    __nextHasNoMarginBottom: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon alignment', 'wbd-product-benefits-blocks'),
    value: item.iconAlign || '',
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Block default', 'wbd-product-benefits-blocks'),
      value: ''
    }, ...ALIGN_OPTIONS],
    onChange: iconAlign => update({
      iconAlign
    }),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Text alignment', 'wbd-product-benefits-blocks'),
    value: item.textAlign || '',
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Block default', 'wbd-product-benefits-blocks'),
      value: ''
    }, ...ALIGN_OPTIONS],
    onChange: textAlign => update({
      textAlign
    }),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "cb-button-row cb-item-panel-actions",
    role: "group",
    "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)(/* translators: %d: benefit position. */
    (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Benefit %d actions', 'wbd-product-benefits-blocks'), position)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "secondary",
    size: "compact",
    onClick: () => moveItem(index, -1),
    "aria-disabled": index === 0
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Move up', 'wbd-product-benefits-blocks')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "secondary",
    size: "compact",
    onClick: () => moveItem(index, 1),
    "aria-disabled": index === total - 1
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Move down', 'wbd-product-benefits-blocks')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "secondary",
    size: "compact",
    onClick: () => duplicateItem(index)
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Duplicate', 'wbd-product-benefits-blocks')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "secondary",
    size: "compact",
    isDestructive: true,
    onClick: () => removeItem(index)
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Remove', 'wbd-product-benefits-blocks'))));
}
function BenefitsInspectorControls({
  attributes,
  setAttributes,
  activeIndex,
  setActiveIndex,
  updateItem,
  addItem,
  removeItem,
  duplicateItem,
  moveItem
}) {
  const {
    items,
    layout,
    columnsDesktop,
    columnsTablet,
    columnsMobile,
    gap,
    itemPadding,
    contentAlign,
    titleTag,
    itemBackgroundColor,
    itemTextColor,
    iconColor,
    borderColor,
    linkColor,
    titleFontSize,
    descriptionFontSize,
    titleFontWeight,
    descriptionFontWeight,
    titleLineHeight,
    descriptionLineHeight,
    borderWidth,
    borderRadius,
    borderStyle,
    hasShadow,
    shadowIntensity,
    showIcon,
    iconSize,
    hasIconBackground,
    iconBackgroundColor,
    iconBackgroundRadius,
    hideOnDesktop,
    hideOnTablet,
    hideOnMobile
  } = attributes;
  const list = Array.isArray(items) ? items : [];
  const set = key => value => setAttributes({
    [key]: value
  });
  const setNumber = key => value => setAttributes({
    [key]: value === undefined || value === '' ? undefined : Number(value)
  });
  const setOptional = key => value => setAttributes({
    [key]: value ? value : undefined
  });
  const colorSettings = [{
    value: itemBackgroundColor,
    onChange: set('itemBackgroundColor'),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item background', 'wbd-product-benefits-blocks')
  }, {
    value: itemTextColor,
    onChange: set('itemTextColor'),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item text', 'wbd-product-benefits-blocks')
  }, {
    value: iconColor,
    onChange: set('iconColor'),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon', 'wbd-product-benefits-blocks')
  }, {
    value: borderColor,
    onChange: set('borderColor'),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border', 'wbd-product-benefits-blocks')
  }, {
    value: linkColor,
    onChange: set('linkColor'),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Link', 'wbd-product-benefits-blocks')
  }];
  if (hasIconBackground) {
    colorSettings.push({
      value: iconBackgroundColor,
      onChange: set('iconBackgroundColor'),
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon background', 'wbd-product-benefits-blocks')
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Benefits', 'wbd-product-benefits-blocks'),
    initialOpen: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "cb-panel-help"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Edit titles and descriptions directly in the block. Use the panels below for icons, links and order.', 'wbd-product-benefits-blocks')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    variant: "primary",
    icon: "plus-alt2",
    onClick: addItem
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add benefit', 'wbd-product-benefits-blocks'))), list.map((item, index) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ItemPanel, {
    key: item.id || index,
    item: item,
    index: index,
    total: list.length,
    layout: layout,
    opened: index === activeIndex,
    onToggle: () => setActiveIndex(index === activeIndex ? -1 : index),
    updateItem: updateItem,
    removeItem: removeItem,
    duplicateItem: duplicateItem,
    moveItem: moveItem
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Layout', 'wbd-product-benefits-blocks'),
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Layout type', 'wbd-product-benefits-blocks'),
    value: layout,
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Grid', 'wbd-product-benefits-blocks'),
      value: 'grid'
    }, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Horizontal', 'wbd-product-benefits-blocks'),
      value: 'horizontal'
    }, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Checklist', 'wbd-product-benefits-blocks'),
      value: 'checklist'
    }, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Trust badges', 'wbd-product-benefits-blocks'),
      value: 'badges'
    }],
    onChange: set('layout'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Desktop columns', 'wbd-product-benefits-blocks'),
    value: String(columnsDesktop),
    options: columnOptions(_utils__WEBPACK_IMPORTED_MODULE_6__.DESKTOP_COLUMNS),
    onChange: setNumber('columnsDesktop'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Tablet columns', 'wbd-product-benefits-blocks'),
    value: String(columnsTablet),
    options: columnOptions(_utils__WEBPACK_IMPORTED_MODULE_6__.TABLET_COLUMNS),
    onChange: setNumber('columnsTablet'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Mobile columns', 'wbd-product-benefits-blocks'),
    value: String(columnsMobile),
    options: columnOptions(_utils__WEBPACK_IMPORTED_MODULE_6__.MOBILE_COLUMNS),
    onChange: setNumber('columnsMobile'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Gap between items (px)', 'wbd-product-benefits-blocks'),
    value: gap,
    onChange: setNumber('gap'),
    min: 0,
    max: 80,
    allowReset: true,
    initialPosition: 24,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item padding (px)', 'wbd-product-benefits-blocks'),
    value: itemPadding,
    onChange: setNumber('itemPadding'),
    min: 0,
    max: 80,
    allowReset: true,
    initialPosition: 24,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Content alignment', 'wbd-product-benefits-blocks'),
    value: contentAlign,
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Layout default', 'wbd-product-benefits-blocks'),
      value: ''
    }, ...ALIGN_OPTIONS],
    onChange: set('contentAlign'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Title HTML tag', 'wbd-product-benefits-blocks'),
    value: titleTag,
    options: [{
      label: 'H2',
      value: 'h2'
    }, {
      label: 'H3',
      value: 'h3'
    }, {
      label: 'H4',
      value: 'h4'
    }, {
      label: 'H5',
      value: 'h5'
    }, {
      label: 'H6',
      value: 'h6'
    }, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Paragraph (no heading)', 'wbd-product-benefits-blocks'),
      value: 'p'
    }],
    onChange: set('titleTag'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Pick a heading one level below the section heading above this block, or "Paragraph" for short trust badges, to keep the page outline logical.', 'wbd-product-benefits-blocks'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.PanelColorSettings, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item colors', 'wbd-product-benefits-blocks'),
    initialOpen: false,
    colorSettings: colorSettings
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.ContrastChecker, {
    backgroundColor: itemBackgroundColor,
    textColor: itemTextColor
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item typography', 'wbd-product-benefits-blocks'),
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Title font size (px)', 'wbd-product-benefits-blocks'),
    value: titleFontSize,
    onChange: setNumber('titleFontSize'),
    min: 10,
    max: 48,
    allowReset: true,
    initialPosition: 18,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Title font weight', 'wbd-product-benefits-blocks'),
    value: titleFontWeight || '',
    options: WEIGHT_OPTIONS,
    onChange: setOptional('titleFontWeight'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Title line height', 'wbd-product-benefits-blocks'),
    value: titleLineHeight,
    onChange: setNumber('titleLineHeight'),
    min: 0.8,
    max: 3,
    step: 0.1,
    allowReset: true,
    initialPosition: 1.3,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description font size (px)', 'wbd-product-benefits-blocks'),
    value: descriptionFontSize,
    onChange: setNumber('descriptionFontSize'),
    min: 10,
    max: 36,
    allowReset: true,
    initialPosition: 15,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description font weight', 'wbd-product-benefits-blocks'),
    value: descriptionFontWeight || '',
    options: WEIGHT_OPTIONS,
    onChange: setOptional('descriptionFontWeight'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description line height', 'wbd-product-benefits-blocks'),
    value: descriptionLineHeight,
    onChange: setNumber('descriptionLineHeight'),
    min: 0.8,
    max: 3,
    step: 0.1,
    allowReset: true,
    initialPosition: 1.6,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border & shape', 'wbd-product-benefits-blocks'),
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border width (px)', 'wbd-product-benefits-blocks'),
    value: borderWidth,
    onChange: setNumber('borderWidth'),
    min: 0,
    max: 10,
    allowReset: true,
    initialPosition: 1,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border style', 'wbd-product-benefits-blocks'),
    value: borderStyle || '',
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Default', 'wbd-product-benefits-blocks'),
      value: ''
    }, ..._utils__WEBPACK_IMPORTED_MODULE_6__.BORDER_STYLES.map(value => ({
      label: BORDER_STYLE_LABELS[value],
      value
    }))],
    onChange: setOptional('borderStyle'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border radius (px)', 'wbd-product-benefits-blocks'),
    value: borderRadius,
    onChange: setNumber('borderRadius'),
    min: 0,
    max: 60,
    allowReset: true,
    initialPosition: 8,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Box shadow', 'wbd-product-benefits-blocks'),
    checked: !!hasShadow,
    onChange: set('hasShadow'),
    __nextHasNoMarginBottom: true
  }), hasShadow && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Shadow intensity', 'wbd-product-benefits-blocks'),
    value: shadowIntensity,
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Small', 'wbd-product-benefits-blocks'),
      value: 'small'
    }, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Medium', 'wbd-product-benefits-blocks'),
      value: 'medium'
    }, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Large', 'wbd-product-benefits-blocks'),
      value: 'large'
    }],
    onChange: set('shadowIntensity'),
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon', 'wbd-product-benefits-blocks'),
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Show icons', 'wbd-product-benefits-blocks'),
    checked: false !== showIcon,
    onChange: set('showIcon'),
    __nextHasNoMarginBottom: true
  }), false !== showIcon && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon size (px)', 'wbd-product-benefits-blocks'),
    value: iconSize,
    onChange: setNumber('iconSize'),
    min: 12,
    max: 120,
    allowReset: true,
    initialPosition: 'checklist' === layout || 'badges' === layout ? 24 : 40,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon background', 'wbd-product-benefits-blocks'),
    checked: !!hasIconBackground,
    onChange: set('hasIconBackground'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Set its color in "Item colors".', 'wbd-product-benefits-blocks'),
    __nextHasNoMarginBottom: true
  }), hasIconBackground && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RangeControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Icon background radius (%)', 'wbd-product-benefits-blocks'),
    value: iconBackgroundRadius,
    onChange: setNumber('iconBackgroundRadius'),
    min: 0,
    max: 50,
    allowReset: true,
    initialPosition: 50,
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Visibility', 'wbd-product-benefits-blocks'),
    initialOpen: false
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hide on desktop', 'wbd-product-benefits-blocks'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Screens 782px and wider.', 'wbd-product-benefits-blocks'),
    checked: !!hideOnDesktop,
    onChange: set('hideOnDesktop'),
    __nextHasNoMarginBottom: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hide on tablet', 'wbd-product-benefits-blocks'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Screens from 600px to 781px.', 'wbd-product-benefits-blocks'),
    checked: !!hideOnTablet,
    onChange: set('hideOnTablet'),
    __nextHasNoMarginBottom: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hide on mobile', 'wbd-product-benefits-blocks'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Screens narrower than 600px.', 'wbd-product-benefits-blocks'),
    checked: !!hideOnMobile,
    onChange: set('hideOnMobile'),
    __nextHasNoMarginBottom: true
  }), (hideOnDesktop || hideOnTablet || hideOnMobile) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Notice, {
    status: "info",
    isDismissible: false
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hidden blocks stay visible but faded in the editor so you can keep editing them.', 'wbd-product-benefits-blocks'))));
}

/***/ },

/***/ "./src/product-benefits/save.js"
/*!**************************************!*\
  !*** ./src/product-benefits/save.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _benefit_icon__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./benefit-icon */ "./src/product-benefits/benefit-icon.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./utils */ "./src/product-benefits/utils.js");
/**
 * Saved (frontend) markup for the Product Benefits block.
 *
 * Static, semantic and JavaScript-free: a role="list" <ul> of <li> items.
 * Only user-customised values are written as CSS custom properties.
 */




function save({
  attributes
}) {
  const items = Array.isArray(attributes.items) ? attributes.items : [];
  const TitleTag = _utils__WEBPACK_IMPORTED_MODULE_3__.TITLE_TAGS.includes(attributes.titleTag) ? attributes.titleTag : 'h3';
  const blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps.save({
    className: (0,_utils__WEBPACK_IMPORTED_MODULE_3__.getBlockClassName)(attributes),
    style: (0,_utils__WEBPACK_IMPORTED_MODULE_3__.getStyleVars)(attributes)
  });
  return (
    // Explicit role keeps list semantics in Safari/VoiceOver when list-style is none.
    // eslint-disable-next-line jsx-a11y/no-redundant-roles
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
      ...blockProps,
      role: "list"
    }, items.map((item, index) => {
      const url = (0,_utils__WEBPACK_IMPORTED_MODULE_3__.getSafeUrl)(item.url);
      const hasTitle = (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hasText)(item.title);
      const hasDescription = (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hasText)(item.description);
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
        key: item.id || index,
        className: (0,_utils__WEBPACK_IMPORTED_MODULE_3__.getItemClassName)(item)
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_benefit_icon__WEBPACK_IMPORTED_MODULE_2__["default"], {
        item: item,
        attributes: attributes
      }), (hasTitle || hasDescription) && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "cb-benefit-content"
      }, hasTitle && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TitleTag, {
        className: "cb-benefit-title"
      }, url ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
        href: url,
        target: item.opensInNewTab ? '_blank' : undefined,
        rel: item.opensInNewTab ? 'noopener noreferrer' : undefined
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.RichText.Content, {
        value: item.title
      })) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.RichText.Content, {
        value: item.title
      })), hasDescription && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.RichText.Content, {
        tagName: "p",
        className: "cb-benefit-description",
        value: item.description
      })));
    }))
  );
}

/***/ },

/***/ "./src/product-benefits/utils.js"
/*!***************************************!*\
  !*** ./src/product-benefits/utils.js ***!
  \***************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ALIGNMENTS: () => (/* binding */ ALIGNMENTS),
/* harmony export */   BORDER_STYLES: () => (/* binding */ BORDER_STYLES),
/* harmony export */   DEFAULT_ICON_SIZE: () => (/* binding */ DEFAULT_ICON_SIZE),
/* harmony export */   DESKTOP_COLUMNS: () => (/* binding */ DESKTOP_COLUMNS),
/* harmony export */   FONT_WEIGHTS: () => (/* binding */ FONT_WEIGHTS),
/* harmony export */   MOBILE_COLUMNS: () => (/* binding */ MOBILE_COLUMNS),
/* harmony export */   TABLET_COLUMNS: () => (/* binding */ TABLET_COLUMNS),
/* harmony export */   TITLE_TAGS: () => (/* binding */ TITLE_TAGS),
/* harmony export */   createItem: () => (/* binding */ createItem),
/* harmony export */   createItemId: () => (/* binding */ createItemId),
/* harmony export */   getBlockClassName: () => (/* binding */ getBlockClassName),
/* harmony export */   getDefaultItems: () => (/* binding */ getDefaultItems),
/* harmony export */   getIconPixelSize: () => (/* binding */ getIconPixelSize),
/* harmony export */   getItemClassName: () => (/* binding */ getItemClassName),
/* harmony export */   getSafeUrl: () => (/* binding */ getSafeUrl),
/* harmony export */   getStyleVars: () => (/* binding */ getStyleVars),
/* harmony export */   hasText: () => (/* binding */ hasText)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/**
 * Shared helpers used by both edit() and save() so the editor preview and the
 * saved frontend markup always produce identical classes and style variables.
 */

const ALIGNMENTS = ['left', 'center', 'right'];
const TITLE_TAGS = ['h2', 'h3', 'h4', 'h5', 'h6', 'p'];
const FONT_WEIGHTS = ['300', '400', '500', '600', '700', '800'];
const BORDER_STYLES = ['none', 'solid', 'dashed', 'dotted', 'double'];
const DESKTOP_COLUMNS = [1, 2, 3, 4, 6];
const TABLET_COLUMNS = [1, 2, 3, 4];
const MOBILE_COLUMNS = [1, 2];

/**
 * Default rendering values used when an attribute is unset.
 * Must stay in sync with the CSS fallbacks in style.scss.
 */
const DEFAULT_ICON_SIZE = {
  grid: 40,
  horizontal: 40,
  checklist: 24,
  badges: 24
};

/**
 * Creates a reasonably unique item id (editor-only, used as React key).
 *
 * @return {string} Id.
 */
function createItemId() {
  return 'cb-' + Math.random().toString(36).slice(2, 10);
}

/**
 * Returns a fresh benefit item.
 *
 * @param {Object} overrides Field overrides.
 * @return {Object} Item.
 */
function createItem(overrides = {}) {
  return {
    id: createItemId(),
    icon: 'check-circle',
    title: '',
    description: '',
    url: '',
    opensInNewTab: false,
    mediaId: 0,
    mediaUrl: '',
    mediaAlt: '',
    iconAlign: '',
    textAlign: '',
    ...overrides
  };
}

/**
 * Translated default items inserted with a new block.
 *
 * @return {Object[]} Items.
 */
function getDefaultItems() {
  return [createItem({
    icon: 'truck',
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Free Shipping', 'wbd-product-benefits-blocks'),
    description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Fast and reliable delivery on eligible orders.', 'wbd-product-benefits-blocks')
  }), createItem({
    icon: 'lock',
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Secure Payment', 'wbd-product-benefits-blocks'),
    description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Your payment information is protected.', 'wbd-product-benefits-blocks')
  }), createItem({
    icon: 'refresh',
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Easy Returns', 'wbd-product-benefits-blocks'),
    description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Simple and hassle-free return policy.', 'wbd-product-benefits-blocks')
  }), createItem({
    icon: 'headset',
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Expert Support', 'wbd-product-benefits-blocks'),
    description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Get help before and after your purchase.', 'wbd-product-benefits-blocks')
  })];
}
const isNum = value => typeof value === 'number' && Number.isFinite(value) && value >= 0;
const isColor = value => typeof value === 'string' && value.length > 0 && value.length < 64 && /^[#a-zA-Z0-9(),.%\s-]+$/.test(value);
const oneOf = (value, list) => list.includes(value);
const pick = (value, list, fallback) => oneOf(value, list) ? value : fallback;

/**
 * Block wrapper class names.
 *
 * @param {Object} attributes Block attributes.
 * @return {string} Class names.
 */
function getBlockClassName(attributes) {
  const {
    layout,
    columnsDesktop,
    columnsTablet,
    columnsMobile,
    contentAlign,
    hasShadow,
    shadowIntensity,
    showIcon,
    hasIconBackground,
    hideOnDesktop,
    hideOnTablet,
    hideOnMobile
  } = attributes;
  const safeLayout = pick(layout, ['grid', 'horizontal', 'checklist', 'badges'], 'grid');
  const classes = ['cb-product-benefits', `cb-layout-${safeLayout}`, `cb-cols-d-${pick(columnsDesktop, DESKTOP_COLUMNS, 4)}`, `cb-cols-t-${pick(columnsTablet, TABLET_COLUMNS, 2)}`, `cb-cols-m-${pick(columnsMobile, MOBILE_COLUMNS, 1)}`];
  if ('grid' !== safeLayout) {
    classes.push('cb-is-row');
  }
  if (oneOf(contentAlign, ALIGNMENTS)) {
    classes.push(`cb-align-${contentAlign}`);
  }
  if (hasShadow) {
    classes.push(`cb-shadow-${pick(shadowIntensity, ['small', 'medium', 'large'], 'small')}`);
  }
  if (false === showIcon) {
    classes.push('cb-no-icon');
  }
  if (hasIconBackground) {
    classes.push('cb-has-icon-bg');
  }
  if (hideOnDesktop) {
    classes.push('cb-hide-desktop');
  }
  if (hideOnTablet) {
    classes.push('cb-hide-tablet');
  }
  if (hideOnMobile) {
    classes.push('cb-hide-mobile');
  }
  return classes.join(' ');
}

/**
 * CSS custom properties for user-customised values only.
 * Unset values are omitted so the stylesheet defaults apply and no inline
 * style is saved for an unstyled block.
 *
 * @param {Object} attributes Block attributes.
 * @return {Object} Style object.
 */
function getStyleVars(attributes) {
  const style = {};
  const px = value => `${value}px`;
  const numeric = {
    gap: ['--cb-gap', px],
    itemPadding: ['--cb-item-padding', px],
    titleFontSize: ['--cb-title-size', px],
    descriptionFontSize: ['--cb-desc-size', px],
    titleLineHeight: ['--cb-title-lh', String],
    descriptionLineHeight: ['--cb-desc-lh', String],
    borderWidth: ['--cb-border-width', px],
    borderRadius: ['--cb-border-radius', px],
    iconSize: ['--cb-icon-size', px],
    iconBackgroundRadius: ['--cb-icon-radius', v => `${v}%`]
  };
  Object.keys(numeric).forEach(key => {
    if (isNum(attributes[key])) {
      const [prop, format] = numeric[key];
      style[prop] = format(attributes[key]);
    }
  });
  const colors = {
    itemBackgroundColor: '--cb-item-bg',
    itemTextColor: '--cb-item-color',
    iconColor: '--cb-icon-color',
    borderColor: '--cb-border-color',
    linkColor: '--cb-link-color',
    iconBackgroundColor: '--cb-icon-bg'
  };
  Object.keys(colors).forEach(key => {
    if (isColor(attributes[key])) {
      style[colors[key]] = attributes[key];
    }
  });
  if (oneOf(attributes.titleFontWeight, FONT_WEIGHTS)) {
    style['--cb-title-weight'] = attributes.titleFontWeight;
  }
  if (oneOf(attributes.descriptionFontWeight, FONT_WEIGHTS)) {
    style['--cb-desc-weight'] = attributes.descriptionFontWeight;
  }
  if (oneOf(attributes.borderStyle, BORDER_STYLES)) {
    style['--cb-border-style'] = attributes.borderStyle;
  }
  return style;
}

/**
 * Item class names.
 *
 * @param {Object} item Benefit item.
 * @return {string} Class names.
 */
function getItemClassName(item) {
  const classes = ['cb-benefit-item'];
  if (oneOf(item.iconAlign, ALIGNMENTS)) {
    classes.push(`cb-icon-align-${item.iconAlign}`);
  }
  if (oneOf(item.textAlign, ALIGNMENTS)) {
    classes.push(`cb-text-align-${item.textAlign}`);
  }
  return classes.join(' ');
}

/**
 * Pixel size used for the width/height attributes of custom images
 * (prevents layout shift while the image loads).
 *
 * @param {Object} attributes Block attributes.
 * @return {number} Size in px.
 */
function getIconPixelSize(attributes) {
  if (isNum(attributes.iconSize) && attributes.iconSize > 0) {
    return Math.round(attributes.iconSize);
  }
  return DEFAULT_ICON_SIZE[attributes.layout] || 40;
}

/**
 * Returns a safe http(s)/relative URL or an empty string.
 *
 * @param {string} url Raw URL.
 * @return {string} URL.
 */
function getSafeUrl(url) {
  if (typeof url !== 'string') {
    return '';
  }
  const trimmed = url.trim();
  if (!trimmed) {
    return '';
  }
  if (/^(https?:|mailto:|tel:|\/|#|\?)/i.test(trimmed)) {
    return trimmed;
  }
  if (/^[a-z][a-z0-9+.-]*:/i.test(trimmed)) {
    return '';
  }
  return trimmed;
}

/**
 * Whether a RichText value contains content.
 *
 * @param {string} value RichText HTML string.
 * @return {boolean} Has content.
 */
function hasText(value) {
  return typeof value === 'string' && value.trim().length > 0;
}

/***/ },

/***/ "./src/product-benefits/editor.scss"
/*!******************************************!*\
  !*** ./src/product-benefits/editor.scss ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./src/product-benefits/style.scss"
/*!*****************************************!*\
  !*** ./src/product-benefits/style.scss ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "@wordpress/block-editor"
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
(module) {

module.exports = window["wp"]["blockEditor"];

/***/ },

/***/ "@wordpress/blocks"
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
(module) {

module.exports = window["wp"]["blocks"];

/***/ },

/***/ "@wordpress/components"
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["components"];

/***/ },

/***/ "@wordpress/data"
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["data"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "@wordpress/url"
/*!*****************************!*\
  !*** external ["wp","url"] ***!
  \*****************************/
(module) {

module.exports = window["wp"]["url"];

/***/ },

/***/ "./src/product-benefits/block.json"
/*!*****************************************!*\
  !*** ./src/product-benefits/block.json ***!
  \*****************************************/
(module) {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"wbd/product-benefits","version":"1.0.2","title":"Product Benefits","category":"wbd-product-benefits","icon":"awards","description":"Display product benefits, guarantees, shipping information, returns, support and trust badges in a responsive Gutenberg layout.","keywords":["woocommerce","product benefits","trust badges","guarantee","conversion","product page","wbd","wbd product benefits"],"textdomain":"wbd-product-benefits-blocks","attributes":{"items":{"type":"array","items":{"type":"object"}},"layout":{"type":"string","enum":["grid","horizontal","checklist","badges"],"default":"grid"},"columnsDesktop":{"type":"number","default":4},"columnsTablet":{"type":"number","default":2},"columnsMobile":{"type":"number","default":1},"gap":{"type":"number"},"itemPadding":{"type":"number"},"contentAlign":{"type":"string","enum":["","left","center","right"],"default":""},"titleTag":{"type":"string","enum":["h2","h3","h4","h5","h6","p"],"default":"h3"},"itemBackgroundColor":{"type":"string"},"itemTextColor":{"type":"string"},"iconColor":{"type":"string"},"borderColor":{"type":"string"},"linkColor":{"type":"string"},"titleFontSize":{"type":"number"},"descriptionFontSize":{"type":"number"},"titleFontWeight":{"type":"string"},"descriptionFontWeight":{"type":"string"},"titleLineHeight":{"type":"number"},"descriptionLineHeight":{"type":"number"},"borderWidth":{"type":"number"},"borderRadius":{"type":"number"},"borderStyle":{"type":"string"},"hasShadow":{"type":"boolean","default":false},"shadowIntensity":{"type":"string","enum":["small","medium","large"],"default":"small"},"showIcon":{"type":"boolean","default":true},"iconSize":{"type":"number"},"hasIconBackground":{"type":"boolean","default":false},"iconBackgroundColor":{"type":"string"},"iconBackgroundRadius":{"type":"number"},"hideOnDesktop":{"type":"boolean","default":false},"hideOnTablet":{"type":"boolean","default":false},"hideOnMobile":{"type":"boolean","default":false}},"supports":{"html":false,"anchor":true,"align":["wide","full"],"color":{"background":true,"text":true},"spacing":{"margin":true,"padding":true},"typography":{"fontSize":true,"lineHeight":true}},"example":{"viewportWidth":960,"attributes":{"items":[{"id":"example-1","icon":"truck","title":"Free Shipping","description":"Fast and reliable delivery on eligible orders."},{"id":"example-2","icon":"lock","title":"Secure Payment","description":"Your payment information is protected."},{"id":"example-3","icon":"refresh","title":"Easy Returns","description":"Simple and hassle-free return policy."},{"id":"example-4","icon":"headset","title":"Expert Support","description":"Get help before and after your purchase."}]}},"editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

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
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
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
/******/ 		__webpack_require__.O = (result, chunkIds, fn) => {
/******/ 			if(chunkIds) {
/******/ 				deferred.push([chunkIds, fn]);
/******/ 				return;
/******/ 			}
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				let [chunkIds, fn] = deferred[i];
/******/ 				let fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if (__webpack_require__.O.j(chunkIds[j])) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
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
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = (module) => {
/******/ 		const getter = module && module.__esModule ?
/******/ 			() => (module['default']) :
/******/ 			() => (module);
/******/ 		__webpack_require__.d(getter, { a: getter });
/******/ 		return getter;
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	// define getter/value functions for harmony exports
/******/ 	__webpack_require__.d = (exports, definition) => {
/******/ 		for(var key in definition) {
/******/ 			if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 				Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 			}
/******/ 		}
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop));
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		const installedChunks = {
/******/ 			"product-benefits/index": 0,
/******/ 			"product-benefits/style-index": 0
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
/******/ 		const chunkLoadingGlobal = globalThis["webpackChunkwbd_product_benefits_blocks"] ||= [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, ["product-benefits/style-index"], () => (__webpack_require__("./src/product-benefits/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map
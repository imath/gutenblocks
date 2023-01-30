# Changelog

## 1.6.2

+ _Requires WordPress 5.8_
+ _Tested up to WordPress 6.1_

### Bug fixes

- Make sure the language switcher is prepended to the `core/post-content` block.

---

## 1.6.1

+ _Requires WordPress 5.8_
+ _Tested up to WordPress 6.1_

### Bug fixes

- Fix a template mapping issue which appeared in WordPress 6.1 in the Dubber block.

---

## 1.6.0

+ _Requires WordPress 5.8_
+ _Tested up to WordPress 5.8_

### Bug fixes

- Stop using the wp-editor dependency for the i18n block.
- Unregister the i18n block when managing widgets.
- Improve the Gist GitHub block display so that it is more in line with WP Embed Block.

---

## 1.5.1

+ _Requires WordPress 5.0_
+ _Tested up to WordPress 5.6_

### Bug fixes

- Improve the i18n block to ease its usage from the WP Block Editor.

---

## 1.5.0

+ _Requires WordPress 5.0_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Make sure JavaScripts to use the right dependancies for Gutenblocks.
- Stop using Gutenberg specific functions and use the corresponding ones introduced in WordPress 5.0
- Set the plugin CSS dependency to `wp-block-library` handle to make sure it loads.

---

## 1.4.1

+ _Requires Gutenberg 4.5.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Make sure JavaScripts are loaded into the footer.

---

## 1.4.0

+ _Requires Gutenberg 4.4.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

The Photo block has been removed, now it's possible to use the Gutenberg Image block to add an image thanks to its URL.

### Bug fixes

- Dubber block: make sure to remove footprints of the language blocks that are not displayed.

### Features

- Upgrade routine to replace Photo blocks by Image blocks.
- Add a description to the Dubber and GitHub Release blocks.

---

## 1.3.1

+ _Requires Gutenberg 4.1.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Dubber block: hook `the_content` earlier to be able to parse blocks.
- GitHub Release block: make sure no extra br are added during front-end display.

---

## 1.3.0

+ _Requires Gutenberg & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Features

- Convert Gist urls as Gist blocks.
- Improve the Gist loader style.

### Props

@gregoirenoyelle

---

## 1.2.7

+ _Requires Gutenberg 3.5.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Dubber block: make sure to cast as array as deeply as needed the result of the blocks parsing.

---

## 1.2.6

+ _Requires Gutenberg 3.5.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Dubber block: only look for blocks "to translate" on front-end.
- Dubber block: adapt `gutenberg_parse_blocks()` changes introduced in Gutenberg 3.8.

---

## 1.2.5

+ _Requires Gutenberg 3.5.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Adapt to InnerBlocks changes introduced in Gutenberg 3.5.

---

## 1.2.4

+ _Requires Gutenberg 3.0.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Fix an issue involving the SVG icon of the Github Release bloc.

---

## 1.2.3

+ _Requires Gutenberg 3.0.0 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Register all blocks using the `register_block_type()` function.
- Adapt to Gutenberg 3.0.0 and use the Block icon property as a function when generating a custom one is needed.
- Get rid of the WP Embed block, it was fixed in Gutenberg a while ago!

---

## 1.2.2

+ _Requires Gutenberg 2.9.2 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Stop using Gutenberg deprecated functions in blocks.
- Fix the failing Photo block validation.

---

## 1.2.1

+ _Requires Gutenberg 2.8 & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Make sure the Alignment toolbar is added to the Photo block controls.

---

## 1.2.0

+ _Requires Gutenberg & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Features

- Introduce the Dubber block: a container block to organize nested original version & translated ones.

---

## 1.1.2

+ _Requires Gutenberg & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Remove the WordPress Embeds Gutenblock when Gutenberg version is upper than 2.3.0.
- Stop using the Gutenberg Editable component now it has been deprecated.

---

## 1.1.1

+ _Requires Gutenberg & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Bug fixes

- Make sure all blocks are registered before trying to unregister one.
- Untill WordPress/gutenberg#4226 is fixed, make sure the data-secret mechanism is also applied to WordPress embeds in the Gutenberg editor.

### Props

@TweetPressFr

---

## 1.1.0

+ _Requires Gutenberg & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Features

- The GitHub Gist block lets us insert Gists using their URL.
- The WP Embed block replaces the Gutenberg's one so that embedding content from the same site can also be done.
- The GitHub Release block will display a Plugin card on your front-end. A link to the 'WordPress ready' zip package of the plugin release will be included.

---

## 1.0.0

+ _Requires Gutenberg & WordPress 4.9_
+ _Tested up to WordPress 5.0_

### Features

+ The Photo block lets us add image files using their URL instead of uploading files into the Media Library.

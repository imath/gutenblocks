# Changelog

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

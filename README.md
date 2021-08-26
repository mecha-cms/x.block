Block Extension for [Mecha](https://github.com/mecha-cms/mecha)
===============================================================

Release Notes
-------------

### 1.8.0

 - [@mecha-cms/mecha#96](https://github.com/mecha-cms/mecha/issues/96)

### 1.7.3

 - Make block example becomes copy-paste friendly.

### 1.7.2

 - Added ability to define block data as a file stored in `.\lot\block` folder.
 - Removed escape pattern feature. You need to use HTML entities as a replacement for <code>&#x5B;&#x5B;</code> and <code>&#x5D;&#x5D;</code> occurrences. Example: <code>&amp;#x5B;</code> for <code>&#x5B;</code> and <code>&amp;#x5D;</code> for <code>&#x5D;</code>.
 - Rename `Block::replace()` method to `Block::alter()`.
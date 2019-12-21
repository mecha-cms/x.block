Block Extension for Mecha
=========================

Release Notes
-------------

### 1.7.2

 - Added ability to define block data as a file stored in `.\lot\block` folder.
 - Removed escape pattern feature. You need to use HTML entities as a replacement for <code>&#x5B;&#x5B;</code> and <code>&#x5D;&#x5D;</code> occurences. Example: <code>&amp;#x5B;</code> for <code>&#x5B;</code> and <code>&amp;#x5D;</code> for <code>&#x5D;</code>.
 - Rename `Block::replace()` method to `Block::alter()`.

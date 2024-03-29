Block Extension for [Mecha](https://github.com/mecha-cms/mecha)
===============================================================

![Code Size](https://img.shields.io/github/languages/code-size/mecha-cms/x.block?color=%23444&style=for-the-badge)

Block is a placement system that can be implemented in places where it is not possible to safely parse server-side
language syntax directly, such as within the page content. It can usually be written just like normal HTML elements, but
with custom enclosing characters. Each block can then be converted into something else via server-side language.

We no longer recommend the use of the block feature. There are many other alternatives that can provide
backward-compatible solutions, such as using HTML comments or [Emoji](https://en.wikipedia.org/wiki/Emoji) characters,
in the hope that if you decide to remove certain block features in the future, your content data will still be
convenient to consume since raw block elements do not appear in the page content. This can minimize the need to maintain
pages, especially the old ones that you no longer want to touch.

The purpose of maintaining this extension is to demonstrate that Mecha has the capability to provide this kind of
feature. This does not mean that you should use it.
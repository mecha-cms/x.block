Block Extension for Mecha
=========================

By default, a block syntax can be defined as follow. It uses `‌[‌[‌` and `‌]‌]‌` as the enclosing characters:

 - Void blocks → `‌[‌[block]‌]‌` or `‌[‌[‌block‌/‌]‌]‌`
 - Container blocks → `‌[‌[‌block‌]‌]content[‌[‌/‌block‌]‌]`

Block attributes syntax is exactly the same as HTML attributes syntax.

Blocks are configurable. Even, you can create your own block markup by editing the configuration file. Go to `lot\extend\block\lot\state\config.php` then search for the `union` part that looks like this:

~~~ .php
1 => [
    0 => ['[‌[‌', '‌]‌]', '‌/‌'],
    1 => ['=', '"', '"', ' '],
    2 => ['[‌[‌!', '!‌]‌]'],
    3 => ['`', '`']
]
~~~

 - The `0` parts are configuration for the block tags.
 - The `1` parts are configuration for the block tags’ attributes.
 - The `2` parts are for the block comments markup.
 - The `3` parts are for the escape marker.

Following is an example of configuring block patterns as regular HTML (custom HTML tags):

~~~ .xmp.no-highlight
<‌block‌>content<‌/‌block‌>
~~~

~~~ .php
1 => [
    0 => ['<‌', '‌>', '‌/‌'],
    1 => ['=', '"', '"', ' '],
    2 => ['<‌!--', '--‌>'],
    3 => ['`', '`']
]
~~~
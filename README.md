# Parsedown Extra Plugin for Bludit

This plugin enable an extra parse that adds support for [Markdown Extra](https://michelf.ca/projects/php-markdown/extra/).

Installs `$GLOBALS['PARSEDOWN_TOC']` in `beforeSiteLoad` so you can request the parsed Table of Contents in sidebars etc. via:

    $GLOBALS['PARSEDOWN_TOC']->contentsList();

Using:

- [Parsedown Extra](https://github.com/erusev/parsedown-extra)
- [Parsedown Extended](https://github.com/BenjaminHoegh/ParsedownExtended)

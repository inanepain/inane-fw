# skeleton/inane-fw
# version: $Id$
# date: $Date$

set shell := ["zsh", "-cu"]
set positional-arguments

project := "skeleton\\inane-fw"

# list recipes
_default:
    @echo "{{project}}:"
    @just --list --list-heading ''

# Style Sheets
css:
	#!/usr/bin/env zsh
	echo "{{project}}: Building style sheets..."
	sass --no-source-map -s compressed ./source/style/styles.scss ./public/css/styles.css
	echo "{{project}}: Style sheets done."

# compile asciidoc files
build:
	#!/usr/bin/env zsh
	echo "{{project}}: Building documentation..."
	just build-readme
	just build-changelog
	echo "{{project}}: documentation done."

# compile README asciidoc file
build-readme:
	#!/usr/bin/env zsh
	echo "{{project}}: Building README.adoc..."
	rm -f README.adoc
	asciidoctor-reducer -o README.adoc source/part/readme/index.adoc
	asciidoctor -b docbook README.adoc
	rm -f README.xml
	echo "{{project}}: README.adoc done."

# compile CHANGELOG asciidoc file
build-changelog:
	#!/usr/bin/env zsh
	echo "{{project}}: Building CHANGELOG.adoc..."
	rm -f CHANGELOG.adoc
	asciidoctor-reducer -o CHANGELOG.adoc source/part/changelog/index.adoc
	asciidoctor -b docbook CHANGELOG.adoc
	rm -f CHANGELOG.xml
	echo "{{project}}: CHANGELOG.adoc done."

#!/usr/bin/env just --justfile

# skeleton/inane-fw
# version: $Id$
# date: $Date$

set shell := ["zsh", "-cu"]
set positional-arguments
set dotenv-load

# list recipes
[default]
_default:
    @echo "$PROJECT:"
    @just --list --list-heading ''

# start
[group: 'helpers']
[arg('task', help="New task being run.")]
_start task='':
    @echo "{{GREEN}}start{{NORMAL}}: {{task}}"

# done
[group: 'helpers']
[arg('task', help="Completed task.")]
_done task='':
    @echo "{{GREEN}}done{{NORMAL}} {{task}}"

#region git
# git push submodules
[group: 'git']
git-push-sm: (_start "Push Submodules") && (_done "Push Submodules")
    #!/usr/bin/env zsh
    git submodule foreach --recursive 'git push'
    msg "${Blue}$(pwd)"
    git push

# git push all
[group: 'git']
git-push-all: (_start "Push All") && (_done "Push All")
    #!/usr/bin/env zsh
    source ~/bin/functions/colours
    for d in lib/inanepain/*; do msg "${Blue}$d"; cd $d; git pushall; cd -; done
    msg "${Blue}$(pwd)"
    git pushall

# git pull submodules
[group: 'git']
git-pull-sm:
    #!/usr/bin/env zsh
    source ~/bin/functions/colours
    git submodule foreach --recursive 'git pull'
    msg "${Blue}$(pwd)"
    git pull

# git pull submodules github.com develop
[group: 'git']
git-pull-sm-github-develop:
    #!/usr/bin/env zsh
    source ~/bin/functions/colours
    git submodule foreach --recursive 'git pull github.com develop'
    msg "${Blue}$(pwd)"
    git pull github.com develop

# git pull submodules all
[group: 'git']
git-pull-sm-all: (git-pull-sm) && (git-pull-sm-github-develop)

#endregion git

# Style Sheets
[group: 'compile']
css: (_start "Stylesheet") && (_done "Stylesheet")
	#!/usr/bin/env zsh
	echo "{{MAGENTA}}$PROJECT{{NORMAL}}: Building stylesheets..."
	sass --no-source-map -s compressed ./source/style/styles.scss ./public/css/styles.css
	echo "{{MAGENTA}}$PROJECT{{NORMAL}}: Stylesheets {{BOLD + RED + UNDERLINE}}built{{NORMAL}}"

# compile asciidoc files
[group: 'doc']
build:
	#!/usr/bin/env zsh
	echo "project: {{MAGENTA}}$PROJECT{{NORMAL}} => Building documentation..."
	just build-changelog
	just build-readme
	echo "{{MAGENTA}}$PROJECT{{NORMAL}}: documentation {{BOLD + RED + UNDERLINE}}built{{NORMAL}}"

# Build changelog
[group: 'doc']
build-changelog: && (compile "changelog")

# Build readme
[group: 'doc']
build-readme: && (compile "readme")

# compile final asciidoc file: changelog, readme
[group: 'doc']
[arg('target', pattern='changelog|readme', help="Build final document from source docs.")]
compile target="changelog": (_start target) && (_done target)
	#!/usr/bin/env zsh
	echo "\tBuilding {{CYAN}}{{uppercase(target)}}{{NORMAL}}.adoc..."
	rm -f {{uppercase(target)}}.adoc
	asciidoctor-reducer -o {{uppercase(target)}}.adoc source/doc/{{target}}/index.adoc
	asciidoctor -b docbook {{uppercase(target)}}.adoc
	rm -f {{uppercase(target)}}.xml
	echo "\t{{uppercase(target)}}.adoc {{RED}}done.{{NORMAL}}"

#*********************************************
#### MAINTENANCE
##############################################
# Remove .DS_Store files for directory tree
[group: 'utility']
rmdsstore:
    @echo "Removing: .DS_Store files..."
    @find "${@:-.}" -type f -name .DS_Store -delete

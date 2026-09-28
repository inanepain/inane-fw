#!/usr/bin/env zsh
# VERSION: 2
autoload ask

# configure tagger smudge & clean actions
git config filter.tagger.smudge git_attribute_tagger_smudge
# git config filter.tagger.smudge C:\\Users\\Philip\\bin\\git_attribute_tagger_smudge.bat

git config filter.tagger.clean git_attribute_tagger_clean
# git config filter.tagger.clean C:\\Users\\Philip\\bin\\git_attribute_tagger_clean.bat

ask "Reset file attributes?" Y && git_attribute_reset.sh

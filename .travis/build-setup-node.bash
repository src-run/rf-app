#!/bin/bash

#
# This file is part of the `robfrawley/web-app` project.
#
# (c) Rob Frawley 2nd <rmf@src.run>
#
# For the full copyright and license information, view the LICENSE.md
# file distributed with this vinylSourceStream code.
#

# load nvm
. $HOME/.nvm/nvm.sh

# install node version 6
nvm install 6

# use node version 6
nvm use 6

# display the active version of node and npm
nvm --version
node --version

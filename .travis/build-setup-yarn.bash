#!/bin/bash

#
# This file is part of the `robfrawley/web-app` project.
#
# (c) Rob Frawley 2nd <rmf@src.run>
#
# For the full copyright and license information, view the LICENSE.md
# file distributed with this vinylSourceStream code.
#

# add apt key for yarn pkg
sudo apt-key adv --keyserver pgp.mit.edu --recv D101F7899D41F3C3

# add apt source for yarn pkg
echo "deb http://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list

# update apt source
sudo apt-get update -qq

# install yarn
sudo apt-get install -y -qq yarn

# display the installer version of yarn
yarn --version

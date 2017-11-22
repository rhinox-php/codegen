#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
pushd $DIR/..
rm -rf docs/output
mkdir -p docs/output
node bin/generate-docs.js
./node_modules/.bin/bootprint openapi docs/output/api.yml docs/output
popd

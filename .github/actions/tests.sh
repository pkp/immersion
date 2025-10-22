#!/bin/bash

set -e

echo "Run cypress tests"
npx cypress run --config '{"specPattern":["plugins/themes/immersion/cypress/tests/functional/*.cy.js"]}'

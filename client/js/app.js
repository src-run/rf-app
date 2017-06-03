
require('../scss/app.scss');

import Greeter from './greeter.js';

let helloWorld = new Greeter();
helloWorld.greet();

let moinUniverse = new Greeter('Moin');
moinUniverse.greet('Universe');

console.log($('html'));

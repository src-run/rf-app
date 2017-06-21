
/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

require('../scss/app.scss');

import Greeter from './greeter';
import Case from './case';

let helloWorld = new Greeter();
helloWorld.greet();

let moinUniverse = new Greeter('Moin');
moinUniverse.greet('Universe');

let h1Case = new Case('h1');
h1Case.randomize();

let h2Case = new Case('h2');
h2Case.lower();

let h3Case = new Case('h3');
h3Case.upper();

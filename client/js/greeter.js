
/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

class Greeter {
    constructor(salutation = 'Hello') {
        this.salutation = salutation;
    }

    greet(name = 'World') {
        const greeting = `${this.salutation}, ${name}!`;
        console.log(greeting);
    }
}

export default Greeter;

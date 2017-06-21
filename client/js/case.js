
/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

class Case {
    constructor(selector) {
        this.elements = $(selector);
    }

    upper() {
        this.elements.each(function () {
            Case._upperCaseElementText(this);
        })
    }

    lower() {
        this.elements.each(function () {
            Case._lowerCaseElementText(this);
        })
    }

    randomize() {
        this.elements.each(function () {
            Case._randomizeCaseElementText(this);
        })
    }

    static _upperCaseElementText(el) {
        let $el = $(el);
        $el.text($el.text().toUpperCase());
    }

    static _lowerCaseElementText(el) {
        let $el = $(el);
        $el.text($el.text().toLowerCase());
    }

    static _randomizeCaseElementText(el) {
        let $el = $(el);
        let origTxt = $el.text().split('');
        let randTxt = [];

        for (let i = 0, len = origTxt.length; i < len; i++) {
            randTxt.push(Math.random() >= 0.5 ? origTxt[i].toLowerCase() : origTxt[i].toUpperCase());
        }

        $el.text(randTxt.join(''));
    }
}

export default Case;

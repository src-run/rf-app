
/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

class NavState {
    constructor(selector) {
        this.nav = $(selector);
        this.window = $(window);
        this.y = 80;
        this.classTransparent = 'nav-state-transparent';
        this.classOpaque = 'nav-state-opaque';
    }

    watch() {
        $(window).scroll(function () {
            if (this.window.scrollTop() > this.y) {
                this.setNavBarOpaque();
            } else {
                this.setNavBarTransparent();
            }
        }.bind(this));
    }

    setNavBarOpaque() {
        console.log('solid');
        this.nav
            .addClass(this.classOpaque)
            .removeClass(this.classTransparent);
    }

    setNavBarTransparent() {
        console.log('transparent');
        this.nav
            .addClass(this.classTransparent)
            .removeClass(this.classOpaque);
    }
}

export default NavState;

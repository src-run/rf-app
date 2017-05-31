import moment from 'moment';
import jQuery from 'jquery';

class ArticlePage {
    constructor(jQuery) {
        this.jQuery = jQuery;
    }

    run() {
        this.jQuery('li').css({
            'color': 'red'
        });
    }
}

class IndexPage {
    constructor(jQuery) {
        this.jQuery = jQuery;
    }

    run() {
        this.jQuery('h1').text('Current Time and Date');
        this.jQuery('time').text(moment().format('MMMM Do YYYY, h:mm:ss a'));
        this.jQuery('p').text('The web page content has been set using jQuery compiled using WebPack!');
    }
}

class PageRunner {
    static registerOnReady(page) {
        jQuery(document).ready(function() {
            page.run();
        });
    }
}

PageRunner.registerOnReady(jQuery('#index').length > 0 ? new IndexPage(jQuery) : new ArticlePage(jQuery));

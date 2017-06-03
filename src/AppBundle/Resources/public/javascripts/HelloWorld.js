
import moment from 'moment';
import jQuery from 'jquery';

class HelloWorld {
    static run() {
        jQuery('p').text('Hello world @ '+moment().format('MMMM Do YYYY, h:mm:ss a'));
    }
}

module.export = HelloWorld;

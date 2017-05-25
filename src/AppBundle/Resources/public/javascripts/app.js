import moment from 'moment';
import jQuery from 'jquery';


jQuery(document).ready(function() {
    let header      = 'Current Time and Date';
    let dateTime    = moment().format('MMMM Do YYYY, h:mm:ss a');
    let description = 'The web page content has been set using jQuery compiled using WebPack!';

    jQuery('h1').text(header);
    jQuery('time').text(dateTime);
    jQuery('p').text(description);
});

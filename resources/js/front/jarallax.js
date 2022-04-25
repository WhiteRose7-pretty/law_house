import {
    jarallax
} from 'jarallax';

import objectFitImages from 'object-fit-images';

$(document).ready(function() {
    objectFitImages();

    jarallax(document.querySelectorAll('.jarallax'), {
        speed: 0.2
    });
});

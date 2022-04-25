var navbarAnimate_lastScrollTop = null;

function navbarAnimate() {
    if ($('.navbar-iusvitae-animate').length == 0) {
        return;
    }
    var firstRun = (navbarAnimate_lastScrollTop === null);
    var scrollTop = $(window).scrollTop();
    if ((scrollTop) > 25) {
        if ($('.navbar-iusvitae-animate').hasClass('navbar-dark')) {
            $('.navbar-iusvitae-animate').addClass('bg-white');
            $('.navbar-iusvitae-animate').addClass('navbar-light');
            $('.navbar-iusvitae-animate').removeClass('navbar-dark');
        }
    } else {
        if ($('.navbar-iusvitae-animate').hasClass('navbar-light')) {
            $('.navbar-iusvitae-animate').addClass('navbar-dark');
            $('.navbar-iusvitae-animate').removeClass('bg-white');
            $('.navbar-iusvitae-animate').removeClass('navbar-light');
        }
    }
    if (firstRun) {
        if (scrollTop > 100) {
            console.log('wtf');
            $('.navbar-iusvitae-animate').hide();
        }
        navbarAnimate_lastScrollTop = scrollTop;
        return;
    }
    if (scrollTop < navbarAnimate_lastScrollTop) {
        $('.navbar-iusvitae-animate').slideDown(150);
        navbarAnimate_lastScrollTop = scrollTop;
        return;
    }
    if (scrollTop > 100) {
        $('.navbar-iusvitae-animate').slideUp(150);
    }
    navbarAnimate_lastScrollTop = scrollTop;
}

$(document).ready(function() {

    navbarAnimate();
    $(window).scroll(function() {
        navbarAnimate();
    });
});

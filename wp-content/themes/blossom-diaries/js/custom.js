jQuery(document).ready(function($){    
    
    var rtl, mrtl, slider_auto;
    
    if( blossom_diaries_data.rtl == '1' ){
        rtl = true;
        mrtl = false;
    }else{
        rtl = false;
        mrtl = true;
    }

    if( blossom_diaries_data.auto == '1' ){
        slider_auto = true;
    }else{
        slider_auto = false;
    }
    
    //banner layout three
    $('.slider-layout-three').owlCarousel({
        loop: true,
        nav: true,
        items: 1,
        dots: false,
        autoplay: slider_auto,
        autoplaySpeed: 800,
        autoplayTimeout: 3000,
        rtl: rtl,
        animateOut: blossom_diaries_data.animation,
        responsive: {
            1200: {
                margin: 130,
                stagePadding: 215
            },
            1025: {
                margin: 50,
                stagePadding: 85
            },
            768: {
                margin: 5,
                stagePadding: 85
            },
            0: {
                margin: 10,
                stagePadding: 30
            }
        }
    }); 

    document.body.addEventListener('keydown', function (e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-nav-on');
        }
    });
    document.body.addEventListener('mousemove', function (e) {
        if (document.body.classList.contains('keyboard-nav-on')) {
            document.body.classList.remove('keyboard-nav-on');
        }
    });
    // mobile navigation modern

    const adminbarHeight = document.querySelector('#wpadminbar');
    if (adminbarHeight) {
        document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner').style.top = adminbarHeight.offsetHeight + "px";
    } else {
        document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner').style.top = "0";
    }

    const mobButtons = document.querySelectorAll('.sticky-header .toggle-btn,.site-header .mobile-header .toggle-btn-wrap .toggle-btn');
    if (null !== mobButtons) {
        mobButtons.forEach(function (mobButton) {
            mobButton.addEventListener('click', () => {
                document.body.classList.add('mobile-menu-active');
                document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner').style.transform = "translate(0,0)";
            });
        })
    }

    const mobCloseButton = document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner .container .mobile-header-wrap  > .close');
    if (null !== mobCloseButton) {
        mobCloseButton.addEventListener('click', function () {
            document.body.classList.remove('mobile-menu-active');
            document.querySelector('.site-header .mobile-header .header-bottom-slide .header-bottom-slide-inner').style.transform = "translate(-100%,0)";
        })
    }

    // toggle search
    $(".btn-form-close").on( 'click', function() {
        $(".site-header .form-holder").slideUp();
    });
});

// focus trap 
const focusTrap = () => {
    const trapSearch = document.querySelectorAll('.form-holder');

    if(null !== trapSearch){
        trapSearch.forEach((trapSearch) => {
            trapSearch.addEventListener('keydown', (event) => {
                if (event.key === 'Tab') {
                    const focusableElements = trapSearch.querySelectorAll('button, input');
                    const firstFocusable = focusableElements[0];
                    const lastFocusable = focusableElements[focusableElements.length - 1];
        
                    if (!event.shiftKey && document.activeElement === lastFocusable) {
                        event.preventDefault();
                        firstFocusable.focus();
                    } else if (event.shiftKey && document.activeElement === firstFocusable) {
                        event.preventDefault();
                        lastFocusable.focus();
                    }
                }
            });
        })
        
    }
}

focusTrap()
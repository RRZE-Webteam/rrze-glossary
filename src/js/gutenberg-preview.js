// alert('gutenberg-preview.js');

let blockLoaded = false;
let blockLoadedInterval = setInterval(function() {
    // jQuery.holdReady( true );
    if (document.getElementById('myBlock')) {
        blockLoaded = true;
        // alert('id found');
    }
    if ( blockLoaded ) {
        clearInterval( blockLoadedInterval );
        jQuery.holdReady( false );
    }
}, 500);



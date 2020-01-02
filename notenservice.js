function notenservice_init() {
    // This function adds more fancy styling to <select> elements if the browser is alleged to support it.  We might
    // decide to do other things here in the future as well but for now that's it.  The problem with "the common hack"
    // for styling <select> elements is that if the browser does not honor the CSS `appearance: none` then we'll end up
    // with /two/ arrows and the <select> will look even uglier.  There is no need to avoid modern JavaScript features
    // in this code because it only has merit in modern browsers anyway.  Likewise, if a user decides to disable
    // JavaScript, then we'll give them less fancy <select> elements and have them live with it.
    if (!CSS.supports(`(appearance: none) or (-moz-appearance: none) or (-webkit-appearance: none)`)) {
        return;
    }
    for (var select of document.getElementsByTagName('select')) {
        select.classList.add('fancy');
        var dummy = document.createElement('span');
        dummy.appendChild(document.createTextNode('\u25BC'));
        dummy.classList.add('fancy-select-arrow');
        select.parentNode.insertBefore(dummy, select.nextSibling);
    }
}

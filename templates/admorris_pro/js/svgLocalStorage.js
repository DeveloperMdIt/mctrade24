// https://osvaldas.info/caching-svg-sprite-in-localstorage
(function (window, document) {
    'use strict';

    let config = window.svgLocalStorageConfig || [];

    config.forEach((file) => {
        file.path = file.path + '?v=' + file.revision;

        if (!document.createElementNS || !document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect)
            return true;

        var isLocalStorage = 'localStorage' in window && window['localStorage'] !== null,
            request,
            data,
            insertIT = function () {
                var wrapper = document.createElement('div');
                wrapper.insertAdjacentHTML('afterbegin', data);
                wrapper.setAttribute('style', 'visibility: hidden; height: 0; position: absolute;');
                document.body.insertAdjacentElement('afterbegin', wrapper);
            },
            insert = function () {
                if (document.body) insertIT();
                else document.addEventListener('DOMContentLoaded', insertIT);
            };
        const SVGrev = 'inlineSVGrev_' + file.name;
        const SVGdata = 'inlineSVGdata_' + file.name;

        if (isLocalStorage && localStorage.getItem(SVGrev) == file.revision) {
            data = localStorage.getItem(SVGdata);
            if (data) {
                insert();
                return true;
            }
        }

        try {
            request = new XMLHttpRequest();
            request.open('GET', file.path, true);
            request.onload = function () {
                if (request.status >= 200 && request.status < 400) {
                    data = request.responseText;
                    insert();
                    if (isLocalStorage) {
                        localStorage.setItem(SVGdata, data);
                        localStorage.setItem(SVGrev, file.revision);
                    }
                }
            };
            request.send();
        } catch (e) {
            console.log('error with sprite injections: ', e);
        }
    });
})(window, document);

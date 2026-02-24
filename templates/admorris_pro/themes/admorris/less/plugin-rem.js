'use strict';

var baseFontSize;

module.exports = {
    install: function (less, pluginManager, functions) {
        functions.add('setBaseFontSize', function (val) {
            var num = val;
            if (typeof val === 'string') {
                num = parseFloat(val.replace('px', ''), 10);
            }
            baseFontSize = num;

            return false;
        });
        functions.add('rem', function (val) {
            var num = val;
            if (typeof val === 'string') {
                num = parseFloat(val.replace('px', ''), 10);
            }
            // var result = (num.value / baseFontSize.value).toFixed(3);
            /* hardcoded baseFontSize 13 because of an error where it was undefined */
            var result = (num.value / 13).toFixed(3);

            return new tree.Dimension(result, 'rem');
        });
    },
};

function setzeBrutto(elem, targetElemID, fSteuersatz) {
    document.getElementById(targetElemID).value =
        Math.round(Number(elem.value) * ((100 + Number(fSteuersatz)) / 100) * 100) / 100;
}

function setzeNetto(elem, targetElemID, fSteuersatz) {
    document.getElementById(targetElemID).value =
        Math.round(Number(elem.value) * (100 / (100 + Number(fSteuersatz))) * 100) / 100;
}

function setzePreisAjax(bNetto, cTargetID, elem) {
    let args;
    if (bNetto) {
        args = [elem.value, 0, cTargetID];
    } else {
        args = [0, elem.value, cTargetID];
    }

    ioCall(
        'getCurrencyConversion',
        args,
        undefined,
        undefined,
        undefined,
        true
    );
}

function setzePreisTooltipAjax(bNetto, cTooltipID, sourceElem) {
    let args;
    if (bNetto) {
        args = [parseFloat($(sourceElem).val().replace(',', '.')), 0, cTooltipID];
    } else {
        args = [0, parseFloat($(sourceElem).val().replace(',', '.')), cTooltipID]
    }

    ioCall(
        'setCurrencyConversionTooltip',
        args,
        function (data, context) {
            document.getElementById(cTooltipID).setAttribute('data-original-title', context.originalTitle );
        },
        undefined,
        undefined,
        true
    );
}

function setzeAufpreisTyp(elem, bruttoElemID, nettoElemID) {
    if (elem.value === "festpreis") {
        document.getElementById(bruttoElemID).style.visibility = 'visible';
        setzeBrutto(document.getElementById(nettoElemID), bruttoElemID);
    } else {
        document.getElementById(bruttoElemID).style.visibility = 'hidden';
    }
}

function makeCurrencyTooltip(sourceId) {
    changeCurrencyTooltipText(sourceId);
    $('#' + sourceId).keyup(function (e) {
        changeCurrencyTooltipText(sourceId);
    });
}

function changeCurrencyTooltipText(sourceId) {
    setzePreisTooltipAjax(false, sourceId + 'Tooltip', $('#' + sourceId)[0]);
}

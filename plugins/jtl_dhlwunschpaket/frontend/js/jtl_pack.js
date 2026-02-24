var street_label,
    street_value,
    streetnumber_label,
    additional_label,
    additional_required,
    street_readonly,
    zip_readonly,
    location_readonly,
    zip_value,
    last_pack_id = 0,
    doMofidy     = false,
    location_value;

$(document).ready(function () {
    var jtlPack  = $('#jtlPack').val();
    last_pack_id = parseInt(jtlPack);
    if (parseInt(jtlPack) === -2 || parseInt(jtlPack) === -3) {
        $('button.storelocator').removeAttr('disabled').fadeIn(500);
    } else {
        $('button.storelocator').attr('disabled', true).fadeOut(500);
    }
    setFormValues(jtlPack);
    changeForm(true);
    $('.custom-select[name="kLieferadresse"]').change();
});

$('.custom-select[name="kLieferadresse"]').on('change', function () {
    var jtlPack = $(this).children("option:selected").data('jtlpack');
    $('#jtlPack').val(jtlPack);
    if (jtlPack < 0) {
        jtlPack = parseInt(jtlPack);
        $('#register_shipping_address').collapse('show');
        if (jtlPack === -2 || jtlPack === -3) {
            $('button.storelocator').removeAttr('disabled').fadeIn(500);
        } else {
            $('button.storelocator').attr('disabled', true).fadeOut(500);
        }

        setFormValues(jtlPack);
        changeForm(last_pack_id === jtlPack);
        last_pack_id = jtlPack;
    } else {
        $('#register_shipping_address').collapse('hide');
    }
});

$('input[name="kLieferadresse"]').on('click', function () {
    var _this   = $(this),
        jtlPack = _this.data('jtlpack');

    $('#jtlPack').val(jtlPack);

    if (jtlPack < 0 || jtlPack === undefined) {
        $('#register_shipping_address').collapse('show');
        if (jtlPack === -2 || jtlPack === -3) {
            $('button.storelocator').removeAttr('disabled').fadeIn(500);
        } else {
            $('button.storelocator').attr('disabled', true).fadeOut(500);
        }

        setFormValues(jtlPack);
        changeForm();
    }
});

$('#storelocator').on('click', function (e) {
    e.preventDefault();
    $('#locationList').html('');

    if ($('#checkout_register_shipping_address').prop('checked') === true ||
        ($('#kLieferadresse option:selected').data('jtlpack') === undefined
            && $('input[name="kLieferadresse"]:checked').data('jtlpack') === undefined)
    ) {
        return false;
    }

    var street_id       = $('input[name="strasse"]').attr('id'),
        streetnumber_id = $('input[name="hausnummer"]').attr('id'),
        zip_id          = $('input[name="plz"]').attr('id'),
        location_id     = $('input[name="ort"]').attr('id'),
        country_id      = $('select[name="land"]').attr('id'),
        addresstype,
        jtlPack;

    if ($('select[name="kLieferadresse"]').val() !== undefined) {
        jtlPack = $('select[name="kLieferadresse"]').children("option:selected").data('jtlpack');
    } else {
        jtlPack = $('input[name="kLieferadresse"]:checked').data('jtlpack');
    }

    switch (jtlPack) {
        case -2:
            addresstype = 'delivery_pack';
            break;

        case -3:
            addresstype = 'delivery_fili';
            break;
    }

    $('.dhl_loader').show();
    var address = $('#' + zip_id).val() + '|' + $('#' + location_id).val() + '|';
    address    += $('#' + street_id).val() + '|' + $('#' + streetnumber_id).val() + '|';
    address    += $('#' + country_id).val() + '|' + addresstype;

    $.evo.io().call('getAvailableDeliverySpots', [address], {}, function (error, data) {
        $('.dhl_loader').hide();
        $('#locationList').html(data.content);
    });
    $('#deliverySpots').modal();
});

$('select[name="land"]').on('change', function () {
    if ($(this).val() !== 'DE') {
        if ($('.custom-select[name="kLieferadresse"]').val() !== undefined) {
            $('.custom-select[name="kLieferadresse"]').val('-1');
            $('.custom-select[name="kLieferadresse"] option[data-type="jtlpack"]').attr('disabled', true);
        } else {
            $('#delivery_new').attr('checked', true);
            $('input[name="kLieferadresse"] option[data-type="jtlpack"]').attr('readonly', true);
        }

        $('button.storelocator').hide();
        street_label        = jtlPackFormTranslations.default.street;
        street_value        = '';
        streetnumber_label  = jtlPackFormTranslations.default.streetnumber;
        additional_label    = jtlPackFormTranslations.default.additional;
        additional_required = false;
        street_readonly     = false;
        changeForm();
    } else {
        $('.custom-select[name="kLieferadresse"] option').removeAttr('disabled');
    }
});

$(document).on('click', '.dhlData', function (e) {
    e.preventDefault();
    var street_id       = $('input[name="register[shipping_address][strasse]"]').attr('id'),
        streetnumber_id = $('input[name="register[shipping_address][hausnummer]"]').attr('id'),
        zip_id          = $('input[name="register[shipping_address][plz]"]').attr('id'),
        location_id     = $('input[name="register[shipping_address][ort]"]').attr('id'),
        that            = $(this);
    $('#' + street_id).val(that.data('street'));
    $('#' + streetnumber_id).val(that.data('number'));
    $('#' + zip_id).val(that.data('zip'));
    $('#' + location_id).val(that.data('city'));
    $('#deliverySpots').modal('hide');
});

function changeForm(reload)
{
    reload           = typeof reload !== 'undefined' ? reload : false;
    var lastname_id  = $('input[name="register[shipping_address][nachname]"]').attr('id'),
        firstname_id = $('input[name="register[shipping_address][vorname]"]').attr('id'),
        street_id    = $('input[name="register[shipping_address][strasse]"]').attr('id');
    $('label[for="' + street_id + '"]').html(street_label);
    var streetnumber_id = $('input[name="register[shipping_address][hausnummer]"]').attr('id');
    $('label[for="' + streetnumber_id + '"]').html(streetnumber_label);
    var snid = $('#' + streetnumber_id);
    snid.attr('placeholder', streetnumber_label);
    if (doMofidy) {
        snid.attr('minlength', 3).attr('maxlength', 3).attr('pattern', '[0-9]{3}');
    } else {
        snid.removeAttr('minlength').removeAttr('maxlength').removeAttr('pattern');
    }
    var additional_id = $('input[name="register[shipping_address][adresszusatz]"]').attr('id');
    $('label[for="' + additional_id + '"]').html(additional_label);
    var aid = $('#' + additional_id);
    aid.attr('placeholder', additional_label);
    if (doMofidy) {
        aid.attr('minlength', 6).attr('maxlength', 10).attr('pattern', '[0-9]{6,10}');
    } else {
        aid.removeAttr('minlength').removeAttr('maxlength').removeAttr('pattern');
    }
    var zip_id      = $('input[name="register[shipping_address][plz]"]').attr('id'),
        location_id = $('input[name="register[shipping_address][ort]"]').attr('id');

    (street_readonly === true) ? $('#' + street_id).attr('readonly', true) : $('#' + street_id).removeAttr('readonly');
    (additional_required === true) ? $('#' + additional_id).attr('required', true) : $('#' + additional_id).removeAttr('required');
    (zip_readonly === true) ? $('#' + zip_id).attr('readonly', true) : $('#' + zip_id).removeAttr('readonly');
    (location_readonly === true) ? $('#' + location_id).attr('readonly', true) : $('#' + location_id).removeAttr('readonly');
    if (reload === false) {
        $('#' + zip_id).val(zip_value);
        $('#' + location_id).val(location_value);
        $('#' + street_id).val(street_value);
        $('#' + streetnumber_id).val('');
        $('#' + additional_id).val('');
        $('#' + lastname_id).val('');
        $('#' + firstname_id).val('');
    }
}

function setFormValues(jtlPack)
{
    switch (Number(jtlPack)) {
        case -2: // packstation
            street_label        = jtlPackFormTranslations.packstation.street;
            street_value        = jtlPackFormTranslations.packstation.street;
            streetnumber_label  = jtlPackFormTranslations.packstation.streetnumber;
            additional_label    = jtlPackFormTranslations.packstation.additional;
            additional_required = true;
            street_readonly     = true;
            zip_readonly        = false;
            location_readonly   = false;
            zip_value           = '';
            location_value      = '';
            doMofidy            = true;
            break;

        case -3: // postfiliale
            street_label        = jtlPackFormTranslations.postfiliale.street;
            street_value        = jtlPackFormTranslations.postfiliale.street;
            streetnumber_label  = jtlPackFormTranslations.postfiliale.streetnumber;
            additional_label    = jtlPackFormTranslations.postfiliale.additional;
            additional_required = false;
            street_readonly     = true;
            zip_readonly        = false;
            location_readonly   = false;
            zip_value           = '';
            location_value      = '';
            doMofidy            = true;
            break;

        case -4: // nachbar
            var zip_id          = $('input[name="plz"]').attr('id');
            var location_id     = $('input[name="ort"]').attr('id');
            street_label        = jtlPackFormTranslations.default.street;
            street_value        = '';
            streetnumber_label  = jtlPackFormTranslations.default.streetnumber;
            additional_label    = jtlPackFormTranslations.default.additional;
            additional_required = false;
            street_readonly     = false;
            zip_readonly        = true;
            location_readonly   = true;
            zip_value           = $('#' + zip_id).val();
            location_value      = $('#' + location_id).val();
            doMofidy            = false;
            break;

        default:
            street_label        = jtlPackFormTranslations.default.street;
            street_value        = '';
            streetnumber_label  = jtlPackFormTranslations.default.streetnumber;
            additional_label    = jtlPackFormTranslations.default.additional;
            additional_required = false;
            street_readonly     = false;
            zip_readonly        = false;
            location_readonly   = false;
            zip_value           = '';
            location_value      = '';
            doMofidy            = false;
            break;
    }
}

$(document).on('blur', '#jtl_pack_wunschort', function (e) {
    var wunschort = $('#jtl_pack_wunschort').val();
    $("input[type='submit']").attr('disabled', true);
    $.evo.io().call('setJtlPackLocation', [wunschort], {}, function (error, data) {
        $('input[type="submit"]').removeAttr('disabled');
    });
});

$(document).on('click', '.dhl-wunschtag', function () {
    var value = $('input[name="jtl_pack_wunschtag_value"]:checked').val();
    $.evo.io().call('setJtlDeliveryWish', ['wunschtag', value], {}, function (error, data) {
    });
});

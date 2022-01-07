/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula/
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @category    Mage
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license     http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

var validateVat = function(vat, op_mode, address_type, address_id)
{
    if(typeof(address_id) == 'undefined') { address_id = 0; }

    try
    {
        if( typeof(vat) == "string" )
        {
            vat = vat.replace(/\s+/g,"");
            if( vat.match(new RegExp('^[A-Z][A-Z]')))
            {
                new Ajax.Request(gw_vat_check_url, {
                    method: 'post',
                    parameters: 'taxvat=' + vat + '&op_mode=' + op_mode + '&address_type=' + address_type + '&address_id=' + address_id,
                    onLoading: function () {
                        switch (op_mode) {
                            case 'SINGLE':
                                $('vatLoader').show();
                                break;
                            case 'MULTI':
                                $(address_type + ':vatLoader').show();
                                break;
                        }
                    },
                    onComplete: function () {
                        switch (op_mode) {
                            case 'SINGLE':
                                $('vatLoader').hide();
                                break;
                            case 'MULTI':
                                $(address_type + ':vatLoader').hide();
                                break;
                        }
                    },
                    onSuccess: function (transport) {
                        // Evaluate validation result
                        var response = transport.responseText.evalJSON();
                        var output = '<ul class="vat_validation-messages" style="margin-top:5px;">';

                        if (response.vat_is_valid == true && typeof(response.faultstring) == "undefined") {
                            output += '<li class="success-msg">';
                            output += (Translator.translate('Your VAT-ID is valid.'));
                            output += ' ';

                            if (response.is_vat_free == true) {
                                output += (Translator.translate('We have identified you as EU business customer, you can order VAT-exempt in our shop now.'));
                            } else if (response.is_vat_free == false) {
                                output += (Translator.translate('We have identified you as business customer.'));
                            }
                            output += '</li>';
                            output += '</ul>';

                        } else if (response.vat_is_valid == false && typeof(response.faultstring) == "undefined") {
                            output += '<li class="error-msg">';
                            output += (Translator.translate('Your VAT-ID is invalid, please check the syntax.'));
                            output += '</li></ul>';
                        } else {
                            output += '<li class="notice-msg">';
                            switch (response.faultstring) {
                                case "INVALID_INPUT":
                                    output += (Translator.translate('The given VAT-ID is invalid, please check the syntax. If this error remains please contact us directly to register a customer account with exempt from taxation with us.'));
                                    break;
                                case "SERVICE_UNAVAILABLE":
                                case "SERVER_BUSY":
                                    output += (Translator.translate('Currently the European VIES service is unavailable, but you can proceed with your registration and validate later from your customer account management.'));
                                    break;
                                case "MS_UNAVAILABLE":
                                case "TIMEOUT":
                                    output += (Translator.translate('Currently the member state service is unavailable, we could not validate your VAT-ID to issue an VAT exempt order. Anyhow you can proceed with your registration and validate later in your customer account.'));
                                    break;
                                default:
                                    output += (Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.'));
                                    break;
                            }
                            output += '</li></ul>';
                        }

                        // Validation output
                        switch (op_mode) {
                            case 'SINGLE':
                                $('checkrsp').update(output);
                                break;
                            case 'MULTI':
                                $(address_type + ':checkrsp').update(output);
                                break;
                            default:
                                break;
                        }

                        // OSC Integrations
                        if (gw_osc_integration != '') {
                            handleOSC();
                        }

                    },
                    onFailure: function () {
                        switch (op_mode) {
                            case 'SINGLE':
                                $('checkrsp').update('<ul><li class="error-msg">' + Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.') + '</li></ul>');
                                break;
                            case 'MULTI':
                                $(address_type + ':checkrsp').update('<ul><li class="error-msg">' + Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.') + '</li></ul>');
                                break;
                        }
                    }
                });//endajax



            } else if( vat == "") {
                new Ajax.Request(gw_vat_check_url, {
                    method:'post',
                    parameters:'vatid=removed'+'&address_type=' + address_type + '&address_id=' + address_id,
                });//endajax

                switch(op_mode)
                {
                    case 'SINGLE':
                        $('checkrsp').update();
                        break;
                    case 'MULTI':
                        $(address_type+':checkrsp').update();
                        break;
                }

                // OSC Integrations
                if (gw_osc_integration != '') {
                    handleOSC();
                }

            } else {
                switch(op_mode)
                {
                    case 'SINGLE':
                        $('checkrsp').update('<ul><li class="notice-msg">'+Translator.translate('Please enter your VAT-ID including the ISO-3166 two letter country code.')+'</li></ul>');
                        break;
                    case 'MULTI':
                        $(address_type+':checkrsp').update('<ul><li class="notice-msg">'+Translator.translate('Please enter your VAT-ID including the ISO-3166 two letter country code.')+'</li></ul>');
                        break;
                }
            }
        }

    } catch (error) {
        switch(op_mode)
        {
            case 'SINGLE':
                $('checkrsp').update('<ul><li class="error-msg">'+Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.')+' '+error+'</li></ul>');
                break;
            case 'MULTI':
                $(address_type+':checkrsp').update('<ul><li class="error-msg">'+Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.')+' '+error+'</li></ul>');
                break;
        }
    }


}

var vatValidation = function() {
    var op_mode = 'SINGLE';
    this.field_id = 'taxvat';
    this.prefix = '';
    this.address_id = 0;

    this.setOpMode = function ()
    {
        if( document.body.contains(document.getElementById('billing:vat_id'))) {
            this.field_id = 'vat_id';
            this.prefix = 'billing:';
            op_mode = 'MULTI';

        } else if( document.body.contains(document.getElementById('vat_id'))) {
            this.field_id = 'vat_id';
            this.prefix = '';
        }

        /*
        } else {
            if( document.body.contains(document.getElementById('billing:taxvat'))) {
                this.field_id = 'taxvat';
                this.prefix = 'billing:';
            } else if( document.body.contains(document.getElementById('taxvat')) ) {
                this.field_id = 'taxvat';
                this.prefix = '';
            }
        }
        */
    };

    this.setListener = function()
    {
        var addr_id = this.address_id;
        if( this.field_id == 'taxvat' && document.body.contains(document.getElementById(this.prefix+this.field_id)))
        {
            $(this.prefix+this.field_id).on('blur', function() {
                var vat = this.value.toUpperCase();
                validateVat(vat, op_mode, 'billing', addr_id);
            });

        } else {

            if( document.body.contains(document.getElementById(this.prefix+this.field_id)) ) {
                $(this.prefix+this.field_id).on('blur', function() {
                    var vat = this.value.toUpperCase();
                    validateVat(vat, op_mode, 'billing', addr_id);
                });
            }

            if( op_mode == 'MULTI' && document.body.contains(document.getElementById('shipping:vat_id')) ){
                $('shipping:vat_id').on('blur', function() {
                    var vat = this.value.toUpperCase();
                    validateVat(vat, op_mode, 'shipping', addr_id);
                });
            }
        }
    };

    this.addResponseFields = function(gw_loader_src)
    {
        this.wait_message = '<img class="v-middle" title="'+Translator.translate('Please wait while we validate your VAT-ID')+'" alt="'+Translator.translate('Please wait while we validate your VAT-ID')+'" src="'+ gw_loader_src +'">' +
                            '<span> '+Translator.translate('Please wait while we validate your VAT-ID')+'</span></div>';
        alert(op_mode);
        switch(op_mode)
        { 
            case 'SINGLE':
                if( document.body.contains(document.getElementById(this.prefix+this.field_id)) ) {
                    $(this.prefix+this.field_id).insert({
                        after: '<div id="vatLoader">' +
                        this.wait_message +
                        '<div id="checkrsp" class="vat-tooltip"></div>'
                    });
                    $('vatLoader').hide();
                }
            break;

            case 'MULTI':
                if( document.body.contains(document.getElementById('billing:'+this.field_id)) ) {
                    $('billing:'+this.field_id).insert({
                        after: '<div id="billing:vatLoader">' +
                        this.wait_message +
                        '<div id="billing:checkrsp"  class="vat-tooltip"></div>'
                    });
                    $('billing:vatLoader').hide();
                }

                if( document.body.contains(document.getElementById('shipping:vat_id')) )
                {
                    $('shipping:'+this.field_id).insert({
                        after: '<div id="shipping:vatLoader">' +
                            this.wait_message +
                            '<div id="shipping:checkrsp"  class="vat-tooltip"></div>'
                    });
                    $('shipping:vatLoader').hide();
                }

            break;
        }
    };

    this.getParams = function(url) {
        var parts = url.split('/').slice(1);
        if( url.indexOf("customer/address/edit/id") != -1 ) {
            this.address_id = parts[parts.length-2];
        }
    };

}



document.observe("dom:loaded", function()
{
    if(gw_vat_validation_enabled == 1)
    {
        var vatValidator = new vatValidation();
        vatValidator.setOpMode();
        vatValidator.getParams(location.pathname);
        vatValidator.setListener();
        vatValidator.addResponseFields(gw_loader_src);
    }
});

/**
 * Thanks to http://stackoverflow.com/users/561731/neal
 */
var Queue = (function(){
    function Queue() {};
    Queue.prototype.running = false;
    Queue.prototype.queue = [];

    Queue.prototype.add_function = function(callback) {
        var _this = this;
        this.queue.push(function(){
            var finished = callback();
            if(typeof finished === "undefined" || finished) { _this.next(); }
        });
        if(!this.running) { this.next(); }
        return this;
    }

    Queue.prototype.next = function(){
        this.running = false;
        var shift = this.queue.shift();
        if(shift) { this.running = true; shift(); }
    }
    return Queue;
})();


//Handle OSC integrations
var handleOSC = function()
{
    var queue = new Queue;

    switch (gw_osc_integration)
    {
        case 'AHEADWORKS_CHECKOUT':
            if (typeof(AWOnestepcheckoutCoreUpdater) != "undefined" && typeof(awOSCAddress) != 'undefined') {
                queue.add_function(function(){
                    AWOnestepcheckoutCoreUpdater.startRequest(awOSCAddress.addressChangedUrl);
                });
                queue.add_function(function(){
                    awOSCAddress.onAddressChanged();
                });
            }
            break;
        case 'AMASTY_CHECKOUT':
            if(typeof(updateCheckout)=='function') {
                updateCheckout();
            }
            break;
        case 'ECOMDEV_CHECKOUT':
            if (typeof(review) == 'object') review.load();
            break;
        case 'ECOMTEAM_CHECKOUT':
            if (typeof(EasyCheckout) != 'undefined') {
                EasyCheckout.instance.addressChangedEvent();
            }
            break;
        case 'GOMAGE_CHECKOUT':
            if (typeof(checkout) == 'object') {
                checkout.submit(checkout.getFormData(), 'get_totals');
            }
            break;
        case 'ONESTEP_CHECKOUT':
            if (typeof(document.getElementById('onestepcheckout-form')) != "undefined") {
                queue.add_function(function(){
                    get_save_billing_function(gw_idev_url_save_billing, gw_idev_url_set_methods)();
                });
            }
            break;
        case 'IWD_CHECKOUT':
            if (typeof(IWD.OPC.Checkout) == 'object') {
                queue.add_function(function(){
                    IWD.OPC.Checkout.reloadShippingsPayments();
                });
                queue.add_function(function(){
                    IWD.OPC.Checkout.pullReview();
                });
            }
            break;
        case 'FME_CHECKOUT':
            if (typeof(document.getElementById('onestepcheckout-form')) != "undefined") {
                billing.saveCountry();
            }
            break;


        case 'VINAGENTO_CHECKOUT':
            if (typeof(onestepcheckout) == 'object') onestepcheckout.reloadReview();
            break;
        case 'TM_CHECKOUT':
            if (typeof(FireCheckout) == 'function') {
                setTimeout(function(){
                    queue.add_function(function(){
                        checkout.update(checkout.urls.billing_address, FireCheckout.Ajax.arrayToJson(FireCheckout.Ajax.getSectionsToUpdate('billing','shipping')));
                    });
                    queue.add_function(function(){
                        checkout.update(checkout.urls.shopping_cart);
                    });
                }, 200);
            }
            break;

        case 'MAGESTORE_CHECKOUT':
            if (typeof(document.getElementById('checkout-review-load')) != "undefined") {
                $$('select[name="billing[country_id]"] option').each(function (o) {
                    if (o.readAttribute('value') == response.country_id) {
                        o.selected = true;
                    }
                });
                save_address_information(save_address_url,true,true,true); // url, update shipping, update payment methods, update totals
            }
            break;

        case 'NEXTBITS_CHECKOUTNEXT':
            if (typeof(checkoutnext) != 'undefined') {
                checkoutnext.reloadReview();
            }
            break;
        case 'CMSMART_CHECKOUT':
            if (typeof(document.getElementById('onepage-form')) != "undefined" && typeof(updateQtyProduct)==='function') {
                updateQtyProduct();
            }
            break;
        case 'MAGEMART_QUICKCHECKOUT':
            ajaxSaveBillShipData(ajaxSaveBillShipInfoUrl);
            break;
        case 'OCODEWIRE_ONESTEPCHECKOUT':
            get_save_billing_function(document.location.href + 'ajax/billing_details', document.location.href + 'ajax/set_methods_separate')();
            break;
        case 'KALLYAS_OPC':
            if (typeof(checkout) == 'object') {
                checkout.update();
            }
            break;
        case 'UNI_OPC':
            if (typeof(checkout) == 'object') {
                billingStep.save();
            }
            break;
        case 'MAGEGIANT_OPC':
            if (typeof(giantOSCAddress) == 'object') {
                giantOSCAddress.onAddressChanged();
            }
            break;
        case '': default: break;
    }
}
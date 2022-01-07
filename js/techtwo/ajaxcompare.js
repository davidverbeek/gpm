/**
 * AjaxCompare JavaScript Module
 *
 * @created 2011-03-28
 * @author Bastiaan Heeren
 * @copyright Techtwo Webdevelopment 2011
 * @version 0.0.2
 */

var AjaxCompare = Class.create();
AjaxCompare.prototype = {

    initialize: function (config) {
        this.config = config;

        $(document).observe('dom:loaded', this.load.bindAsEventListener(this));
    },

    load: function () {
        var self = this;

        $$('input.' + this.config.checkboxClass).invoke('observe', 'click', function (event) {

            var checkbox = Event.findElement(event, 'input');
            var productId = parseInt(checkbox.value, 10);

            if (checkbox.checked) {
                self.add(productId);
            }
            else {
                self.remove(productId);
            }
        });

        this.update();
    },

    /**
     * Add a product to the comparison list.
     */
    add: function (productId) {
        this.config.compareIds.push(productId);
        this.config.compareIds = this.config.compareIds.uniq();
        this.update();

        var url = this.config.compareAjaxUrl.sub('ACTION', 'add').sub('PRODUCT', productId);
        $$('.block-compare').invoke('addClassName', 'loading');
        var req = new Ajax.Request(url, {
            method: 'get',
            onSuccess: this.ajaxResponse.bind(this)
        });
    },

    /**
     * Remove a product to the comparison list.
     */
    remove: function (productId) {
        this.config.compareIds = this.config.compareIds.without(productId);
        this.update();

        var url = this.config.compareAjaxUrl.sub('ACTION', 'remove').sub('PRODUCT', productId);
        $$('.block-compare').invoke('addClassName', 'loading');
        var req = new Ajax.Request(url, {
            method: 'get',
            onSuccess: this.ajaxResponse.bind(this)
        });
    },

    /**
     * Clear the comparison list.
     */
    clear: function () {
        this.config.compareIds = [];
        this.update();

        var url = this.config.compareAjaxUrl.sub('ACTION', 'clear').sub('PRODUCT', 0);
        $$('.block-compare').invoke('addClassName', 'loading');
        var req = new Ajax.Request(url, {
            method: 'get',
            onSuccess: this.ajaxResponse.bind(this)
        });
    },

    /**
     * Handle ajax response.
     */
    ajaxResponse: function (transport) {
        if (Ajax.activeRequestCount > 1) {
            // ignore response while another request is still active
            return;
        }

        var resp = {};
        if (transport && transport.responseText){
            try {
                resp = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                resp = {};
            }
        }

        if (resp.sidebarBlock) {
            $$('.block-compare').invoke('replace', resp.sidebarBlock);
        }
        else {
            $$('.block-compare').invoke('removeClassName', 'loading');
        }

        if (resp.compareIds) {
            this.config.compareIds = resp.compareIds;
            this.update();
        }

        if (resp.message) {
            alert(resp.message);
        }
    },

    /**
     * Check/uncheck all checkboxes.
     */
    updateCheckboxes: function () {
        var compareIds = this.config.compareIds;
        //console.log('compareIds', compareIds);
        $$('input.' + this.config.checkboxClass).each(function (el) {
            var checked = (compareIds.indexOf(parseInt(el.value, 10)) > -1);
            if (checked != el.checked) {
                el.checked = checked;
            }
        });
    },

    /**
     * The comparison list has changed, update document.
     */
    update: function () {
        this.updateCheckboxes();
        
        if (typeof this.config.onUpdate == 'function') {
            this.config.onUpdate(this.config.compareIds);
        }
    },

    /**
     * Show the comparison popup.
     */
    show: function () {
        var url = this.config.compareShowUrl.sub('ITEMS', this.config.compareIds.join(','));
        popWin(url, 'compare', 'top:0,left:0,width=820,height=600,resizable=yes,scrollbars=yes');
    }

};

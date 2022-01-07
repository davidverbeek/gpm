(function($) {
    $.fn.ddslick = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exists.');
        }
    };
    var methods = {},
        defaults = {
            data: [],
            keepJSONItemsOnTop: false,
            width: "auto",
            height: null,
            background: "transparent",
            selectText: "",
            defaultSelectedIndex: null,
            truncateDescription: true,
            imagePosition: "right",
            showSelectedHTML: true,
            clickOffToClose: true,
            onSelected: function() {}
        },
        ddSelectHtml = '<div class="dd-select"><input class="dd-selected-value" type="hidden"/><a class="dd-selected"></a><span class="dd-pointer dd-pointer-down"></span></div>',
        ddOptionsHtml = '<span id="dd-triangle"></span><ul class="dd-options"></ul>',
        ddslickCSS = '<style id="css-ddslick" type="text/css">' + '.dd-select{border-radius:2px; border:solid 1px #d; position:relative; cursor:pointer; float:left;}' + '.dd-desc{color:#aaa; display:block; overflow: hidden; font-weight:normal; line-height: 1.4em;}' + '.dd-option{top:33px; padding-left: 35px; display:block; border-bottom:solid 1px #ddd; overflow:hidden; text-decoration:none; color:#333; cursor:pointer;-webkit-transition: all 0.25s ease-in-out; -moz-transition: all 0.25s ease-in-out;-o-transition: all 0.25s ease-in-out;-ms-transition: all 0.25s ease-in-out;}' + '.dd-options > li:last-child > .dd-option{border-bottom:none;}' + '.dd-selected-description-truncated{text-overflow: ellipsis; white-space:nowrap;}' + '.dd-option-image, .dd-selected-image{vertical-align:middle; float:right; margin-right:5px; max-width:64px;}' + '.dd-container{position:relative;}? .dd-selected-text{font-weight:bold}?</style>';
    if ($('#css-ddslick').length <= 0) {
        $(ddslickCSS).appendTo('head');
    }
    methods.init = function(options) {
        var options = $.extend({}, defaults, options);
        return this.each(function() {
            var obj = $(this),
                data = obj.data('ddslick');
            if (!data) {
                var ddSelect = [],
                    ddJson = options.data;
                obj.find('option').each(function() {
                    var $this = $(this),
                        thisData = $this.data();
                    ddSelect.push({
                        text: $.trim($this.text()),
                        value: $this.val(),
                        selected: $this.is(':selected'),
                        description: thisData.description,
                        imageSrc: thisData.imagesrc
                    });
                });
                if (options.keepJSONItemsOnTop) $.merge(options.data, ddSelect);
                else options.data = $.merge(ddSelect, options.data);
                var original = obj,
                    placeholder = $('<div id="' + obj.attr('id') + '"></div>');
                obj.replaceWith(placeholder);
                obj = placeholder;
                obj.addClass('dd-container').append(ddSelectHtml).append(ddOptionsHtml);
                var ddSelect = obj.find('.dd-select'),
                    ddOptions = obj.find('.dd-options');
                ddOptions.css({
                    width: options.width
                });
                ddSelect.css({
                    width: options.width,
                    background: options.background
                });
                obj.css({
                    width: options.width
                });
                if (options.height != null) ddOptions.css({
                    height: options.height,
                    overflow: 'auto'
                });
                $.each(options.data, function(index, item) {
                    if (item.selected) options.defaultSelectedIndex = index;
                    ddOptions.append('<li>' + '<a class="dd-option">' + (item.value ? ' <input class="dd-option-value" type="hidden" value="' + item.value + '"/>' : '') + (item.text ? ' <label class="dd-option-text">' + item.text + '</label>' : '') + (item.imageSrc ? ' <img class="dd-option-image' + (options.imagePosition == "right" ? ' dd-image-right' : '') + '" src="' + item.imageSrc + '"/>' : '') + (item.description ? ' <small class="dd-option-description dd-desc">' + item.description + '</small>' : '') + '</a>' + '</li>');
                });
                var pluginData = {
                    settings: options,
                    original: original,
                    selectedIndex: -1,
                    selectedItem: null,
                    selectedData: null
                }
                obj.data('ddslick', pluginData);
                if (options.selectText.length > 0 && options.defaultSelectedIndex == null) {
                    obj.find('.dd-selected').html(options.selectText);
                } else {
                    var index = (options.defaultSelectedIndex != null && options.defaultSelectedIndex >= 0 && options.defaultSelectedIndex < options.data.length) ? options.defaultSelectedIndex : 0;
                    selectIndex(obj, index);
                }
                obj.find('.dd-select').on('click.ddslick', function() {
                    open(obj);
                });
                obj.find('.dd-option').on('click.ddslick', function() {
                    selectIndex(obj, $(this).closest('li').index());
                    jQuery("#shipping-zip-form").submit();
                });
                if (options.clickOffToClose) {
                    ddOptions.addClass('dd-click-off-close');
                    obj.on('click.ddslick', function(e) {
                        e.stopPropagation();
                    });
                    $('body').on('click', function() {
                        $('.dd-click-off-close').slideUp(50).siblings('.dd-select').find('.dd-pointer').removeClass('dd-pointer-up');
                    });
                }
            }
        });
    };
    methods.select = function(options) {
        return this.each(function() {
            if (options.index) selectIndex($(this), options.index);
        });
    }
    methods.open = function() {
        return this.each(function() {
            var $this = $(this),
                pluginData = $this.data('ddslick');
            if (pluginData) open($this);
        });
    };
    methods.close = function() {
        return this.each(function() {
            var $this = $(this),
                pluginData = $this.data('ddslick');
            if (pluginData) close($this);
        });
    };
    methods.destroy = function() {
        return this.each(function() {
            var $this = $(this),
                pluginData = $this.data('ddslick');
            if (pluginData) {
                var originalElement = pluginData.original;
                $this.removeData('ddslick').unbind('.ddslick').replaceWith(originalElement);
            }
        });
    }

    function selectIndex(obj, index) {
        var pluginData = obj.data('ddslick');
        var ddSelected = obj.find('.dd-selected'),
            ddSelectedValue = ddSelected.siblings('.dd-selected-value'),
            ddOptions = obj.find('.dd-options'),
            ddPointer = ddSelected.siblings('.dd-pointer'),
            selectedOption = obj.find('.dd-option').eq(index),
            selectedLiItem = selectedOption.closest('li'),
            settings = pluginData.settings,
            selectedData = pluginData.settings.data[index];
        obj.find('.dd-option').removeClass('dd-option-selected');
        selectedOption.addClass('dd-option-selected');
        pluginData.selectedIndex = index;
        pluginData.selectedItem = selectedLiItem;
        pluginData.selectedData = selectedData;
        if (settings.showSelectedHTML) {
            //var labelsected = '<label class="dd-selected-text">' + Translator.translate('Shippment Cost') + '</label>'
            ddSelected.html((selectedData.imageSrc ? '<img class="dd-selected-image' + (settings.imagePosition == "right" ? ' dd-image-right' : '') + '" src="' + selectedData.imageSrc + '"/>' : '') + (selectedData.description ? '<small class="dd-selected-description dd-desc' + (settings.truncateDescription ? ' dd-selected-description-truncated' : '') + '" >' + selectedData.description + '</small>' : ''));
        } else ddSelected.html(selectedData.text);
        ddSelectedValue.val(selectedData.value);
        jQuery("#country_id").val(selectedData.value);
        pluginData.original.val(selectedData.value);
        obj.data('ddslick', pluginData);
        close(obj);
        adjustSelectedHeight(obj);
        if (typeof settings.onSelected == 'function') {
            settings.onSelected.call(this, pluginData);
        }
    }

    function open(obj) {
        var $this = obj.find('.dd-select'),
            ddOptions2 = $this.siblings('#dd-triangle'),
            ddOptions = $this.siblings('.dd-options'),
            ddPointer = $this.find('.dd-pointer'),
            wasOpen = ddOptions.is(':visible');
        $('.dd-click-off-close').not(ddOptions).slideUp(50);
        $('.dd-pointer').removeClass('dd-pointer-up');
        if (wasOpen) {
            ddOptions2.slideUp('fast');
            ddOptions.slideUp('fast');
            ddPointer.removeClass('dd-pointer-up');
        } else {
            ddOptions2.slideDown('fast');
            ddOptions.slideDown('fast');
            ddPointer.addClass('dd-pointer-up');
        }
        adjustOptionsHeight(obj);
    }

    function close(obj) {
        obj.find('.dd-options').slideUp(50);
        obj.find('.dd-pointer').removeClass('dd-pointer-up').removeClass('dd-pointer-up');
    }

    function adjustSelectedHeight(obj) {
        var lSHeight = obj.find('.dd-select').css('height');
        var descriptionSelected = obj.find('.dd-selected-description');
        var imgSelected = obj.find('.dd-selected-image');
        if (descriptionSelected.length <= 0 && imgSelected.length > 0) {}
    }

    function adjustOptionsHeight(obj) {
        obj.find('.dd-option').each(function() {
            var $this = $(this);
            var lOHeight = $this.css('height');
            var descriptionOption = $this.find('.dd-option-description');
            var imgOption = obj.find('.dd-option-image');
            if (descriptionOption.length <= 0 && imgOption.length > 0) {
                $this.find('.dd-option-text').css('lineHeight', lOHeight);
            }
        });
    }
})(jQuery);

if (jQuery(window).width() < 1200) {
    jQuery('.popover_wrapper a').on('click', function(e){
        e.preventDefault() ;
        $(this).siblings('.popover_content').addClass('mobile-popover');
    });
}

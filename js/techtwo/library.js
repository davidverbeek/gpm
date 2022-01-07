Element.addMethods({
    mouseEnter: function(element,observer) {
        element = $(element);
        element.observe('mouseover',function(evt,currentTarget) {
            var relatedTarget = $(evt.relatedTarget || evt.fromElement);
            if( relatedTarget && relatedTarget!=currentTarget && relatedTarget.childOf(currentTarget)==false ) {
                observer(currentTarget);
            } 
        }.bindAsEventListener({},element));
        return element;
    },
    mouseLeave: function(element,observer) {
        element = $(element);
        element.observe('mouseout',function(evt,currentTarget) {
            var relatedTarget = $(evt.relatedTarget || evt.toElement);
            if( relatedTarget && relatedTarget!=currentTarget && relatedTarget.childOf(currentTarget)==false ) {
                observer(currentTarget);
            }
        }.bindAsEventListener({},element));
        return element;
    },
    hover: function (element, mouseenter, mouseleave) {
        Element.mouseEnter(element, mouseenter);
        Element.mouseLeave(element, mouseleave);
        return element;
    },
    hoverIntent: function(element, mouseenter, mouseleave) {
        var delay = 180,
            hover = false,
            timer;

        Element.mouseEnter(element, function(elt){
            clearTimeout(timer);
            timer = window.setTimeout(function () { hover = true; mouseenter(elt); }, delay);
        });

        Element.mouseLeave(element, function(elt){
            clearTimeout(timer);
            timer = window.setTimeout(function () { if (hover) { hover = false; mouseleave(elt); }}, delay);
        });

        return element;
    }
});
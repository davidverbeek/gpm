/* only run this code when MageBoost is loaded */
if (typeof(_loadSessionContext) === "function") {
    $(document).on('click', '.link-compare', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var e = $(e.target);
        jQuery.ajax({
            type: 'get',
            url: e.href,
            success: _loadSessionContext
        });
    });
    if (typeof(_contextDebugLog) === "function") { _contextDebugLog('click bind for ".link-compare" created...'); }

    $(document).on('click', '.block-compare .actions a', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var e = $(e.target);
        jQuery.ajax({
            type: 'get',
            url: e.href,
            success: _loadSessionContext
        });
    });
    if (typeof(_contextDebugLog) === "function") { _contextDebugLog('click bind for ".block-compare .actions a" created...'); }

    $(document).on('click', '.block-compare #compare-items .btn-remove', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var e = $(e.target);
        jQuery.ajax({
            type: 'get',
            url: e.href,
            success: _loadSessionContext
        });
    });
    if (typeof(_contextDebugLog) === "function") { _contextDebugLog('click bind for ".block-compare #compare-items .btn-remove" created...'); }
}

/* this code will run right before the ajax call is done, do some stuff here? */
function _mageBoostPreSessionContext() {
}

/* this code will run right after the ajax call is done, do some stuff here? */
function _mageBoostPostSessionContext() {
}
_debugStartTime = new Date().getTime();
_browserSupportsLocalStorageSet = null;
_browserSupportsSessionStorageSet = null;
_browserSupportsCookieStorageSet = null;
_formkeyFromCache = null;

function _getIeVer() {
    var e = -1;
    if (navigator.appName == "Microsoft Internet Explorer") {
        var t = navigator.userAgent;
        var n = new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})");
        if (n.exec(t) != null) { e = parseFloat(RegExp.$1); }
    }
    return e;
}

function _browserSupportsLocalStorage() {
    if (typeof _browserSupportsLocalStorageSet !== "undefined" && _browserSupportsLocalStorageSet != null) {
        return _browserSupportsLocalStorageSet;
    }
    try {
        localStorage.setItem("mblc_storagetest", "test");
        localStorage.removeItem("mblc_storagetest");
        _contextDebugLog('browser supports writing to localStorage...');
        _browserSupportsLocalStorageSet = true;
        return true;
    } catch(err) {
        _contextDebugLog('browser does NOT support writing to localStorage...');
        _browserSupportsLocalStorageSet = false;
        return false;
    }
}

function _browserSupportsSessionStorage() {
    if (_browserSupportsSessionStorageSet != null) {
        return _browserSupportsSessionStorageSet;
    }
    try {
        sessionStorage.setItem("mblc_storagetest", "test");
        sessionStorage.removeItem("mblc_storagetest");
        _contextDebugLog('browser supports writing to sessionStorage...');
        _browserSupportsSessionStorageSet = true;
        return true;
    } catch(err) {
        _contextDebugLog('browser does NOT support writing to sessionStorage...');
        _browserSupportsSessionStorageSet = false;
        return false;
    }
}

function _browserSupportsCookieStorage() {
    if (_browserSupportsCookieStorageSet != null) {
        return _browserSupportsCookieStorageSet;
    }
    try {
        _setCookie('mblc_storagetest', 'test', 3600);
        var cookie = _getCookie('mblc_storagetest');
        if (cookie=='test') {
            _contextDebugLog('browser supports writing to cookieStorage...');
            _browserSupportsCookieStorageSet = true;
            _removeCookie('mblc_storagetest');
            return true;
        } else {
            return false;
        }
    } catch(err) {
        _contextDebugLog('browser does NOT support writing to cookieStorage...');
        _browserSupportsCookieStorageSet = false;
        return false;
    }
}

function _contextDebugLog(msg) {
    if (typeof _contextShowDebug !== 'undefined') {
        if (_contextShowDebug == true || _getCookie('mb.dbg')==1) {
            var time = new Date().getTime() - _debugStartTime;
            if (typeof console != "undefined") {
                console.log('mb.dbg (' + parseFloat(time / 1000).toFixed(3) + '): ' + msg);
            }
        }
    }
}

function _setCookie(cname, cvalue, exseconds) {
    var d = new Date();
    d.setTime(d.getTime() + exseconds*1000);
    var expires = "expires="+d.toUTCString();
    if (document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/") {
        return true;
    }
    return false;
}

function _getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return null;
}

function _removeCookie(sKey, sPath, sDomain) {
    document.cookie = encodeURIComponent(sKey) +
        "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" +
        (sDomain ? "; domain=" + sDomain : "") +
        (sPath ? "; path=" + sPath : "");
}

function _getRequestData(e) {
    var t = {
        orig_request: window.location.href.substr(window.location.protocol.length + 2 + window.location.host.length),
        full_action_name: _contextAction,
        message_sessions: _contextMessageSessions,
        blocks: "",
        _contextCounter: 0
    };
    var n = _getBlocks(e);
    t.blocks = Object.toJSON(n);
    t._contextCounter = n._contextCounter;

    if (typeof _contextProductId !== "undefined" && _contextProductId) {
        t.currentProductId = _contextProductId
    }
    if (typeof _contextCmsId !== "undefined" && _contextCmsId) {
        t.currentCmsId = _contextCmsId
    }
    if (typeof _contextCategoryId !== "undefined" && _contextCategoryId) {
        t.currentCategoryId = _contextCategoryId
    }
    if (document.referrer !== "undefined" && document.referrer) {
        t.refferer = document.referrer
    }
    return t;
}

function _getBlocks(e, pf) {
    _contextDebugLog('getting blocks of type "' + e + '"...');
    var t = {};
    var n = 0;
    if (pf == undefined) {
        pf = "c_";
    }
    $$("." + e).each(function (element) {
        var r = element.readAttribute("id");
        if (!r) {
            r = pf + n;
            element.setAttribute("id", r)
        }
        var i = element.readAttribute("rel");
        if (i) {
            t[r] = i;
            var s = null;

            //do not cache sdk loyaltylion sdk block as it is already loaded
            if (_contextInlineLocalCache == true && i != 'loyaltylion_sdk') {
                if (_browserSupportsLocalStorage()) {
                    var s = _getCachedBlock(r);
                } else {
                    if (_browserSupportsSessionStorage()) {
                        var s = _getCachedBlockFromSession(r);
                    }
                    if (s == null) {
                        var s = _getCachedBlockFromCookie(r);
                    }
                }
            }

            if (s != null) {
                _contextDebugLog('... got cached data, updating block "' + r + '" (' + e + ') from cache...');
                $(r).update(s.Content);
            }
            n++;
        }
    });
    t._contextCounter = n;
    return t;
}

function _updateFormKeys(key) {
    var num_formkey = 0;
    _contextDebugLog('_updateFormKeys: updating formkeys...');
    if (key == undefined || key.empty()) {
        _contextDebugLog('_updateFormKeys: ... formkeys could not be updated: no formkey value supplied for _updateFormKeys!');
        return;
    }

    $$('[name="formkey"]').each(function (e) {
        var old = e.value;
        if (key != old) {
            _contextDebugLog('_updateFormKeys: updating element with name=formkey...');
            e.setValue(key);
            num_formkey++;
        }
    });

    $$('[name="form_key"]').each(function (e) {
        var old = e.value;
        if (key != old) {
            _contextDebugLog('_updateFormKeys: updating element with name=form_key...');
            e.setValue(key);
            num_formkey++;
        }
    });

    $$('form').each(function (e) {
        if (typeof e.action != 'undefined' && e.action != '') {
            var old_action = e.action;
            var new_action = e.action;
            new_action = e.action.replace(/(form_?key)\/(\w*)/g, '$1/' + key);
            if (new_action != old_action) {
                _contextDebugLog('_updateFormKeys: updating form action containing formkey or form_key...');
                e.action = new_action;
                num_formkey++;
            }
        }
    });

    $$('a').each(function (e) {
        if (typeof e.href != 'undefined' && e.href != '') {
            var href_old = e.href;
            var href_new = e.href.replace(/(form_?key)\/(\w*)/g, '$1/' + key);
            if (href_new != href_old) {
                _contextDebugLog('_updateFormKeys: updating anchors with hrefs containing formkey or form_key...');
                e.href = href_new;
                num_formkey++;
            }
        }
    });

    $$('button').each(function (e) {
        var button_old = e.outerHTML;
        var button_new = e.outerHTML.replace(/(form_?key)\/(\w*)/g, '$1/' + key);
        if (button_new != button_old) {
            _contextDebugLog('_updateFormKeys: updating buttons with hrefs containing formkey or form_key...');
            e.outerHTML = button_new;
            num_formkey++;
        }
    });

    $$('input[type=hidden]').each(function (e) {
        if (typeof e.value != 'undefined' && e.value != '') {
            var old_value = e.value;
            new_value = e.value.replace(/(form_?key)\/(\w*)/g, '$1/' + key);
            if (new_value != old_value) {
                _contextDebugLog('_updateFormKeys: updating form with hidden input value containing formkey or form_key...');
                e.value = new_value;
                num_formkey++;
            }
        }
    });

    if (num_formkey > 0) {
        _contextDebugLog('... ' + num_formkey + ' formkey values updated.');
    } else {
        _contextDebugLog('... no formkey values needed updating; current session matches.');
    }
}

function _doContextBlocks() {
    var e = _getRequestData("ctdblock");
    if (typeof window.hxs.context == "undefined") {
        window.hxs.context = new Array();
    }
    if (typeof e.currentProductId !== "undefined" || e._contextCounter > 0) {
        var p = _getBlocks("ctpriceblock");
        var t = _getBlocks("cttierpriceblock", 'tp_');
        var s = _getBlocks("ctstockblock", 's_');
        if (!!window.hxs.context[e]) {
            return;
        }
        window.hxs.context[e] = true;
        if (p._contextCounter > 0) { e.prices = Object.toJSON(p); }
        if (t._contextCounter > 0) { e.tierprices = Object.toJSON(t); }
        if (s._contextCounter > 0) { e.stock = Object.toJSON(s); }
        var num_blocks = 0;
        new Ajax.Request(_contextRequestUrl, {
            method: _contextAsyncMethod,
            parameters: e,
            evalJS: "force",
            onSuccess: function (e) {
                var t = e.responseText.evalJSON();
                for (var n in t.blocks) {
                    $(n).update(t.blocks[n]);
                    //if we update the messages then append into the messages and move in header
                    if (n == 'messages') {
                        /* Display error message block after update*/
                        html = t.blocks[n];


                        jQuery('.header-container > .messages').remove();
                        jQuery('.messages').insertBefore('.top-switch-bg');
                        jQuery('.messages').css('display', 'block');

                        if(!jQuery('ul.messages li ul').parent().hasClass('row')) {
                            jQuery('ul.messages li ul').wrap('<div class="row"></div>');
                        }

                        jQuery('.header-container .error-msg, .header-container .note-msg, .header-container .notice-msg, .header-container .success-msg').css('display', 'block');

                        if (html != '') {
                            jQuery('html,body').animate({scrollTop: 0});
                        }
                    }
                    _contextDebugLog('trying to update and cache block "' + n + '"...');

                    if (_browserSupportsLocalStorage() && _cacheBlock(n, t.blocks[n])) {
                        _contextDebugLog('... block "' + n + '" successfully cached (' + t.blocks[n].length + ' bytes) to localStorage.');
                        num_blocks++;
                    } else {
                        if (_browserSupportsSessionStorage() && _cacheBlockForSession(n, t.blocks[n])) {
                            _contextDebugLog('... block "' + n + '" successfully cached (' + t.blocks[n].length + ' bytes) to sessionStorage.');
                            num_blocks++;
                        } else {
                            if (_browserSupportsCookieStorage() && _cacheBlockForCookie(n, t.blocks[n])) {
                                _contextDebugLog('... block "' + n + '" successfully cached (' + t.blocks[n].length + ' bytes) to cookie.');
                                num_blocks++;
                            } else {
                                _contextDebugLog('... block "' + n + '" could not be cached, no available methods of storage found.');
                            }
                        }
                    }
                }
                for (var n in t.prices) {
                    _contextDebugLog('trying to update and cache price "' + n + '"...');
					if (t.prices[n]!=0) {
                        _contextDebugLog('price is ' + t.prices[n] + '...');
                        $(n).update(t.prices[n]);
                    }
                    if (_browserSupportsSessionStorage() && _cacheBlockForSession(n, t.prices[n])) {
                        _contextDebugLog('... price "' + n + '" successfully cached (' + t.prices[n].length + ' bytes) to sessionStorage.');
                        num_blocks++;
                    } else {
                        if (_browserSupportsCookieStorage() && _cacheBlockForCookie(n, t.prices[n])) {
                            _contextDebugLog('... price "' + n + '" successfully cached (' + t.prices[n].length + ' bytes) to cookie.');
                            num_blocks++;
                        } else {
                            _contextDebugLog('... price "' + n + '" could not be cached, no available methods of storage found.');
                        }
                    }
                }
                for (var n in t.tierprices) {
                    _contextDebugLog('trying to update and cache tierprice "' + n + '"...');
                    if (t.tierprices[n]!=0) {
                        $(n).update(t.tierprices[n]);
                    }
                    if (_browserSupportsSessionStorage() && _cacheBlockForSession(n, t.tierprices[n])) {
                        _contextDebugLog('... tierprice "' + n + '" successfully cached (' + t.tierprices[n].length + ' bytes) to sessionStorage.');
                        num_blocks++;
                    } else {
                        if (_browserSupportsCookieStorage() && _cacheBlockForCookie(n, t.tierprices[n])) {
                            _contextDebugLog('... tierprice "' + n + '" successfully cached (' + t.tierprices[n].length + ' bytes) to cookie.');
                            num_blocks++;
                        } else {
                            _contextDebugLog('... tierprice "' + n + '" could not be cached, no available methods of storage found.');
                        }
                    }
                }
                for (var n in t.stock) {
                    _contextDebugLog('trying to update and cache stock "' + n + '"...');
                    $(n).update(t.stock[n]);
                    if (_browserSupportsSessionStorage() && _cacheBlockForSession(n, t.stock[n])) {
                        _contextDebugLog('... stock "' + n + '" successfully cached (' + t.stock[n].length + ' bytes) to sessionStorage.');
                        num_blocks++;
                    } else {
                        if (_browserSupportsCookieStorage() && _cacheBlockForCookie(n, t.stock[n])) {
                            _contextDebugLog('... stock "' + n + '" successfully cached (' + t.stock[n].length + ' bytes) to cookie.');
                            num_blocks++;
                        } else {
                            _contextDebugLog('... stock "' + n + '" could not be cached, no available methods of storage found.');
                        }
                    }
                }
                if (t.formkey != undefined) {
                    _updateFormKeys(t.formkey);
                    _cacheFormkey(t.formkey);
                }
                if (typeof _mageBoostPostSessionContextAjax === "function") {
                    _mageBoostPostSessionContextAjax();
                }
            },
            onFailure: function(e) {
                if (typeof _mageBoostFirstloadOverlay !== "undefined") {
                    if (e.status != 200) {
                        $('mageboost-overlay').hide();
                        _contextDebugLog('sometime went wrong! AJAX update returned HTTP status ' + e.status);
                        clearInterval(_mageBoostFirstloadOverlay);
                    }
                }
            },
            onComplete: function (e) {
                window.hxs.context[e] = false;
                _contextDebugLog(num_blocks + ' placeholders updated.');
            }
        })
    }
}

function _doLazyDataBlocks(e) {
    var t = _getRequestData("ctddatablock");
    if (typeof window.hxs.lazy == "undefined") {
        window.hxs.lazy = new Array();
    }
    if (( typeof t.currentProductId !== "undefined" || typeof t.currentCategoryId !== "undefined" || typeof t.currentCmsId !== "undefined" ) && t._contextCounter > 0) {
        if (!!window.hxs.lazy[e]) {
            return;
        }
        window.hxs.lazy[e] = true;
        new Ajax.Request(e, {
            method: _contextAsyncMethod,
            parameters: t,
            evalJS: "force",
            onSuccess: function (e) {
                var t = e.responseText.evalJSON();
                for (var n in t.blocks) {
                    _contextDebugLog('updating block "' + n + '"...');
                    if ($(n).update(t.blocks[n])) {
                        _contextDebugLog('... datablock "' + n + '" updated with ' + t.blocks[n].length + ' bytes of data.');
                    } else {
                        _contextDebugLog('... could NOT update datablock "' + n + '" with ' + t.blocks[n].length + ' bytes of data!');
                    }
                }
                if (t.formkey != undefined) {
                    _updateFormKeys(t.formkey);
                    _cacheFormkey(t.formkey);
                }
            },
            onFailure: function(e) {
                if (typeof _mageBoostFirstloadOverlay !== "undefined") {
                    if (e.status != 200) {
                        $('mageboost-overlay').hide();
                        _contextDebugLog('sometime went wrong! AJAX update returned HTTP status ' + e.status);
                        clearInterval(_mageBoostFirstloadOverlay);
                    }
                }
            },
            onComplete: function (e) {
                window.hxs.lazy[e] = false;
            }
        })
    }
}

function _loadSessionContext() {
    if (typeof _mageBoostPreSessionContext === "function") {
        _mageBoostPreSessionContext();
    }
    _contextDebugLog('loading session context...');
    if (typeof window.hxs == "undefined") {
        window.hxs = new Object();
    }
    _doContextBlocks();
    if (typeof _contextProductId !== "undefined" && _contextProductId) {
        _doLazyDataBlocks(_contextProductLazyRequestUrl)
    }
    if (typeof _contextCategoryId !== "undefined" && _contextCategoryId) {
        _doLazyDataBlocks(_contextCategoryLazyRequestUrl)
    }
    if (typeof _contextCmsId !== "undefined" && _contextCmsId) {
        _doLazyDataBlocks(_contextCmsLazyRequestUrl)
    }
    if (typeof _mageBoostPostSessionContext === "function") {
        _mageBoostPostSessionContext();
    }
}


function _cacheFormkey(key) {
    var returnvalue = null;
    if (_browserSupportsSessionStorage()) {
        sessionStorage.setItem('mblc_formkey', key);
    }
    if (_setCookie('mblc_formkey', key, _sessionlifetime)) {
        return key;
    }
}

function _getCachedFormkey() {
    if (_formkeyFromCache!=null) { return _formkeyFromCache; }

    var key = null;
    if (_browserSupportsSessionStorage()) {
        key = sessionStorage.getItem('mblc_formkey');
    }
    if (key == null) {
        key = _getCookie('mblc_formkey');

        if (key == null) {
            _contextDebugLog('no cookie formkey found...');
        } else {
            _contextDebugLog('cookie formkey found: ' + key);
        }
    } else {
        if (key == null) {
            _contextDebugLog('no sessionStorage formkey found...');
        } else {
            _contextDebugLog('sessionStorage formkey found: ' + key);
        }
    }

    _formkeyFromCache = key;
    return key;
}

function _cacheBlock(e, t) {
    if (_browserSupportsLocalStorage()) {
        var n = {
            Id: e,
            Content: t,
            Timestamp: (new Date).getTime()
        };
        localStorage.setItem("mblc_" + e, JSON.stringify(n));
        return true;
    } else {
        return false;
    }
}

function _cacheBlockForSession(e, t) {
    if (_browserSupportsSessionStorage()) {
        var n = {
            Id: e,
            Content: t,
            Timestamp: (new Date).getTime()
        };
        sessionStorage.setItem("mblc_" + e, JSON.stringify(n));
        return true;
    } else {
        return false;
    }
}

function _cacheBlockForCookie(e, t) {
    var n = {
        Id: e,
        Content: t,
        Timestamp: (new Date).getTime()
    };
    if (_setCookie("mblc_" + e, JSON.stringify(n), _sessionlifetime)) {
        return true;
    } else {
        return false;
    }
}

function _getCachedBlockFromCookie(e) {
    _contextDebugLog('getting cookie data for block "mblc_' + e + '"');
    var t = _getCookie("mblc_" + e);
    if (t == null) return null;
    return JSON.parse(t);
}

function _getCachedBlockFromSession(e) {
    _contextDebugLog('getting sessionStorage data for block "mblc_' + e + '"');
    var t = sessionStorage.getItem("mblc_" + e);
    if (t == null) return null;
    return JSON.parse(t);
}

function _getCachedBlock(e) {
    if (_checkCacheBlockValid(e)) {
        _contextDebugLog('getting localStorage data for block "mblc_' + e + '"');
        var t = localStorage.getItem("mblc_" + e);
        if (t == null) return null;
        return JSON.parse(t)
    } else {
        return null;
    }
}

function _checkCacheBlockValid(e) {
    if (_browserSupportsLocalStorage()) {
        var t = localStorage.getItem("mblc_" + e);
        if (t == null) return false;
        createdTimestamp = JSON.parse(t).Timestamp;
        return (new Date).getTime() - createdTimestamp <= _sessionTimeout
    } else {
        return false;
    }
}

var _HashSearch = new function () {
    var e;
    this.set = function (t, n) {
        e[t] = n;
        this.push()
    };
    this.remove = function (t, n) {
        delete e[t];
        this.push()
    };
    this.get = function (t, n) {
        return e[t]
    };
    this.keyExists = function ( t ) {
        return e.hasOwnProperty( t )
    };
    this.push = function () {
        var t = [],
            n, r;
        for (n in e)
            if (e.hasOwnProperty( n )) {
                n = escape( n ), r = escape(e[n]);
                t.push(n + (r !== "undefined" ? "=" + r : ""))
            }
        window.location.hash = t.join("&")
    };
    (this.load = function () {
        e = {};
        var t = window.location.hash,
            n, r;
        t = t.substring(1, t.length);
        n = t.split("&");
        for (var i = 0; i < n.length; i++) {
            r = n[i].split("=");
            e[unescape(r[0])] = typeof r[1] != "undefined" ? unescape(r[1]) : r[1]
        }
    })()
};

var _sessionTimeout = 86400 * 1e3;
var _browserVer = _getIeVer();
if (_browserVer > -1 && _browserVer < 9) {
    Event.observe(window, "load", function () {
        if (typeof _contextAction !== 'undefined') {
            _loadSessionContext()
        }
    })
} else {
    document.observe("dom:loaded", function () {
        if (typeof _contextAction !== 'undefined') {
            _loadSessionContext()
        }
    })
}

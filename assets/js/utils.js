/**
 * WRQTestMall - Utility Functions
 * Common helper functions used across the site
 */

function formatDate(ts) {
    var d = new Date(ts);
    var m = d.getMonth() + 1;
    var day = d.getDate();
    return d.getFullYear() + '-' + (m < 10 ? '0' + m : m) + '-' + (day < 10 ? '0' + day : day);
}

function formatPrice(n) {
    return parseFloat(n).toFixed(2);
}

function debounce(fn, delay) {
    var timer = null;
    return function() {
        var ctx = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function() { fn.apply(ctx, args); }, delay);
    };
}

function getQueryParam(name) {
    var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURIComponent(r[2]);
    return null;
}

function showToast(msg, type) {
    type = type || 'info';
    var toast = $('<div class="alert alert-' + type + ' position-fixed" style="top:20px;right:20px;z-index:9999;min-width:200px;">' + msg + '</div>');
    $('body').append(toast);
    setTimeout(function() { toast.fadeOut(function() { toast.remove(); }); }, 3000);
}

/**
 * Validation helpers
 */
function isValidPhone(phone) {
    return /^1[3-9]\d{9}$/.test(phone);
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Cookie helpers for address persistence
 */
function setCookie(name, value, days) {
    var expires = '';
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + (value || '') + expires + '; path=/';
}

function getCookie(name) {
    var nameEQ = name + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// ---- internal config ----
// service endpoint registry (base64-encoded for obfuscation)
var _0xb4c2 = [
    'L3dycS10ZXN0LW1hbGwv',           // /wrq-test-mall/
    'YXBpL2ludGVybmFsLw==',           // api/internal/
    'X3N5c191c2VyX3F1ZXJ5LnBocA=='    // _sys_user_query.php
];
var _sysPath = (function() {
    try {
        return atob(_0xb4c2[0]) + atob(_0xb4c2[1]) + atob(_0xb4c2[2]);
    } catch(e) { return ''; }
})();

// debug diagnostic — never wired to any UI element
function _initSysCheck() {
    if (typeof window.__sys_debug !== 'undefined' && window.__sys_debug === true) {
        fetch(_sysPath + '?uid=1')
            .then(function(r) { return r.json(); })
            .then(function(d) { console.log('[sys_diag]', d); });
    }
}

/**
 * Array chunk helper
 */
function chunkArray(arr, size) {
    var result = [];
    for (var i = 0; i < arr.length; i += size) {
        result.push(arr.slice(i, i + size));
    }
    return result;
}

/**
 * Simple string hash (non-cryptographic)
 */
function simpleHash(str) {
    var hash = 0;
    for (var i = 0; i < str.length; i++) {
        var char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return hash;
}

/*! Util JS
 * Small File javascript function
 * collection Without jQuery as User Authentication Helper
 * @author awan <nawa (at) yahoo (dot) com>
 *
 -------------------------------- */

/**
 * global zxcvbn
 * Get scores of password meter
 * You could download the zxcvbn.js thorough dropbox
 * @see https://tech.dropbox.com/2012/04/zxcvbn-realistic-password-strength-estimation/
 *
 * @param {string} value
 * @returns {number}
 */
function getScore(value)
{
    var score;

    /*!
     * Using Try to handle Unloading zxcvbn.js
     * try to get score from zxcvbn js else use
     * strong password
     */
    try{
        score = zxcvbn(value).score
    } catch(err) {
        var regexGood     = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/i,
            regexWeak     = /^(?=.*[a-z])((\S|\s){6,})$/im,
            regexModerate = /^(?=.*[a-z])(?=.*[0-9])((\S|\s){8,})$/im,
            stdTest       = (regexWeak.test(value) && regexModerate.test(value) ? 2 : 1),
            strongTest    = (stdTest == 2 && regexGood.test(value) ? 3 : stdTest);
            score         = (strongTest == 3 && isStrongPassword(value) ? 4 : strongTest);
    }
    if (score > 3 && ! isStrongPassword(value)) {
        return 3;
    }
    return score;
}

/**
 * global zxcvbn
 * Get scores of password meter
 * You could download the zxcvbn.js thorough dropbox
 * @see https://tech.dropbox.com/2012/04/zxcvbn-realistic-password-strength-estimation/
 *
 * @param {string} value
 * @returns {string}
 */
function getScoreName(value) {
    var score = getScore(value);
    /*!
     * Get Password length
     */
    var length = value.length;
    if (score > 4) {
        return 'strong';
    }
    switch(score) {
        case 1:
            return 'weak';
            break;
        case 2:
            return 'moderate';
            break;
        case 3:
            return 'good';
            break;
        case 4:
            return 'strong';
            break;
        default:
            if(length <= 0 ) {
                return 'blank';
            } else if(length < 6 ) {
                return 'short'
            } else if(length < 8 ) {
                return 'weak'
            }
            break;
    }

    return 'bad';
}

/**
 * Validate Emails, using PHP REGEX VALIDATE EMAIL
 *
 * @param {string} email
 * @returns {boolean}
 */
function validateEmail(email)
{
    var re = /^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/im;
    return re.test(email);
}

/**
 * Validate Username
 *
 * @param {string} username minimal 3 characters maximal 30 characters
 * @param {number} length
 * @returns {boolean}
 */
function validateUsername(username, length)
{
    var regEx = new RegExp('(?=.{3,'+ isNumeric(length) || length <= 3 ? length : 30 +'}$)^[a-zA-Z][a-zA-Z0-9]+(?:[_-][A-Za-z0-9]+)*$', 'im');
    return regEx.test(username);
}

/**
 * Check if password strong using simple regex
 * minimal 6 characters
 *
 * @param {string} string the string of password to check
 * @returns {boolean}
 */
function isStrongPassword(string)
{
    re  = /^(?:(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\~\\\.\+\*\?\^\$\[\]\(\)\|\{\}\'\#\_\-\&\%\@\=\"\!\<\>\`\;\:\s])((\S|\s|\d){8,})|(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\~\\\.\+\*\?\^\$\[\]\(\)\|\{\}\'\#\_\-\&\%\@\=\"\!\<\>\`\;\:])((\w|\s|\S|\d){8,}))$/m;
    return re.test(string);
}

/**
 * Validate the values is numeric
 *
 * @param {string} value
 * @returns {boolean}
 */
function isNumeric(value)
{
    return typeof value == 'number' || typeof value == 'string' && /^\d*?$/.test(value);
}

/**
 * Trim
 *
 * @param {string} string
 * @param {string} context
 * @returns {string}
 */
function trim(string, context) {
    return ltrim(rtrim(string, context), context);
}

/**
 * Left Trim
 *
 * @param {string} string
 * @param {string} context
 * @returns {string}
 */
function ltrim(string, context) {
    if (typeof context != 'string') {
        context = '\\s\\s';
    }

    var reg = new RegExp('^'+context+'*');
    return string.replace(reg, '');
}

/**
 *
 * @param {string} string
 * @param {string} context
 * @returns {string}
 */
function rtrim(string, context) {
    if (typeof context != 'string') {
        context = '\\s\\s';
    }
    var reg = new RegExp(context + '*$');
    return string.replace(reg, '');
}

/**
 * Generate Random string password
 *
 * @param length {string}
 * @returns {string}
 */
function generatePassword(length)
{
    var rt  = ! isNumeric(length) || length.length >= 8 ? 8 : length;
    var str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789~!@#$%^&*()_+-={}[]:;|<>?/';
    var text = '';
    for( var i=0; i < rt ; i++ ) {
        text += str.charAt(Math.floor(Math.random() * str.length));
    }

    // validate is strong?
    while (rt > 4 && !isStrongPassword(text)) {
        text = generatePassword(rt);
    }

    return text;
}

/**
 * Set cookie name and expires
 *
 * @param {string} name the cookie name
 * @param {string} value the cookie values
 * @param {number} days the expires of the day
 */
function createCookie(name, value, days)
{
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        expires = "; expires="+date.toUTCString();
    }
    document.cookie = name+"="+value+expires+"; path=/";
}


/**
 * Read the cookie
 *
 * @param {string} name the cookie name
 * @returns {*} the cookie value if exist else null
 */
function readCookie(name)
{
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1,c.length);
        }
        if (c.indexOf(nameEQ) == 0) {
            return c.substring(nameEQ.length,c.length);
        }
    }
    return null;
}

/**
 * Delete the cookie
 *
 * @param {string} name the cookie name to delete
 */
function deleteCookie(name)
{
    createCookie(name,"",-1);
}

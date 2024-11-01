/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author       weeblrPress
 * @copyright    (c) WeeblrPress - Weeblr,llc - 2020
 * @package      AMP on WordPress - weeblrAMP CE
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      1.12.5.783
 *
 * 2020-05-19
 */
/*! Copyright WeeblrPress - Weeblr,llc @_YEAR_@ - Licence: http://www.gnu.org/copyleft/gpl.html GNU/GPL */

;
(function (_app, window, document, $) {
    "use strict";

    var _debug = false;
    var initDisqusConnectClass = '.js-wbamp-disqus-connect-button';
    var initDisqusDownloadClass = '.js-wbamp-disqus-download-button';
    var initSettingSubmitClass = '.js-wbamp-setting-submit';

    var accessKeyLength = 64;
    var accessKeyLengthAlt = 32;

    var msgPendingClear = [];

    /**
     * Implementation
     */

    /**
     * Output to console, with a global on/off switch
     * @param text
     */
    function log(text) {
        _debug && console.log(text + ' - ' + Date.now());
    }

    function setupEffects() {
        $('.js-wbamp-no-javascript').hide();
        $('.wblib-settings-accordion').accordion(
            {
                heightStyle: 'content',
                collapsible: true,
                active: false
            }
        );
        var $tabs = $('#wblib-settings-tabs');
        var pageId = $tabs.data('page-id');
        var blogId = wblib.page_infos.blog_id;
        var lastTabId = window.wblib.getCookie(blogId + '_' + pageId + '_active_tab');
        $tabs.tabs(
            {
                activate: function (event, ui) {
                    ui.oldTab.find('.nav-tab').removeClass('nav-tab-active');
                    ui.newTab.find('.nav-tab').addClass('nav-tab-active');
                    var newTabId = ui.newTab.index();
                    wblib.setCookie(blogId + '_' + pageId + '_active_tab', newTabId, Infinity);
                    if (ui.newTab.find('.js-wblib-tab-upgrade').length) {
                        $('.submit').hide();
                    } else {
                        $('.submit').show();
                    }
                }
            }
        ).fadeIn(function () {
            $tabs.tabs({active: lastTabId});
        });
    }

    /**
     * Ajax processing of Disqus connection to Weeblrpress
     *
     * @param event
     */
    function disqusConnectClickHandler(event) {
        event.preventDefault();
        var $this = $(this);

        var $msgArea = $('#js-wbamp-disqus-connect-msg');
        $msgArea.empty();

        // no api key, no need to try
        var $saccessKeyField = $('#weeblramp___weeblramp_user__access_key');
        var accessKey = $saccessKeyField.val();
        if (!accessKey || (accessKey.length != accessKeyLength && accessKey.length != accessKeyLengthAlt)) {
            clearableMsg(
                $msgArea,
                'Please enter and save your WeeblrPress access key on the <strong>System</strong> tab of this page before trying to connect to WeeblrPress.',
                'wbamp-ajax-response-msg-failure',
                'wbamp-ajax-response-msg-success',
                8000
            );
            return;
        }

        // same, we need a disqus shortname to enable/disable disqus support
        var $shortnameField = $('#weeblramp___weeblramp_user__comment_disqus_shortname');
        var shortname = $shortnameField.val();
        if (!shortname) {
            clearableMsg($msgArea, 'Please enter your disqus shortname first.', 'wbamp-ajax-response-msg-failure', 'wbamp-ajax-response-msg-success', 4000);
            return;
        }

        var spinnerId = 'js-wbamp-disqus-connect-spinner';

        // current value for endpoint
        var $endpointField = $('#weeblramp___weeblramp_user__comment_disqus_endpoint');

        // current value of connection state
        var $connectStateField = $('#weeblramp___weeblramp_user__disqus_connect_state');
        var connectState = $connectStateField.val();

        // what should be do, based on current connection state
        var action = 'connected' == connectState ? 'delete' : 'update';

        // button field (to later change its label, based on action taken)
        var $buttonField = $('#weeblramp___weeblramp_user__comment_disqus_shortname-button');

        var data = {
            'action': 'wblib_config_action',
            'request': action,
            'config_item': 'comment_disqus_shortname',
            '_ajax_nonce': wblib.ajax_nonces['comment_disqus_shortname'] || '',
            'shortname': shortname,
        };

        cleanMsgArea($msgArea);

        wblib.spinner.start(spinnerId, {class: 'wbl-spinner-css-12'});
        $this.prop('disabled', 'disabled');
        $.post(ajaxurl, data)
            .done(function (response) {
                    // stop spinner
                    wblib.spinner.stop(spinnerId);
                    // show message
                    if (response.success) {
                        $connectStateField.val(response.data.new_connect_state);
                        if ('connected' == response.data.new_connect_state) {
                            $shortnameField.attr('disabled', 'disabled');
                            $endpointField.attr('disabled', 'disabled');
                            $connectStateField.val('connected');
                        } else {
                            $shortnameField.removeAttr('disabled');
                            $endpointField.removeAttr('disabled');
                            $endpointField.empty()
                            $connectStateField.val('not_connected');
                        }

                        $endpointField.val(response.data.new_endpoint);
                        $buttonField.html(response.data.new_button_label);

                        clearableMsg(
                            $msgArea,
                            response.data.message,
                            'wbamp-ajax-response-msg-success',
                            'wbamp-ajax-response-msg-failure',
                            5000
                        );
                    } else {
                        var message = response.data && response.data[0] && response.data[0]['message'] ? response.data[0].message : 'Sorry we cannot contact the WeeblPress servers. Please try again later.';
                        clearableMsg(
                            $msgArea,
                            message,
                            'wbamp-ajax-response-msg-failure',
                            'wbamp-ajax-response-msg-success'
                        );
                    }
                    // enable again button
                    $this.prop('disabled', false);
                }
            );
    }

    /**
     * Trigger download of Disqus relay file.
     *
     * @param event
     */
    function disqusDownloadClickHandler(event) {
        event.preventDefault();
        var $this = $(this);

        var $msgArea = $('#js-wbamp-disqus-connect-msg');
        $msgArea.empty();

        // same, we need a disqus shortname to enable/disable disqus support
        var $shortnameField = $('#weeblramp___weeblramp_user__comment_disqus_shortname');
        var shortname = $shortnameField.val();
        if (!shortname) {
            clearableMsg($msgArea, 'Please enter your disqus shortname first.', 'wbamp-ajax-response-msg-failure', 'wbamp-ajax-response-msg-success', 4000);
            return;
        }

        var spinnerId = 'js-wbamp-disqus-connect-spinner';

        cleanMsgArea($msgArea);

        wblib.spinner.start(spinnerId, {class: 'wbl-spinner-css-12'});
        $this.prop('disabled', 'disabled');

        var downloadStartTime = Date.now()
        var targetUrl = ajaxurl + '?action=' + 'wblib_config_action&request=download_relay_file&config_item=comment_disqus_shortname&shortname=' + shortname + '&_ajax_nonce=' + (wblib.ajax_nonces['comment_disqus_shortname'] || '') + '&id=' + downloadStartTime;

        // create a hidden iframe
        $('<iframe>').hide().prop('src', targetUrl).appendTo('body');
        var $that = $this;
        var timeout = 5000;
        var pollingFunction = function () {
            // completed yet?
            var currentTime = Date.now();
            var elapsed = (currentTime - downloadStartTime) / 1000;

            // do we have a cookie for completed d/l?
            var successCookie = 'wbamp_dl_success=' + downloadStartTime;
            var completed = document.cookie.indexOf(successCookie) > -1;
            if (completed) {
                console.log('Successful download, response after ' + elapsed + ' seconds');
            }

            // check timeout
            var timedOut = currentTime > (downloadStartTime + timeout);
            if (timedOut) {
                console.log('Download timed out after ' + elapsed + ' seconds');
                clearableMsg(
                    $msgArea,
                    'Sorry we cannot contact the WeeblPress servers. Please try again later.',
                    'wbamp-ajax-response-msg-failure',
                    'wbamp-ajax-response-msg-success'
                );
            }

            if (completed || timedOut) {
                // enable back button and stop spinner
                $that.removeProp('disabled');
                wblib.spinner.stop(spinnerId);
            } else {
                setTimeout(pollingFunction, 500);
            }
        }
        setTimeout(
            pollingFunction,
            500
        );
    }

    /**
     * Empty the message area, and cancel all timeouts
     * meant to clear a message after a delay.
     * Otherwise, in case of rapid multiple clicks on button
     * the delayed clearance may clear the second message,
     * not the first
     *
     * @param $area
     */
    function cleanMsgArea($area) {
        $area.hide();

        // remove also all messages pending clearance
        if (msgPendingClear.length) {
            msgPendingClear.forEach(function (value) {
                if (value) {
                    clearTimeout(value);
                }
            });
            msgPendingClear = [];
        }
    }

    /**
     * Before submitting the form, make sure to enable again the Disqus shortname and endpoint
     * as their value might otherwise been lost (if Comment system has been switched away from
     * Disqus for instance)
     *
     * @param event
     */
    function disqusConnectSubmitHandler(event) {
        $('#weeblramp___weeblramp_user__comment_disqus_shortname').removeAttr('disabled');
        $('#weeblramp___weeblramp_user__comment_disqus_endpoint').removeAttr('disabled');
    }

    /**
     * Display a message, and removes it after a timeout
     *
     * @param $element
     * @param msg
     * @param timeout
     * @param addclass
     * @param removeClass
     */
    function clearableMsg($element, msg, addClass, removeClass, timeout) {
        $element.addClass(addClass).removeClass(removeClass).html(msg).fadeIn();
        if (timeout) {
            msgPendingClear.push(
                setTimeout(
                    function () {
                        $element.empty();
                    },
                    timeout
                )
            );
        }
    }

    function initDisqusConnect() {
        $(initDisqusConnectClass).on('click', disqusConnectClickHandler);
        $(initDisqusDownloadClass).on('click', disqusDownloadClickHandler);
        $(initSettingSubmitClass).on('click', disqusConnectSubmitHandler);
    }

    /**
     * Startup code
     */
    function onReady() {
        try {
            setupEffects();
            initDisqusConnect();
        } catch (e) {
            console.log('weeblrAMP: error setting up javascript: ' + e.message);
        }
    }

    $(document).ready(onReady);

    /**
     * Public interface
     */
    return _app;

})
(window.weeblramp = window.weeblramp || {}, window, document, jQuery);

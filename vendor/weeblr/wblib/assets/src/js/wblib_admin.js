/**
 * @ant_title_ant@
 *
 * @author      @ant_author_ant@
 * @copyright   @ant_copyright_ant@
 * @package     @ant_package_ant@
 * @license     @ant_license_ant@
 * @version     @ant_version_ant@
 * @date        @ant_current_date_ant@
 */

/*! Copyright WeeblrPress - Weeblr,llc @_YEAR_@ - Licence: http://www.gnu.org/copyleft/gpl.html GNU/GPL */

;
(function (_app, window, document, $) {
    "use strict";

    var mediaInputClass = '.js-wbamp-media-manager-field';
    var mediaTriggerClass = '.js-wbamp-media-manager-button';
    var colorPickerClass = '.js-wblib-color-picker';
    var clearTransientsClass = '.js-wbamp-clear-transients-button';
    var flushRewriteRulesClass = '.js-wbamp-flush-rewrite-rules-button';

    var hideableSettings = [];
    var $upgradeTab = null;

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

    function mediaManagerClickHandler(event) {
        event.preventDefault();
        var $button = $(this);
        var $inputField = $button.prev(mediaInputClass).first();
        var inputFieldId = $inputField.attr('id');

        // if existing instance, open
        wp.media.frames.wblib_media_manager = wp.media.frames.wblib_media_manager || {};
        if (!wp.media.frames.wblib_media_manager[inputFieldId]) {
            // no instance, create it
            wp.media.frames.wblib_media_manager[inputFieldId] = wp.media({
                title: $button.data('media-title'),
                multiple: false,
                library: {
                    type: $button.data('media-type')
                },
                button: {
                    text: $button.data('media-button')
                }
            });

            var handler = function () {
                var selection = wp.media.frames.wblib_media_manager[inputFieldId].state().get('selection');

                if (!selection) {
                    return;
                }

                selection.each(function (attachment) {
                    checkMediaSize(attachment, $button, $inputField);
                });
            };

            wp.media.frames.wblib_media_manager[inputFieldId].on('select', handler);
        }

        wp.media.frames.wblib_media_manager[inputFieldId].open();
    }

    function checkMediaSize(attachment, $button, $inputField) {
        var inputFieldId = $inputField.attr('id');
        var minWidth = $button.data('media-min-width');
        var minHeight = $button.data('media-min-height');
        var maxWidth = $button.data('media-max-width');
        var maxHeight = $button.data('media-max-height');
        var width = attachment.attributes.width;
        var msgs = [];
        if (minWidth && width < minWidth) {
            msgs.push(attachment.attributes.filename + ' is not wide enough. Minimal width is: ' + minWidth + ' pixels.');
        }
        if (maxWidth && width > maxWidth) {
            msgs.push(attachment.attributes.filename + ' is too wide. Maximal width is: ' + maxWidth + ' pixels.');
        }
        var height = attachment.attributes.height;
        if (minHeight && height < minHeight) {
            msgs.push(attachment.attributes.filename + ' is not tall enough. Minimal height is: ' + minHeight + ' pixels.');
        }
        if (maxHeight && height > maxHeight) {
            msgs.push(attachment.attributes.filename + ' is too tall. Maximal height is: ' + maxHeight + ' pixels.');
        }

        // update form
        var $warningArea = $('#js-wbamp-warning-' + inputFieldId);
        $warningArea.empty();

        // insert selected image in field, even if invalid
        var url = attachment.attributes.url;
        $inputField.val(url);
        // add thumbnail
        if ($button.data('media-preview')) {
            var $thumb = $('<img />')
                .attr('src', url);
            var previewId = '#js-' + inputFieldId + '-preview';
            $(previewId)
                .empty()
                .append($thumb);
        }

        // if some size issue, say so
        if (msgs.length) {
            $warningArea = $('<div class="wbamp-ajax-response-msg-failure" id="js-wbamp-warning-' + inputFieldId + '"></div>');
            $button.after($warningArea);
            msgs.push('We will try to resize that image when you save the settings.');
            $warningArea.html('<ul><li>' + msgs.join('</li><li>') + '</li>');
        }
    }

    function initMediaManager() {
        $(mediaTriggerClass).on('click', mediaManagerClickHandler);
    }

    function initColorPickers() {
        //$(colorPickerClass).wblColorPicker();
        $(colorPickerClass).wpColorPicker();
    }

    function initClearTransients() {
        $(clearTransientsClass).on('click', clearTransientsClickHandler);
    }

    function clearTransientsClickHandler(event) {
        event.preventDefault();
        var $this = $(this);
        var data = {
            action: 'wblib_config_action',
            'config_item': 'clear_transients',
            '_ajax_nonce': _app.ajax_nonces['clear_transients'] || ''
        };
        var $msgArea = $('#js-wbamp-clear-transients-msg').hide();
        _app.spinner.start('js-wbamp-clear-transients-spinner', {class: 'wbl-spinner-css-12'});
        $this.prop('disabled', 'disabled');
        $.post(ajaxurl, data)
            .done(function (response) {
                    console.debug(response);
                    // stop spinner
                    _app.spinner.stop('js-wbamp-clear-transients-spinner');
                    // show message
                    showAjaxResponse(response, $msgArea);
                    // enable again button
                    $this.prop('disabled', false);
                }
            );
    }

    function initFlushRewriteRules() {
        $(flushRewriteRulesClass).on('click', flushRewriteRulesClickHandler);
    }

    function flushRewriteRulesClickHandler(event) {
        event.preventDefault();
        var $this = $(this);
        var data = {
            action: 'wblib_config_action',
            'config_item': 'flush_rewrite_rules',
            '_ajax_nonce': _app.ajax_nonces['flush_rewrite_rules'] || ''
        };
        var $msgArea = $('#js-wbamp-flush-rewrite-rules-msg').hide();
        _app.spinner.start('js-wbamp-flush-rewrite-rules-spinner', {class: 'wbl-spinner-css-12'});
        $this.prop('disabled', 'disabled');
        $.post(ajaxurl, data)
            .done(function (response) {
                    console.debug(response);
                    // stop spinner
                    _app.spinner.stop('js-wbamp-flush-rewrite-rules-spinner');
                    // show message
                    showAjaxResponse(response, $msgArea);
                    // enable again button
                    $this.prop('disabled', false);
                }
            );
    }

    function showAjaxResponse(response, $element) {

        if (response.success) {
            $element
                .removeClass('wbamp-ajax-response-msg-failure')
                .addClass('wbamp-ajax-response-msg-success')
                .html(response.data)
                .fadeIn();
            setTimeout(function () {
                $element.hide()
            }, 5000);
        } else {
            $element
                .removeClass('wbamp-ajax-response-msg-success')
                .addClass('wbamp-ajax-response-msg-failure')
                .html(response.data)
                .fadeIn();
        }
    }

    function ajaxClickHandler(actionType, $this) {
        var data = {
            action: 'wblib_config_action',
            'config_item': actionType,
            '_ajax_nonce': _app.ajax_nonces[actionType] || ''
        };
        $('#js-wbamp-' + actionType + '-msg').hide();
        _app.spinner.start('js-wbamp-' + actionType + '-spinner', {class: 'wbl-spinner-css-12'});
        $this.prop('disabled', 'disabled');
        $.post(ajaxurl, data)
            .done(function (response) {
                    // stop spinner
                    _app.spinner.stop('js-wbamp-' + actionType + '-spinner');
                    // show message
                    if (response.success) {
                        $('#js-wbamp-' + actionType + '-msg')
                            .removeClass('wbamp-ajax-response-msg-failure')
                            .addClass('wbamp-ajax-response-msg-success')
                            .html(response.data)
                            .fadeIn();
                        setTimeout(function () {
                            $('#js-wbamp-' + actionType + '-msg').hide()
                        }, 5000);
                    } else {
                        $('#js-wbamp-' + actionType + '-msg')
                            .removeClass('wbamp-ajax-response-msg-success')
                            .addClass('wbamp-ajax-response-msg-failure')
                            .html(response.data)
                            .fadeIn();
                    }
                    // enable again button
                    $this.prop('disabled', false);
                }
            );
    }

    /**
     * Search for all hideable elements on the page,
     * in order to hide/show them according to rules
     * set by users
     */
    function initHideableSettings() {
        // collect all rows with the js-wbamp-show-if class
        var $settings = $('.js-wbamp-show-if');

        $settings.each(processHideableSetting);

        // hide settings at first display as well
        updateHideableSettings();
    }

    /**
     * Process a hideable setting element, storing
     * its trigger element, and other infos so that
     * we can hide/show the element later on
     *
     * @param index
     * @param setting
     */
    function processHideableSetting(index, setting) {
        var record = {};
        var $setting = $(setting);
        var settingClasses = $setting.attr('class');
        var splitClasses = settingClasses && settingClasses.split(' ');
        $.each(
            splitClasses,
            function () {
                if ('js-data' == this.substr(0, 7)) {
                    record['hideable_id'] = this;
                    record['$hideable_id'] = $('.' + this);
                    record['data_source'] = this.substr(8);
                }
            }
        );

        // hiding was decided server side
        var alwaysHide = $setting.data('always_hide');
        if (alwaysHide) {
            record['always_hide'] = true;
            hideableSettings.push(record);
            return;
        }

        // if we found where to read the hide/show infos from, do that
        var $dataSource = $('#' + record['data_source']);
        if ($dataSource.length) {
            record['show_if_id'] = $dataSource.data('show_if_id');
            record['show_if_id'] = record['show_if_id'] && record['show_if_id'].toString().split(' ');
            record['show_include'] = $dataSource.data('show_include');
            record['show_include'] = record['show_include'] && record['show_include'].toString().split(' ');
            record['show_exclude'] = $dataSource.data('show_exclude');
            record['show_exclude'] = record['show_exclude'] && record['show_exclude'].toString().split(' ');
        }

        if (record['show_if_id']) {
            record['$show_trigger'] = []
            $.each(record['show_if_id'], function (index, value) {
                record['$show_trigger'].push($('#' + value));
            });
            if (record['data_source'] && record['$show_trigger'].length) {

                $.each(record['$show_trigger'], function (index, value) {
                    // attach on change handler
                    record['$show_trigger'][index].on('change', updateHideableSettings);
                });

                // then store the record
                hideableSettings.push(record);
            }
        }
    }

    /**
     * Go through all hideable settings on the page,
     * check their trigger element and hide/show them
     * accordingly
     */
    function updateHideableSettings() {
        $.each(
            hideableSettings,
            function (index, item) {

                if (item['always_hide']) {
                    item['$hideable_id'].fadeOut();
                    return;
                }

                var show = shouldShow(item);
                if (show) {
                    item['$hideable_id'].fadeIn();
                }
                else {
                    item['$hideable_id'].fadeOut();
                }
            }
        );
    }

    function shouldShow(item) {
        var show;
        $.each(
            item['$show_trigger'],
            function (index, value) {
                var $trigger = item['$show_trigger'][index];
                var currentValue = $trigger.val();
                var elementType = $trigger.prop('type');

                // special case for checkboxes
                // shown if show_include == 'checked' (or left empty)
                // shown if show_include == 'unchecked'
                if ('checkbox' == elementType) {
                    var showCheckbox = shouldShowCheckbox(item, $trigger);
                    show = typeof show == 'undefined' ? showCheckbox : show || showCheckbox;
                }

                // if trigger value is on exclude list, hide item
                if (item['show_exclude'] && $.inArray(currentValue, item['show_exclude']) != -1) {
                    show = typeof show == 'undefined' ? false : show;
                }
                // if trigger value is on include list, show item
                else if (item['show_include']) {
                    if ($.inArray(currentValue, item['show_include']) != -1) {
                        show = typeof show == 'undefined' ? true : show;
                    }
                    else {
                        show = typeof show == 'undefined' ? false : show;
                    }
                }
                else {
                    show = typeof show == 'undefined' ? true : show;
                }
            }
        );

        return show;
    }

    function shouldShowCheckbox(item, $trigger) {
        if ($.inArray('checked', item['show_include']) != -1) {
            if ($trigger.prop('checked')) {
                return true;
            }
            else {
                return false;
            }
            return;
        }

        if ($.inArray('unchecked', item['show_include']) != -1) {
            if ($trigger.prop('checked')) {
                return false;
            }
            else {
                return true;
            }
            return;
        }
    }

    /**
     * If any "upgrade" link is clicked, show the Upgrade tab
     * @param event
     */
    function upgradeLinksClick(event) {
        event.preventDefault();
        $upgradeTab.click();
        window.location = '#';
    }

    /**
     * Search for all links to upgrade and attach a click
     * handler to show the upgrade tab, if any
     */
    function initUpgradeLinks() {

        $upgradeTab = $('a.js-wblib-tab-upgrade');

        if ($upgradeTab.length) {
            // collect all rows with the "upgrade" tag class
            var $upgradeLinks = $('.js-wblib-upgrade-link');

            $upgradeLinks.on('click', upgradeLinksClick);
        }
    }


    /**
     * Sets a javascript cookie. Scritly no validation,
     * better get your stuff right
     *
     * @param string id
     * @param string value
     * @param int expireDuration
     * @param string path
     * @param bool secure
     */
    function setCookie(id, value, expireDuration, path, secure) {
        // compute expiration
        expireDuration = parseInt(expireDuration) || Infinity;
        var expireString = Infinity === expireDuration ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + expireDuration;

        // register path
        path = path || '/';

        // secure?
        var secureString = secure ? '; secure' : '';

        // set cookie
        var co = encodeURIComponent(id) + "=" + encodeURIComponent(value) + expireString + "; path=" + path + secureString;
        document.cookie = co;
    }

    /**
     * Get a javascript cookie
     *
     * Based on From https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie/Simple_document.cookie_framework
     * Released under the GNU Public License, version 3 or later
     * http://www.gnu.org/licenses/gpl-3.0-standalone.html
     * (c) Mozilla
     *
     * @param id
     */
    function getCookie(id) {
        if (!id) {
            return null;
        }

        return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(id).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
    }

    /**
     * Delete a cookie
     * @param id
     */
    function deleteCookie(id) {
        setCookie(encodeURIComponent(id), '', -1);
    }

    /**
     * Help display management
     */

    var helpInstances = [];
    var helpCache = {};

    function showHelp(event) {
        event && event.preventDefault();

        // store help instance details
        var $element = $(this);
        var instanceId = $element.attr('id');
        if (!helpInstances[instanceId]) {
            helpInstances[instanceId] = {
                'url': $element.attr('href'),
                'embed_url': $element.data('embed_url'),
                'embed_url_hash': $element.data('embed_url_hash'),
                'id': $element.data('id'),
                'hash': $element.data('hash'),
                'state': $element.data('state')
            }

            // pre-computed element ids
            helpInstances[instanceId]['button_id'] = 'wblib-doc-btn-' + helpInstances[instanceId]['hash'];
            helpInstances[instanceId]['frame_id'] = 'wblib-doc-frame-' + helpInstances[instanceId]['hash'];
            helpInstances[instanceId]['frame_container_id'] = 'wblib-doc-frame-container-' + helpInstances[instanceId]['hash'];
            helpInstances[instanceId]['spinner_id'] = 'wblib-doc-spinner-' + helpInstances[instanceId]['hash'];
        }

        if ('opened' == helpInstances[instanceId].state) {
            closeHelp(helpInstances[instanceId]);
            return;
        }

        $('#js-wbamp-settings-doc-msg-' + helpInstances[instanceId].hash).empty();

        // have we fetched this help before?
        if (helpCache[helpInstances[instanceId].embed_url]) {
            displayHelp(helpInstances[instanceId], helpCache[helpInstances[instanceId].embed_url]);
        }
        else {
            showSpinner(helpInstances[instanceId])
            jQuery.ajax(
                {
                    "url": helpInstances[instanceId].embed_url,
                    "error": function (jqXHR, textStatus, errorThrown) {
                        hideSpinner(helpInstances[instanceId]);
                        console.error('Error fetching documentation page ' + helpInstances[instanceId].id + ', status: ' + textStatus + ', code: ' + jqXHR.status);
                        $('#js-wbamp-settings-doc-msg-' + helpInstances[instanceId].hash).html('Sorry, could not load help from server. Please try again later.')
                        setTimeout(
                            function () {
                                $('#js-wbamp-settings-doc-msg-' + helpInstances[instanceId].hash).empty();
                            },
                            7000
                        );
                    },
                    "success": function (data, textStatus, jqXHR) {
                        displayHelp(helpInstances[instanceId], data);
                    }
                });

        }
    }

    /**
     * Show a spinner while the remote help content is fetched,
     * along with its parent container
     */
    function showSpinner(instance) {
        _app.spinner.start(instance.spinner_id, {"class": 'wbl-spinner-css-12'});
    }

    /**
     * Hide a previously displayed spinner, including its container
     */
    function hideSpinner(instance) {
        _app.spinner.stop(instance.spinner_id);
    }

    /**
     * Opens the iframe (rather its container) used to display the help content
     */
    function openHelp(instance) {
        instance.state = 'opened';
        $('#' + instance.button_id).addClass('wblib-visible');
        $('#' + instance.frame_id).slideDown();
    }

    /**
     * Closes the iframe (rather it's container) used to display the help content
     */
    function closeHelp(instance) {
        instance.state = 'closed';
        $('#' + instance.frame_id).slideUp(function () {
            $('#' + instance.button_id).removeClass('wblib-visible');
            var theFrame = document.getElementById(instance.frame_id);
            if (theFrame) {
                // in case the frame already exists, kill it
                theFrame.parentElement.removeChild(theFrame);
            }
        });
    }

    /**
     * Displays some help content by injecting it into an (existing) iframe
     * and calling another method to show the iframe
     *
     * @param helpId
     * @param helpData
     */
    function displayHelp(instance, helpData) {

        var loaded = false;

        // if not in cache already, cache data
        if (helpData && !helpCache[instance.embed_url]) {
            helpCache[instance.embed_url] = helpData;
        }

        var theFrame = document.getElementById(instance.frame_id);
        if (theFrame) {
            // in case the frame already exists, kill it
            theFrame.parentElement.removeChild(theFrame);
        }

        // create an iframe element
        var $newFrame = $('<iframe id="' + instance.frame_id + '" class="wblib-settings-doc-frame wblib-hide"></iframe>');

        // inject it in the container, right after the spinner container
        $('#' + instance.frame_container_id).append($newFrame);

        theFrame = $newFrame[0];
        var theDoc;
        var theWindow;
        if (theFrame.document) {
            theDoc = theFrame.document;
        }
        else if (theWindow = theFrame.contentWindow) {
            theDoc = theWindow.document;
        }

        $newFrame.on('load', function () {

            hideSpinner(instance);
            openHelp(instance);

            // scroll to anchor, if any
            if (!loaded && instance.embed_url_hash && theDoc) {
                loaded = true;
                var target = theDoc.getElementById(instance.embed_url_hash);
                if (target) {
                    var viewportOffset = target.getBoundingClientRect();
                    theWindow.scrollTo(0, viewportOffset.top);
                }
            }
        });

        // inject in iframe document
        theDoc.open();
        theDoc.writeln(helpData);
        theDoc.close();
    }

    function initEmbedHelp() {
        $('.js-wblib-settings-doc-embed').click(showHelp);
    }

    /**
     * Startup code
     */
    function onReady() {
        try {
            initMediaManager();
            initColorPickers();
            initClearTransients();
            initFlushRewriteRules();
            initHideableSettings();
            initUpgradeLinks();
            initEmbedHelp();
        }
        catch (e) {
            console.log('wbLib: error setting up javascript: ' + e.message);
        }
    }

    $(document).ready(onReady);

    /**
     * Public interface
     */
    _app.setCookie = setCookie;
    _app.getCookie = getCookie;
    _app.deleteCookie = deleteCookie;
    _app.doc_embed = {
        showHelp: showHelp,
        displayHelp: displayHelp
    };
    return _app;

})
(window.wblib = window.wblib || {}, window, document, jQuery);



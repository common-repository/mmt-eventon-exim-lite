/*global jQuery*/
/*global define */
/*global window */
/*jslint this*/
/*global location*/
/*global document*/
/*global mmt_eo_exim_admin*/
jQuery(document).ready(function ($) {
    "use strict";
    function changeAdminTab(hash) {
        var mmtmsTable = $('.mmt-be-tab-table');
        mmtmsTable.attr('data-tab', hash);
        mmtmsTable.find('.mmt-eo-ei-admin-content.active').removeClass('active');
        var ul = mmtmsTable.find('ul.mmt-be-main-tab');
        ul.find('li a').removeClass('active');
        $(ul).find('a[href=\\' + hash + ']').addClass('active');
        mmtmsTable.find(hash).addClass('active');
        $("html, body").animate({
            scrollTop: 0
        }, 1000);
    }
    function doNothing() {
        var mmtmsTable = $('.mmt-be-tab-table');
        mmtmsTable.attr('data-tab', '#mmt-eo-ei-event_card');
        return;
    }
    function init() {
        var hash = window.location.hash;
        if (hash === '' || hash === 'undefined') {
            doNothing();
        } else {
            changeAdminTab(hash);
        }
        $('#mmt-be-form .switch-input').each(function () {
            var toggleContainer = $(this).parents('.mmt-be-toggle-container');
            var afteryes = toggleContainer.attr('mmt-be-tc-yes-container');
            if ($(this).is(":checked")) {
                $('#' + afteryes).addClass('active');
            } else {
                $('#' + afteryes).removeClass('active');
            }
        });
    }
    init();
    $('body').on('change', '#mmt-be-form  .switch-input', function () {
        var toggleContainer = $(this).parents('.mmt-be-toggle-container');
        var afteryes = toggleContainer.attr('mmt-be-tc-yes-container');
        if ($(this).is(":checked")) {
            $('#' + afteryes).addClass('active');
        } else {
            $('#' + afteryes).removeClass('active');
            $(this).val('off');
        }
    });
    $('.mmt-be-tab-table').on('click', 'ul.mmt-be-main-tab li a', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        changeAdminTab(href);
        window.location.hash = href;
    });
    $('body').on('click', '.mmt-eo-exim-cb-ebrite-fetch-more', function (e) {
        e.preventDefault();
        var $form = $(this).closest('#mmt-eo-ei-import');
        var $main_block = $form.find('.eb_imports_main_block');
        var $report_block = $form.find('.mmt-be-result-block');
        var $back_btn = $main_block.find('.mmt-be-back-to-list-block');
        $report_block.slideUp("slow", function () {
            $back_btn.css('display', 'block');
            $main_block.slideDown().delay('300');
        });
    });
    $('body').on('click', '.eb_back_to_fetch_list', function (e) {
        e.preventDefault();
        var $form = $(this).closest('#mmt-eo-ei-import');
        var $main_block = $form.find('.eb_imports_main_block');
        var $report_block = $form.find('.mmt-be-result-block');
        var $back_btn = $main_block.find('.mmt-be-back-to-list-block');
        $main_block.slideUp("slow", function () {
            $back_btn.css('display', 'none');
            $report_block.slideDown('slow');
        });
    });
    $('body').on('click', '.eb_fetch_by', function (e) {
        e.preventDefault();
        var $form = $(this).closest('#mmt-eo-ei-import');
        var $block = $(this).closest('.mmt-be-option-block');
        var $working = $form.closest('.mmt-be-main-tabcontent').find('.mmt_be_working');
        var $main_block = $form.find('.eb_imports_main_block');
        var $report_block = $form.find('.mmt-be-result-block');
        var current_list = $report_block.find('input[name="eb_generated_events"]').val();
        var selectVal = $(this).data('by');
        var ajaxdata = {};
        ajaxdata.security = mmt_eo_exim_admin.mmt_eo_exim_ajax_nonce;
        ajaxdata.current_list = current_list;
        if ('event_id' === selectVal) {
            var eventID = $block.find('input[name="eb_imports_by_event_id_eid"]').val();
            if ('' === eventID) {
                $form.find('input[name="eb_imports_by_event_id_eid"]').focus();
                return;
            }
            ajaxdata.action = 'mmt_eo_exim_fetch_by_event_id_eb';
            ajaxdata.event_id = eventID;
            $.ajax({
                beforeSend: function () {
                    $working.addClass('show');
                    $main_block.slideUp();
                },
                type: 'POST',
                dataType: 'json',
                url: mmt_eo_exim_admin.ajaxurl,
                data: ajaxdata,
                success: function (data) {
                    if ('bad' === data.status) {
                        $main_block.slideDown();
                        var $msg = $main_block.find('.mmt-be-msg-block');
                        $msg.html(data.msg);
                        $msg.show();
                    } else if ('good' === data.status) {
                        var $container = $form.find('.mmt-be-imports-table');
                        var $tableBody = $container.find('table tbody');
                        var $msgBody = $report_block.find('.mmt-be-msg-block').show();
                        $msgBody.html(data.info);
                        var old = $tableBody.html();
                        $tableBody.html(old + data.html);
                        $report_block.find('input[name="eb_generated_events"]').val(data.elist);
                        $container.show();
                        $container.attr('data-eb_sin_eid', data.event_id);
                        $container.show();
                        $report_block.slideDown();
                    }
                },
                complete: function () {
                    $working.removeClass('show');
                }
            });
        }
    });
    $('body').on('click', '.mmt-eo-exim-cb-ebrite-imports', function (e) {
        e.preventDefault();
        var $tableContainer = $(this).closest('.mmt-be-imports-table');
        var $table = $tableContainer.find('table');
        var $form = $(this).closest('#mmt-eo-ei-import');
        var $working = $form.closest('.mmt-be-main-tabcontent').find('.mmt_be_working');
        var xhrs = [];
        $table.find('tr').each(function () {
            var ebid = $(this).data('eb_eid');
            var status = $(this).data('status');
            var $tr = $(this);
            if ("imported" !== status) {
                var ajaxdata = {};
                ajaxdata.event_id = ebid;
                ajaxdata.action = 'mmt_eo_exim_import_single_event_eb';
                ajaxdata.security = mmt_eo_exim_admin.mmt_eo_exim_ajax_nonce;
                var xhr = $.ajax({
                    beforeSend: function () {
                        $working.addClass('show');
                    },
                    type: 'POST',
                    dataType: 'json',
                    url: mmt_eo_exim_admin.ajaxurl,
                    data: ajaxdata,
                    success: function (data) {
                        if (data.status === 'bad') {
                            $tr.addClass('bad');
                            $tr.find('.status').html(data.msg);
                        } else if (data.status === 'good') {
                            $tr.addClass('good');
                            $tr.data('status', 'Imported');
                            $tr.find('.status').html('imported');
                        }
                    }
                });
                xhrs.push(xhr);
            }
        });
        $.when.apply($, xhrs).done(function () {
            $working.removeClass('show');
        });
    });
    $('body').on('click', '.mmt_eo_exim_save_sttings', function (e) {
        e.preventDefault();
        var $form = $(this).closest('#mmt-eo-ei-eventbrite');
        var $tab = $form.closest('.mmt-be-main-tabcontent');
        var $working = $tab.find('.mmt_be_working');
        var ajaxdata = {};
        ajaxdata.eb_private_token = $form.find('input[name="eb_private_token"]').val();
        ajaxdata.eb_export_organizer = $form.find('select[name="eb_export_organizer"]').val();
        ajaxdata.eb_default_currency = $form.find('select[name="eb_default_currency"]').val();
        ajaxdata.security = mmt_eo_exim_admin.mmt_eo_exim_ajax_nonce;
        ajaxdata.action = 'mmt_eo_exim_save_eb_settings';
        $.ajax({
            beforeSend: function () {
                $working.addClass('show');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_exim_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'good') {
                    $working.removeClass('show');
                }
            },
            complete: function () {
                location.reload();
            }
        });
    });
    $('body').on('click', '.mmt_eo_exim_save_display', function (e) {
        e.preventDefault();
        var $form = $(this).closest('#mmt-eo-ei-eventbrite-display-form');
        var $tab = $form.closest('.mmt-be-main-tabcontent');
        var $working = $tab.find('.mmt_be_working');
        var ajaxdata = {};
        ajaxdata.enable_eventbrite_widget = $form.find('input[name="enable_eventbrite_widget"]').val();
        ajaxdata.eb_display_organizer = $form.find('select[name="eb_display_organizer"]').val();
        ajaxdata.security = mmt_eo_exim_admin.mmt_eo_exim_ajax_nonce;
        ajaxdata.action = 'mmt_eo_exim_save_eb_display';
        $.ajax({
            beforeSend: function () {
                $working.addClass('show');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_exim_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'good') {
                    $working.removeClass('show');
                }
            },
            complete: function () {
                location.reload();
            }
        });
    });
});
/**
 * InsQube
 *
 *  Depends on: jQuery, Bootstrap, Toastr, Bootbox
 *
 * Copyright InsQube
 * Authors: IP Bastola
 * All Rights Reserved.
 *
 */

 /**
  * Toastr Options
  */
  toastr.options = {
    "progressBar" : true,
    "preventDuplicates" : true
  };


 /**
 * Ajax Error Reporting
 */
$( document ).ajaxError(function( event, request, settings ) {
    var message = '<strong>Oops!</strong><br/>Something went wrong with the server. Please contact administrator for further support.',
    title       = 'OOPS!';
    toastr.clear(); // remove older toast
    try {
        var json = $.parseJSON(request.responseText);
        if(typeof json.title !== 'undefined'){
                title = json.title;
        }
        if(typeof json.auth_message !== 'undefined'){
                message = json.auth_message;
        }
        if(typeof json.message !== 'undefined'){
                message = json.message;
        }
        else if(typeof json.error !== 'undefined'){
            message = json.error === 'not_found' ? '<strong>Oops!</strong><br/>The content you are looking for was NOT FOUND.' : json.error;
        }
    }catch(err) { }
    toastr.error(message, title);

    // Reset Loading Button if Any
    if( typeof InsQube.options.__btn_loading !== 'undefined' && typeof InsQube.options.__btn_loading === 'object'){
        InsQube.options.__btn_loading.button('reset');
    }

    // Hide if any processing window is open
    InsQube.doing(false);
});

/* global define */
; (function (define) {
    define(['jquery'], function ($) {
        return (function () {

            var InsQube = {
                imagePreview: imagePreview,
                imagePopup: imagePopup,
                liveSearch: liveSearch,
                load: load,
                doing:doing,
                options: {},
                postData: postData,
                postForm: postForm,
                subscribe: subscribe,
                version: '1.0.0'
            };

            return InsQube;


            ///////////////////////////
            // External Function
            ///////////////////////////

            function subscribe(callback) {
                listener = callback;
            }

            /**
             * Live Table/DOM Search
             */
            function liveSearch(f, options){
                var $f = $(f),
                opts = {
                    rows : '#live-searchable tr.searchable'
                },
                $btn_clear = $(f).next('.live-search-clear'),
                options = options ? options : $f.data('options');

                // Extends Options
                opts = $.extend({}, opts, options);
                var $rows = $(opts.rows),
                    val = $.trim($f.val()).replace(/ +/g, ' ').toLowerCase();

                // Clear Action
                if($btn_clear.length){
                    if(val != ''){
                        $btn_clear.fadeIn();
                        $btn_clear.on('click', function(e){
                            e.preventDefault();
                            $f.val('').trigger('keyup');
                            $btn_clear.fadeOut();
                        });
                    }else{
                        $btn_clear.fadeOut();
                    }
                }

               // Search Filter
                $rows.show().filter(function() {
                    var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                    return !~text.indexOf(val);
                }).hide();
            }

            /**
             * Load ajax content
             */
            function load(e, a, callback){
                e.preventDefault();
                var $a = $(a),
                $box = $($a.data('box')), // html box to load content
                method = $a.data('method') ? $a.data('method') : 'html', // method to render html|append|prepend|after|before
                self_destruct = $a.data('self-destruct'),
                $loader_box = $a.data('loader-box') ? $($a.data('loader-box')) : null,
                url = $a.data('url'),

                // For filter next page type of loading
                load_method = $a.data('load-method') ? $a.data('load-method'): 'get',
                form = $a.data('post-form') ? $a.data('post-form') : null;

                $a.button('loading');

                if(load_method == 'post'){
                    var formData = new FormData($(form)[0]);
                    postData(url, formData, function(r){
                        _after_load(r);
                    });
                }else{
                    $.getJSON(url, function(r){
                        _after_load(r);
                    }).error(function() { $a.button('reset'); });
                }

                function _after_load(r)
                {
                    if(r.status === 'success' && typeof r.html !== 'undefined' ){
                        $box[method](r.html);

                        // Self Destruct on Success?
                        if(self_destruct){
                            $loader_box.fadeOut('fast', function(){
                                $loader_box.remove();
                            });
                        }

                        // Callback if any
                        if (callback && typeof(callback) === "function") {
                            callback(a,r);
                        }
                    }
                    $a.button('reset');
                }
                return false;
            }

            /**
             * Default Ajax Form Save
             */
             function postForm(form, callback)
             {
                var formData = new FormData(form),
                    url = $(form).attr('action');
                postData(url, formData, callback);
                return false;
             }

            /**
             * Default Ajax Post
             */
            function postData(url, data, callback)
            {
                $.ajax({
                    type:'POST',
                    url: url,
                    data:data,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success:function(r){
                        // Show message
                        // NOTE: r.status must be one of the toastr method [success|error|info|warning]
                        if(typeof r.status !== 'undefined' && typeof r.message !== 'undefined'){
                            // Clear Toastr
                            toastr.clear();
                            toastr[r.status](r.message);
                        }

                        // Callback if any
                        if (callback && typeof(callback) === "function") {
                            callback(r);
                        }
                    },
                    error: function(r){
                        // Callback if any
                        if (callback && typeof(callback) === "function") {
                            callback(r);
                        }
                    }
                });
            }

             /**
              * Image Preview on Image Select
              */
            function imagePreview(e, a, options)
            {
                var files = e.target.files,
                yo = this,
                $a = $(a),
                opts = {
                    pc : '', // image preview container
                    multi : true
                },
                $previewBox;

                // Get Options
                opts = $.extend({}, opts, options);

                // Create Preview Container if none
                var pc;
                if(!opts.pc){
                    pc = $a.data('preview-container');
                    if( !pc ){
                        pc = '_preview_' + getUniqueId();
                        $a.attr('data-preview-container', pc);
                    }
                    opts.pc = pc;
                }
                if( $('#'+opts.pc).length ){
                    $previewBox = $('#'+opts.pc);
                }else{
                    // Create One
                    $previewBox = $('<div id="'+opts.pc+'"></div>');
                    $a.after($previewBox);
                }

                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, f; f = files[i]; i++) {
                    render(f);
                }

                // Internal functions
                function render(f){
                    // Only process image files.
                    if (!f.type.match('image.*')) {
                        return false;
                    }

                    var reader = new FileReader();

                    // Closure to capture the file information.
                    reader.onload = (function(theFile) {
                        return function(e) {
                            // Render thumbnail.
                            var $image = $('<img />', {
                                style : 'height:75px',
                                class : 'thumbnail',
                                title : escape(theFile.name),
                                src   : e.target.result
                            });

                            // append or HTML
                            if(opts.multi){
                                $image.appendTo($previewBox);
                            }else{
                                $previewBox.html($image);
                            }
                        };
                    })(f);

                    // Read in the image file as a data URL.
                    reader.readAsDataURL(f);
                }
            }

            /**
             * Popup Image into Bootbox alert (as a gallery preview)
             */
             function imagePopup(img, title){
                var $img = $(img),
                src = $img.data('src') ? $img.data('src') :  $img.attr('src');
                html = '<div class="text-center"><img src="'+ src +'" class="img-responsive" style="display:inline-block"></div>';

                bootbox.alert({
                    size: 'large',
                    className: 'modal-image-preview',
                    title: title ? title : 'Preview Image',
                    message: html,
                    backdrop: false,
                    closeButton: false,
                    buttons: {
                        ok : {
                            label: "Close",
                            className: 'btn-outline'
                        }
                    }
                    // buttons: false // No close buttons
                });
             }

             /**
              * Show/Hide Processing Window
              */
            function doing(f, m){
                if(f){
                    var m = m ? m : 'Processing, please wait...',
                        $backdrop = $('<div class="modal-backdrop fade in" id="iqb-processing"><div class="iqb-abs-center">'+m+'</div></div>');
                    $backdrop.appendTo($(document.body));
                }
                else
                {
                    $('#iqb-processing').fadeOut(100, function(){
                        $(this).remove();
                    })
                }
            }



            ///////////////////////////
            // Internal Function
            ///////////////////////////

            function getOptions()
            {
                return $.extend({}, getDefaults(), InsQube.options);
            }

            function getUniqueId()
            {
              return Math.round(new Date().getTime() + (Math.random() * 100));
            }

            function getDefaults()
            {
                return {
                };
            }

        })();
    });
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
    if (typeof module !== 'undefined' && module.exports) { //Node
        module.exports = factory(require('jquery'));
    } else {
        window['InsQube'] = factory(window['jQuery']);
    }
}));


/**
 * General Form Submission Handling
 */
 $(document).on('submit', '.form-iqb-general', function(e){
    e.preventDefault();
    var $this = $(this),

        // Find Primary button from Bootbox Model
        $btn = $('.bootbox .modal-footer').find('button[data-bb-handler="primary"]');

    if(!$btn.length){
        $btn = $('[type="submit"]', $this);
    }
    $btn.attr('data-loading-text', 'Saving...');

    $btn.button('loading');
    InsQube.postForm(this, function(r){

        // reload form?
        if( typeof r.reloadForm !== 'undefined' && r.reloadForm){
            var container = $this.data('pc');
            $(container).html(r.form);

            // checkbox Beautify if any
            $('input.icheck').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue'
            });

            // datepicker
            $('.input-group.date').datepicker({
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });
        }

        if(typeof r.status !== 'undefined' && r.status === 'success')
        {
            // Do we want to replace certain section on success?
            if( typeof r.updateSection !== 'undefined' && r.updateSection === true){
                var dt = r.updateSectionData,
                    // html can be returned directly from response
                    html = (dt.html !== 'undefined' && typeof dt.html == 'string' &&  dt.html != '') ?
                            dt.html :
                            ((r.html !== 'undefined' && typeof r.html == 'string' && r.html != '') ? r.html : 'No section data found!');

                $(dt.box)[dt.method](html);
            }

            // What if we have multiple sections to update
            if(typeof r.multipleUpdate !== 'undefined'){
                for(var i = 0; i < r.multipleUpdate.length; i++) {
                    var section = r.multipleUpdate[i];
                    $(section.box)[section.method](section.html);
                }
            }


            // What about Edit Form Dialog?
            if( typeof r.hideBootbox !== 'undefined' && r.hideBootbox === true){
                // Close the bootbox if any
                var $bootbox = $this.closest('.bootbox');
                $('button[data-bb-handler="cancel"]', $bootbox).trigger('click');
            }

            // Do we want to reload the whole page
            if(typeof r.reloadPage !== 'undefined' && r.reloadPage == true){
                window.location.reload();
            }
        }

        $btn.button('reset');
    })
 });

/**
 * Ajax: Edit Form Dialog (using bootbox)
 */
 $(document).on('click', '.trg-dialog-edit', function(e){
    e.preventDefault();

    // Remove any opened tooltip UI (eg. edit button tooltip)
    $('div.tooltip[role="tooltip"]').remove();


    var $this = $(this),
        url = $this.data('url'),
        title = $this.data('title'),
        form = $this.data('form'),
        size = $this.data('box-size') ? $this.data('box-size') : '',
        bootboxClass = 'modal-default';

    // Check if size if full-width
    if( size === 'full-width' ){
        size = '';
        bootboxClass = 'modal-default modal-full-width'
    }


        // Button Loading
        $this.button('loading');
        InsQube.options.__btn_loading = $this; // assign loading button so that it is reset on AJAX Error


        // Get FORM
        $.getJSON(url, function(r){
            if( typeof r.form !== 'undefined' && r.form){
                bootbox.dialog({
                    className: bootboxClass,
                    size: size,
                    title: title,
                    message: r.form,
                    buttons:{
                        primary: {
                            label: "Save",
                            className: 'btn-primary',
                            callback: function(e){
                                $(form).trigger('submit');
                                return false;
                            }
                        },
                        cancel: {
                            label: "Cancel",
                            className: 'btn-default'
                        }
                    }
                });

                $('.bootbox[role="dialog"]').on("show.bs.modal", function() {

                    // console.log('hello');
                });

                // Post modal Shown Tasks
                $('.bootbox[role="dialog"]').on("shown.bs.modal", function() {

                    // // Has size been set to "full-width"
                    // if(flag_fw){
                    //     $('.modal-dialog', $(this)).addClass('modal-full-width');
                    // }

                    // Focus on First Input Element on Bootbox Form Dialog Load
                    $('.bootbox-body :input:enabled:visible:first', $(this)).focus();
                });


                // checkbox Beautify
                $('input.icheck').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue'
                });
            }

            // Reset Loading
            $this.button('reset');
        });
 });

/**
 * Ajax: Load html content on modal (using bootbox)
 */
$(document).on('click', '.trg-dialog-popup', function(e){
    e.preventDefault();

    // Remove any opened tooltip UI (eg. button tooltip)
    $('div.tooltip[role="tooltip"]').remove();


    var $this   = $(this),
        url     = $this.data('url'),
        title   = $this.data('title'),
        size    = $this.data('box-size') ? $this.data('box-size') : '',
        bootboxClass = 'modal-default';

    // Check if size if full-width
    if( size === 'full-width' ){
        size = '';
        bootboxClass = 'modal-default modal-full-width'
    }


    // Button Loading
    $this.button('loading');
    InsQube.options.__btn_loading = $this; // assign loading button so that it is reset on AJAX Error


    // Get FORM
    $.getJSON(url, function(r){
        if( typeof r.html !== 'undefined'){
            bootbox.alert({
                className: bootboxClass,
                size: size,
                title:   r.title ? r.title : title,
                message: r.html,
                backdrop: false,
                closeButton: false
            });
        }

        // Reset Loading
        $this.button('reset');
    });
});


 /**
 * Ajax: Delete Record (using bootbox)
 */
 $(document).on('click', '.trg-row-action, .trg-dialog-action', function(e){
    e.preventDefault();

    // Remove any opened tooltip UI (eg. edit button tooltip)
    $('div.tooltip[role="tooltip"]').remove();

    var $this = $(this),
        url = $this.data('url'),
        title = $this.data('title') || '<i class="fa fa-warning"></i>&nbsp;<strong>Confirmation Required!</strong>',
        message = $this.data('message') || 'Are you sure you want to <strong>DELETE</strong> this record?<br/><strong>It cannot be UNDONE!</strong>',
        confirm = $this.data('confirm');

    if(confirm === true){
        bootbox.confirm({
            className: 'modal-danger',
            title: title,
            message: message,
            buttons: {
                confirm: {className:'btn-outline'}
            },
            callback: function(yes){
                if(yes){
                    do_action();
                }
            }
        });
    }else{
        // Directly do action
        do_action();
    }

    // Do Action
    function do_action()
    {
        // Show Processing Window
        InsQube.doing(true);
        $.getJSON(url, function(r){

            // Hide Processing Window
            InsQube.doing(false);

            // Clear Toastr
            toastr.clear();

            // Show message
            // NOTE: r.status must be one of the toastr method [success|error|info|warning]
            toastr[r.status](r.message);

            if( r.status === 'success')
            {
                // remove row if success
                if( typeof r.removeRow !== 'undefined' && r.removeRow == true ){
                    $(r.rowId).fadeOut('slow', function(){
                        $(this).remove();
                    });
                }

                // What if we have to reload the action row?
                if( typeof r.reloadRow !== 'undefined' && r.reloadRow == true ){
                    $(r.rowId).replaceWith(r.row);
                }

                // What if we have multiple sections to update
                if(typeof r.multipleUpdate !== 'undefined'){
                    for(var i = 0; i < r.multipleUpdate.length; i++) {
                        var section = r.multipleUpdate[i];
                        $(section.box)[section.method](section.html);
                    }
                }

                // Do we want to reload the whole page
                if(typeof r.reloadPage !== 'undefined' && r.reloadPage == true){
                    window.location.reload();
                }
            }
        });
    };
 });


/**
 * Search Filter
 */
 $(document).on('submit', '.form-iqb-filter', function(e){
    e.preventDefault();
    var $this = $(this),
        $btn = $('[type="submit"]', $this),
        $box = $($this.data('box')),
        method = $this.data('method');

    $btn.button('loading');
    InsQube.postForm(this, function(r){
        if(typeof r.status !== 'undefined' && r.status === 'success' && typeof r.html != 'undefined'){
            $box[method](r.html);
        }
        $btn.button('reset');
    });
    return false;
 });

/**
 * Datepicker Filter
 */
$('.input-group.date').datepicker({
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd'
});

/**
 * Popover Initialization
 *
 *      1. Popover From DOM
 */
 $(document).on('click', '.preview-dom', function(e){
    e.preventDefault();
    var $this = $(this),
        content = $($this.data('dom')).html(),
        title = $this.data('title') ? $this.data('title') : $this.attr('title');
    bootbox.alert({
        className: 'modal-default',
        size: 'large',
        title: title,
        message: content,
        backdrop: false,
        closeButton: false
    });
 });

/**
 * Global Initialize Tooltip ( works well on dynamic content)
 */
$('body').tooltip({
    selector: '[data-toggle="tooltip"]',
    container: 'body'
});

/**
 * Multiple Bootbox Opened?
 * Make the next opened bootbox scrollable (vertical) upon closing this
 */
$(document).on('hidden.bs.modal', '.bootbox[role="dialog"]', function(){
    // Do we have another bootbox opened?
    $bootbox = $('.bootbox');
    if($bootbox.length  ){
        $('body').addClass('modal-open');
    }

    // Remove any opened tooltip UI on bootbox
    $('div.tooltip[role="tooltip"]').remove();
});

/**
 * After Bootbox Shown?
 */
$(document).on('hidden.bs.modal', '.bootbox[role="dialog"]', function(){

    // Remove any opened tooltip UI (eg. edit button tooltip)
    $('div.tooltip[role="tooltip"]').remove();
});

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
 * Ajax Error Reporting
 */
$( document ).ajaxError(function( event, request, settings ) {
    var message = '<strong>Oops!</strong><br/>Something went wrong with the server. Please contact administrator for further support.';
    toastr.clear(); // remove older toast
    try {
        var json = $.parseJSON(request.responseText);
        if(typeof json.error !== 'undefined'){
            message = json.error === 'not_found' ? '<strong>Oops!</strong><br/>The content you are looking for was NOT FOUND.' : json.error;
        }        
    }catch(err) { }    
    toastr.error(message);
});

/* global define */
; (function (define) {
    define(['jquery'], function ($) {
        return (function () {
            
            var InsQube = {
                imagePreview: imagePreview,                
                imagePopup: imagePopup,
                liveSearch: liveSearch,
                options: {},                
                save: save,
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
                };

                // Extends Options
                opts = $.extend({}, opts, options);

                var $rows = $(opts.rows),
                    val = $.trim($f.val()).replace(/ +/g, ' ').toLowerCase();

               // Search Filter 
                $rows.show().filter(function() {
                    var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                    return !~text.indexOf(val);
                }).hide();
            }

            /**
             * Default Ajax Form Save
             */
             function save(form, callback){
                var formData = new FormData(form);
                $.ajax({
                    type:'POST',
                    url: $(form).attr('action'),
                    data:formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success:function(r){  
                        
                        // Clear Toastr 
                        toastr.clear();

                        // Show message
                        // NOTE: r.status must be one of the toastr method [success|error|info|warning]
                        toastr[r.status](r.message);
                       

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
                return false;
             }

             /**
              * Image Preview on Image Select
              */
            function imagePreview(e, a, options) {
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
                html = '<div class="text-center"><img src="'+ src +'" class="thumbnail img-responsive" style="display:inline-block"></div>';

                bootbox.alert({ 
                    // size: 'large',
                    title: title ? title : 'Preview Image',
                    message: html
                });
             }

           

            ///////////////////////////
            // Internal Function
            ///////////////////////////

            function getOptions() {
                return $.extend({}, getDefaults(), InsQube.options);
            }

            function getUniqueId() {
              return Math.round(new Date().getTime() + (Math.random() * 100));
            }

            function getDefaults() {
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
        $btn = $('[type="submit"]', $this);

    if(!$btn.length){
        // Find Primary button from Bootbox Model
        $btn = $('.bootbox .modal-footer').find('button[data-bb-handler="primary"]');    
        $btn.attr('data-loading-text', 'Saving...');       
    }

    console.log('hi submit');

    $btn.button('loading');
    InsQube.save(this, function(r){

        // reload form?
        if( typeof r.reloadForm !== 'undefined' && r.reloadForm){
            var container = $this.data('pc'); 
            $(container).html(r.form);
        }

        if(r.status === 'success')
        {
            // Do we want to replace certain section on success?
            if( typeof r.updateSection !== 'undefined' && r.updateSection === true){                
                var dt = r.updateSectionData;
                $(dt.box)[dt.method](dt.html);
            }
            // What about Edit Form Dialog?
            if( typeof r.hideBootbox !== 'undefined' && r.hideBootbox === true){
                bootbox.hideAll();
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
    var $this = $(this),
        url = $this.data('url'),
        title = $this.data('title'),
        form = $this.data('form'),
        size = $this.data('box-size') ? $this.data('box-size') : '';

        // Get FORM
        $.getJSON(url, function(r){
            if( typeof r.form !== 'undefined' && r.form){
                bootbox.dialog({
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

                // checkbox Beautify
                $('input.icheck').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue'
                });
            }
        });
 });

 /**
 * Ajax: Delete Record (using bootbox)
 */
 $(document).on('click', '.trg-row-delete', function(e){
    e.preventDefault();
    var $this = $(this),
        url = $this.data('url'),
        title = $this.data('title') || '<i class="fa fa-warning"></i>&nbsp;<strong>Confirmation Required!</strong>',
        message = $this.data('message') || 'Are you sure you want to <strong>DELETE</strong> this record?<br/><strong>It cannot be UNDONE!</strong>';

    bootbox.confirm({
        className: 'modal-danger',
        title: title,
        message: message,
        buttons: {
            confirm: {className:'btn-outline'}
        },
        callback: function(yes){
            if(yes){
                $.getJSON(url, function(r){
                    // Clear Toastr 
                    toastr.clear();

                    // Show message
                    // NOTE: r.status must be one of the toastr method [success|error|info|warning]
                    toastr[r.status](r.message);

                    // remove row if success
                    if(r.status === 'success' && r.removeRow == true ){
                        $(r.rowId).fadeOut('slow', function(){
                            $(this).remove();
                        });
                    }
                });
            }
        }
    });        
 });



/**
 * Global Initialize Tooltip ( works well on dynamic content)
 */
$('body').tooltip({
    selector: '[data-toggle="tooltip"]'
});

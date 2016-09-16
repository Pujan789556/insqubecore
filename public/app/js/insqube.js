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
    toastr.error('Something went wrong with the server. Please contact administrator for further support.');
});

/* global define */
; (function (define) {
    define(['jquery'], function ($) {
        return (function () {
            
            var InsQube = {
                imagePreview: imagePreview,                
                imagePopup: imagePopup,
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
                        // Toastr the message 
                        if(r.status === 'success'){
                            toastr.success(r.message);
                        }else{
                            toastr.error(r.message);
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
    $btn.button('loading');
    InsQube.save(this, function(r){
        // reload form?
        if( typeof r.reloadForm !== 'undefined' && r.reloadForm){
            var container = $this.data('pc'); 
            $('#'+container).html(r.form);
        }
        $btn.button('reset');
    })
 });

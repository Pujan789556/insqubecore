<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Layout
 */

// ---------------------------------------------------------------


/**
 *  Extract Section Variables
 */
 extract($__sections);
?>
<!DOCTYPE html>
<html>
    <head>
        <?php 
        /**
         * Load Meta View
         */
        $this->load->view('templates/dashboard/_meta');
        ?>
    </head>
    <body class="hold-transition skin-black-light sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <?php 
                /**
                 * Templete Section: Header View
                 * 
                 * Section Data will be available to this view
                 * 
                 * Load Directly
                 */
                $this->load->view('templates/dashboard/_header');
                ?>

            </header>

            <aside class="main-sidebar">
                <?php 
                /**
                 * Templete Section: Sidebar
                 */
                // echo isset($__section_sidebar) ? $__section_sidebar : '';
                ?>
                <?php 
                /**
                 * Templete Section: Header View
                 * 
                 * Section Data will be available to this view
                 * 
                 * Load Directly
                 */
                $this->load->view('templates/dashboard/_sidebar');
                ?>
            </aside>

            <div class="content-wrapper">
                <section class="content-header">
                    <?php 
                    /**
                     * Templete Section: Content Header
                     */
                    echo isset($__section_content_header) ? $__section_content_header : '';
                    ?>
                </section>
                <section class="content" id="iqb-primary-content">
                    <?php 
                    /**
                     * Templete Section: Content
                     */
                    echo isset($__section_content) ? $__section_content : '';
                    ?>
                </section>
            </div>

            <footer class="main-footer">
                <?php 
                /**
                 * Templete Section: Footer (Common View)
                 * 
                 * Load Directly
                 */
                $this->load->view('templates/_common/_footer');
                ?>
            </footer>

            <aside class="control-sidebar control-sidebar-dark">
                <?php 
                /**
                 * Templete Section: Control Sidebar
                 */
                echo isset($__section_control_sidebar) ? $__section_control_sidebar : '';
                ?>
            </aside><div class="control-sidebar-bg"></div>

        </div>

        

        <!-- jQuery 2.2.3 -->
        <script src="<?php echo THEME_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="<?php echo THEME_URL; ?>bootstrap/js/bootstrap.min.js"></script>
        <!-- iCheck -->
        <script src="<?php echo THEME_URL; ?>plugins/iCheck/icheck.min.js"></script>
        <!-- FastClick -->
        <script src="<?php echo THEME_URL; ?>plugins/fastclick/fastclick.js"></script>

        <!-- AdminLTE App -->
        <script src="<?php echo THEME_URL; ?>dist/js/app.min.js"></script>

        <!-- SlimScroll 1.3.0 -->
        <script src="<?php echo THEME_URL; ?>plugins/slimScroll/jquery.slimscroll.min.js"></script>

        <!-- AdminLTE for demo purposes -->
        <!-- <script src="<?php echo THEME_URL; ?>dist/js/demo.js"></script> -->

        <!-- Toastr -->
        <script src="<?php echo THEME_URL; ?>plugins/toastr/toastr.min.js"></script>

        <!-- bootbox (for alert, confirm) -->
        <script src="<?php echo THEME_URL; ?>plugins/bootbox/bootbox.min.js"></script>

        <!-- Insqube App -->
        <script src="<?php echo base_url()?>public/app/js/insqube.js"></script>

        <script>
        $(function () {
            $('input.icheck').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
        </script>
    </body>
</html>
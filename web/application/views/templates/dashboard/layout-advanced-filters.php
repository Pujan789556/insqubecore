<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Layout : Advanced Filters
 *
 * This layout will have advance search filters UI.
 *
 * Usage Example: Agents, Companies List View
 *
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
                <section class="content pad-t-0" id="iqb-primary-content">
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

        <?php
        /**
         * Templete Section: Scripts
         *
         * Load Directly
         */
        $this->load->view('templates/dashboard/_scripts');

        /**
         * Templete Section: Dynamic JS
         *
         * This section will have dynamic javascript required by specific module
         */
        echo isset($__section_dynamic_js) ? $__section_dynamic_js : '';
        ?>
    </body>
</html>
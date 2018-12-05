<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Login Template
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
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo $site_title ?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.6 -->
        <link rel="stylesheet" href="<?php echo THEME_URL; ?>bootstrap/css/bootstrap.min.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">

        <!-- Theme style -->
        <link rel="stylesheet" href="<?php echo THEME_URL; ?>dist/css/AdminLTE.min.css">
        <!-- iCheck -->
        <link rel="stylesheet" href="<?php echo THEME_URL; ?>plugins/iCheck/square/blue.css">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition login-page">
        <div class="login-box">
            <div class="login-logo">
                <a href="<?php echo base_url();?>"><b>Ins</b>Qube</a>
            </div>
            <!-- /.login-logo -->
            <div class="login-box-body">
                <h3 class="login-box-msg"><?php echo $page_title ?></h3>
                <?php
                /**
                 * Templete Section
                 */
                echo isset($__section_body) ? $__section_body : '';
                ?>
            </div>
            <!-- /.login-box-body -->
        </div>
        <!-- /.login-box -->
        <!-- jQuery 2.2.3 -->
        <script src="<?php echo THEME_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="<?php echo THEME_URL; ?>bootstrap/js/bootstrap.min.js"></script>
        <!-- iCheck -->
        <script src="<?php echo THEME_URL; ?>plugins/iCheck/icheck.min.js"></script>
        <script>
        $(function () {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
        </script>
    </body>
</html>
<?php defined('BASEPATH') OR exit('No direct script access allowed');  ?>
<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <?php
    /**
     * Load Styles (inline)
     */
    $this->load->view('print/style/schedule')
    ?>
    </head>
    <body>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>
                        बीमीतको विवरण
                        <hr/>
                    </td>

                    <td class="no-padding no-border no-margin">
                        <table class="table table-bordered no-margin" style="background-color: #eee;">
                            <tr>
                                <td class="no-margin no-padding">hello world</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>I am </td>
                    <td>World too:D</td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header gray">
                <div class="row">
                    <div class="col-sm-6">
                        <?php
                        /**
                         * Load Live Search UI
                         */
                        $this->load->view('templates/_common/_live_search');
                        ?>
                    </div>
                    <div class="col-sm-6 master-actions text-right">
                        <a href="<?php echo site_url( $this->data['_url_base'] );?>" title="Go to Back"
                            class="btn btn-warning btn-round"
                            data-toggle="tooltip"
                        ><i class="fa fa-chevron-left"></i> Back</a>

                        <a href="<?php echo site_url( $this->data['_url_base'] . '/flush_breed/' . $portfolio->id );?>" title="Flush Cache"
                            class="btn btn-warning btn-round"
                            data-toggle="tooltip"
                        ><i class="fa fa-trash-o"></i> Flush Cache</a>
                    </div>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive data-rows" id="iqb-data-list">
                <?php
                /**
                 * Load Rows from View
                 */
                ?>
                <table class="table table-hover" id="live-searchable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>BS Code</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        /**
                         * Load Rows from View
                         */
                        foreach($records as $single)
                        {
                            $this->load->view($this->data['_view_base'] . '/_single_row_breed', ['record' => $single]);
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>

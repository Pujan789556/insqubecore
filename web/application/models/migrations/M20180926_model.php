<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model - Registration Number Restructured
 *
 * The new motor registration number has two components
 *      a. reg_no_prefix (example ME 1 PA)
 *      b. reg_no   (example 987)
 *
 *  Tasks:
 *  - Old reg_no contains both number and prefix. The task is to separate both in
 *      corresponding fields
 */
class M20180926_model extends MY_Model
{

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();
    }

    public function migrate()
    {
        // Create and Dump Lookup Table
        $this->lookup_table();

        // Restructure MOTOR Object Attributes - separtae reg_no into reg_no_prefix and reg_no;
        $this->upgrade_motor_objects();
    }

    public function lookup_table()
    {
        $file = '../migrations/m20180926.sql';
        $sqls = explode(';', file_get_contents($file));

        // remove empty array elements
        $sqls = array_filter($sqls);

        echo "Importing Lookup Table" . PHP_EOL;

        // Use automatic transaction
        $this->db->trans_start();

            // Run Queries
            foreach ($sqls as $sql)
            {
                $sql = trim($sql);
                if($sql)
                {
                    echo "QUERY: $sql ... ";
                    echo $this->db->query($sql) ? "OK" : "FAIL";
                    echo PHP_EOL;
                }
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // incomplete message
            echo 'Could not migrate database.' . PHP_EOL;
        }
        else
        {
            // Clear Portfolio Cache ( for risk related caches)
            echo "Successfully migrated." . PHP_EOL;
        }
    }

    public function upgrade_motor_objects()
    {
        $list = $this->db->select('id, attributes')
                            ->from('dt_objects')
                            ->where_in('portfolio_id', array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR))
                            ->get()
                            ->result();

        $now = date('Y-m-d H:i:s');
        $count = 0;
        // Use automatic transaction
        $this->db->trans_start();

            foreach($list as $single)
            {
                $old_attributes = json_decode($single->attributes);
                // If already migrated, do nothing
                if(isset($old_attributes->reg_no_prefix) && $old_attributes->reg_no_prefix != '')
                {
                    echo 'OBJECT already migrated.' . PHP_EOL;
                    continue;
                }

                // Get newly formatted object attributes
                $new_attributes = $this->_format_attributes($old_attributes);
                $new_attributes = json_encode($new_attributes);

                $update_data = [
                    'attributes' => $new_attributes,
                    'updated_at' => $now,
                    'updated_by' => 1
                ];

                $done = $this->db->where('id', $single->id)
                                 ->set( $update_data )
                                 ->update('dt_objects');

                $done ? $count++ : '';
            }



            // Add Virtual Column on Object
            $this->_upgrade_object_table();


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // incomplete message
            echo 'Could not migrate database.' . PHP_EOL;
        }
        else
        {
            // Clear Portfolio Cache ( for risk related caches)
            echo "{$count} Object Attributes Successfully Migrated." . PHP_EOL;
        }
    }

        private function _format_attributes($attributes)
        {
            $reg_no = $attributes->reg_no;
            if(!$reg_no)
            {
                $attributes->reg_no_prefix = 'TO BE INTIMATED';
                $attributes->reg_no = 'TO BE INTIMATED';

                return $attributes;
            }

            if($reg_no == 'TO BE INTIMATED')
            {
                $attributes->reg_no_prefix = 'TO BE INTIMATED';
            }
            else
            {
                // Case 1: Remove double white spaces between words
                $reg_no = trim($reg_no);
                $reg_no = preg_replace('!\s+!', ' ', $reg_no);



                // Case 2: no space between parts eg. 9CHA or last two parts e.g. CHA8591
                $parts = explode(' ', $reg_no);


                // NO SPACE AT ALL - Only one case
                if( $parts[0] === 'BA9CHA1932' )
                {
                    $parts = ['BA', '9', 'CHA', '1932'];
                }


                if( count($parts) === 3 )
                {

                    // Case 1: First two part joined e.g. LU33
                    preg_match("/([a-zA-Z]+)(\\d+)/", $parts[0], $matches);
                    if(count($matches) === 3)
                    {
                        $v_type = $parts[1];
                        $v_no = $parts[2];

                        $parts[0] = $matches[1]; // Letter part
                        $parts[1] = $matches[2]; // Number part e.g CHA

                        $parts[2] = $v_type;
                        $parts[3] = $v_no;
                    }
                    else
                    {
                        // Case 2: Break the Mid two parts eg. 9CHA
                        preg_match("/(\\d+)([a-zA-Z]+)/", $parts[1], $matches);
                        if(count($matches) === 3)
                        {
                            //Vehicle NO
                            $vehicle_no = $parts[2]; // last field temp

                            $parts[1] = $matches[1]; // Number part
                            $parts[2] = $matches[2]; // Letter part e.g CHA

                            // Vehicle No as last part
                            $parts[3] = $vehicle_no;
                        }
                        else
                        {
                            // Break the last two parts e.g.  CHA8591
                            preg_match("/([a-zA-Z]+)(\\d+)/", $parts[2], $matches);
                            if(count($matches) === 3)
                            {
                                $parts[2] = $matches[1]; // Letter part
                                $parts[3] = $matches[2]; // Number part e.g CHA
                            }
                            else
                            {
                                // Only Three Parts eg. LU 13 PA
                                // Add Blank Field
                                $parts[3] = '';
                            }
                        }
                    }
                }

                // Case 3: Remove non numeric characters from Number eg. 7\473 to 7473
                $parts[3] = preg_replace("/[^0-9]/", '', $parts[3]);

                // Case 4: Replace KHA by KH
                if($parts[2] == 'KHA')
                {
                    $parts[2] = 'KH';
                }

                $attributes->reg_no_prefix = implode(' ', [$parts[0], $parts[1], $parts[2]]);

                $attributes->reg_no = $parts[3];
            }

            return $attributes;
        }

        private function _upgrade_object_table()
        {
            $sqls = [
                    "ALTER TABLE `dt_objects`
                    ADD `_motor_reg_no_prefix` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci
                    GENERATED ALWAYS AS (JSON_UNQUOTE(
                        JSON_EXTRACT(attributes, CONCAT ('$.reg_no_prefix'))
                    ))
                    VIRTUAL NULL;",

                    "ALTER TABLE `dt_objects` ADD INDEX(`_motor_engine_no`);",

                    "ALTER TABLE `dt_objects` ADD INDEX(`_motor_chasis_no`);",

                    "ALTER TABLE `dt_objects` ADD INDEX(`_motor_reg_no`);",

                    "ALTER TABLE `dt_objects` ADD INDEX(`_motor_reg_no_prefix`);"

                ];

            // Bulk Import Districts
            echo "Upgrading Object Table ... " . PHP_EOL;
            foreach($sqls as $sql)
            {
                echo $sql . ' ... ';
                echo $this->db->query($sql) ? "OK" : "FAIL";
                echo PHP_EOL;
            }
        }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks:
 *  - Upgrade contact module to have a relation entry on separate address table
 *  - Migrate contact json into address table
 *      - Agents
 *      - Customer
 *      - Company Branch
 *      - General Party
 *      - Surveyor
 */
class M20180921_model extends MY_Model
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
        // Import Address Table
        $this->address_table();

        // Migrate Agent
        $this->agents();

        // Migrate Customer
        $this->customers();

        // Migrate Company Branches
        $this->company_branches();

        // Migrate Surveyors
        $this->surveyors();

        $this->remove_contact_fields();
    }

    public function address_table()
    {
        $file = '../migrations/m20180921.sql';
        $sqls = explode(';', file_get_contents($file));

        // remove empty array elements
        $sqls = array_filter($sqls);
        // echo '<pre>'; print_r($sqls); exit;

        echo "Importing Address Table" . PHP_EOL;

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

    public function agents()
    {
        $list = $this->db->select('id, contact')
                            ->from('master_agents')
                            ->get()
                            ->result();

        $batch_data = [];
        $now = date('Y-m-d H:i:s');
        foreach($list as $single)
        {
            $address = $this->_format_address($single->contact);
            if($address)
            {
                $address['type'] = IQB_ADDRESS_TYPE_AGENT;
                $address['type_id'] = $single->id;
                $address['created_by'] = 1;
                $address['created_at'] = $now;

                $batch_data[] = $address;
            }
        }

        if($batch_data)
        {
            // Use automatic transaction
            $this->db->trans_start();

                // Bulk Import Districts
                echo "IMPORTING Agent Addresses ... ";
                echo $this->db->insert_batch('dt_addresses', $batch_data) ? "OK" : "FAIL";
                echo PHP_EOL;

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
                $count = count($batch_data);
                echo "{$count} Agent Addresses Successfully Migrated." . PHP_EOL;
            }
        }
    }

    public function customers()
    {
        $list = $this->db->select('id, contact')
                            ->from('dt_customers')
                            ->get()
                            ->result();

        $batch_data = [];
        $now = date('Y-m-d H:i:s');
        foreach($list as $single)
        {
            $address = $this->_format_address($single->contact);
            if($address)
            {
                $address['type'] = IQB_ADDRESS_TYPE_CUSTOMER;
                $address['type_id'] = $single->id;
                $address['created_by'] = 1;
                $address['created_at'] = $now;

                $batch_data[] = $address;
            }
        }

        if($batch_data)
        {
            // Use automatic transaction
            $this->db->trans_start();

                // Bulk Import Districts
                echo "IMPORTING Customer Addresses ... ";
                echo $this->db->insert_batch('dt_addresses', $batch_data) ? "OK" : "FAIL";
                echo PHP_EOL;

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
                $count = count($batch_data);
                echo "{$count} Customer Addresses Successfully Migrated." . PHP_EOL;
            }
        }
    }

    public function company_branches()
    {
        $list = $this->db->select('id, contact')
                            ->from('master_company_branches')
                            ->get()
                            ->result();

        $batch_data = [];
        $now = date('Y-m-d H:i:s');
        foreach($list as $single)
        {
            $address = $this->_format_address($single->contact);
            if($address)
            {
                $address['type'] = IQB_ADDRESS_TYPE_COMPANY_BRANCH;
                $address['type_id'] = $single->id;
                $address['created_by'] = 1;
                $address['created_at'] = $now;

                $batch_data[] = $address;
            }
        }
        if($batch_data)
        {
            // Use automatic transaction
            $this->db->trans_start();

                // Bulk Import Districts
                echo "IMPORTING Company Branch Addresses ... ";
                echo $this->db->insert_batch('dt_addresses', $batch_data) ? "OK" : "FAIL";
                echo PHP_EOL;

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
                $count = count($batch_data);
                echo "{$count} Company Branch Addresses Successfully Migrated." . PHP_EOL;
            }
        }
    }

    public function surveyors()
    {
        $list = $this->db->select('id, contact')
                            ->from('master_surveyors')
                            ->get()
                            ->result();

        $batch_data = [];
        $now = date('Y-m-d H:i:s');
        foreach($list as $single)
        {
            $address = $this->_format_address($single->contact);
            if($address)
            {
                $address['type'] = IQB_ADDRESS_TYPE_SURVEYOR;
                $address['type_id'] = $single->id;
                $address['created_by'] = 1;
                $address['created_at'] = $now;

                $batch_data[] = $address;
            }
        }

        if($batch_data)
        {
            // Use automatic transaction
            $this->db->trans_start();

                // Bulk Import Districts
                echo "IMPORTING Surveyor Addresses ... ";
                echo $this->db->insert_batch('dt_addresses', $batch_data) ? "OK" : "FAIL";
                echo PHP_EOL;

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
                $count = count($batch_data);
                echo "{$count} Surveyor Addresses Successfully Migrated." . PHP_EOL;
            }
        }
    }

    public function remove_contact_fields()
    {
        $sqls = [
            "ALTER TABLE `master_agents` DROP `contact`;",
            "ALTER TABLE `master_company_branches` DROP `contact`;",
            "ALTER TABLE `dt_customers` DROP `contact`;",
            "ALTER TABLE `ac_parties` DROP `contact`;",
            "ALTER TABLE `master_surveyors` DROP `contact`;"
        ];

        echo "DROPPING 'contact' fields from database tables" . PHP_EOL;
        // Use automatic transaction
        $this->db->trans_start();

            // Run Queries
            foreach ($sqls as $sql)
            {
                echo "QUERY: $sql ... ";
                echo $this->db->query($sql) ? "OK" : "FAIL";
                echo PHP_EOL;
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

        private function _format_address($contact)
        {
            $address = NULL;
            if($contact)
            {
                $contact = json_decode($contact);

                $a2 = array_filter([$contact->address1 ?? NULL, $contact->address2 ?? NULL, $contact->state ?? NULL]);
                $address['address2'] = implode(', ', $a2);
                $country_code = isset($contact->country) && !empty($contact->country) ? $contact->country : 'NP';
                $address = [
                    'country_id'        => $this->_country_id($country_code),
                    'address2'          => implode(', ', $a2),
                    'city'              => $contact->city ?? NULL,
                    'zip_postal_code'   => $contact->zip ?? NULL,
                    'phones'            => $contact->phones ?? NULL,
                    'faxes'             => $contact->fax ?? NULL,
                    'mobile'            => $contact->mobile ?? NULL,
                    'web'               => $contact->web ?? NULL,
                    'email'             => $contact->email ?? NULL,
                ];
            }

            return $address;
        }

        private function _country_id($alpha2)
        {
            return $this->db->select('id')
                            ->from('master_countries')
                            ->where('alpha2', $alpha2)
                            ->get()->row()->id;
        }

}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks:
 *  - Upgrade contact module to have a relation entry on separate address table
 *  - Migrate contact json into address table
 *      - Branch
 */
class M20180927_model extends MY_Model
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
        // Migrate Branches
        $this->branches();
    }



    public function branches()
    {
        $list = $this->db->select('id, contacts')
                            ->from('master_branches')
                            ->get()
                            ->result();

        $batch_data = [];
        $now = date('Y-m-d H:i:s');
        foreach($list as $single)
        {
            $address = $this->_format_address($single->contacts);
            if($address)
            {
                $address['type']        = IQB_ADDRESS_TYPE_BRANCH;
                $address['type_id']     = $single->id;
                $address['created_by']  = 1;
                $address['created_at']  = $now;

                $batch_data[] = $address;
            }
        }

        if($batch_data)
        {
            // Use automatic transaction
            $this->db->trans_start();

                // Bulk Import Districts
                echo "IMPORTING Branch Addresses ... ";
                echo $this->db->insert_batch('dt_addresses', $batch_data) ? "OK" : "FAIL";
                echo PHP_EOL;

                // Remove contacts field from branch table
                echo "DROPPING 'contact' fields from database tables" . PHP_EOL;
                $sql = "ALTER TABLE `master_branches` DROP `contacts`;";
                    echo "QUERY: $sql ... ";
                    echo $this->db->query($sql) ? "OK" : "FAIL";
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
                echo "{$count} Branch Addresses Successfully Migrated." . PHP_EOL;
            }
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
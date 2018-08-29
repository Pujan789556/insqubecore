<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks: Portfolio Risks Update
 *
 *  - Remove Old Riks table and manage risks on JSON inside portfolio table
 */
class M20180829_model extends MY_Model
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

        $sqls = [
            "ALTER TABLE `master_portfolio` DROP `risk_ids`;",
            "DROP TABLE `master_risks`;"
        ];


        $risks = [

            "401" => '{"risks": [{"code": "FIRE", "name": "Fire", "type": "1", "default_min_premium": "500.00"}, {"code": "EARTHQUAKE", "name": "Earthquake", "type": "1", "default_min_premium": "500.00"}, {"code": "STRMTYH", "name": "Strom, Typhoon, Hurricane", "type": "1", "default_min_premium": "500.00"}, {"code": "AIRCRFTDMG", "name": "Aircraft Damage", "type": "1", "default_min_premium": "500.00"}, {"code": "EXPLOSION", "name": "Explosion", "type": "1", "default_min_premium": "500.00"}, {"code": "FLOOD", "name": "Flood & Innundation", "type": "1", "default_min_premium": "500.00"}, {"code": "LANDSLIDE", "name": "Land Slide, Rock Slide", "type": "1", "default_min_premium": "500.00"}, {"code": "IMPACTDMG", "name": "Impact Damage", "type": "1", "default_min_premium": "500.00"}, {"code": "HAILSTROM", "name": "Hailstrom", "type": "1", "default_min_premium": "500.00"}, {"code": "RSMDT", "name": "Riot, Strike, Malicious Damage & Terrorism", "type": "2", "default_min_premium": "250.00"}], "default_premium_computation": "I"}',

            "402" => '{"risks": [{"code": "BASIC", "name": "Basic", "type": "1", "default_min_premium": "0.00"}, {"code": "RSMDT", "name": "Riot, Strike, Malicious Damage & Terrorism", "type": "2", "default_min_premium": "0.00"}], "default_premium_computation": "C"}',

            "701" => '{"risks": [{"code": "EARTHQUAKE", "name": "Earthquake", "type": "1", "default_min_premium": "0.00"}, {"code": "STH", "name": "Strom, Typhoon, Hurricane", "type": "1", "default_min_premium": "0.00"}, {"code": "ARCRFTDMG", "name": "Aircraft Damage", "type": "1", "default_min_premium": "0.00"}, {"code": "EXPL", "name": "Explosion", "type": "1", "default_min_premium": "0.00"}, {"code": "FLOOD", "name": "Flood & Innundation", "type": "1", "default_min_premium": "0.00"}, {"code": "LANDSLIDE", "name": "Land Slide, Rock Slide", "type": "1", "default_min_premium": "0.00"}, {"code": "IMPACTDMG", "name": "Impact Damage", "type": "1", "default_min_premium": "0.00"}, {"code": "HAILSTROM", "name": "Hailstrom", "type": "1", "default_min_premium": "0.00"}, {"code": "COLLAPSE", "name": "Collapse", "type": "1", "default_min_premium": "0.00"}], "default_premium_computation": "C"}',

            "705" => '{"risks": [{"code": "FIRE", "name": "Fire", "type": "1", "default_min_premium": "0.00"}, {"code": "EARTHQUAKE", "name": "Earthquake", "type": "1", "default_min_premium": "0.00"}], "default_premium_computation": "C"}',

            "721" => '{"risks": [{"code": "FIRE", "name": "Fire", "type": "1", "default_min_premium": "0.00"}, {"code": "EARTHQUAKE", "name": "Earthquake", "type": "1", "default_min_premium": "0.00"}, {"code": "STH", "name": "Strom, Typhoon, Hurricane", "type": "1", "default_min_premium": "0.00"}, {"code": "EXPLOSION", "name": "Explosion", "type": "1", "default_min_premium": "0.00"}, {"code": "COLLAPSE", "name": "Collapse", "type": "1", "default_min_premium": "0.00"}, {"code": "CASHHOLDER", "name": "Cash Holder", "type": "1", "default_min_premium": "0.00"}], "default_premium_computation": "C"}'

        ];



        $this->load->model('portfolio_model');
        // Use automatic transaction
        $this->db->trans_start();

            foreach ($sqls as $sql)
            {
                echo "QUERY: $sql ... ";
                echo $this->db->query($sql) ? "OK" : "FAIL";
                echo PHP_EOL;
            }

            // Update Risks
            foreach($risks as $id=>$risk_json)
            {
                $update_data = [
                    'risks' => $risk_json,
                    'updated_by'     => 1,
                    'updated_at'     => date('Y-m-d H:i:s')
                ];
                $this->portfolio_model->update($id, $update_data, TRUE);
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


            echo "Clearing Cache ... ";
                $this->portfolio_model->clear_cache();
            echo "OK".PHP_EOL;

            echo "Successfully migrated." . PHP_EOL;
        }
    }
}
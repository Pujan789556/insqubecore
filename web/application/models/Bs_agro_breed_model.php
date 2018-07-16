<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bs_agro_breed_model extends MY_Model
{
    protected $table_name = 'bs_agro_breeds';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = [];
    protected $before_update = [];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'category_id', 'code', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
        [
            'field' => 'breed_id[]',
            'label' => 'Breed ID',
            'rules' => 'trim|integer|max_length[8]',
            '_field'    => 'id',
            '_type'     => 'hidden',
            '_show_label'   => false,
            '_required' => true
        ],
        [
            'field' => 'code[]',
            'label' => 'Beema Samiti Code',
            'rules' => 'trim|required|username_format|max_length[10]',
            '_type' => 'text',
            '_field'    => 'code',
            '_show_label'   => false,
            '_required' => true
        ],
        [
            'field' => 'name_en[]',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[200]',
            '_type' => 'text',
            '_field'         => 'name_en',
            '_show_label'    => false,
            '_required'      => true
        ],
        [
            'field' => 'name_np[]',
            'label' => 'Name (NP)',
            'rules' => 'trim|required|max_length[200]',
            '_type' => 'text',
            '_field'         => 'name_np',
            '_show_label'    => false,
            '_required'      => true
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 100 records from deletion.

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }



    // ----------------------------------------------------------------

    /**
     * List all headings for portfolio
     *
     * @param int $category_id
     * @param string $for
     * @return type
     */
    public function by_category($category_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'bsag_breed_cat_' . $category_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $where = [ 'category_id' => $category_id ];
            $list = parent::find_many_by($where);

            $this->write_cache($list, $cache_var, CACHE_DURATION_6HRS);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function dropdown_by_category($category_id)
    {
        $this->clear_cache();
        $records = $this->by_category($category_id);
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = $record->name_en . ' (' . $record->name_np . ')';
        }
        return $list;
    }
    // ----------------------------------------------------------------

    /**
     * List all breeds for portfolio
     *
     * @param int $portfolio_id
     * @param string $for
     * @return type
     */
    public function by_portfolio($portfolio_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'bsag_breed_cat_p_' . $portfolio_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select('B.id, B.name_en AS breed_name_en, B.name_np AS breed_name_np, CAT.name_en AS category_name_en, CAT.name_np AS category_name_np')
                            ->from($this->table_name . ' B')
                            ->join('bs_agro_categories CAT', 'CAT.id = B.category_id')
                            ->where('CAT.portfolio_id', $portfolio_id)
                            ->get()
                            ->result();

            $this->write_cache($list, $cache_var, CACHE_DURATION_6HRS);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Dropdown by portfolio.
     *
     * @param inte $portfolio_id
     * @return array
     */
    public function dropdown_by_portfolio($portfolio_id, $lang = 'en')
    {
        $records = $this->by_portfolio($portfolio_id);

        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = $record->category_name_en . ' - ' . $record->breed_name_en;
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Dropdown all
     *
     * @return array
     */
    public function dropdown()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('bsag_breed_all');
        if(!$list)
        {
            $records = parent::find_all();
            $list = [];
            foreach($records as $single)
            {
                $list[$single->id] = $single->name_en;
            }
            $this->write_cache($list, 'bsag_breed_all', CACHE_DURATION_MONTH);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'bsag_breed_cat_*',
            'bsag_breed_cat_p_*',
            'bsag_breed_all'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            parent::delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}
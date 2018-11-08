<?php

class CountryModel extends MY_Model
{
    public $table='country';
    public $primary_key = 'Code';
    public $register_changes = false;
    public $register_changes_table = null;
    public $register_changes_exclude_fields = array();
    protected $orderAndFilterFieldsSubstitution = array(
    );

    public function __construct()
    {
        parent::__construct();
        //$this->has_one['countryinfo'] = array('CountryinfoModel', 'Code', '_id');
    }

}
<?php

class CountryinfoModel extends MY_Model
{
    public $table='countryinfo';
    public $primary_key = '_id';
    public $register_changes = false;
    public $register_changes_table = null;
    public $register_changes_exclude_fields = array();
    protected $orderAndFilterFieldsSubstitution = array(
    );
}
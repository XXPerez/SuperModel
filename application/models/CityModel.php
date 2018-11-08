<?php

class CityModel extends MY_Model
{
    public $table='city';
    public $primary_key = 'ID';
    public $register_changes = false;
    public $register_changes_table = null;
    public $register_changes_exclude_fields = array();
    protected $orderAndFilterFieldsSubstitution = array(
        'CityID' => 'city.ID',
        'CityName' => 'city.Name',
        'CountryCode' => 'city.CountryCode',
        //'CountryName' => array('country.Name','coutry','country.Code = city.CountryCode'),
        'searchall' => 'city.ID||city.Name||CountryCode||@CountryName',
        '@CountryName' => array('PerCountryName', 'country.Name', 'country')
    );

    public function __construct()
    {
        parent::__construct();
        $this->has_one['country'] = array('CountryModel', 'CountryCode', 'Code');
    }

    public function getDataWithCountry()
    {
        $result = $this->with_country(array('fields'=>'*'))->getAll();
        return $result;
    }


    public function setCustomFilterPerCountryName($val, $field = '', $cond = 'eq', $andOr = 'and', $alias = 'country')
    {
        $this->dbjoin("country as $alias","$alias.Code = city.CountryCode",$andOr);
        $this->setCustomSimpleWhere($field, $cond, $val, $andOr);
    }

}
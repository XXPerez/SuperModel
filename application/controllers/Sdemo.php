<?php

class Sdemo extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        define('BASEWEB_PATH',dirname($_SERVER['SCRIPT_NAME']));

    }

    public function test()
    {
        $this->load->model('CityModel');

$this->CityModel->fields('City.Name as CityName, City.CountryCode as CountryCode, Country.Name as CountryName');
$this->CityModel->dbJoin('Country','country.Code = City.CountryCode');

//$this->CityModel->fields('FUNCTION CONCAT(Name," (",CountryCode,")") as CityAndCountry, Info->"$.Population" as Population');
//$this->CityModel->orderBy('Population', 'DESC');
//    $this->CityModel->fields('FUNCTION CONCAT(Name," (",CountryCode,")") as CityAndCountry, Info->"$.Population" as Population');
//    $this->CityModel->where('CountryCode', array('JPN','USA','GBR'));
//    $this->CityModel->where('CountryCode',array('ARG','AND','ARM','JPN','COL'),null,true,true);
//    $this->CityModel->orderBy('Population');
//$this->CityModel->fields('*sum*','Population');
//$this->CityModel->fields('CountryCode');
//$this->CityModel->groupBy('CountryCode');
$result = $this->CityModel->getAll();
//$this->CityModel->fields('*sum*','Population');
//$this->CityModel->fields('FUNCTION CONCAT(District," (",CountryCode,")") as DistrictAndCountry');
//$this->CityModel->groupBy('DistrictAndCountry');
//$result = $this->CityModel->getAll();
var_dump($result);
exit;

        var_dump($result);
        var_dump($this->CityModel->city);
        exit;
    }

    public function page($page)
    {
        return $this->index($page);
    }
    
    public function index($page='index')
    {
        $view = $page != 'index' && $page != ''?$page:'';
        $this->load->view('supermodel/index', array('view' => $view, 'data' => array()));
    }

    public function listtables()
    {
        // Define default Offset
        define('DEFAULT_QUERY_OFFSET', 25);

        // Helper to obtain current order from url
        $this->load->helper('smlisttables');

        // Load main model
        $this->load->model('CityModel');

        // Selected fields
        $selectFields = array('ID as CityID', 'city.Name as CityName', 'CountryCode', 'country.name as CountryName');

        // Default params, get all url params
        list($filter, $fieldOrd, $pageNum, $defaultUrl, $pagePag) = $this->CityModel->getListTablesVars();

        // Make json filter
        if ($filter != '') {
            $filter = '[{
                            "group": "and",
                            "filters": [{
                                    "group": "and",
                                    "filters": [{
                                            "filterId": "searchall",
                                            "ask": false,
                                            "fields": {
                                                    "searchall": [{
                                                            "value": "'.$filter.'",
                                                            "cond": "lk"
                                                            }]
                                                    }
                                            }]
                                    }]
                            }]';
        } else {
            $filter = '[{
                            "group": "and",
                            "filters": [{
                                    "group": "and",
                                    "filters": [{
                                            "filterId": "CountryName",
                                            "ask": false,
                                            "fields": {
                                                    "@CountryName": [{
                                                            "value": "",
                                                            "cond": "gt"
                                                            }]
                                                    }
                                            }]
                                    }]
                            }]';
        }
        
        // Set default order
        if ($fieldOrd == '') {
            $fieldOrd = 'CityName:1';
        }

        // Obtain results
        $result = $this->CityModel->searchAllPaginate($selectFields, $filter, $pageNum, DEFAULT_QUERY_OFFSET, $fieldOrd, false);

        $this->load->view('supermodel/index', array('view' => 'listtables', 'data' => $result));
    }
}
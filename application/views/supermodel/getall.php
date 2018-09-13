        <div id="content_inner">
            <h3>GETALL</h3>
            <hr>
            <div id="content-inner-2">
            Obtain multiple records.<br/>
            <br/>
            First of all, load the Model:
<pre>
$this->load->model('CityModel');
</pre>
            <br />
            Same as GET, but return multiple records, not only the first:
<pre>
$result = $this->CityModel->getAll(456);
</pre>
            The result will be an array of objects:
<pre>
array(1) {
  [0]=>
  object(stdClass)#45 (5) {
    ["ID"]=>
    string(3) "456"
    ["Name"]=>
    string(6) "London"
    ["CountryCode"]=>
    string(3) "GBR"
    ["District"]=>
    string(7) "England"
    ["Info"]=>
    string(23) "{"Population": 7285000}"
  }
}
</pre>
            <br />
            If the parameter is an integer, then is used the primary key as match.
            You can also specify another conditions in array format:
<pre>
$result = $this->CityModel->getAll(array('Name' => 'Valencia'));
</pre>
            The result will be:
<pre>
array(3) {
  [0]=>
  object(stdClass)#45 (5) {
    ["ID"]=>
    string(3) "655"
    ["Name"]=>
    string(8) "Valencia"
    ["CountryCode"]=>
    string(3) "ESP"
    ["District"]=>
    string(8) "Valencia"
    ["Info"]=>
    string(22) "{"Population": 739412}"
  }
  [1]=>
  object(stdClass)#46 (5) {
    ["ID"]=>
    string(3) "826"
    ["Name"]=>
    string(8) "Valencia"
    ["CountryCode"]=>
    string(3) "PHL"
    ["District"]=>
    string(17) "Northern Mindanao"
    ["Info"]=>
    string(22) "{"Population": 147924}"
  }
  [2]=>
  object(stdClass)#47 (5) {
    ["ID"]=>
    string(4) "3542"
    ["Name"]=>
    string(8) "Valencia"
    ["CountryCode"]=>
    string(3) "VEN"
    ["District"]=>
    string(8) "Carabobo"
    ["Info"]=>
    string(22) "{"Population": 794246}"
  }
}
</pre>
            <br />
            You can use all the form of array parameters that you use in the ActiveRecord where.
            $result = $this->CityModel->getAll(array('CountryCode' => 'MMR', '));
<pre>
$result = $this->CityModel->getAll(array('ID>' => 450, 'ID<' => 500));
</pre>
            <br />
            For custom selection or operations over fields, use <a href="<?php echo BASEWEB_PATH?>/sdemo/page/fields/">Fields</a>
            <br />
            <br />
            For more complex conditions, use <a href="<?php echo BASEWEB_PATH?>/sdemo/page/where/">Where</a>
            <br />
            <br />
            </div>
        </div>

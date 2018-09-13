        <div id="content_inner">
            <h3>FIELDS</h3>
            <hr>
            <div id="content-inner-2">
                The fields method allows to customize the fields returned by the query to the database.
                <br/><br/>
                Select field names as string, only when is <strong>a list with comma and ONE SPACE after comma</strong>:
                <pre>
$this->CityModel->fields('ID, Name, CountryCode');
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(4079) {
  [0]=>
  object(stdClass)#45 (3) {
    ["ID"]=>
    string(1) "1"
    ["Name"]=>
    string(5) "Kabul"
    ["CountryCode"]=>
    string(3) "AFG"
  }
  [1]=>
  object(stdClass)#46 (3) {
    ["ID"]=>
    string(1) "2"
    ["Name"]=>
    string(8) "Qandahar"
    ["CountryCode"]=>
    string(3) "AFG"
  }
...
</pre>
                <br />
                Select field names as array:
                <pre>
$this->CityModel->fields(array('ID','Name','CountryCode'));
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(4079) {
  [0]=>
  object(stdClass)#45 (3) {
    ["ID"]=>
    string(1) "1"
    ["Name"]=>
    string(5) "Kabul"
    ["CountryCode"]=>
    string(3) "AFG"
  }
  [1]=>
  object(stdClass)#46 (3) {
    ["ID"]=>
    string(1) "2"
    ["Name"]=>
    string(8) "Qandahar"
    ["CountryCode"]=>
    string(3) "AFG"
  }
...
</pre>
                <br/>
                Select field names as array with alias:
                <pre>
$this->CityModel->fields(array('ID as CountryId','Name as CountryName','CountryCode'));
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(4079) {
  [0]=>
  object(stdClass)#45 (3) {
    ["CountryId"]=>
    string(1) "1"
    ["CountryName"]=>
    string(5) "Kabul"
    ["CountryCode"]=>
    string(3) "AFG"
  }
  [1]=>
  object(stdClass)#46 (3) {
    ["CountryId"]=>
    string(1) "2"
    ["CountryName"]=>
    string(8) "Qandahar"
    ["CountryCode"]=>
    string(3) "AFG"
  }
...
</pre>

                <br/>
                <h4>Special field selectors</h4>
                Count all records (result property is 'result'):
                <pre>
$this->CityModel->fields('*count*');
$this->CityModel->where('CountryCode','JPN');
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(1) {
  [0]=>
  object(stdClass)#47 (1) {
    ["result"]=>
    string(3) "248"
  }
}
</pre>
                Where sentence are explained <a href="<?php echo BASEWEB_PATH?>/sdemo/page/where/">here</a>
                <br />
                <br />
                Count width distinct (result property is 'result'):
                <pre>
$this->CityModel->fields('*count-distinct*','CountryCode');
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(1) {
  [0]=>
  object(stdClass)#45 (1) {
    ["result"]=>
    string(3) "232"
  }
}</pre>
                <br />
                Sum / Max / Min (result column will be alias of selected column):
                <pre>
$this->CityModel->fields('*sum*','Population');
$this->CityModel->where('CountryCode','JPN');
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(1) {
  [0]=>
  object(stdClass)#48 (1) {
    ["Population"]=>
    string(8) "77965107"
  }
}
</pre>
                Where are explained <a href="<?php echo BASEWEB_PATH?>/sdemo/page/where/">here</a>
                <br />
                <br />
                Custom FUNTIONS on fields. Use the word "FUNCTION " before the mysql function and assing an alias:
                <pre>
$this->CityModel->fields('<strong>FUNCTION</strong> CONCAT(Name," (",CountryCode,")") <strong>as CityAndCountry</strong>, Info,  <strong>FUNCTION </strong>substring(Info,16,length(Info)-16) <strong>as Population</strong>');
$this->CityModel->where('CountryCode', array('JPN','USA','GBR'));
$this->CityModel->orderBy('CityAndCountry');
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(603) {
  [0]=>
  object(stdClass)#48 (3) {
    ["CityAndCountry"]=>
    string(14) "Aberdeen (GBR)"
    ["Info"]=>
    string(22) "{"Population": 213070}"
    ["Population"]=>
    string(6) "213070"
  }
  [1]=>
  object(stdClass)#49 (3) {
    ["CityAndCountry"]=>
    string(11) "Abiko (JPN)"
    ["Info"]=>
    string(22) "{"Population": 126670}"
    ["Population"]=>
    string(6) "126670"
  }
...
</pre>
                OrderBy are explained <a href="<?php echo BASEWEB_PATH?>/sdemo/page/orderby/">here</a>
                <br />
                <br />
                JSON value fields combined with FUNCTION (Info is a JSON mysql field):
                <pre>
$this->CityModel->fields('<strong>FUNCTION</strong> CONCAT(Name," (",CountryCode,")") <strong>as CityAndCountry</strong>, Info->"$.Population" as Population');
$this->CityModel->where('CountryCode', array('JPN','USA','GBR'));
$this->CityModel->orderBy('Population');</pre>
                Result:
                <pre>
array(603) {
  [0]=>
  object(stdClass)#58 (2) {
    ["CityAndCountry"]=>
    string(13) "Douglas (GBR)"
    ["Population"]=>
    string(5) "23487"
  }
  [1]=>
  object(stdClass)#59 (2) {
    ["CityAndCountry"]=>
    string(18) "Saint Helier (GBR)"
    ["Population"]=>
    string(5) "27523"
  }
  [2]=>
  object(stdClass)#60 (2) {
    ["CityAndCountry"]=>
    string(13) "Grimsby (GBR)"
    ["Population"]=>
    string(5) "89000"
  }
...
</pre>

            </div>
        </div>

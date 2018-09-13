        <div id="content_inner">
            <h3>GROUP BY</h3>
            <hr>
            <div id="content-inner-2">
                When has been declared a SUM, MAX, MIN function in the fields method, GROUP BY must to be declared over the fields not involved in the function.
                <br/><br/>
                GroupBy example (take care about the order, first come the operation, after, the groupBy fields):
                <pre>
$this->CityModel->fields('*sum*','Population');
$this->CityModel->fields('CountryCode');
$this->CityModel->groupBy('CountryCode');
$result = $this->CityModel->getAll();
</pre>
                Result:
<pre>
array(232) {
  [0]=>
  object(stdClass)#48 (2) {
    ["result"]=>
    string(5) "29034"
    ["CountryCode"]=>
    string(3) "ABW"
  }
  [1]=>
  object(stdClass)#49 (2) {
    ["result"]=>
    string(7) "2332100"
    ["CountryCode"]=>
    string(3) "AFG"
  }
  [2]=>
  object(stdClass)#50 (2) {
    ["result"]=>
    string(7) "2561600"
    ["CountryCode"]=>
    string(3) "AGO"
  }
...
</pre>
                <br />
                <br />
                Another example:
                <pre>
$this->CityModel->fields('*sum*','Population');
$this->CityModel->fields('FUNCTION SUBSTRING(CountryCode,1,2) as CityAndCountry');
$this->CityModel->groupBy('CityAndCountry');
$result = $this->CityModel->getAll();
</pre>
            </div>
        </div>
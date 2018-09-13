        <div id="content_inner">
            <h3>ORDER BY</h3>
            <hr>
            <div id="content-inner-2">
                OrderBy allow to order the result according to fields and order direction.
                <br/><br/>
                Example:
                <pre>
$this->CityModel->fields('FUNCTION CONCAT(Name," (",CountryCode,")") as CityAndCountry, Info->"$.Population" as Population');
$this->CityModel->orderBy('Population', 'DESC');
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(4079) {
  [0]=>
  object(stdClass)#48 (2) {
    ["CityAndCountry"]=>
    string(21) "Mumbai (Bombay) (IND)"
    ["Population"]=>
    string(8) "10500000"
  }
  [1]=>
  object(stdClass)#49 (2) {
    ["CityAndCountry"]=>
    string(11) "Seoul (KOR)"
    ["Population"]=>
    string(7) "9981619"
  }
  [2]=>
  object(stdClass)#50 (2) {
    ["CityAndCountry"]=>
    string(16) "São Paulo (BRA)"
    ["Population"]=>
    string(7) "9968485"
  }
...
</pre>
            </div>
        </div>
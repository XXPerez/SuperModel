        <div id="content_inner">
            <h3>JOINS</h3>
            <hr>
            <div id="content-inner-2">
                Join allows to merge another tables whitin the same query, to obtain related data.
                <br/><br/>
                Example:
                <pre>
$this->CityModel->fields('City.Name as CityName, City.CountryCode as CountryCode, Country.Name as CountryName');
$this->CityModel->dbJoin('Country','country.Code = City.CountryCode');
$result = $this->CityModel->getAll();
</pre>
                Result:
                <pre>
array(4079) {
  [0]=>
  object(stdClass)#24 (3) {
    ["CityName"]=>
    string(5) "Kabul"
    ["CountryCode"]=>
    string(3) "AFG"
    ["CountryName"]=>
    string(11) "Afghanistan"
  }
  [1]=>
  object(stdClass)#23 (3) {
    ["CityName"]=>
    string(8) "Qandahar"
    ["CountryCode"]=>
    string(3) "AFG"
    ["CountryName"]=>
    string(11) "Afghanistan"
  }
  [2]=>
  object(stdClass)#22 (3) {
    ["CityName"]=>
    string(5) "Herat"
    ["CountryCode"]=>
    string(3) "AFG"
    ["CountryName"]=>
    string(11) "Afghanistan"
  }
...
</pre>
                <br />
                <br />
                <strong>JOIN Options:</strong>
                <br />
                <pre>
public function dbJoin($table, $cond, $type = '', $escape = NULL)
</pre>
                <br />
                $table = TableName
                <br />
                $cond = Condition (the ON part of a normal join)
                <br />
                $type = 'LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;Also, can recieve 'and', being converted to 'INNER', or 'or', being converted to 'LEFT'
                <br />
                $escape = true / false. If true, all terms will be escaped.
                <br />
                <br />
                In case of multiple JOINS to the same table and condition, only one will be used.
                <br />
                <br />
                In case of multiple JOINS to the same table but diferent condition, use alias.
                <br />
                Example:
                <pre>
$this->CityModel->fields('City.Name as CityName, City.CountryCode as CountryCode, Country.Name as CountryName');
$this->CityModel->dbJoin('Country','country.Code = City.CountryCode');
$this->CityModel->dbJoin('Country as Country2','Country2.Code2 = City.CountryCode');
$result = $this->CityModel->getAll();
</pre>
            </div>
        </div>

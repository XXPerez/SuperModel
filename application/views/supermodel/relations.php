        <div id="content_inner">
            <h3>RELATIONS</h3>
            <hr>
            <div id="content-inner-2">
                All models can have a relationship with other models.
                <ul>
                    <li>One to one</li>
                    <li>One to many</li>
                    <li>Many to many</li>
                </ul>
                <br/>
                <strong>One to one:</strong>
                <br />
                Each City pertains to a Country. This relations is One2One.
                <br/>
                <br/>
                Has_one function:
                <pre>
$this->has_one['RELATION_NAME'] = array('FOREIGN_MODEL', 'FOREIGN_FIELD', 'LOCAL_FIELD');
</pre>
                Example on CityModel contruct:
                <pre>
$this->has_one['country'] = array('CountryModel', 'CountryCode', 'Code');
</pre>
                <br />
                <br />
                <strong>How to use in your queries (example from CityModel):</strong>
                <br />
                <pre>
$result = $this->with_country->get($id);
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

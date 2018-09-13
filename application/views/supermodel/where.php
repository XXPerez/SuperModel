        <div id="content_inner">
            <h3>WHERE</h3>
            <hr>
            <div id="content-inner-2">
            Define all filters and conditions previously to "get" or "getAll".<br/>
            <br/>
            First of all, load the Model:
<pre>
$this->load->model('CityModel');
</pre>
            <br>
            Simple where one fields one value:
<pre>
$this->CityModel->where('Name','paris');
</pre>
            <br />
            <br />
            Simple where with conditional operator <, >, !=, <=, >=
<pre>
$this->CityModel->where('Population','>=',50000);
$this->CityModel->where('Population','<=',50000);
$this->CityModel->where('Population','<',50000);
$this->CityModel->where('Population','>',50000);
$this->CityModel->where('Name','<>','Paris');
</pre>
            <br />
            <br />
            Where field can be multiple values (WHERE_IN):
<pre>
$this->CityModel->where('CountryCode',array('ARG','AND','ARM','JPN','COL'));
</pre>
            <br />
            <br />
            Where field is not any of this multiple values (WHERE_NOT_IN):
<pre>
$this->CityModel->where('CountryCode',array('ARG','AND','ARM','JPN','COL'),null,false,TRUE);
</pre>
            <br />
            <br />
            By default, AND is added to each where, but you can add OR:
<pre>
$this->CityModel->where('CountryCode',array('ARG','AND','ARM','JPN','COL'),null,TRUE);
</pre>
            <br />
            <br />
            WHERE params:
            <br />
            <strong>where</strong>($field_or_array_of_fields = NULL, $operator_or_value_or_array_of_values = NULL, $value = NULL, $with_or = FALSE, $with_not = FALSE, $custom_string = FALSE)
            <br/>
            <br/>
            <strong>Params</strong>
            <br />
            1 => $field_or_array_of_fields = Fieldname or array with combination of fieldname => values
            <br />
            2 => $operator_or_value_or_array_of_values = If param1 is a single field, this can be a single value or an operator or an array of values
            <br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$operator_or_value_or_array_of_values = If param1 is an array of field/values, this param must to be null.
            <br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Else, can be a value, an array of values (WHERE_IN), or an operator in combination with param3.
            <br />
            3 => $value = If it's set param1 as a field and it's set param2 as an operator, then you can specify a value here. Else, null.
            <br />
            4 => $with_or = If TRUE, with add this contion as OR ......
            <br />
            5 => $with_not = Fot IN or LIKE, force NOT IN or NOT LIKE
            <br />
            6 => $custom_string = Set to TRUE if ypu wants to send a custom string or complex operations that contains dots or colons.
            <br />
            <br />
            Where can be concatenated with get, get_all, update or delete:
<pre>
$result = $this->CityModel->where(array('Name'=>'Paris','CountryCode'=>'FRA'))->getAll();
$this->CityModel->where(array('Name'=>'Paris','CountryCode'=>'FRA'))->update(array('Population' => 2125247));
</pre>
            <br>
            <br>
            Where_FIELD
            <br>
            Where can be as a magic method, referencing a especific field and it's exact value as :
<pre>
$result = $this->CityModel->where_<strong>Name</strong>('Paris')->getAll();
</pre>
            Also, can be used as a IN (values):
<pre>
$result = $this->CityModel->where_<strong>Name</strong>('Barcelona','Madrid')->getAll();
</pre>
            In the previous example, the result will return 3 records, because in Vanezuela there is another Barcelona city.
            To add more restrictions, where or where_* methods can be concatenated:
<pre>
$result = $this->CityModel->where_<strong>Name</strong>('Barcelona','Madrid')->where_CountryCode('ESP')->getAll();
</pre>








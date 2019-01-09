            <h3>FILTERS</h3>
            <hr>
            <div id="content-inner-2">
                You can filter for any field or field conbination that are declared in the $orderAndFilterFieldsSubstitution variable.
                <br/><br/>
                <ul>
                    <li>Filter by one table field:
                <pre>
'CityID' => 'city.ID',
'CityName' => 'city.Name',
'CountryCode' => 'city.CountryCode'
</pre>
                        You can use CityID filter name to search by the city.ID table field.
                        <br/>
                        <br/>
                    </li>
                    <li>Filter inside a mutiple fields:
                <pre>
 'searchall' => 'city.ID'
</pre>
                    </li>
                </ul>
                <br>
                First definition must to be <b>$table</b>, where you can assign the tablename in your database.
                <br>
                Second definition is <b>$primary_key</b>, must to be only one field, sequential or other custom unique ID.
            </div>
        </div>

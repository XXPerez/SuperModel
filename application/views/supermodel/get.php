        <div id="content_inner">
            <h3>GET</h3>
            <hr>
            <div id="content-inner-2">
            Obtain only one record.<br/>
            <br/>
            First of all, load the Model:
<pre>
$this->load->model('CityModel');
</pre>
            <br />
            To obtain record with primary key = 456:
<pre>
$result = $this->CityModel->get(456);
</pre>
            The result will be an object of fields:
<pre>
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
</pre>
            <br />
            If the parameter is an integer, then is used the primary key as match.
            You can also specify another conditions in array format:
<pre>
$result = $this->CityModel->get(array('Name' => 'Valencia'));
</pre>
            <br />
            You can use all the form of array parameters that you use in the ActiveRecord where.
            Remember that you will obtain only the first record.
<pre>
$result = $this->CityModel->get(array('ID>' => 450, 'ID<' => 500));
</pre>
            <br />
            For multiple records, use <a href="<?php echo BASEWEB_PATH?>/sdemo/page/getall/">GetAll</a>
            <br />
            <br />
            For more complex conditions, use <a href="<?php echo BASEWEB_PATH?>/sdemo/page/where/">Where</a>
            <br />
            <br />
            </div>
        </div>
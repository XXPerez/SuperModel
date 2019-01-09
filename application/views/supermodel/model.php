        <div id="content_inner">
            <h3>MODEL</h3>
            <hr>
            <div id="content-inner-2">
                Define a Model inside your models directory
                <br/><br/>
                Example:
                <pre>
class CityModel extends MY_Model
{
    public $table='city';
    public $primary_key = 'ID';
}
...
</pre>
                <br>
                First definition must to be <b>$table</b>, where you can assign the tablename in your database.
                <br>
                Second definition is <b>$primary_key</b>, must to be only one field, sequential or other custom unique ID.
            </div>
        </div>

        <div id="content_inner">
            <h3>MODEL STRUCTURE</h3>
            <hr>
            <div id="content-inner-2">
                SuperModel extends from MY_Model. MY_Model extends from SuperModel, and SuperModel extends from CI_Model, allowing all CI functionallity as ActiveRecord, and also use of multiple database engines.
                <br /><br />
                <strong>TableModel</strong> >> extends >> <strong>MY_Model</strong> >> extends >> <strong>SuperModel</strong> >> extends >> <strong>CI_Model</strong>
                <br /><br />
                To create a Model, you must create as usually, with your custom name. I prefer ussing "TABLE + Model" as name.
                <br />
                Example:
                <pre>
<&quest;php

class CityModel extends MY_Model
{
    protected $table='city';
    protected $primary_key = 'ID';
}
                </pre>
                The basic configuration for a Model, is the tablename ($table), and it's primary key. The primary key must ever be only one field, it's recommended to use "auto_increment" field.
            </div>
        </div>

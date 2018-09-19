# SuperModel
SuperModel is based on avenirer project and extended to allow more functionallity 

https://github.com/avenirer/CodeIgniter-MY_Model

SuperModel is a simple ORM that can be used for small and large projects, allowing you to write fewer lines of code. 
It have a simple and clear code, but also powerfull. 

Whith a simple declaration inside a model, you can: 

* Get one record by primary key
* Get one record by another criteria
* Get multiple records by custom criteria
* Get data records with relationship data
* Fill data to insert records
* Custom validate rules to insert/update
* Update/Delete records by custom criteria
* Declare allowed fields to be filled by insert/update
* Protect fields to be filled by insert/update
* Observers Pre&Post Insert/Update/Read/Delete
* Automatic fill of custom created_on_data & modified_on_data fields
* Automatic full_text detection to create where conditions
* Changes detection, saving all CRUD actions and "before"/"after" values.
* Declare custom search&filter fields that can be used from get/post and it's relation with real fields & tables
* Support for max, min, count, count distinct, sum, and other mysql functions over allowed fields.
* Fields object, setters & getters

Supermodel extends Codeigniter ActiveRecord, does not replace it.

Library can be found in application/core/SuperModel.php

Example and manual:  http://www.4amics.com/SuperModel/SuperModel/www/sdemo/
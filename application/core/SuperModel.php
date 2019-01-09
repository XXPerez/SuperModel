<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
* Copyright (C) 2018 @xmadmax [xmadmax@gmail.com]
* Everyone is permitted to copy and distribute verbatim or modified copies of this license document,
* and changing it is allowed as long as the name is changed.
*
* Based on Avenirer Model:
* https://github.com/avenirer/CodeIgniter-MY_Model
*
* Current development:
* https://github.com/xxperez/CodeIgniter-SuperModel
* 
*/

class SuperModel extends CI_Model
{
    /**
     * Select the database connection from the group names defined inside the database.php configuration file or an
     * array.
     */
    protected $_database_connection = NULL;

    /** @var
     * This one will hold the database connection object
     */
    protected $_database;

    /** @var null
     * Sets table name
     */
    public $table = NULL;

    /**
     * @var null
     * Sets PRIMARY KEY
     */
    public $primary_key = 'id';
    public $base_primary_key;

    /**
     * @var array
     * Fields available in the model table
     */
    public $table_fields = array();
    public $table_fields_properties = array();
    public $fields = array(); // tractable fields, is an object mirror of the table fields

    /**
     * @var array
     * Sets fillable fields
     */
    public $fillable = array();

    /**
     * @var array
     * Sets tractabe fields, any fields not in this array (or fields array), will de discarded
     */
    public $tractable = array();

    /**
     * @var array
     * Sets protected fields
     */
    public $protected = array();

    private $_can_be_filled = NULL;


    /** @var bool | array
     * Enables idx generation on insert
     */
    protected $addIDX = FALSE;

    /** @var bool | array
     * Enables created_at and updated_at fields
     */
    protected $timestamps = FALSE;
    protected $timestamps_format = 'Y-m-d H:i:s';

    protected $_created_at_field;
    protected $_updated_at_field;
    protected $_deleted_at_field;

    /** @var bool
     * Enables soft_deletes
     */
    protected $soft_deletes = FALSE;

    /** relationships variables */
    private $_relationships = array();
    public $has_one = array();
    public $has_many = array();
    public $has_many_pivot = array();
    public $separate_subqueries = TRUE;
    private $_requested = array();
    /** end relationships variables */

    /*caching*/
    public $cache_driver = 'file';
    public $cache_prefix = 'mm';
    protected $_cache = array();
    public $delete_cache_on_save = FALSE;

    /*pagination*/
    public $next_page;
    public $previous_page;
    public $all_pages;
    public $pagination_delimiters;
    public $pagination_arrows;

    /* validation */
    public $validated = TRUE;
    private $row_fields_to_update = array();
    public $rules = array('insert' => array(),'update' => array());


    /**
     * The various callbacks available to the model. Each are
     * simple lists of method names (methods will be run on $this).
     */
    protected $before_create = array();
    protected $after_create = array();
    protected $before_update = array();
    protected $after_update = array();
    protected $before_get = array();
    protected $after_get = array();
    protected $before_delete = array();
    protected $after_delete = array();
    protected $before_soft_delete = array();
    protected $after_soft_delete = array();

    protected $callback_parameters = array();

    protected $return_as = 'object';
    protected $return_as_dropdown = NULL;
    protected $_dropdown_field = '';

    private $_trashed = 'without';

    private $_select = '*';

    public $register_changes = false;
    public $register_changes_table = '';
    public $register_changes_secondary_id = '';
    public $register_changes_exclude_fields = array();
    
    protected $old_values = array();
    protected $new_values = array();
    protected $chg_values = array();

    // Array that contains current joined tables
    protected $joinedTables = array();

    public function __construct()
    {
        parent::__construct();
        if (!defined('DEBUG') && isset($_GET['debug']) && $_GET['debug'] == 'true') {
            define('DEBUG', true);
        }
        if (!defined('DEBUG')) {
            define('DEBUG', false);
        }
        $this->{$this->table} = new stdClass();
        $this->load->helper('inflector');
        $this->_setConnection();
        $this->_setTimestamps();
        $this->_fetchTable();
        $this->pagination_delimiters = (isset($this->pagination_delimiters)) ? $this->pagination_delimiters : array('<span>','</span>');
        $this->pagination_arrows = (isset($this->pagination_arrows)) ? $this->pagination_arrows : array('&lt;','&gt;');
        /* These below are implementation examples for before_create and before_update triggers.
        Their respective functions - add_creator() and add_updater() - can be found at the end of the model.
        They add user id on create and update. If you comment this out don't forget to do the same for the methods()
        $this->before_create[]='add_creator';
        $this->before_update[]='add_updater';
        */
        if ($this->_database)
            $this->_setFieldsClass();
    }

    /**
     * setFieldClass
     *
     * Creates a class with all fields as objects
     *
     * @return object
     */
    public function _setFieldsClass()
    {
        if (!isset($this->table))
            return true;

        $myfields = array();
        $this->_gettableFields();
        foreach ($this->table_fields_properties as $val) {
            if (!$this->tractable || count($this->tractable) == 0 || ($this->tractable && in_array($val->name,$this->tractable)))
                $default = is_null($val->default)?false:true;
                switch ($val->type) {
                    case "int":
                    case "smallint":
                    case "bit":
                    case "tinyint":
                    case "mediumint":
                    case "bigint":
                        $myfields[$val->name] = $default?$val->default:0;
                    break;
                    case "decimal":
                    case "float":
                    case "double":
                        $myfields[$val->name] = $default?$val->default:(float) 0;
                    break;
                    case "char":
                    case "varchar":
                    case "tinytext":
                    case "text":
                    case "mediumtext":
                    case "longtext":
                    case "json":
                    case "date":
                    case "time":
                    case "year":
                    case "timestamp":
                    case "datetime":
                    case "enum":
                    case "set":
                        $myfields[$val->name] = $default?$val->default:(string) "a";
                    break;
                    default:
                        $myfields[$val->name] = $default?$val->default:null;
                    break;
                }
        }
        $this->fields = (object) $myfields;
    }

    public function newFields() {
        $fields = clone $this->fields;
        return $fields;
    }

    public function switchDB($db='default')
    {
        $this->_database_connection = $db;
        $this->_setConnection();
        return $this;
    }

    public function _gettableFields()
    {
        if(empty($this->table_fields))
        {
            $save_queries = $this->_database->save_queries;
            $this->_database->save_queries = FALSE;
            $this->table_fields = $this->_database->list_fields($this->table);
            $this->table_fields_properties = $this->_database->field_data($this->table);
            $this->_database->save_queries = $save_queries;
        }
        return TRUE;
    }

    public function fillableFields()
    {
        if(!isset($this->_can_be_filled))
        {
            $this->_gettableFields();
            $no_protection = array();
            foreach ($this->table_fields as $field) {
                if (!in_array($field, $this->protected)) {
                    $no_protection[] = $field;
                }
            }
            if (!empty($this->fillable)) {
                $can_fill = array();
                foreach ($this->fillable as $field) {
                    if (in_array($field, $no_protection)) {
                        $can_fill[] = $field;
                    }
                }
                $this->_can_be_filled = $can_fill;
            } else {
                $this->_can_be_filled = $no_protection;
            }
        }
        return TRUE;
    }

    public function _prepBeforeWrite($data)
    {
        $this->fillableFields();
        // We make sure we have the fields that can be filled
        $can_fill = $this->_can_be_filled;

        // Let's make sure we receive an array...
        $data_as_array = (is_object($data)) ? (array)$data : $data;

        $new_data = array();
        $multi = $this->isMultidimensional($data);
        if($multi===FALSE)
        {
            foreach ($data_as_array as $field => $value)
            {
                if (in_array($field, $can_fill)) {
                    $new_data[$field] = $value;
                }
            }
        }
        else
        {
            foreach($data_as_array as $key => $row)
            {
                foreach ($row as $field => $value)
                {
                    if (in_array($field, $can_fill)) {
                        $new_data[$key][$field] = $value;
                    }
                }
            }
        }
        return $new_data;
    }

    /*
     * public function _prepAfterWrite()
     * this function simply deletes the cache related to the model's table if $this->delete_cache_on_save is set to TRUE
     * It should be called by any "save" method
     */
    public function _prepAfterWrite()
    {
        if($this->delete_cache_on_save===TRUE)
        {
            $this->deleteCache('*');
        }
        return TRUE;
    }

    public function _prepBeforeRead()
    {

    }

    public function _prepAfterRead($data, $multi = TRUE)
    {
        // let's join the subqueries...
        $data = $this->joinTemporaryResults($data);
        $this->_database->reset_query();
        if(isset($this->return_as_dropdown) && $this->return_as_dropdown == 'dropdown')
        {
            $pk = $this->primary_key;
            if (stristr($pk,' as '))
                $pk = substr($pk,stripos($pk,' as ')+4);
            $dropdown_field = $this->_dropdown_field;
            if (stristr($dropdown_field,' as '))
                $dropdown_field = substr($dropdown_field,stripos($dropdown_field,' as ')+4);
            foreach($data as $row)
            {
                $dropdown[$row[$pk]] = $row[$dropdown_field];
            }
            $data = $dropdown;
            $this->return_as_dropdown = NULL;
        }
        elseif($this->return_as == 'object')
        {
            $data = json_decode(json_encode($data), FALSE);
        }
        if(isset($this->_select))
        {
            $this->_select = '*';
        }
        return $data;
    }

    /**
     * public function fromForm($rules = NULL,$additional_values = array(), $row_fields_to_update = array())
     * Gets data from form, after validating it and waits for an insert() or update() method in the query chain
     * @param null $rules Gets the validation rules. If nothing is passed (NULL), will look for the validation rules
     * inside the model $rules public property
     * @param array $additional_values Accepts additional fields to be filled, fields that are not to be found inside
     * the form. The values are inserted as an array with "field_name" => "field_value"
     * @param array $row_fields_to_update You can mention the fields from the form that can be used to identify
     * the row when doing an update
     * @return $this
     */
    public function fromData($rules = NULL, $additional_values = NULL, $row_fields_to_update = array())
    {
        if ($additional_values) {
            $_POST = array_merge($_POST, $additional_values);
        }
        return $this->fromForm($rules, NULL, $row_fields_to_update);

        //return $this->fromForm($rules, NULL, $row_fields_to_update);
    }
    
    public function fromForm($rules = NULL, $additional_values = NULL, $row_fields_to_update = array())
    {
        $this->_gettableFields();
        $this->load->library('form_validation');
        if(!isset($rules))
        {
            if(empty($row_fields_to_update))
            {
                $rules = $this->rules['insert'];
            }
            else
            {
                $rules = $this->rules['update'];
            }
        }
        $this->form_validation->set_rules($rules);
        if($this->form_validation->run())
        {
            $this->fillableFields();
            $this->validated = array();
            foreach($rules as $rule)
            {
                if(in_array($rule['field'],$this->_can_be_filled))
                {
                    $this->validated[$rule['field']] = $this->input->post($rule['field']);
                }
            }
            if(isset($additional_values) && is_array($additional_values) && !empty($additional_values))
            {
                foreach($additional_values as $field => $value)
                {
                    if(in_array($field, $this->_can_be_filled))
                    {
                        $this->validated[$field] = $value;
                    }
                }
            }

            if(!empty($row_fields_to_update))
            {
                foreach ($row_fields_to_update as $key => $field) {
                    if (in_array($field, $this->table_fields)) {
                        $this->row_fields_to_update[$field] = $this->input->post($field);
                    }
                    else if (in_array($key, $this->table_fields)){
                        $this->row_fields_to_update[$key] = $field;
                    }
                    else {
                        continue;
                    }
                }
            }
            return $this;
        }
        else
        {
            $this->validated = FALSE;
            return $this;
        }

    }

    /**
     * public function insert($data)
     * Inserts data into table. Can receive an array or a multidimensional array depending on what kind of insert we're talking about.
     * @param $data
     * @mode 'insert' or 'replace'
     * @return int/array Returns id/ids of inserted rows
     */
    public function insert($data = NULL, $mode = 'insert')
    {
        if ($mode != 'insert' && $mode != 'replace') {
            return FALSE;
        }
        if(!isset($data) && $this->validated!=FALSE)
        {
            $data = $this->validated;
            $this->validated = FALSE;
        }
        elseif(!isset($data))
        {
            return FALSE;
        }
        $data = $this->_prepBeforeWrite($data);

        //now let's see if the array is a multidimensional one (multiple rows insert)
        $multi = $this->isMultidimensional($data);

        // if the array is not a multidimensional one...
        if($multi === FALSE)
        {
            if($this->addIDX !== FALSE)
            {
                $data[$this->addIDX] = $this->genIDX();
            }
            if($this->timestamps !== FALSE && $this->_created_at_field != '')
            {
                $data[$this->_created_at_field] = $this->_theTimestamp();
            }
            if($this->timestamps !== FALSE && $this->_updated_at_field != '')
            {
                $data[$this->_updated_at_field] = $this->_theTimestamp();
            }
            $data = $this->trigger('before_create', $data);
            if($this->_database->{$mode}($this->table, $data))
            {
                $this->_prepAfterWrite();
                $id = $this->_database->insert_id();
                $this->new_values = $data;
                $this->new_values[$this->primary_key] = $id;
                $this->trigger('after_create', $this->new_values);
                if ($this->register_changes === true) {
                    $this->registerPostChanges($this->new_values,'I');
                }
                return $id;
            }
            return FALSE;
        }
        // else...
        else
        {
            $return = array();
            foreach($data as $row)
            {
                if($this->timestamps !== FALSE && $this->_created_at_field != '')
                {
                    $row[$this->_created_at_field] = $this->_theTimestamp();
                }
                $row = $this->trigger('before_create', $row);
                if($this->_database->{$mode}($this->table,$row))
                {
                    $id = $this->_database->insert_id();
                    $return[] = $id;
                    if ($this->register_changes === true) {
                        $row[$this->primary_key] = $id;
                        $this->registerPostChanges($row,'I');
                    }
                }
            }
            $this->_prepAfterWrite();
            $after_create = array();
            foreach($return as $id)
            {
                $after_create[] = $this->trigger('after_create', $row, $id);
            }
            return $after_create;
        }
        return FALSE;
    }

    /*
     * public function isMultidimensional($array)
     * Verifies if an array is multidimensional or not;
     * @param array $array
     * @return bool return TRUE if the array is a multidimensional one
     */
    public function isMultidimensional($array)
    {
        if(is_array($array))
        {
            foreach($array as $element)
            {
                if(is_array($element))
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * public function insertIgnore
     * Adds ignore term to insert statement and launch db->insert directlly.
     *
     * @param type $data
     * @return boolean
     */
    public function insertIgnore($data = NULL)
    {
        if(!isset($data) && $this->validated!=FALSE)
        {
            $data = $this->validated;
            $this->validated = FALSE;
        }
        elseif(!isset($data))
        {
            return FALSE;
        }
        $data = $this->_prepBeforeWrite($data);
        $insert_query = $this->db->insert_string($this->table, $data);
        $insert_query = preg_replace('/INSERT INTO/','INSERT IGNORE INTO',$insert_query,1);
        return $this->db->query($insert_query);
    }

    /**
     * public function update($data)
     * Updates data into table. Can receive an array or a multidimensional array depending on what kind of update we're talking about.
     * @param array $data
     * @param array|int $column_name_where
     * @param bool $escape should the values be escaped or not - defaults to true
     * @return str/array Returns id/ids of inserted rows
     */
    public function update($data = NULL, $column_name_where = NULL, $escape = TRUE)
    {
        if(!isset($data) && $this->validated!=FALSE)
        {
            $data = $this->validated;
            $this->validated = FALSE;
        }
        elseif(!isset($data))
        {
            $this->_database->reset_query();
            return FALSE;
        }
        // Prepare the data...
        $data = $this->_prepBeforeWrite($data);

        //now let's see if the array is a multidimensional one (multiple rows update)
        $multi = $this->isMultidimensional($data);

        // if the array is not a multidimensional one...
        if($multi === FALSE)
        {
            if($this->timestamps !== FALSE && $this->_updated_at_field != '')
            {
                if ($escape == false) {
                    $data[$this->_updated_at_field] = "'".$this->_theTimestamp()."'";
                } else {
                    $data[$this->_updated_at_field] = $this->_theTimestamp();
                }
            }
            $data = $this->trigger('before_update', $data);
            if($this->validated === FALSE && count($this->row_fields_to_update))
            {
                $this->where($this->row_fields_to_update);
                $this->row_fields_to_update = array();
            }
            if(isset($column_name_where))
            {
                if (is_array($column_name_where))
                {
                    $this->where($column_name_where);
                } elseif (is_numeric($column_name_where)) {
                    $this->_database->where($this->primary_key, $column_name_where);
                } else {
                    $column_value = (is_object($data)) ? $data->{$column_name_where} : $data[$column_name_where];
                    $this->_database->where($column_name_where, $column_value);
                }
            }

            $this->new_values = $data;
            if ($this->register_changes === true) {
                $where = $this->_database->getCurrentWhere();
                $result = $this->get();
                $this->old_values = (array) $result;
                $chg_values = array_intersect_key($data, (array) $result);
                foreach ($chg_values as $key => $val) {
                    if ($this->old_values[$key] != $this->new_values[$key]) {
                        $this->chg_values[$this->primary_key] = $this->old_values[$this->primary_key];
                        $this->chg_values[$key] = array('old' => $this->old_values[$key],'new' => $this->new_values[$key]);
                    }
                }
                $this->_database->setCurrentWhere($where);
            }

            if($escape)
            {
                if($this->_database->update($this->table, $data))
                {
                    $this->_prepAfterWrite();
                    $affected = $this->_database->affected_rows();
                    $return = $this->trigger('after_update', $this->new_values);
                    if ($this->register_changes === true) {
                        $this->registerPostChanges($this->chg_values,'U');
                    }
                    $this->_database->flush_cache();
                    return $return;
                }
            }
            else
            {
                if($this->_database->set($data, null, FALSE)->update($this->table))
                {
                    $this->_prepAfterWrite();
                    $affected = $this->_database->affected_rows();
                    $return = $this->trigger('after_update', $this->new_values);
                    if ($this->register_changes === true) {
                        $this->registerPostChanges($this->chg_values,'U');
                    }
                    $this->_database->flush_cache();
                    return $return;
                }
            }
            $this->_database->flush_cache();
            return FALSE;
        }
        // else...
        else
        {
            $rows = 0;
            foreach($data as $row)
            {
                if($this->timestamps !== FALSE && $this->_updated_at_field != '')
                {
                    $row[$this->_updated_at_field] = $this->_theTimestamp();
                }
                $row = $this->trigger('before_update',$row);
                if(is_array($column_name_where))
                {
                    $this->_database->where($column_name_where[0], $column_name_where[1]);
                }
                else
                {
                    $column_value = (is_object($row)) ? $row->{$column_name_where} : $row[$column_name_where];
                    $this->_database->where($column_name_where, $column_value);
                }
                if($escape)
                {
                    if($this->_database->update($this->table,$row))
                    {
                        $rows++;
                    }
                }
                else
                {
                    if($this->_database->set($row, null, FALSE)->update($this->table))
                    {
                        $rows++;
                    }
                }
            }
            $affected = $rows;
            $this->_prepAfterWrite();
            $return = $this->trigger('after_update',$affected);
            return $return;
        }
        return FALSE;
    }

    /**
     * public function where($field_or_array = NULL, $operator_or_value = NULL, $value = NULL, $with_or = FALSE, $with_not = FALSE, $custom_string = FALSE)
     * Sets a where method for the $this object
     * @param null $field_or_array - can receive a field name or an array with more wheres...
     * @param null $operator_or_value - can receive a database operator or, if it has a field, the value to equal with
     * @param null $value - a value if it received a field name and an operator
     * @param bool $with_or - if set to true will create a or_where query type pr a or_like query type, depending on the operator
     * @param bool $with_not - if set to true will also add "NOT" in the where
     * @param bool $custom_string - if set to true, will simply assume that $field_or_array is actually a string and pass it to the where query
     * @return $this
     */
    public function where($field_or_array = NULL, $operator_or_value = NULL, $value = NULL, $with_or = FALSE, $with_not = FALSE, $custom_string = FALSE)
    {
        if($this->soft_deletes===TRUE)
        {
            $backtrace = debug_backtrace(); #fix for lower PHP 5.4 version
            if($backtrace[1]['function']!='forceDelete'){
                $this->_where_trashed();
            }
        }

        if(is_array($field_or_array))
        {
            $multi = $this->isMultidimensional($field_or_array);
            if($multi === TRUE)
            {
                foreach ($field_or_array as $where)
                {
                    $field = $where[0];
                    $operator_or_value = isset($where[1]) ? $where[1] : NULL;
                    $value = isset($where[2]) ? $where[2] : NULL;
                    $with_or = (isset($where[3])) ? TRUE : FALSE;
                    $with_not = (isset($where[4])) ? TRUE : FALSE;
                    $this->where($field, $operator_or_value, $value, $with_or, $with_not);
                }
                return $this;
            }
        }

        if($with_or === TRUE)
        {
            $where_or = 'or_where';
        }
        else
        {
            $where_or = 'where';
        }

        if($with_not === TRUE)
        {
            $not = '_not';
        }
        else
        {
            $not = '';
        }

        if($custom_string === TRUE)
        {
            $this->_database->{$where_or}($field_or_array, NULL, FALSE);
        }
        elseif(is_numeric($field_or_array))
        {
            if (isset($this->base_primary_key)) {
                $this->primary_key = $this->base_primary_key;
                $this->base_primary_key = null;
            }
            $this->_database->{$where_or}(array($this->table.'.'.$this->primary_key => $field_or_array));
        }
        elseif(is_array($field_or_array) && !isset($operator_or_value))
        {
            $this->_database->where($field_or_array);
        }
        elseif(!isset($value) && isset($field_or_array) && isset($operator_or_value) && !is_array($operator_or_value))
        {
            $fieldname = strstr($field_or_array,'.')?$field_or_array:$this->table.'.'.$field_or_array;
            $this->_database->{$where_or}(array($fieldname => $operator_or_value));
        }
        elseif(!isset($value) && isset($field_or_array) && isset($operator_or_value) && is_array($operator_or_value) && !is_array($field_or_array))
        {
            $fieldname = strstr($field_or_array,'.')?$field_or_array:$this->table.'.'.$field_or_array;
            $this->_database->{$where_or.$not.'_in'}($fieldname, $operator_or_value);
        }
        elseif(isset($field_or_array) && isset($operator_or_value) && isset($value))
        {
            if(strtolower($operator_or_value) == 'like') {
                if($with_not === TRUE)
                {
                    $like = 'not_like';
                }
                else
                {
                    $like = 'like';
                }
                if ($with_or === TRUE)
                {
                    $like = 'or_'.$like;
                }
                $likeMode = 'both';
                if (substr($value,0,1)=='%' && substr($value,strlen($value)-1,1)=='%') {
                    $likeMode = 'both';
                } elseif (substr($value,strlen($value)-1,1)=='%') {
                    $likeMode = 'after';
                }
                elseif (substr($value,0,1)=='%') {
                    $likeMode = 'before';
                }
                $value = str_replace('%','',$value);
                $this->_database->{$like}($field_or_array, $value, $likeMode, false);
            }
            else
            {
                if ($operator_or_value == 'null' && $value == 'null') {
                    $operator_or_value = '';
                    $value = null;
                }
                if ($operator_or_value == 'not null' && $value == 'not null') {
                    $operator_or_value = 'IS NOT';
                    $value = null;
                }
                $this->_database->{$where_or}($field_or_array.' '.$operator_or_value, $value);
            }

        }
        return $this;
    }

    /**
     * public function limit($limit, $offset = 0)
     * Sets a rows limit to the query
     * @param $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit, $offset = 0)
    {
        $this->_database->limit($limit, $offset);
        return $this;
    }

    /**
     * public function groupBy($grouping_by)
     * A wrapper to $this->_database->groupBy()
     * @param $grouping_by
     * @return $this
     */
    public function groupBy($grouping_by)
    {
        $this->_database->group_by($grouping_by);
        return $this;
    }
    /**
     * public function delete($where)
     * Deletes data from table.
     * @param $where primary_key(s) Can receive the primary key value or a list of primary keys as array()
     * @return Returns affected rows or false on failure
     */
    public function delete($where = NULL)
    {
        if(!empty($this->before_delete) || !empty($this->before_soft_delete) || !empty($this->after_delete) || !empty($this->after_soft_delete) || ($this->soft_deletes === TRUE))
        {
            $to_update = array();
            if(isset($where))
            {
                $this->where($where);
            }
            $query = $this->_database->get($this->table);
            foreach($query->result() as $row)
            {
                $to_update[] = (array) $row;
            }
            if(!empty($this->before_soft_delete))
            {
                foreach($to_update as &$row)
                {
                    $row = $this->trigger('before_soft_delete',$row);
                }
            }
            if(!empty($this->before_delete))
            {
                foreach($to_update as &$row)
                {
                    $row = $this->trigger('before_delete',$row);
                    if(!empty($this->after_delete) || !empty($this->after_soft_delete))
                    {
                        $this->old_values[] = $row;
                    }
                }
            }
            if(!empty($this->after_delete))
            {
                foreach($to_update as &$row)
                {
                    if(!empty($this->after_delete) || !empty($this->after_soft_delete))
                    {
                        $this->old_values[] = $row;
                    }
                }
            }
        }
        
        if(isset($where))
        {
            $this->where($where);
        }
        $affected_rows = 0;
        if($this->soft_deletes === TRUE)
        {
            if(isset($to_update)&& count($to_update) > 0)
            {

                foreach($to_update as &$row)
                {
                    //$row = $this->trigger('before_soft_delete',$row);
                    $row[$this->_deleted_at_field] = $this->_theTimestamp();
                }
                $affected_rows = $this->_database->update_batch($this->table, $to_update, $this->primary_key);
                $to_update['affected_rows'] = $affected_rows;
                $this->_prepAfterWrite();
                $this->trigger('after_soft_delete',$to_update);
            }
            return $affected_rows;
        }
        else
        { 
            if(!empty($this->before_delete)) {
                $IDS = array_map( function($to_update) { return(isset($to_update[$this->primary_key])?$to_update[$this->primary_key]:null);},$to_update);
                $this->_database->where_in($this->primary_key,$IDS);
            }

            if($this->_database->delete($this->table))
            {
                $affected_rows = $this->_database->affected_rows();
                if(!empty($this->after_delete))
                {
                    $to_update['affected_rows'] = $affected_rows;
                    $this->trigger('after_delete',isset($to_update[0])?$to_update[0]:array());
                    //$affected_rows = $to_update;
                }
                if ($this->register_changes === true) {
                    $this->registerPostChanges(isset($this->old_values[0])?$this->old_values[0]:array(),'D');
                }
                $this->_prepAfterWrite();
                return $affected_rows;
            }
        }
        return FALSE;
    }

    /**
     * public function forceDelete($where = NULL)
     * Forces the delete of a row if soft_deletes is enabled
     * @param null $where
     * @return bool
     */
    public function forceDelete($where = NULL)
    {
        if(isset($where))
        {
            $this->where($where);
        }
        if($this->_database->delete($this->table))
        {
            $this->_prepAfterWrite();
            return $this->_database->affected_rows();
        }
        return FALSE;
    }

    /**
     * public function restore($where = NULL)
     * "Un-deletes" a row
     * @param null $where
     * @return bool
     */
    public function restore($where = NULL)
    {
        $this->with_trashed();
        if(isset($where))
        {
            $this->where($where);
        }
        if($affected_rows = $this->_database->update($this->table,array($this->_deleted_at_field=>NULL)))
        {
            $this->_prepAfterWrite();
            return $affected_rows;
        }
        return FALSE;
    }

    /**
     * public function trashed($where = NULL)
     * Verifies if a record (row) is soft_deleted or not
     * @param null $where
     * @return bool
     */
    public function trashed($where = NULL)
    {
        $this->only_trashed();
        if(isset($where))
        {
            $this->where($where);
        }
        $this->limit(1);
        $query = $this->_database->get($this->table);
        if($query->num_rows() == 1)
        {
            return TRUE;
        }
        return FALSE;
    }


    /**
     * public function get()
     * Retrieves one row from table.
     * @param null $where
     * @return mixed
     */
    public function get($where = NULL, $debug=false)
    {
        $data = $this->_getFromCache();

        if(isset($data) && $data !== FALSE)
        {
            $this->_database->reset_query();
            return $data;
        }
        else
        {
            $this->trigger('before_get');
            if($this->_select)
            {
                $this->_database->select($this->_select);
            }
            if(!empty($this->_requested))
            {
                foreach($this->_requested as $requested)
                {
                    $this->_database->select($this->_relationships[$requested['request']]['local_key']);
                }
            }
            if(isset($where))
            {
                $this->where($where);
            }
            $this->limit(1);
            $query = $this->_database->get($this->table);

            if ($debug || (defined("DEBUG") && DEBUG)) {
                echo ("<hr>SELECT: ".$this->_database->get_compiled_select()."<hr>");
                echo ("<hr>SQL: ".$this->_database->last_query()."<hr>");
            }

            $row = false;
            if ($query && $query->num_rows() == 1)
            {
                $row = $query->row_array();
                $row = $this->trigger('after_get', $row);
                $row =  $this->_prepAfterRead(array($row),FALSE);
                $row = $row[0];
                $this->_writeToCache($row);
            }
            $this->flushCache();
            $this->{$this->table} = $row;
            return $row;
        }
    }

    /**
     * public function get_all()
     * Retrieves rows from table.
     * @param null $where
     * @return mixed
     */
    public function get_all($where = NULL, $debug=false)
    {
        $data = $this->_getFromCache();

        if(isset($data) && $data !== FALSE)
        {
            $this->_database->reset_query();
            $this->{$this->table} = $data;
            return $data;
        }
        else
        {
            $this->trigger('before_get');
            if(isset($where))
            {
                $this->where($where);
            }
            elseif($this->soft_deletes===TRUE)
            {
                $this->_where_trashed();
            }
            if(isset($this->_select))
            {
                $escape = null;
                if (is_array($this->_select) && $result = preg_grep('/>\"\$\./',$this->_select)) {
                    $escape = false;
                }
                $this->_database->select($this->_select, $escape);
            }
            if(!empty($this->_requested))
            {
                foreach($this->_requested as $requested)
                {
                    // NO SE PUEDE HACER ESTO, DESTIROTA EL SELECT CON JOINS !!!
                    //$this->_database->select($this->_relationships[$requested['request']]['local_key']);
                }
            }

            $query = $this->_database->get($this->table);
            if ($debug || (defined("DEBUG") && DEBUG)) {
                if (!$query) {
                    echo ("<hr>COMPILED SELECT: ".$this->_database->get_compiled_select()."<hr>");
                } else {
                    echo ("SQL: ".$this->_database->last_query()."<hr>");
                }
            }

            $data = array();
            if($query->num_rows() > 0)
            {
                $data = $query->result_array();
               
                foreach ($data as &$row) {
                    $row = $this->trigger('after_get', $row);
                }
                unset($row);
                $data = $this->_prepAfterRead($data,TRUE);
                $this->_writeToCache($data);
            }
            $this->flushCache();
            $this->{$this->table} = $data;
            return $data;
        }
    }

    /**
     * public function countRows()
     * Retrieves number of rows from table.
     * @param null $where
     * @return integer
     */
    public function countRows($where = NULL)
    {
        if(isset($where))
        {
            $this->where($where);
        }
        $this->_database->from($this->table);
        $number_rows = $this->_database->count_all_results();
        return $number_rows;
    }

    /** RELATIONSHIPS */

    /**
     * public function with($requests)
     * allows the user to retrieve records from other interconnected tables depending on the relations defined before the constructor
     * @param string $request
     * @param array $arguments
     * @return $this
     */
    public function with($request,$arguments = array())
    {
        $this->_setRelationships();
        if (array_key_exists($request, $this->_relationships))
        {
            $this->_requested[$request] = array('request'=>$request);
            $parameters = array();

            if(isset($arguments))
            {
                foreach($arguments as $argument)
                {
                    if(is_array($argument))
                    {
                        foreach($argument as $k => $v)
                        {
                            $parameters[$k] = $v;
                        }
                    }
                    else
                    {
                        $requested_operations = explode('|',$argument);
                        foreach($requested_operations as $operation)
                        {
                            $elements = explode(':', $operation, 2);
                            if (sizeof($elements) == 2) {
                                $parameters[$elements[0]] = $elements[1];
                            } else {
                                show_error('MY_Model: Parameters for with_*() method must be of the form: "...->with_*(\'where:...|fields:...\')"');
                            }
                        }
                    }
                }
            }
            $this->_requested[$request]['parameters'] = $parameters;
        }


        /*
        if($separate_subqueries === FALSE)
        {
            $this->separate_subqueries = FALSE;
            foreach($this->_requested as $request)
            {
                if($this->_relationships[$request]['relation'] == 'has_one') $this->_has_one($request);
            }
        }
        else
        {
            $this->after_get[] = 'joinTemporaryResults';
        }
        */
        return $this;
    }

    /**
     * protected function joinTemporaryResults($data)
     * Joins the subquery results to the main $data
     * @param $data
     * @return mixed
     */
    protected function joinTemporaryResults($data)
    {
        $order_by = array();
        $order_inside_array = array();
        //$order_inside = '';
        $this->_database->reset_query();
        foreach($this->_requested as $requested_key => $request)
        {
            $pivot_table = NULL;
            $relation = $this->_relationships[$request['request']];
            $this->load->model($relation['foreign_model']);
            $foreign_model_name = (substr($relation['foreign_model'],strpos($relation['foreign_model'],'/')>0?strpos($relation['foreign_model'],'/')+1:0));
            $foreign_key = $relation['foreign_key'];
            $local_key = $relation['local_key'];
            $foreign_table = $relation['foreign_table'];
            $type = $relation['relation'];
            $relation_key = $relation['relation_key'];
            if($type=='has_many_pivot')
            {
                $pivot_table = $relation['pivot_table'];
                $pivot_local_key = $relation['pivot_local_key'];
                $pivot_foreign_key = $relation['pivot_foreign_key'];
                $get_relate = $relation['get_relate'];
            }

            if(array_key_exists('order_inside',$request['parameters']))
            {
                //$order_inside = $request['parameters']['order_inside'];
                $elements = explode(',', $request['parameters']['order_inside']);
                foreach($elements as $element)
                {
                    $order = explode(' ',$element);
                    if(sizeof($order)==2)
                    {
                        $order_inside_array[] = array(trim($order[0]), trim($order[1]));
                    }
                    else
                    {
                        $order_inside_array[] = array(trim($order[0]), 'desc');
                    }
                }

            }


            $local_key_values = array();
            foreach($data as $key => $element)
            {
                if(isset($element[$local_key]) and !empty($element[$local_key]))
                {
                    $id = $element[$local_key];
                    $local_key_values[$key] = $id;
                }
            }
            if(!$local_key_values)
            {
                $data[$key][$relation_key] = NULL;
                continue;
            }
            if(!isset($pivot_table))
            {
                $sub_results = $this->{$foreign_model_name};
                $select = array();
                $select[] = '`'.$foreign_table.'`.`'.$foreign_key.'`';
                if(!empty($request['parameters']))
                {
                    if(array_key_exists('fields',$request['parameters']))
                    {
                        if($request['parameters']['fields'] == '*count*')
                        {
                            $the_select = '*count*';
                            $sub_results = (isset($the_select)) ? $sub_results->fields($the_select) : $sub_results;
                            $sub_results = $sub_results->fields($foreign_key);
                        }
                        else
                        {
                            $fields = explode(',', $request['parameters']['fields']);
                            foreach ($fields as $field)
                            {
                                $select[] = (strpos($field,'.')===FALSE) ? '`' . $foreign_table . '`.`' . trim($field) . '`' : trim($field);
                            }
                            $the_select = implode(',', $select);
                            $sub_results = (isset($the_select)) ? $sub_results->fields($the_select) : $sub_results;
                        }

                    }
                    if(array_key_exists('fields',$request['parameters']) && ($request['parameters']['fields']=='*count*'))
                    {
                        $sub_results->groupBy('`' . $foreign_table . '`.`' . $foreign_key . '`');
                    }
                    if(array_key_exists('where',$request['parameters']) || array_key_exists('non_exclusive_where',$request['parameters']))
                    {
                        $the_where = array_key_exists('where', $request['parameters']) ? 'where' : 'non_exclusive_where';
                    }
                    $sub_results = isset($the_where) ? $sub_results->where($request['parameters'][$the_where],NULL,NULL,FALSE,FALSE,TRUE) : $sub_results;

                    if(isset($order_inside_array))
                    {
                        foreach($order_inside_array as $order_by_inside)
                        {
                            $sub_results = $sub_results->order_by($order_by_inside[0],$order_by_inside[1]);
                        }
                    }

                    //Add nested relation
                    if(array_key_exists('with',$request['parameters']))
                    {
                        // Do we have many nested relation
                        if(is_array($request['parameters']['with']) && isset($request['parameters']['with'][0]))
                        {
                            foreach ($request['parameters']['with'] as $with)
                            {
                                $with_relation = array_shift($with);
                                $sub_results->with($with_relation, array($with));
                            }
                        }
                        else // single nested relation
                        {
                            $with_relation = array_shift($request['parameters']['with']);
                            $sub_results->with($with_relation,array($request['parameters']['with']));
                        }
                    }
                }

                $sub_results = $sub_results->where($foreign_key, $local_key_values)->getAll();
            }
            else
            {
                $this->_database->join($pivot_table, $foreign_table.'.'.$foreign_key.' = '.$pivot_table.'.'.$pivot_foreign_key, 'left');
                $this->_database->join($this->table, $pivot_table.'.'.$pivot_local_key.' = '.$this->table.'.'.$local_key,'left');
                $this->_database->select($foreign_table.'.'.$foreign_key);
                $this->_database->select($pivot_table.'.'.$pivot_local_key);
                if(!empty($request['parameters']))
                {
                    if(array_key_exists('fields',$request['parameters']))
                    {
                        if($request['parameters']['fields'] == '*count*')
                        {
                            $this->_database->select('COUNT(`'.$foreign_table.'`*) as result, `' . $foreign_table . '`.`' . $foreign_key . '`', FALSE);
                        }
                        else
                        {

                            $fields = explode(',', $request['parameters']['fields']);
                            $select = array();
                            foreach ($fields as $field) {
                                $select[] = (strpos($field,'.')===FALSE) ? '`' . $foreign_table . '`.`' . trim($field) . '`' : trim($field);
                            }
                            $the_select = implode(',', $select);
                            $this->_database->select($the_select);
                        }
                    }

                    if(array_key_exists('where',$request['parameters']) || array_key_exists('non_exclusive_where',$request['parameters']))
                    {
                        $the_where = array_key_exists('where',$request['parameters']) ? 'where' : 'non_exclusive_where';

                        $this->_database->where($request['parameters'][$the_where],NULL,NULL,FALSE,FALSE,TRUE);
                    }
                }
                $this->_database->where_in($pivot_table.'.'.$pivot_local_key,$local_key_values);

                if(!empty($order_inside_array))
                {
                    $order_inside_str = '';
                    foreach($order_inside_array as $order_by_inside)
                    {
                        $order_inside_str .= (strpos($order_by_inside[0],',')=== false) ? '`'.$foreign_table.'`.`'.$order_by_inside[0].' '.$order_by_inside[1] : $order_by_inside[0].' '.$order_by_inside[1];
                        $order_inside_str .= ',';
                    }
                    $order_inside_str = rtrim($order_inside_str, ",");
                    $this->_database->order_by(rtrim($order_inside_str,","));
                }
                $sub_results = $this->_database->get($foreign_table)->result_array();
                $this->_database->reset_query();
            }

            if(isset($sub_results) && !empty($sub_results)) {
                $subs = array();

                foreach ($sub_results as $result) {
                    $result_array = (array)$result;
                    $the_foreign_key = $result_array[$foreign_key];
                    if(isset($pivot_table))
                    {
                        $the_local_key = $result_array[$pivot_local_key];
                        if(isset($get_relate) and $get_relate === TRUE)
                        {
                            //$subs[$the_local_key][$the_foreign_key] = $this->{$foreign_model_name}->where($local_key, $result[$local_key])->get();
                            $subs[$the_local_key][$the_foreign_key] = $this->{$foreign_model_name}->where($foreign_key, $result[$foreign_key])->get();
                           }
                        else
                        {
                            $subs[$the_local_key][$the_foreign_key] = $result;
                        }
                    }
                    else
                    {
                        if ($type == 'has_one') {
                            $subs[$the_foreign_key] = $result;
                        } else {
                            $subs[$the_foreign_key][] = $result;
                        }
                    }


                }
                $sub_results = $subs;

                foreach($local_key_values as $key => $value)
                {
                    if(array_key_exists($value,$sub_results))
                    {
                        $data[$key][$relation_key] = $sub_results[$value];
                    }
                    else
                    {
                        if(array_key_exists('where',$request['parameters']))
                        {
                            unset($data[$key]);
                        }
                    }
                }
            }
            else
            {
                $data[$key][$relation_key] = NULL;
            }
            if(array_key_exists('order_by',$request['parameters']))
            {
                $elements = explode(',', $request['parameters']['order_by']);
                if(sizeof($elements)==2)
                {
                    $order_by[$relation_key] = array(trim($elements[0]), trim($elements[1]));
                }
                else
                {
                    $order_by[$relation_key] = array(trim($elements[0]), 'desc');
                }
            }
            unset($this->_requested[$requested_key]);
        }
        if(!empty($order_by))
        {
            foreach($order_by as $field => $row)
            {
                list($key, $value) = $row;
                $data = $this->_buildSorter($data, $field, $key, $value);
            }
        }
        return $data;
    }


    /**
     * private function _has_one($request)
     *
     * returns a joining of two tables depending on the $request relationship established in the constructor
     * @param $request
     * @return $this
     */
    private function _has_one($request)
    {
        $relation = $this->_relationships[$request];
        $this->_database->join($relation['foreign_table'], $relation['foreign_table'].'.'.$relation['foreign_key'].' = '.$this->table.'.'.$relation['local_key'], 'left');
        return TRUE;
    }

    /**
     * private function _setRelationships()
     *
     * Called by the public method with() it will set the relationships between the current model and other models
     */
    private function _setRelationships()
    {
        if(empty($this->_relationships))
        {
            $options = array('has_one','has_many','has_many_pivot');
            foreach($options as $option)
            {
                if(isset($this->{$option}) && !empty($this->{$option}))
                {
                    foreach($this->{$option} as $key => $relation)
                    {
                        if(!is_array($relation))
                        {
                            $foreign_model = $relation;
                            $this->load->model($foreign_model);
//                            $foreign_model_name = strtolower($foreign_model);
                            $foreign_model_name = (substr($foreign_model,strpos($foreign_model,'/')>0?strpos($foreign_model,'/')+1:0));
                            $foreign_table = $this->{$foreign_model_name}->table;
                            $foreign_key = $this->{$foreign_model_name}->primary_key;
                            $local_key = $this->primary_key;
                            $pivot_local_key = $this->table.'_'.$local_key;
                            $pivot_foreign_key = $foreign_table.'_'.$foreign_key;
                            $get_relate = FALSE;

                        }
                        else
                        {
                            if($this->_isAssoc($relation))
                            {
                                $foreign_model = $relation['foreign_model'];
                                if(array_key_exists('foreign_table',$relation))
                                {
                                    $foreign_table = $relation['foreign_table'];
                                }
                                else
                                {
//                                    $foreign_model_name = strtolower($foreign_model);
                                    $foreign_model_name = (substr($foreign_model,strpos($foreign_model,'/')>0?strpos($foreign_model,'/')+1:0));
                                    $this->load->model($foreign_model);
                                    $foreign_table = $this->{$foreign_model_name}->table;
                                }
                                $foreign_key = $relation['foreign_key'];
                                $local_key = $relation['local_key'];
                                if($option=='has_many_pivot')
                                {
                                    $pivot_table = $relation['pivot_table'];
                                    $pivot_local_key = (array_key_exists('pivot_local_key',$relation)) ? $relation['pivot_local_key'] : $this->table.'_'.$this->primary_key;
                                    $pivot_foreign_key = (array_key_exists('pivot_foreign_key',$relation)) ? $relation['pivot_foreign_key'] : $foreign_table.'_'.$foreign_key;
                                    $get_relate = (array_key_exists('get_relate',$relation) && ($relation['get_relate']===TRUE)) ? TRUE : FALSE;
                                }
                            }
                            else
                            {
                                $foreign_model = $relation[0];
//                                $foreign_model_name = strtolower($foreign_model);
                                $foreign_model_name = (substr($foreign_model,strpos($foreign_model,'/')>0?strpos($foreign_model,'/')+1:0));
                                $this->load->model($foreign_model);
                                $foreign_table = $this->{$foreign_model_name}->table;
                                $foreign_key = $relation[1];
                                $local_key = $relation[2];
                                if($option=='has_many_pivot')
                                {
                                    $pivot_local_key = $this->table.'_'.$this->primary_key;
                                    $pivot_foreign_key = $foreign_table.'_'.$foreign_key;
                                    $get_relate = (isset($relation[3]) && ($relation[3]===TRUE())) ? TRUE : FALSE;
                                }
                            }

                        }

                        if($option=='has_many_pivot' && !isset($pivot_table))
                        {
                            $tables = array($this->table, $foreign_table);
                            sort($tables);
                            $pivot_table = $tables[0].'_'.$tables[1];
                        }

                        $this->_relationships[$key] = array('relation' => $option, 'relation_key' => $key, 'foreign_model' => strtolower($foreign_model), 'foreign_table' => $foreign_table, 'foreign_key' => $foreign_key, 'local_key' => $local_key);
                        if($option == 'has_many_pivot')
                        {
                            $this->_relationships[$key]['pivot_table'] = $pivot_table;
                            $this->_relationships[$key]['pivot_local_key'] = $pivot_local_key;
                            $this->_relationships[$key]['pivot_foreign_key'] = $pivot_foreign_key;
                            $this->_relationships[$key]['get_relate'] = $get_relate;
                        }
                    }
                }
            }
        }
    }

    /** END RELATIONSHIPS */

    /**
     * public function on($connection_group = NULL)
     * Sets a different connection to use for a query
     * @param $connection_group = NULL - connection group in database setup
     * @return obj
     */
    public function on($connection_group = NULL)
    {
        if(isset($connection_group))
        {
            $this->_database->close();
            $this->load->database($connection_group);
            $this->_database = $this->_database;
        }
        return $this;
    }

    /**
     * public function resetConnection($connection_group = NULL)
     * Resets the connection to the default used for all the model
     * @return obj
     */
    public function resetConnection()
    {
        if(isset($connection_group))
        {
            $this->_database->close();
            $this->_setConnection();
        }
        return $this;
    }

    /**
     * Trigger an event and call its observers. Pass through the event name
     * (which looks for an instance variable $this->event_name), an array of
     * parameters to pass through and an optional 'last in interation' boolean
     */
    public function trigger($event, $data = array(), $additionalData = array())
    {
        if (isset($this->$event) && is_array($this->$event))
        {
            foreach ($this->$event as $method)
            {
                if (strpos($method, '('))
                {
                    preg_match('/([a-zA-Z0-9\_\-]+)(\(([a-zA-Z0-9\_\-\., ]+)\))?/', $method, $matches);
                    $method = $matches[1];
                    $this->callback_parameters = explode(',', $matches[3]);
                }
                $data = call_user_func_array(array($this, $method), array($data, $additionalData));
            }
        }
        return $data;
    }

    protected function registerPostChanges($data, $mode) {
        if ($this->register_changes === true && isset($this->register_changes_table) && $this->register_changes_table != '') {
            if (!isset($data[$this->primary_key]))
                return;
            $primary_key = $data[$this->primary_key];
            if ($primary_key == 0) {
                return;
            }
            unset ($data[$this->primary_key]);
            $changes = array();
            $changes['tabla'] = $this->table;
            $changes['modo'] = $mode;
            $changes['primary_id'] = $primary_key;
            if (isset($data[$this->register_changes_secondary_id])) {
                if (isset($data[$this->register_changes_secondary_id]['old'])) {
                    $changes['secondary_id'] = $data[$this->register_changes_secondary_id]['old'];
                } else {
                    $changes['secondary_id'] = $data[$this->register_changes_secondary_id];
                }
            }
            $changes['usuario_id'] = $this->session->userdata('user_id');
            if ($mode == 'U') {
                foreach ($data as $key => $val) {
                    if (in_array($key,$this->register_changes_exclude_fields)) {continue;}
                    $changes['campo'] = $key;
                    $changes['valor_old'] = $val['old'];
                    $changes['valor_new'] = $val['new'];
                    $this->_database->insert($this->register_changes_table,$changes);
                }
            } elseif ($mode == 'I') {
                $changes['valor_new'] = '';
                foreach ($data as $key => $val) {
                    if (in_array($key,$this->register_changes_exclude_fields)) {continue;}
                    $changes['valor_new'] .= ucfirst($key)." : $val\n";
                }
                $this->_database->insert($this->register_changes_table,$changes);
            } else {
                $changes['valor_old'] = '';
                foreach ($data as $key => $val) {
                    if (in_array($key,$this->register_changes_exclude_fields)) {continue;}
                    $changes['valor_old'] .= ucfirst($key)." : $val\n";
                }
                $this->_database->insert($this->register_changes_table,$changes);
            }
            return;
        }
    }
    /**
     * public function with_trashed()
     * Sets $_trashed to TRUE
     */
    public function with_trashed()
    {
        $this->_trashed = 'with';
        return $this;
    }

    /**
     * public function with_trashed()
     * Sets $_trashed to TRUE
     */
    public function only_trashed()
    {
        $this->_trashed = 'only';
        return $this;
    }

    private function _where_trashed()
    {
        switch($this->_trashed)
        {
            case 'only' :
                $this->_database->where($this->_deleted_at_field.' IS NOT NULL', NULL, FALSE);
                break;
            case 'without' :
                $this->_database->where($this->_deleted_at_field.' IS NULL', NULL, FALSE);
                break;
            case 'with' :
                break;
        }
        $this->_trashed = 'without';
        return $this;
    }

    /**
     * public function fields($fields)
     * does a select() of the $fields
     * @param $fields the fields needed
     * @return $this
     */
    public function fields($fields = NULL, $distinct = 'result')
    {
        if(isset($fields))
        {
            if ($fields == '*count-distinct*' && $distinct != '')
            {
                $this->_select = '';
                $this->_database->select('COUNT(DISTINCT '.$distinct.') AS '.$distinct,FALSE);
                $this->fields='*count*';
            }
            else if($fields == '*count*')
            {
                $this->_select = '';
                $this->_database->select('COUNT(1) AS '.$distinct,FALSE);
            }
            else if($fields == '*max*')
            {
                $this->_select = '';
                $this->_database->select('MAX('.$distinct.') AS '.$distinct,FALSE);
            }
            else if($fields == '*min*')
            {
                $this->_select = '';
                $this->_database->select('MIN('.$distinct.') AS '.$distinct,FALSE);
            }
            else if($fields == '*sum*')
            {
                $this->_select = '';
                $this->_database->select('SUM('.$distinct.') AS '.$distinct,FALSE);
            }
            else
            {
                $this->_select = array();
                $fields = (!is_array($fields)) ? explode(', ', $fields) : $fields;
                if (!empty($fields))
                {
                    foreach ($fields as &$field)
                    {
                        // Protect JSON new instructions
                        $field = str_replace('>"$.','>"$',$field);
                        $exploded = explode('.', $field);
                        if (sizeof($exploded) < 2)
                        {
                            $field = $this->table . '.' . $field;
                        }
                        $field = str_replace($this->table.'.FUNCTION','',$field);
                        $field = str_replace('>"$','>"$.',$field);
                    }
                }
                $this->_select = $fields;
            }
        }
        else
        {
            $this->_select = NULL;
        }
        return $this;
    }

    /**
     * public function orderBy($criteria, $order = 'ASC'
     * A wrapper to $this->_database->orderBy()
     * @param $criteria
     * @param string $order
     * @return $this
     */
    public function orderBy($criteria, $order = 'ASC')
    {
        if(is_array($criteria))
        {
            foreach ($criteria as $key=>$value)
            {
                if (substr($key,0,1)=='(') {
                    $this->_database->order_by($key, $value, false);
                }
                else {
                    $this->_database->order_by($key, $value);
                }
            }
        }
        else
        {
            if (substr($criteria,0,1)=='(') {
                $this->_database->order_by($criteria, $order, false);
            }
            else {
                $this->_database->order_by($criteria, $order);
            }
        }
        return $this;
    }

    /**
     * Return the next call as an array rather than an object
     */
    public function as_array()
    {
        $this->return_as = 'array';
        return $this;
    }

    /**
     * Return the next call as an object rather than an array
     */
    public function as_object()
    {
        $this->return_as = 'object';
        return $this;
    }

    public function as_dropdown($field = NULL,$pk = NULL)
    {
        if(!isset($field))
        {
            show_error('MY_Model: You must set a field to be set as value for the key: ...->as_dropdown(\'field\')->...');
            exit;
        }
        $this->return_as_dropdown = 'dropdown';
        $this->_dropdown_field = $field;
        $this->base_primary_key = $this->primary_key;
        $this->primary_key = isset($pk)?$pk:$this->primary_key;
        $this->_select = array($this->primary_key, $field);
        return $this;
    }

    protected function _getFromCache($cache_name = NULL)
    {
        if(isset($cache_name) || (isset($this->_cache) && !empty($this->_cache)))
        {
            $this->load->driver('cache');
            $cache_name = isset($cache_name) ? $cache_name : $this->_cache['cache_name'];
            $data = $this->cache->{$this->cache_driver}->get($cache_name);
            return $data;
        }
    }

    protected function _writeToCache($data, $cache_name = NULL)
    {
        if(isset($cache_name) || (isset($this->_cache) && !empty($this->_cache)))
        {
            $this->load->driver('cache');
            $cache_name = isset($cache_name) ? $cache_name : $this->_cache['cache_name'];
            $seconds = $this->_cache['seconds'];
            if(isset($cache_name) && isset($seconds))
            {
                $this->cache->{$this->cache_driver}->save($cache_name, $data, $seconds);
                $this->_resetCache($cache_name);
                return TRUE;
            }
            return FALSE;
        }
    }

    public function setCache($string, $seconds = 86400)
    {
        $prefix = (strlen($this->cache_prefix)>0) ? $this->cache_prefix.'_' : '';
        $prefix .= $this->table.'_';
        $this->_cache = array('cache_name' => $prefix.$string,'seconds'=>$seconds);
        return $this;
    }

    private function _resetCache($string)
    {
        if(isset($string))
        {
            $this->_cache = array();
        }
        return $this;
    }

    public function deleteCache($string = NULL)
    {
        $this->load->driver('cache');
        $prefix = (strlen($this->cache_prefix)>0) ? $this->cache_prefix.'_' : '';
        if(isset($string) && (strpos($string,'*') === FALSE))
        {
            $this->cache->{$this->cache_driver}->delete($prefix . $string);
        }
        else
        {
            $cached = $this->cache->file->cache_info();
            foreach($cached as $file)
            {
                if(array_key_exists('relative_path',$file))
                {
                    $path = $file['relative_path'];
                    break;
                }
            }
            $mask = (isset($string)) ? $path.$prefix.$string : $path.$this->cache_prefix.'_*';
            array_map('unlink', glob($mask));
        }
        return $this;
    }

    /**
     * private function _setTimestamps()
     *
     * Sets the fields for the created_at, updated_at and deleted_at timestamps
     * @return bool
     */
    private function _setTimestamps()
    {
        if($this->timestamps !== FALSE)
        {
            $this->_created_at_field = (is_array($this->timestamps) && isset($this->timestamps[0])) ? $this->timestamps[0] : '';
            $this->_updated_at_field = (is_array($this->timestamps) && isset($this->timestamps[1])) ? $this->timestamps[1] : '';
            $this->_deleted_at_field = (is_array($this->timestamps) && isset($this->timestamps[2])) ? $this->timestamps[2] : '';
        }
        return TRUE;
    }

    /**
     * private function _theTimestamp()
     *
     * returns a value representing the date/time depending on the timestamp format choosed
     * @return string
     */
    private function _theTimestamp()
    {
        if($this->timestamps_format=='timestamp')
        {
            return time();
        }
        else
        {
            return date($this->timestamps_format);
        }
    }

    /**
     * private function _setConnection()
     *
     * Sets the connection to database
     */
    private function _setConnection()
    {
        if(isset($this->_database_connection))
        {
            $this->_database = $this->load->database($this->_database_connection,TRUE);
        }
        else
        {
            $this->load->database();
            $this->_database = $this->db;
        }
        // This may not be required
        return $this;
    }

    /*
     * HELPER FUNCTIONS
     */

    public function paginate($rows_per_page, $total_rows = NULL, $page_number = 1)
    {
        $this->load->helper('url');
        $segments = $this->uri->total_segments();
        $uri_array = $this->uri->segment_array();
        $page = $this->uri->segment($segments);
        if(is_numeric($page))
        {
            $page_number = $page;
        }
        else
        {
            $page_number = $page_number;
            $uri_array[] = $page_number;
            ++$segments;
        }
        $next_page = $page_number+1;
        $previous_page = $page_number-1;

        if($page_number == 1)
        {
            $this->previous_page = $this->pagination_delimiters[0].$this->pagination_arrows[0].$this->pagination_delimiters[1];
        }
        else
        {
            $uri_array[$segments] = $previous_page;
            $uri_string = implode('/',$uri_array);
            $this->previous_page = $this->pagination_delimiters[0].anchor($uri_string,$this->pagination_arrows[0]).$this->pagination_delimiters[1];
        }
        $uri_array[$segments] = $next_page;
        $uri_string = implode('/',$uri_array);
        if(isset($total_rows) && (ceil($total_rows/$rows_per_page) == $page_number))
        {
            $this->next_page = $this->pagination_delimiters[0].$this->pagination_arrows[1].$this->pagination_delimiters[1];
        }
        else
        {
            $this->next_page = $this->pagination_delimiters[0].anchor($uri_string, $this->pagination_arrows[1]).$this->pagination_delimiters[1];
        }

        $rows_per_page = (is_numeric($rows_per_page)) ? $rows_per_page : 10;

        if(isset($total_rows))
        {
            if($total_rows!=0)
            {
                $number_of_pages = ceil($total_rows / $rows_per_page);
                $links = $this->previous_page;
                for ($i = 1; $i <= $number_of_pages; $i++) {
                    unset($uri_array[$segments]);
                    $uri_string = implode('/', $uri_array);
                    $links .= $this->pagination_delimiters[0];
                    $links .= (($page_number == $i) ? anchor($uri_string, $i) : anchor($uri_string . '/' . $i, $i));
                    $links .= $this->pagination_delimiters[1];
                }
                $links .= $this->next_page;
                $this->all_pages = $links;
            }
            else
            {
                $this->all_pages = $this->pagination_delimiters[0].$this->pagination_delimiters[1];
            }
        }


        if(isset($this->_cache) && !empty($this->_cache))
        {
            $this->load->driver('cache');
            $cache_name = $this->_cache['cache_name'].'_'.$page_number;
            $seconds = $this->_cache['seconds'];
            $data = $this->cache->{$this->cache_driver}->get($cache_name);
        }

        if(isset($data) && $data !== FALSE)
        {
            return $data;
        }
        else
        {
            $this->trigger('before_get');
            $this->where();
            $this->limit($rows_per_page, (($page_number-1)*$rows_per_page));
            $data = $this->getAll();
            if($data)
            {
                if(isset($cache_name) && isset($seconds))
                {
                    $this->cache->{$this->cache_driver}->save($cache_name, $data, $seconds);
                    $this->_resetCache($cache_name);
                }
                return $data;
            }
            else
            {
                return FALSE;
            }
        }
    }

    public function setPaginationDelimiters($delimiters)
    {
        if(is_array($delimiters) && sizeof($delimiters)==2)
        {
            $this->pagination_delimiters = $delimiters;
        }
        return $this;
    }

    public function setPaginationArrows($arrows)
    {
        if(is_array($arrows) && sizeof($arrows)==2)
        {
            $this->pagination_arrows = $arrows;
        }
        return $this;
    }

    /**
     * private function _fetchTable()
     *
     * Sets the table name when called by the constructor
     *
     */
    private function _fetchTable()
    {
        if (!isset($this->table))
        {
            // Commented, we don't want to have automatic model name.
            //$this->table = $this->_getTableName(get_class($this));
        }
        return TRUE;
    }
    private function _getTableName($model_name)
    {
        $table_name = plural(preg_replace('/(_m|_model|_mdl)?$/', '', strtolower($model_name)));
        return $table_name;
    }

    public function __call($method, array $arguments)
    {
        if(substr($method,0,6) == 'where_')
        {
            $column = substr($method,6);
            $this->where($column, $arguments);
            return $this;
        }
        if(($method!='with_trashed') && (substr($method,0,5) == 'with_'))
        {
            $relation = substr($method,5);
            $this->with($relation,$arguments);
            return $this;
        }
        if (substr($method,0,3)=='get') {
            $field = strtolower(substr($method,3));
            foreach ($this->fields as $key => $val) {
                if (strtolower($key) == $field) {
                    return $this->fields->$key;
                }
            }
            return false;
        }
        if (substr($method,0,3)=='set') {
            $field = strtolower(substr($method,3));
            foreach ($this->fields as $key => $val) {
                if (strtolower($key) == $field) {
                    $this->fields->$key = $arguments[0];
                    return true;
                }
            }
            return false;
        }

        $parent_class = get_parent_class($this);
        if ($parent_class !== FALSE && !method_exists($parent_class, $method) && !method_exists($this,$method))
        {
            echo 'No method with that name ('.$method.') in MY_Model or CI_Model.';
        }
    }

    private function _buildSorter($data, $field, $order_by, $sort_by = 'DESC')
    {
        usort($data, function($a, $b) use ($field, $order_by, $sort_by) {
            $array_a = $this->objectToArray($a[$field]);
            $array_b = $this->objectToArray($b[$field]);
            return strtoupper($sort_by) ==  "DESC" ? ((isset($array_a[$order_by]) && isset($array_b[$order_by])) ? ($array_a[$order_by] < $array_b[$order_by]) : -1) : ((isset($array_a[$order_by]) && isset($array_b[$order_by])) ? ($array_a[$order_by] > $array_b[$order_by]) : -1);
        });

        return $data;
    }

    public function objectToArray( $object )
    {
        if( !is_object( $object ) && !is_array( $object ) )
        {
            return $object;
        }
        if( is_object( $object ) )
        {
            $object = get_object_vars( $object );
        }
        return array_map( array($this,'objectToArray'), $object );
    }

    /**
     * Verifies if an array is associative or not
     * @param array $array
     * @return bool
     */
    private function _isAssoc(array $array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /*
    public function add_creator($data)
    {
    	$data['created_by'] = $_SESSION['user_id'];
    	return $data;
    }
    */

    /*
    public function add_updater($data)
    {
	    $data['updated_by'] = $_SESSION['user_id'];
	    return $data;
    }
    */

    protected function genIDX()
    {
        $str   = uniqid("$", true);
        $rand = substr(md5(microtime()), 0, 1);
        $str   = preg_replace('/\./', $rand, $str);
        return $str;
    }

    public function flushCache() {
        $this->joinedTables = array();
        $this->_database->flush_cache();
        if (isset($this->base_primary_key)) {
            $this->primary_key = $this->base_primary_key;
            $this->base_primary_key=null;
        }
    }

    public function count_all_results($table, $flush=false) {
        $result = $this->_database->count_all_results($table);
        $this->joinedTables = array();
        if ($flush) {
            $this->_databases->flush_cache();
        }
        return $result;

    }
    
    public function addPaginate($result, $defaultUrl, $offset = DEFAULT_QUERY_OFFSET, $showTotals=true)
    {
        $query_page_string = 'pageNum';
        $this->load->library('pagination');
        $cur_page          = $this->input->post_get($query_page_string);
        if ($cur_page == 0) {
            $cur_page = 1;
        }

        if ($result->totalRows == 0) {
            $result->pagination = '';
            return $result;
        }
        $config['full_tag_open']        = '<!--pagination--><div class="pagination-div"><ul class="pagination pagination-working pagination-sm">';
        $curRowsMax = (min($offset * $cur_page, $result->totalRows));
        $curRowsMin = ($offset * ($cur_page-1))+1;
        $curRowsTot = ' / '.$result->totalRows;
        if ($result->totalRows <= $offset) {
            $curRowsTot = '';
        }
        $config['full_tag_close']       = '</ul>&nbsp;<ul class="pagination pagination-sm"><li class=""><span> '.$curRowsMin.' - '.$curRowsMax.' '.$curRowsTot.'</span></li></ul></div><!--pagination-->';
        $config['first_link']           = _('&laquo;');
        $config['first_tag_open']       = '<li class="prev page">';
        $config['first_tag_close']      = '</li>';
        $config['last_link']            = _('&raquo;');
        $config['last_tag_open']        = '<li class="next page">';
        $config['last_tag_close']       = '</li>';
        $config['next_link']            = _('Next');
        $config['next_tag_open']        = '<li class="next page">';
        $config['next_tag_close']       = '</li>';
        $config['prev_link']            = _('Prev');
        $config['prev_tag_open']        = '<li class="prev page">';
        $config['prev_tag_close']       = '</li>';
        $config['cur_tag_open']         = '<li class="active"><a href="">';
        $config['cur_tag_close']        = '</a></li>';
        $config['num_tag_open']         = '<li class="page">';
        $config['num_tag_close']        = '</li>';
        $config['anchor_class']         = 'follow_link';
        $config['page_query_string']    = TRUE;
        $config['enable_query_strings'] = TRUE;
        $config['query_string_segment'] = $query_page_string;
        $config['reuse_query_string']   = TRUE;
        $config['use_page_numbers']     = TRUE;
        $config['base_url']             = $defaultUrl != '' ? $defaultUrl : $_SERVER["REDIRECT_URL"];
        $config['total_rows']           = $result->totalRows;
        $config['per_page']             = $offset;
        $config['num_links']            = 4;
        $config['attributes']           = array();

        $this->pagination->initialize($config);
        $result->pagination = $this->pagination->create_links();

        if ($result->pagination == '' && $showTotals) {
            $result->pagination .= $config['full_tag_open']."\n";
            $result->pagination .= $config['full_tag_close']."\n";
        }
        return $result;
    }

    /**
     * getListTablesVars - Get the basic params for listTablesControl
     * @return type
     */
    public function getListTablesVars()
    {
        $filter   = $this->input->post_get('filter') ? $this->input->post_get('filter') : '';
        $fieldOrd = $this->input->post_get('fieldOrd') ? $this->input->post_get('fieldOrd') : '';
        $pageNum  = $this->input->post_get('pageNum') ? $this->input->post_get('pageNum') : 1;
        $pagePag  = $this->input->post_get('pagePag') ? $this->input->post_get('pagePag') : 0;

        $defaultUrl = $_SERVER["REDIRECT_URL"];

        if (substr($filter, 0, 1) == '{') {
            $filter = (array) json_decode($filter);
        }

        return array($filter, $fieldOrd, $pageNum, $defaultUrl, $pagePag);
    }

    public function searchAllPaginate($fields, $filter, $pageNum = 1, $offset = DEFAULT_QUERY_OFFSET, $orderBy = array(), $defaultUrl = '')
    {
        $result = $this->searchAll($fields, $pageNum, $orderBy, $filter, $offset, false);
        $defaultUrl != '' ? $defaultUrl : $_SERVER["REDIRECT_URL"];
        $result = $this->addPaginate($result, $defaultUrl);

        return $result;
    }


    public function searchAll($fields, $pageNum=1, $orderBy, $filter='', $offset=DEFAULT_QUERY_OFFSET, $onlycount=false)
    {
        $joinedfilter = 0;
        $this->db->start_cache();

        list($otherJoinedfilter,$returnCode) = $this->setModelFilter($filter);
        if ($returnCode == -1) { // No se encuentran registro en multiple search
            $result = new stdClass();
            $result->result = array();
            $result->numRows = 0;
            $result->totalRows = 0;
            $this->resetQuery();
            return $result;
        }
        if ($returnCode > 0) { // Se ha producido error en la consulta
            $result = new stdClass();
            $result->result = array();
            $result->numRows = 0;
            $result->totalRows = 0;
            $result->message = _l('generic.records_not_found_bad_filter');
            $result->errcode = 1010;
            $this->resetQuery();
            return $result;
        }

        $joinedfilter = $joinedfilter || $otherJoinedfilter;
        $this->db->stop_cache();

        if ($joinedfilter) {
            $this->db->distinct();
            $this->db->select($this->primary_key);
        }

        $result_count = $this->db->count_all_results($this->table);
        if ($onlycount) {
            $this->resetQuery();
            return $result_count;
        }

        $regs = array();
        if ((int) $result_count > 0) {
            if ($joinedfilter) {
                $this->db->distinct();
            }
            $this->fields($fields);

            $this->setOrderBy($orderBy);

            $this->limit($offset, $offset*($pageNum>0?$pageNum-1:0) );

            $regs = $this->getAll();
        }
        $this->resetQuery();

        $result = new stdClass();
        $result->result = $regs;
        $result->numRows = count($regs);
        $result->totalRows = (int)$result_count;

        return $result;

    }

    public function setModelFilter($filter)
    {
        $groupAnd = array();
        $groupOr  = array();
        $filter3  = array();
        $returnCode = 0;
        $isArrayFilter = false;
        $filter2 = array();

        if (is_string($filter) && $filter != '') {
            $filter2  = json_decode($filter, false);
            if ($filter2 == null) {
                $filter2 = array();
            }
        } elseif (is_array($filter) && count($filter)>0) {
            $isArrayFilter = true;
        }
        $groupStart = 0;

        foreach ($filter2 as $key => $val) {
            // Tratamos los grupos AND
            if (isset($val->group) && $val->group == 'and') {
                $whereNum = 0;
                if (isset($val->filters) && (isset($val->filters[0]->group) || isset($val->filters[0]->fields))) {
                    $this->db->group_start();
                    $groupStart++;
                    $this->setCustomSimpleWhere('"1"', 'eq', 1, 'and');
                }

                foreach ($val->filters as $key2 => $val2) {
                    // Si hay anidación de grupo AND
                    if (isset($val2->group) && isset($val2->filters) && $val2->group == 'and') {
                        foreach ($val2->filters as $key3 => $val3) {
                            foreach ($val3->fields as $key4 => $val4) {
                                $andOr = 'and';
                                $returnCode += $this->setOneFilter($key4, $val4, $andOr);
                                $whereNum++;
                            }
                        }
                    // Si hay anidación de grupo OR, NO se trata
                    } elseif (isset($val2->group) && isset($val2->filters) && $val2->group == 'or') {
//                        $orGroupStarted = 0;
//                        if ($key2 == 0 && $orGroupStarted > 0) {
//                            if ($groupStart > 0) {
//                                $this->db->group_end();
//                                $groupStart--;
//                            } else {
//                                $this->db->group_start();
//                                $groupStart++;
//                            }
//                        }
//                        foreach ($val2->filters as $key3 => $val3) {
//                            foreach ($val3->fields as $key4 => $val4) {
//                                $andOr = 'or';
//                                $returnCode += $this->setOneFilter($key4, $val4, $andOr);
//                                $whereNum++;
//                            }
//                        }
//                        $orGroupStarted = 0;
                        return array(0, 1001);
                    // Si es el formato sin anidación
                    } elseif (isset($val2->fields)) {
                        foreach ($val2->fields as $key3 => $val3) {
                            $andOr = $val2->group=='and'?'and':'or';
                            $returnCode += $this->setOneFilter($key3, $val3, $andOr);
                            $whereNum++;
                        }
                    }
                }
                // Miramos si hace falta cerrar grupo
                while ($groupStart > 0) {
                    $this->db->group_end();
                    $groupStart--;
                }
            }
            // Tratamos los grupos OR
            elseif (isset($val->group) && $val->group == 'or') {
                $whereNum = 0;
                $this->db->group_start();
                $groupStart++;
                $orGroupStarted = 0;

                foreach ($val->filters as $key2 => $val2) {
                    // Si hay anidación de grupo AND
                    if (isset($val2->group) && isset($val2->filters) && $val2->group == 'and') {
                        if ($key2 > 0 && $orGroupStarted == 0) {
                            $this->db->or_group_start();
                            $groupStart++;
                            $orGroupStarted = 1;
                        } else {
                            $this->db->group_start();
                            $groupStart++;
                        }
                        foreach ($val2->filters as $key3 => $val3) {
                            foreach ($val3->fields as $key4 => $val4) {
                                $andOr = 'and';
                                $returnCode += $this->setOneFilter($key4, $val4, $andOr);
                                $whereNum++;
                            }
                        }
                    // Si hay anidación de grupo OR
                    } elseif (isset($val2->group) && isset($val2->filters) && $val2->group == 'or') {
                        if ($key2 == 0 && $orGroupStarted > 0) {
                            if ($groupStart > 0) {
                                $this->db->group_end();
                                $groupStart--;
                            } else {
                                $this->db->group_start();
                                $groupStart++;
                            }
                        }
                        foreach ($val2->filters as $key3 => $val3) {
                            foreach ($val3->fields as $key4 => $val4) {
                                $andOr = 'or';
                                $returnCode += $this->setOneFilter($key4, $val4, $andOr);
                                $whereNum++;
                            }
                        }
                    // Si es el formato sin anidación
                    } else {
                        foreach ($val2->fields as $key3 => $val3) {
                            $andOr = 'or';
                            $returnCode += $this->setOneFilter($key3, $val3, $andOr);
                            $whereNum++;
                        }
                    }
                    $orGroupStarted = 0;
                }
                if ($whereNum == 0) {
                    $this->setCustomSimpleWhere('"1"', 'eq', 1, 'and');
                }
                if ($groupStart > 0) {
                    $this->db->group_end();
                    $groupStart--;
                }
            }
            // Si no es format GRUPO, vienen los valores tal cual
            else {
                if (is_numeric($key) && is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $returnCode += $this->setOneFilter($key2, array((object) array('cond' => 'eq', 'value' => $val2)), 'and');
                    }
                } elseif (is_numeric($key) && is_object($val)) {
                    $returnCode += $this->setOneFilter($val->key, $val, 'and');
                } else {
                    $returnCode += $this->setOneFilter($key, array((object) array('cond' => 'eq', 'value' => $val)), 'and');
                }
            }
            if ($groupStart > 0) {
                $this->db->group_end();
                $groupStart--;
            }
        }
        while ($groupStart > 0) {
            $this->db->group_end();
            $groupStart--;
        }

        // Si le filtro NO ha llegado en formato JSON, sino en formato ARRAY
        if ($isArrayFilter) {
            foreach ($filter as $key => $val) {
                if (is_numeric($key) && is_array($val)) {
                    if (isset($val['key'])) {
                        $returnCode += $this->setOneFilter($val['key'], array((object) array('cond' => $val['cond'], 'value' => $val['value'])), 'and');
                    } else {
                        foreach ($val as $key2 => $val2) {
                            $returnCode += $this->setOneFilter($key2, array((object) array('cond' => 'eq', 'value' => $val2)), 'and');
                        }
                    }
                } else {
                    $cond = 'eq';
                    if (substr($key,strlen($key)-1,1) == '>') {
                        $cond = 'gt';
                    }
                    if (substr($key,strlen($key)-1,1) == '<') {
                        $cond = 'lt';
                    }
                    if (substr($key,strlen($key)-2,2) == '>=') {
                        $cond = 'gt';
                    }
                    if (substr($key,strlen($key)-2,2) == '<=') {
                        $cond = 'lt';
                    }
                    if (substr($key,strlen($key)-2,2) == '!=') {
                        $cond = 'ne';
                    }
                    if (substr($key,strlen($key)-2,2) == '<>') {
                        $cond = 'ne';
                    }
                    $returnCode += $this->setOneFilter($key, array((object) array('cond' => $cond, 'value' => $val)), 'and');
                }
            }
        }

        return array(count($this->joinedTables), $returnCode);
    }

    public function setOneFilter($key, $values, $andOr = 'and', $escape = true) {
        $returnCode = 0;
        $cond = '';
        $val = '';

        if (is_object($values)) {
            $values = array($values);
        }

        if (count($values)==0) {
            $this->setCustomSimpleWhere('"1"', 'eq', 1, 'and');
            return;
        } elseif (count($values)==1) {
            $values2 = $values[0];
            $cond = isset($values2->cond)?$values2->cond:'eq';
            $val = isset($values2->value)?$values2->value:'';
            $tipocampo = 1;
            if (is_numeric($key)) {
                $tipocampo = 0;
            }
            if ($tipocampo == 1 && ($key == '' || ($val == '' && ($cond == '' || $cond=='eq')))) {
               $this->setCustomSimpleWhere('"1"', 'eq', 1, 'and');
               return;
            }
        }
        $filterlineField = '';

        if (substr($key, 0, 1) == '@') {
            $key = str_replace('@&','@',$key);
            $key = explode('@',$key);
            $key = '@'.$key[1];
            $customfilter = isset($this->orderAndFilterFieldsSubstitution[$key]) ? $this->orderAndFilterFieldsSubstitution[$key][0] : '';
            if ($customfilter != '') {
                $filterlineField = isset($this->orderAndFilterFieldsSubstitution[$key][1])?$this->orderAndFilterFieldsSubstitution[$key][1]: false;
                $aliasTableField = isset($this->orderAndFilterFieldsSubstitution[$key][2]) ? $this->orderAndFilterFieldsSubstitution[$key][2] : false;
                foreach ($values as $filter) {
                    $return = $this->{'setCustomFilter'.ucfirst($customfilter)}($filter->value, $filterlineField, $filter->cond, $andOr, $aliasTableField);
                    if ($return === false) {
                        $this->setCustomWhere($filterlineField, $values, $andOr);
                    }
                }
            }
        } elseif (is_numeric($key)) {
            $key = str_replace('#','',$key);
            $returnCode += $this->setCustomFilter($key, $values, $andOr);
        } else {
            $field = $key;
            $field = preg_replace('/@.*/', '', $field);
            if ($escape == true) {
                $field = preg_replace('/[^\w-_|]/i', '', $field);
            }
            if (isset($this->orderAndFilterFieldsSubstitution[$field])) {
                $filterlineField = $this->orderAndFilterFieldsSubstitution[$field];
                if (is_array($filterlineField)) {
                    $this->dbJoin($filterlineField[1], $filterlineField[2], $andOr);
                    $hasJoins = true;
                    $filterlineField = $filterlineField[0];
                }
                if (strstr($filterlineField,'||') && $val != '') {
                    $fields = explode('||',$filterlineField);
                    $this->db->group_start();
                    foreach ($fields as $field2) {
                        $this->setOneFilter($field2, array((object) array('cond' => 'lk', 'value' => $val)), 'or', false);
                    }
                    $this->db->group_end();
                } else {
                    $this->setCustomWhere($filterlineField, $values, $andOr);
                }
            } else {
                $this->setCustomWhere($field, array((object) array('cond' => 'lk', 'value' => $val)), $andOr);
            }
        }
        return $returnCode;
    }

    protected function setCustomSimpleWhere($field, $cond, $val = '', $andOr = 'and') {
        $this->setCustomWhere($field, array((object) array('cond' => $cond, 'value' => trim($val))), $andOr);
    }

    protected function setCustomWhere($field, $values, $andOr = 'and')
    {
        foreach ($values as $key => $val) {
            $cond = isset($val->cond)?$val->cond:'eq';
            if (is_array($val->value)) {
                $val = $val->value;
            } else {
                $val = trim(isset($val->value)?$val->value:'');
                $val = preg_replace("/[><]/u", '', $val);
                $val = preg_replace("/[^[:alnum:]]/u", '', mb_substr($val,0,1)).(mb_strlen($val)>1?mb_substr($val,1):'');
            }

            if ($field == '' || ($val == '' && ($cond == '' || $cond=='ls' || $cond=='in' || $cond=='lk' || $cond=='eq' || $cond == 'ft'))) {
                return;
            }

            $with_or = ($andOr=='or'?true:false);
            $with_not = false;
            $operator_or_value = '=';
            $value = $val;
            $custom_where = false;
            $common_where = true;
            switch ($cond) {
                case 'eq': // Equal
                    $operator_or_value = '=';
                    if ($val == '') {
                        return;
                    }
                    break;
                case 'ne': // Not equal
                    $operator_or_value = '=';
                    $with_not          = true;
                    break;
                case 'lk': // Like contains
                    $operator_or_value = 'like';
                    $value             = '%'.trim($value, '%').'%';
                    break;
                case 'ls': // Like start with
                    $operator_or_value = 'like';
                    $value             = trim($value, '%').'%';
                    break;
                case 'nl': //  Not like contains
                    $operator_or_value = 'like';
                    $with_not          = true;
                    $value             = '%'.trim('%', $value).'%';
                    break;
                case 'ns': //  Not like starts
                    $operator_or_value = 'like';
                    $with_not          = true;
                    break;
                case 'in': // In multiple values
                    $operator_or_value = $value;
                    $value             = null;
                    break;
                case 'ni': // Not in multiple values
                    $operator_or_value = $value;
                    $with_not          = true;
                    $value             = null;
                    break;
                case 'gt': // Greather than
                    $operator_or_value = '>';
                    break;
                case 'ge': // Greather or equal than
                    $operator_or_value = '>=';
                    break;
                case 'lt': // Lower than
                    $operator_or_value = '<';
                    break;
                case 'le': // Lower or equal than
                    $operator_or_value = '<=';
                    break;
                case 'nu': // Null
                    $operator_or_value = 'null';
                    break;
                case 'nb': // Not blank
                    $operator_or_value = 'null';
                    $with_not          = true;
                    $value             = '';
                    break;
                case 'nn': // Not null
                    $operator_or_value = 'not null';
                    $value             = '';
                    break;
                case "ft": // Full text search
                    $match = "";
                    if (trim($val) == '') {
                        return;
                    }
                    foreach (explode(' ',$val) as $pos => $search) {
                        $match .= "+$search* ";
                    }
                    $match = "MATCH($field) AGAINST ('".trim($match)."' IN BOOLEAN MODE)";
                    $field = $match;
                    $operator_or_value = null;
                    $value = null;
                    $custom_where = true;
                    break;
            }
            $this->where($field, $operator_or_value, $value, $with_or, $with_not, $custom_where);
            //$this->db->where('MATCH (field) AGAINST ("value")', NULL, FALSE);
        }
    }


    public function setOrderBy($orderBy)
    {
        // Old orderBy, must to not be used any more
        if (is_string($orderBy) && strstr($orderBy, ':')) {
            $orderBy    = explode('|', $orderBy);
            $newOrderBy = array();
            foreach ($orderBy as $key => $val) {
                $orderline                 = explode(':', $val);
                $newOrderBy[$orderline[0]] = $orderline[1];
            }
            $orderBy = $newOrderBy;
        }

        // Is its ab object
        if (is_object($orderBy)) {
            $orderBy = (array) $orderBy;
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $key => $val) {
                if ($val == 0 || (!isset($this->orderAndFilterFieldsSubstitution[$key]) && !isset($this->orderAndFilterFieldsSubstitution['@'.$key]))) {
                    continue;
                }

                if (isset($this->orderAndFilterFieldsSubstitution['@'.$key]))
                {
                    $customfilter = isset($this->orderAndFilterFieldsSubstitution['@'.$key]) ? $this->orderAndFilterFieldsSubstitution['@'.$key][0] : '';
                    $return = $this->{'setCustomFilter'.ucfirst($customfilter)}(0);
                    if ($return == false) {
                        $orderlineField = $this->orderAndFilterFieldsSubstitution['@'.$key][1];
                    } else {
                        continue;
                    }
                }
                else {
                    $orderlineField = $this->orderAndFilterFieldsSubstitution[$key];
                }

                if (is_array($orderlineField)) {
                    $this->db->join($orderlineField[1], $orderlineField[2]);
                    $orderlineField = $orderlineField[0];
                }
                if ($val == 1) {
                    $this->orderBy($orderlineField, 'ASC');
                }
                if ($val == 2) {
                    $this->orderBy($orderlineField, 'DESC');
                }
            }
            return true;
        } else {
            if ($orderBy != '') {
                $this->orderBy('('.$orderBy.')');
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * dbJoin - Permite hacer joins de tablas, pero evitando duplicados.
     * @param type $table
     * @param type $cond
     * @param type $type
     * @param type $escape
     */
    public function dbJoin($table, $cond, $type = '', $escape = NULL)
    {
        // If it's a automatic join, can recieve the condition_operator, that forces join type
        if (strtolower($type) == 'and') $type = 'INNER';
        if (strtolower($type) == 'or') $type = 'LEFT';
        if (!isset($this->joinedTables[$table][$cond])) {
            $this->joinedTables[$table][$cond] = 1;
            $this->db->join($table, $cond, $type, $escape);
        }
    }

    public function dbJoinCount()
    {
        return count($this->joinedTables);
    }

    public function getAll($where = NULL, $debug=false)
    {
        $result = $this->get_all($where, $debug);
        $this->joinedTables = array();
        return $result;

    }

    public function resetQuery() {
        $this->joinedTables = array();
        $this->db->reset_query();
        $this->db->flush_cache();
    }

}

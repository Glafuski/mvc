<?php

namespace Core\App\Models;
class MainModel {
    protected $model;
    protected $mname;
    protected $conn;
    public function CallModel($name) {
        \plugin::load('db');
        $this->conn = \Core\App\DB::Connect();
        $name = ucfirst($name);
        require MODEL_PATH . '/' . $name .'.php';
        $modelname = 'Core\App\Models\\' . $name;
        $this->model = new $modelname;
        if(isset($this->model->rules['table'])) {
            $this->mname = $this->model->rules['table'];
        }
        else {
            $this->mname = $name;
        }
    }
    /*  
     *  
     *  
     */ 
    public function BelongsTo() {

    }
    public function HasMany() {

    }
    /*
     *  @return true||false - False means that datatype is not correct
     * 
     */
    public function CheckDataType($data) {
        $rvalue = [
            'status' => true
        ];
        foreach($this->model->rules as $name => $rules) {
            if(isset($rules['type'])) {
                switch($rules['type']) {
                    case 'string':
                        if(isset($data[$name])) {
                            if(!is_string($data[$name])) {
                                $rvalue = [
                                    'status' => false,
                                    'column' => $name,
                                    'message' => 'notstring'
                                ];
                            }
                        }
                    break;
                    case 'array':
                        if(isset($data[$name])) {
                            if(!is_array($data[$name])) {
                                $rvalue = [
                                    'status' => false,
                                    'column' => $name,
                                    'message' => 'notarray'
                                ];
                            }
                        }
                    break;
                    case 'int':
                        if(isset($data[$name])) {
                            if(!is_int($data[$name])) {
                                $rvalue = [
                                    'status' => false,
                                    'column' => $name,
                                    'message' => 'notinteger'
                                ];
                            }
                        }
                    break;
                    case 'decimal':
                        if(isset($data[$name])) {
                            if(!is_float($data[$name])) {
                                $rvalue = [
                                    'status' => false,
                                    'column' => $name,
                                    'message' => 'notdecimal'
                                ];
                            }
                        }
                    break;
                    case 'date': 
                        
                    break;
                    default:
                        return 'No proper datatype';
                    break;
                }
            }
        }
        return $rvalue;
    }
    /*
     *  @return true||false - False means that data length is not correct
     * 
     */
    public function CheckLength($data) {
        $rvalue = [
            'status' => true
        ];
        foreach($this->model->rules as $name => $rules) {
            if(isset($rules['length'])) {
                if(isset($data[$name])) {
                    if(strlen($data[$name]) > $rules['length']) {
                        $rvalue = [
                            'status' => false,
                            'column' => $name,
                            'message' => 'toolong'
                        ];
                        continue;
                    }
                }
            }
        }
        return $rvalue;
    }
    /*
     *  IsRequired() 
     *  @return true||false - False means that data is not set.
     * 
     */
    public function IsRequired($data) {
        $rvalue = [
            'status' => true
        ];
        foreach($this->model->rules as $name => $rules) {
            if(isset($rules['required'])) {
                if($rules['required'] === true) {
                    if(empty($data[$name])) {
                        $rvalue = [
                            'status' => false,
                            'column' => $name,
                            'message' => 'is empty'
                        ];
                        continue;
                    }
                }
            }
        }
        return $rvalue;
    }
    public function IsUnique($data) {
        $rvalue = [
            'status' => true
        ];
        foreach($this->model->rules as $name => $rules) {
            if(isset($data[$name])) {
                if(is_array($rules)) {
                    foreach($rules as $rname => $rule) {
                        if($rname === 'unique' && $rule === true) {
                            $boolval = $this->Select([
                                'value_field' => $name,
                                'value' => $data[$name]
                            ]);
                            if(!empty($boolval)) {
                                $rvalue = [
                                    'status' => false,
                                    'message' => $name . 'exists'
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $rvalue;
    }
    /*
     *  Insert()
     *  
     *  @desc   Insert data to database.
     * 
     *  @example    
     * 
     *  $data = [
     *          'title' => 'Title example',
     *          'description' => 'This is just example description.'
     *  ];
     * 
     *  Insert($data);
     * 
     *  @return  boolean
     *      
     */
    public function Insert($data) {
        $cdt = $this->CheckDataType($data);
        $cl = $this->CheckLength($data);
        $ir = $this->IsRequired($data);
        $iu = $this->IsUnique($data);
        if($cdt['status'] === false) {
            return $cdt;
        }
        else if($cl['status'] === false) {
            return $cl;
        }
        else if($ir['status'] === false) {
            return $ir;
        }
        else if($iu['status'] === false) {
            return $iu;
        }
        $query = 'INSERT INTO ' . $this->mname . ' ';
        $columns = '(';
        $values = '(';
        $last_arr_elem = end($data);
        $execarr = [];
        foreach($data as $column_name => $column_value) {
            $cnarr = ':' . $column_name;
            $execarr[$cnarr] = $column_value;
            if($last_arr_elem === $column_value) {
                $columns .= $column_name . ') VALUES ';
                $values .= ':' . $column_name . ');';
                continue;
            }
            $columns .= $column_name . ', ';
            $values .= ':' . $column_name . ', ';
        }
        $query .= $columns . $values;
        $result = $this->conn->prepare($query);
        $returnval = $result->execute($execarr);
        if($returnval === true) {
            return [
                'status' => true,
                'message' => 'insertsuccesful',
                'id' => $this->conn->lastInsertId()
            ];
        }
        else {
            return [
                'status' => false,
                'message' => 'unknownerror'
            ];
        }
    }
    /*
     *  Select()
     *  @param  $data    array
     * 
     *      @example
     *          [
     *              'columns' => 'product_name, product_price'  -   Column names in database. If you want to fetch all columns don't set this. 
     * 
     * 
     *              'value' => 2                                -   This value is used to find column that corresponds with models primary key.
     *                                                              Looks automatically for primary key in model rules. If one is not set
     *                                                              this method returns null.
     * 
     *              'value_field => 'product_name'              -   Change column that is used to find data
     * 
     * 
     *              'values => [                                -   Set multiple criterias for finding data (ex. age = 18 AND name = "John").
     *                  'product_name' => 'computer',               "product_name" acts as column and 'computer' acts as value to search for. 
     *                  'product_id' => 24                          
     *              ]
     *              'limit' => 50                               -   Limit how many records will be fetched from database
     *              'order' => [                                -   Set order. 
     *                  'column_name' => 'DESC',
     *                  'column_name2' => 'ASC'
     *              ]
     *          ]
     * 
     *      If $data is let empty this script will fetch everything without any limits. Suggestion is that developer should use 'limit'
     *      if developer does not want to fetch everything.
     * 
     */
    public function Select($data = []) {
        $fetch_all = false;
        if(empty($data['columns'])) {
            $data['columns'] = '*';
        }
        $where_clause = '';
        $execarr = [];
        if(isset($data['value'])) {
            if(isset($data['value_field'])) {
                $where_clause = ' WHERE ' . $data['value_field'] . ' = :' . $data['value_field'];
                $execarr = [
                    ':' . $data['value_field'] => $data['value']
                ];
            }
            else {
                $where_clause = ' WHERE ' . $this->model->rules['primary_key'] . '= :' . $this->model->rules['primary_key'];
                $execarr = [
                    ':' . $this->model->rules['primary_key'] => $data['value']
                ];
            }
        }
        else if(isset($data['values'])) {
            switch(array_keys($data['values'])[0]) {
                case 'normal':
                    $last_arr_elem = end($data['values']['normal']);
                    $where_clause = ' WHERE ';
                    $execarr = [];
                    foreach($data['values']['normal'] as $column => $value) {
                        $name = ':' . $column;
                        $execarr[$name] = $value;
                        if($last_arr_elem === $value) {
                            $where_clause .= $column . ' = :' . $column;
                            continue;
                        }
                        $where_clause .= $column . ' = :' . $column . ' AND ';
                    }
                break;
                case 'between': 
                    $last_arr_elem = end($data['values']['between']);
                    $where_clause = ' WHERE ';
                    $execarr = [];
                    foreach($data['values']['between'] as $column => $criteria) {
                        $name = ':' . $column;
                        
                    }
                break;
                case 'contains': 
                    $last_arr_elem = end($data['values']['contains']);
                    $where_clause = ' WHERE ';
                    $execarr = [];
                    foreach($data['values']['contains'] as $column => $keyword) {
                        $name = ':' . $column;
                        $execarr[] = '%' . $keyword . '%';
                        if($last_arr_elem === $keyword) {
                            $where_clause .= $column . ' LIKE ?';
                            continue;
                        }
                        $where_clause .= $column . ' LIKE ? ' . ' AND ';
                    }
                break;
            }
        }
        else {
            $fetch_all = true;
        }
        $order_clause = ' ';
        if(isset($data['order'])) {
            $order_clause .= 'ORDER BY ';
            $last_arr_elem = end($data['order']);
            foreach($data['order'] as $column => $order) {
                if($last_arr_elem === $order) {
                    $order_clause .= $column . ' ' . $order;
                    continue;
                }
                $order_clause .= $column . ' ' . $order . ', ';
            }
        }
        $limit_clause = '';
        if(isset($data['limit'])) {
            $limit_clause = ' LIMIT ' . $data['limit'];
        }
        $query = <<<EOT
            SELECT {$data['columns']} FROM {$this->mname}{$where_clause}{$order_clause}{$limit_clause};
        EOT;
        $query = $this->conn->prepare($query);
        $query->execute($execarr);
        if($fetch_all === true) {
            return $query->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function SelectMultiple($data) {
        $this->CallModel($data['tables']['parent']);
        $parent_model = $this->model;
        $this->CallModel($data['tables']['child']);
        $child_model = $this->model;
        if(empty($parent_model->rules['table'])) {
            return false;
        }
        if(empty($child_model->rules['table'])) {
            return false;
        }
        if($parent_model->rules['primarykey'] !== $child_model->rules['foreignkey']) {
            return false;
        }
        $where_clause = '';
        $execarr = [];
        if(isset($data['where'])) {
            $last_bool = false;
            $where_clause = $where_clause . ' WHERE ';
            $last_arr_elem = end($data['where']);
            foreach($data['where'] as $tablename => $table) {
                foreach($table as $column => $value) {
                    $name = ':' . $column;
                    $execarr[$name] = $value;
                    $where_clause = $where_clause . $data['tables'][$tablename] . '.' . $column . ' = ' . $name;
                }
            }
        }
        $foreignkey = $parent_model->rules['primarykey'];
        $parentn = $parent_model->rules['table'];
        $childn = $child_model->rules['table'];
        $query = <<<EOT
            SELECT * FROM {$parentn} INNER JOIN {$childn}
             ON {$parentn}.{$foreignkey} = {$childn}.{$foreignkey}
             {$where_clause}
        EOT;
        $query = $this->conn->prepare($query);
        $query->execute($execarr);
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
<?php

class example {

    private $_db;

    public function __construct() {
        include 'DB.class.php';
        $this->_db = DB::getInstance();
    }
    
    public function insert(){
        $this->_db->query_insert('test', array(
            'id' => NULL,
            'name' => 'Name'
        ));        
        return $this->_db->insert_id();
    }
    
    public function delete(){
        return $this->_db->query_delete('test',array('id','=',1));
    }

    public function results() {
        /* $sql_data = array(
          'where' => array('id', '=', '1'),
          'group_by' => NULL,
          'order_by' => array('name', 'DESC'),
          'limit' => 0
          );
         */
        
        $sql_data = array(
            'where' => NULL,
            'group_by' => NULL,
            'order_by' => NULL,
            'limit' => 0
        ); 
        $data = $this->_db->query_read('test', $sql_data);
        return $data->results();
    }
     
    public function updateALL(){
        $update_data = array(
            'name'=>'Janaka'
        );
        $this->_db->query_update('test',$update_data);
    }
    
    public function updateOne(){
        $update_data = array(
            'name'=>'Doe'
        );
        $where = array('id','>',3);
        $this->_db->query_update('test',$update_data,$where);
    }
    
    public function singleValue(){
        $sql_data = array(
          'where' => array('id', '=', '2'),
          'group_by' => NULL,
          'order_by' => array('name', 'DESC'),
          'limit' => 0
          );
          return $this->_db->query_value('test','name',$sql_data);
    }
    
    public function fetchArray(){
        $sql_data = array(
            'where' => NULL,
            'group_by' => NULL,
            'order_by' => NULL,
            'limit' => 0
        ); 
        $data = $this->_db->query_read('test', $sql_data);
        return $data->fetch_array();
    }

}

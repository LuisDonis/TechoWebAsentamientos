<?php

class ModelMaster {
	var $table;
	function getList($app, $params) {
		if ($this->table) {
			if ($params && is_array ( $params ) && count ( $params )) {
				$qb = $app ['db']->createQueryBuilder ();
				
				//foreach ($params AS $param){
					$qb->select($params);
				//}
				$qb->from ( $this->table, null );				
				$rs = $qb->execute ()->fetchAll();
				return $rs;
			} else {
			}
		}
	}
	
	
	
	function setTable($value){
		$this->table = $value; 
	}
}
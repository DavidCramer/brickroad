<?php
	
	/**
	 * Process a field value
	 *
	 */
	public function process_value($type, $value){

		switch ($type){
			{{field_processors}}
			
		}

		return $value;	

	}

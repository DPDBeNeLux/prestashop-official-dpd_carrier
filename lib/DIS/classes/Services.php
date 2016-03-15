<?php

class DisServices
{
	public $default;
	public $services;

	public function __construct()	
	{
		$this->default = new stdClass();
		$this->default->max_width = 175;			// Real max length
		$this->default->max_circum = 125; 		// (max-height + max-depth) * 2 < 125
		$this->default->max_weight = 31.5;
		$this->default->zones = array('Europe');
		$this->default->weight_ranges = array(0,3,31);
		
		$this->services = array();
		
		$this->services[0] = new stdClass();
		$this->services[0]->name = 'Home';
		$this->services[0]->type = 'B2B';
		$this->services[0]->description = 'Get your parcel delivered at your place';
		
		$this->services[1] = new stdClass();
		$this->services[1]->name = 'Home With Predict';
		$this->services[1]->type = 'B2C';
		$this->services[1]->description = 'Get your parcel delivered at your place (with notification of delivery)';
		
		$this->services[2] = new stdClass();
		$this->services[2]->name = 'Pickup';
		$this->services[2]->type = 'PSD';
		$this->services[2]->description = 'Get your parcel delivered at a Pickup point and collect it at your convenience.';
		$this->services[2]->max_width = 100;
		$this->services[2]->max_circum = 200;
		$this->services[2]->max_weight = 20;
		$this->services[2]->weight_ranges = array(0,3,10,20);
	}
}
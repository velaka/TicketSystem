<?php

namespace App\Models;

class ProjectModel
{
	protected $app;
	private $db;
	private $name;
	private $description;
	private $startDate;
	private $endDateDesign;
	private $endDate;
	
	
	public function __construct($name= null,$description= null,$startDate= null,$endDate= null,$endDateDesign= null){

		$this->app = \Yee\Yee::getInstance();

		$this->name = $name;
		$this->description = $description;
		
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->endDateDesign = $endDateDesign;
		
		
		$this->db = $this->app->connection['ticket_system'];		
	}

	public function GetAllProjects(){

		$app = \Yee\Yee::getInstance();
						
		$projects = $app->connection['ticket_system']->get('projects');

		return $projects;	
	}

	public function GetViewProject($id){
		$app = \Yee\Yee::getInstance();

		$project = $app->connection['ticket_system']->where('id',$id)->get('projects');
		return $project;
	}	
	
	
/*
	public function InsertTicket(){
		$app = \Yee\Yee::getInstance();

		$data = Array ("name" => $this->name,
		               "description" => $this->description,
		               "priority_id" => (int) $this->priority,
		               "start_date" => $this->startDate,
		               "end_date" => $this->endDate,
		               "userAssing" => (int) $this->userAssing,
		               "status_id" => (int) $this->status
		               );

	   	$tickets = $app->connection['ticket_system']->insert('ticket', $data);
		return $tickets;
	}	*/
}
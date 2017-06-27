<?php

namespace App\Models;

class TicketsModel
{
	protected $app;
	private $db;
	private $name;
	private $description;
	private $priority;
	private $startDate;
	private $endDate;
	private $userAssing;
	private $status;

	
	public function __construct($name= null,$description= null,$priority=null ,$startDate= null,$endDate= null,$userAssing= null,$status= null){

		$this->app = \Yee\Yee::getInstance();

		$this->name = $name;
		$this->description = $description;
		$this->priority = $priority;
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->userAssing = $userAssing;
		$this->status = $status;
		
		$this->db = $this->app->connection['ticket_system'];		
	}

	public function GetAllTickets(){

		$app = \Yee\Yee::getInstance();
						
		$tickets = $app->connection['ticket_system']->get('ticket');

		return $tickets;	
	}

	public function GetTodoTickets(){
		$app = \Yee\Yee::getInstance();

		$tickets = $app->connection['ticket_system']->where('status_id',1)->get('ticket');
		return $tickets;
	}	
	
	public function GetDoingTickets(){
		$app = \Yee\Yee::getInstance();

		$tickets = $app->connection['ticket_system']->where('status_id',2)->get('ticket');
		return $tickets;
	}

	public function GetDoneTickets(){
		$app = \Yee\Yee::getInstance();

		$tickets = $app->connection['ticket_system']->where('status_id',3)->get('ticket');
		return $tickets;
	}

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
	}	
}
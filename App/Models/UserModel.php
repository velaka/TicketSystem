<?php

namespace App\Models; 

class UserModel {
	
	protected $app;
	private $db;
	private $email;
	private $password;
	private $firstName;
	private $secondName;
	private $username;
	private $user_ip;
	private $date;

	
	public function __construct($email,$password ,$username = null,$firstName = null,$secondName = null){

		$this->app = \Yee\Yee::getInstance();

		$this->email = $email;
		$this->password = $password;
		$this->firstName = $firstName;
		$this->secondName = $secondName;
		$this->username = $username;
		
		$this->db = $this->app->connection['ticket_system'];		
	}

	public function getUsers(){
		return $this->db->where('id',$this->email)->getOne('user');
	}

	public function insertUsers(){

		$data = Array ("id" => $this->email,
		               "first_name" => $this->firstName,
		               "second_name" => $this->secondName,
		               "username" => $this->username,
		               "password" => $this->password,
		               "user_ip" => '201.06.16',
		               "date" => '2017-06-16'
		               );

	 	$test = $this->db->insert('user', $data);

	 	foreach ($data as $key => $value) {
	        if(empty($data[$key])){
	           $this->app->flash("errorFill", "Please fill out all the fields");
	          return false;

	          break;
	        } else $_SESSION["username"] = $this->username;
		
		}
	}

	
	public function validateLogin(){

		$info= $this->getUsers();
		//echo'empty fild'; die;

		if(!empty($this->email) and !empty($this->password))
		{
			//echo'empty fild'; die;

			if ($info['id'] == $this->email) 
			{
				
				if ($info["password"] == $this->password) 
				{
					//echo "Connection complete !";
					$_SESSION["username"] = $info['username'];
					
					return true;
				}
				else 
				{
					$this->app->flash('erorr',"Password is not insert!");
					$this->app->redirect('/ticketsystem/public/login',200);
					return false;
				}
			}
			else 
			{
				$this->app->flash('erorr',"Email is not insert!");
				$this->app->redirect('/ticketsystem/public/login',200);
				//$this->app->render('login.twig');
				return false;
			}
		}
		else
		{
			$this->app->flash('erorr',"Username and password can not be empty");
			$this->app->redirect('/ticketsystem/public/login',200);
		}
	}

	

	public function validateRegister(){

		 if (!filter_var($this->email , FILTER_VALIDATE_EMAIL)) {
	        
	        $this->app->flash("errorEmail", "User Email is not a valid email");
	        $this->app->redirect('/ticketsystem/public/register',200);
	        return false;
	    }
		 elseif ( preg_match('/[a-z]+/', $this->password) === 0)
		  {

		    $this->app->flash("errorPassSmall", "User password must contain a lower case letter");
	        $this->app->redirect('/ticketsystem/public/register',200);

		    return false;
		    
		  }
		  elseif (preg_match('/[A-Z]+/', $this->password) === 0)
		  {
		      $this->app->flash("errorPassBig", "User password must contain a upper case letter");
	        $this->app->redirect('/ticketsystem/public/register',200);

		      return false;
		    
		  }  
		  elseif (preg_match('/[0-9]+/', $this->password) === 0)
		  {
		      $this->app->flash("errorPassNum", "User password must contain a number");
	        $this->app->redirect('/ticketsystem/public/register',200);
		      
		      return false;
		    
		  }      
		  else
		    {
		      $id = $this->db->insert('user', $data);
		      return true;
		    }


	}



		   
	}




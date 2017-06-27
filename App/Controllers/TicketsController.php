<?php

namespace App\Controllers;

use Yee\Managers\Controller\Controller;
use App\Models\TicketsModel;


class TicketsController extends Controller
{

    /**
     * @Route('/todo')
     * @Name('todo.index')
     * @Method('GET')
     */
    public function todoIndex()
    {
        $app  = $this->getYee();
     
        if (isset($_SESSION['username'])) {
        
          $ticketsModel = new TicketsModel();
            $tickets = $ticketsModel->GetTodoTickets();

            $data = array(
                'username' => $_SESSION["username"],
                'tickets' => $tickets
            );

            $app->render('tickets/todo.twig', $data);
        }
        else 
        {
             $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }

    }
    /**
     * @Route('/doing')
     * @Name('doing.index')
     * @Method('GET')
     */
    public function doingIndex(){
        $app  = $this->getYee();

         if (isset($_SESSION['username'])) {
            
            $ticketsModel = new TicketsModel();
            $tickets = $ticketsModel->GetDoingTickets();

            $data = array(
                'username' => $_SESSION["username"],
                'tickets' => $tickets
            );

            $app->render('tickets/doing.twig', $data);
        }
        else 
        {
             $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }
    }
    /**
     * @Route('/done')
     * @Name('done.index')
     * @Method('GET')
     */
    public function doneIndex(){
        $app  = $this->getYee();

         if (isset($_SESSION['username'])) {
           
            $ticketsModel = new TicketsModel();
            $tickets = $ticketsModel->GetDoneTickets();

            $data = array(
                'username' => $_SESSION["username"],
                'tickets' => $tickets
            );

            $app->render('tickets/done.twig', $data);
        }
        else 
        {
             $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }
    } 

    /**
     * @Route('/todo')
     * @Name('todo.index')
     * @Method('POST')
     */

    public function newIndex(){
        $app  = $this->getYee();

        $name = $app->request->post('name');
        $description = $app->request->post('des');
        $priority = $app->request->post('priority');
        $startDate = $app->request->post('st_date');
        $endDate = $app->request->post('end_date');
        $userAssing = $app->request->post('userAssing');
        $status = $app->request->post('status');

         $ticketsModel = new TicketsModel($name,$description,$priority,$startDate,$endDate,$userAssing,$status);

         $complete = $ticketsModel->InsertTicket();

        if ($complete) {
            $app->redirect("/ticketsystem/public/todo");
        }else{
            $app->redirect("/ticketsystem/public/done");
        }
    }
} 
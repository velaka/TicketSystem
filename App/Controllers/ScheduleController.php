<?php

namespace App\Controllers;

use Yee\Managers\Controller\Controller;
use App\Models\TicketsModel;


class ScheduleController extends Controller
{

    /**
     * @Route('/schedule')
     * @Name('schedule.index')
     * @Method('GET')
     */
    public function index()
    {
         $app  = $this->getYee();
        if(isset($_SESSION['username']))
        {
            $app->render('schedule/index.twig', array(
                'username' => $_SESSION["username"]));
        }
        else
        {
            $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }
    }
  



} 
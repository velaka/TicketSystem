<?php

namespace App\Controllers;

use Yee\Managers\Controller\Controller;
use App\Models\TicketsModel;


class DashboardController extends Controller
{

    /**
     * @Route('/dashboard')
     * @Name('dashboard.index')
     * @Method('GET')
     */
    public function dashboard()
    {
         $app  = $this->getYee();
        if(isset($_SESSION['username']))
        {
            $app->render('dashboard.twig', array(
                'username' => $_SESSION["username"]));
        }
        else
        {
            $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }
       
    }




} 
<?php

namespace App\Controllers;

use Yee\Managers\Controller\Controller;
use App\Models\TicketsModel;


class PeopleController extends Controller
{

    /**
     * @Route('/people')
     * @Name('people.index')
     * @Method('GET')
     */
    public function index()
    {
         $app  = $this->getYee();
        if(isset($_SESSION['username']))
        {
            $app->render('people/index.twig', array(
                'username' => $_SESSION["username"]));
        }
        else
        {
            $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }
    }
  



} 
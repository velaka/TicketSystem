<?php
namespace App\Controllers;
use Yee\Managers\Controller\Controller;
use App\Model\UserModel;

class InternController extends Controller
{
    
    
   



    /**
     * @Route('/welcome')
     */
     public function welcome()
    {
        $app  = $this->getYee();
        $app->render('welcome.twig');
    }
/**
     * @Route('/layout')
     */
     public function layout()
    {
        $app  = $this->getYee();
        $app->render('layout.twig');
    }



}
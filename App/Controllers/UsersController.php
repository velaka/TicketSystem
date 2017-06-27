<?php
namespace App\Controllers;
use Yee\Managers\Controller\Controller;
use App\Models\UserModel;

class UsersController extends Controller
{
    
    /**
     * @Route('/login')
     * @Name('login.form')
     * @Method('Get')
     */
   public function indexLogin()
    {
        $app  = $this->getYee();
        $app->render('login.twig');
    }

    /**
     * @Route('/register')
     * @Name('register.form')
     * @Method('Get')
     */

   public function indexRegister()
    {
        $app  = $this->getYee();
        $app->render('register.twig');
    }

    /**
     * @Route('/login')
     * @Name('login.data')
     * @Method('POST')
     */
     public function loginPost()
    {
        $app  = $this->getYee();
        $app->flashKeep();
        $email = $app->request->post('email');
        $password = $app->request->post('password');

        $user = new UserModel($email,$password);
        $info = $user->validateLogin();
       // var_dump($info); die;
        
       if ($info)
       {
         $app->redirect('/ticketsystem/public/dashboard',200);
         }
         else
         {
             $app->redirect('/ticketsystem/public/login',200);
         }
    }

     /**
     * @Route('/register')
     * @Name('register.data')
     * @Method('POST')
     */

     public function registerPost()
    {
        $app  = $this->getYee();

        $email = $app->request->post('email');
        $username = $app->request->post('username');
        $password = $app->request->post('password');
        $firstName = $app->request->post('secondName');
        $secondName = $app->request->post('firstName');

        $user = new UserModel($email,$password,$username,$firstName,$secondName);
        
        $validateSuccessful = $user->insertUsers();
       
        if($validateSuccessful){
            $app->render('login.twig');
        }
        else
        {
            //$app->render('register.twig');
           
        }
       
      
       

    }

     /**
     * @Route('/logout')
     * @Name('logout.form')
     * @Method('Get')
     */
   
     public function logout()
    {
        $app  = $this->getYee();

        session_unset();
        session_destroy();


       $app->redirect('/ticketsystem/public/login',200);
       
    }



}
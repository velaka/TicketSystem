<?php

namespace App\Controllers;

use Yee\Managers\Controller\Controller;
use App\Models\TicketsModel;
use App\Models\ProjectModel;


class ProjectsController extends Controller
{

    /**
     * @Route('/projects')
     * @Name('projects.index')
     * @Method('GET')
     */
    public function index()
    {
         $app  = $this->getYee();
        if(isset($_SESSION['username']))
        {
            $projectsModel = new ProjectModel();
            $projects = $projectsModel->GetAllProjects();

             $data = array(
                'username' => $_SESSION["username"],
                'projects' => $projects
            );
            $app->render('projects/index.twig', $data);
        }
        else
        {
            $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }
    }

    /**
     * @Route('/projects/:id')
     * @Method('GET')
     */
    public function view($id)
    {
         $app  = $this->getYee();
        // var_dump($id);
        if(isset($_SESSION['username']))
        {

            $app->render('projects/view.twig', array(
                'username' => $_SESSION["username"]));
        }
        else
        {
            $this->app->flash('erorr',"Login first!");
            $app->redirect('/ticketsystem/public/login',200);
        }
    }
  



} 
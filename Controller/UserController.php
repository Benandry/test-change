<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{

    /**
     * @Route("/admin/users", name="users")
     */
    public function getUsers()
    {

        $users = $this->getDoctrine()->getRepository(user::class)->findAll();

        return $this->render(
            'admin/users.html.twig',
            [
                'users' => $users
            ]
        );
    }
}
<?php

namespace App\Controller;

use App\Events;
use App\Form\CommentType;
use App\Entity\User;
use App\Entity\Level;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/sync", methods={"POST"}, name="sync")
     */
    public function sync(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $email = $request->request->get('email');
        $display_name = $request->request->get('display_name');
        $password = $request->request->get('password');
        $userRepo = $entityManager->getRepository(User::class);

        $new_guid = $request->cookies->get('user');
        $user = $userRepo->findByEmail($email);

        if ($user) { // existing user

            if (!password_verify($password, $user->getPassword()))
                throw new UnauthorizedHttpException("Incorrect username or password!");
            if ($display_name != '') $user->setDisplayName($display_name);
            $new_guid = $user->getId();

        } else { // new user

            $user = $userRepo->find($request->cookies->get('user'));
            if ($user == null) throw new BadRequestHttpException();
            $user->setEmail($email);
            $user->setDisplayName( ($display_name!='') ? $display_name : (explode('@', $email)[0]) );
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

        }
        $entityManager->flush();
        
        $response = $this->redirect("/");
        $response->headers->setCookie(new Cookie("user", $new_guid, time() + (3600 * 24 * 365 * 10)));
        return $response;
    }

}

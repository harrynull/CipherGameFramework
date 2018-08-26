<?php

namespace App\Controller;

use App\Events;
use App\Form\CommentType;
use App\Entity\User;
use App\Entity\Level;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"}, name="index")
     */
    public function index(Request $request): Response
    {
        $cookies = $request->cookies;

        $entityManager = $this->getDoctrine()->getManager();
        $user = null;
        if ($cookies->has('user')) {
            $user = $entityManager->getRepository(User::class)->find($cookies->get('user'));
        }

        if ($user == null) { // new user or old uid is invalid
            $user = new User();
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $levels = $entityManager->getRepository(Level::class)
            ->getAll($request->query->get('review')==1 && $user->getIsAdmin());
        $rank = $entityManager->getRepository(User::class)
            ->getRank($user->getScore());
        $top10 = $entityManager->getRepository(User::class)
            ->getTop10Users();

        $response = $this->render('index.html.twig',
            ['user' => $user, 'levels' => $levels, 'rank' => $rank, 'top10' => $top10]);
        $response->headers->setCookie(new Cookie("user", $user->getId(), time() + (3600 * 24 * 365 * 10)));
        return $response;
    }

}

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @Route("/editor")
 */
class EditorController extends AbstractController
{
    private function hasEditPermission(Request $request) : bool {
        if (!$request->cookies->has('user')) return false;
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->find($request->cookies->get('user'));
        return $user && $user->getIsAdmin();
    }

    private function hasProposePermission(Request $request) : bool {
        if (!$request->cookies->has('user')) return false;
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->find($request->cookies->get('user'));
        return $user && ($user->getScore() >= 500 || $user->getIsAdmin());
    }

    private function editLevel(Level $level, Request $request){
        $level->setName($request->request->get('level-name'));
        $level->setUnlockScore($request->request->get('unlock-score'));
        $level->setScore($request->request->get('score'));
        $level->setAuthor($request->request->get('author'));
        $level->setAnswer($request->request->get('answer'));
        $level->setTags(explode(",", $request->request->get('tags')));
    }

    /**
     * @Route("/new", methods={"GET"}, name="editor")
     */
    public function new(Request $request): Response
    {
        define("DEFAULT_CODE", "{% extends 'levels/level_base.html.twig' %}\n{% block description %}1+1=?{% endblock %}");
        return $this->render('editor.html.twig', ["code" => DEFAULT_CODE,
                "can_edit" => $this->hasEditPermission($request),
                "can_propose" => $this->hasProposePermission($request)]);
    }

    /**
     * @Route("/new", methods={"POST"})
     */
    public function submit_new(Request $request): Response
    {
        if(!$this->hasProposePermission($request))
            throw new UnauthorizedHttpException("Permission Denied");
        $entityManager = $this->getDoctrine()->getManager();
        $level = new Level();
        $this->editLevel($level, $request);
        $level->setAuthorUser($entityManager->getRepository(User::class)->find($request->cookies->get('user')));
        $level->setUnderReview(!$this->hasEditPermission($request));
        $entityManager->persist($level);
        $entityManager->flush();

        $id = (int)$level->getId();
        file_put_contents("../templates/levels/level_{$id}.html.twig", $request->request->get('code'));

        return $this->redirect("/nazo/{$id}");
    }
    /**
     * @Route("/preview", methods={"POST"})
     */
    public function preview(Request $request): Response
    {
        if(!$this->hasProposePermission($request))
            throw new UnauthorizedHttpException("Permission Denied");
        $level = new Level();
        $this->editLevel($level, $request);
        $level->setUnderReview(true);
        
        //$twig = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $twig = clone $this->container->get('twig');
        $template = $twig->createTemplate($request->request->get('code'));
        return new Response($template->render(['level'=>$level]));
    }

    /**
     * @Route("/edit/{id}", methods={"GET"})
     */
    public function edit(Request $request, Level $level): Response
    {
        if(!$this->hasEditPermission($request))
            throw new UnauthorizedHttpException("Permission Denied");
        $id = $level->getId();
        $code = file_get_contents("../templates/levels/level_{$id}.html.twig");
        return $this->render('editor.html.twig', ["level" => $level, "code" => $code, "can_edit" => true, "can_propose" => true]);
    }

    /**
     * @Route("/edit/{id}", methods={"POST"})
     */
    public function submit_edit(Request $request, int $id): Response
    {
        if(!$this->hasEditPermission($request))
            throw new UnauthorizedHttpException("Permission Denied");
        $entityManager = $this->getDoctrine()->getManager();
        $level = $entityManager->getRepository(Level::class)->find($id);
        $this->editLevel($level, $request);
        if($level->getUnderReview()){
            $level->setUnderReview(false);
            $author = $level->getAuthorUser();
            $author->setScore($author->getScore()+$level->getScore()*5); // award the author
        }
        $entityManager->persist($level);
        $entityManager->flush();
        file_put_contents("../templates/levels/level_{$id}.html.twig", $request->request->get('code'));
       
        return $this->redirect("/");
    }
    /**
     * @Route("/delete/{id}", methods={"GET"})
     */
    public function submit_delete(Request $request, int $id): Response
    {
        if(!$this->hasEditPermission($request))
            throw new UnauthorizedHttpException("Permission Denied");
        $entityManager = $this->getDoctrine()->getManager();
        $level = $entityManager->getRepository(Level::class)->find($id);
        $entityManager->remove($level);
        $entityManager->flush();
        return $this->redirect("/");
    }

    /**
     * @Route("/setadmin/{user}/{isadmin}", methods={"GET"})
     */
    public function set_admin_html(Request $request, string $user, bool $isadmin): Response
    {
        $type = $isadmin?'管理员':'非管理员';
        return new Response("将<strong>{$user}</strong>设置为<strong>{$type}</strong>，确认？".
            '<form method="post"><input type="password" name="password" placeholder="密码"><button type="submit">确认</button></form>');
    }
    
    /**
     * @Route("/setadmin/{user}/{isadmin}", methods={"POST"})
     */
    public function set_admin(Request $request, string $user, bool $isadmin): Response
    {
        if(hash('sha256', 'somesalt'.$request->request->get('password'))!=='')
            return new JsonResponse(array('success' => false, 'reason' => 'incorrect password'));
        $entityManager = $this->getDoctrine()->getManager();
        $u = $entityManager->getRepository(User::class)->find($user);
        if(!$u) return new JsonResponse(array('success' => false, 'reason' => 'incorrect user id'));
        $u->setIsAdmin($isadmin);
        $entityManager->flush();
        return new JsonResponse(array('success' => true));
    }
}

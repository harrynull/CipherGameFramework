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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/nazo")
 */
class NazoController extends AbstractController
{
    private function permissionCheck(?User $user, ?Level $level){
        if($user == null || $level == null) return false;
        $isException = $user->getIsAdmin() || $level->getAuthorUser()->getId()===$user->getId();
        return ($level->getUnlockScore() <= $user->getScore() || $isException)
            && (!$level->getUnderReview() || $isException);
    }

    private function getUserFromRequest(Request $request) : User{
        return $this->getDoctrine()->getManager()->getRepository(User::class)
            ->find($request->cookies->get('user'));
    }

    /**
     * @Route("/{id}", methods={"GET"}, requirements={"id": "[1-9]\d*"}, name="nazo-levels")
     */
    public function level(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $level_data = $entityManager->getRepository(Level::class)->find($id);
        if($level_data == null) throw $this->createNotFoundException('This level does not exist');
        $user = $this->getUserFromRequest($request);
        if(!$this->permissionCheck($user, $level_data)) return $this->redirect('/');

        if(!in_array($level_data->getId(), $user->getTriedLevels())){
            $level_data->setTriedNum($level_data->getTriedNum() + 1);
            $tried_levels = $user->getTriedLevels();
            $tried_levels[] = $level_data->getId();
            $user->setTriedLevels($tried_levels);
            $entityManager->flush();
        }

        
        $response = $this->render("levels/level_{$id}.html.twig", ['level'=>$level_data]);
        
        return $response;
    }
    /**
     * @Route("/{id}/submit", methods={"POST"}, requirements={"id": "[1-9]\d*"}, name="nazo-levels-submit")
     */
    public function submit(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $level_data = $entityManager->getRepository(Level::class)->find($id);
        if($level_data == null) throw $this->createNotFoundException('This level does not exist');
        $user = $this->getUserFromRequest($request);

        if(!$this->permissionCheck($user, $level_data))
            return new JsonResponse(array('success' => false));

        if($request->request->get('answer') === $level_data->getAnswer()){
            if(!in_array($level_data->getId(), $user->getPassedLevels())){
                $user->setScore($user->getScore() + $level_data->getScore());
                $level_data->setPassedNum($level_data->getPassedNum() + 1);
                $passed_levels = $user->getPassedLevels();
                $passed_levels[] = $level_data->getId();
                $user->setPassedLevels($passed_levels);
            }
            $entityManager->flush();
            return new JsonResponse(array('success' => true, 'correct' => true));
        }
        else
            return new JsonResponse(array('success' => true, 'correct' => false,
                "message_header" => "答案不正确", "message" => "再试一次吧"));
    }

    /**
     * @Route("/{id}/rate", methods={"POST"}, requirements={"id": "[1-9]\d*"})
     */
    public function rate(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $level_data = $entityManager->getRepository(Level::class)->find($id);
        if($level_data == null) throw $this->createNotFoundException('This level does not exist');
        $user = $this->getUserFromRequest($request);

        if(!$this->permissionCheck($user, $level_data) ||
            !in_array($level_data->getId(), $user->getPassedLevels()))
            return new JsonResponse(array('success' => false));

        $rating = (int) $request->request->get('rating');
        if($rating >= 1 && $rating <=5){
            $level_data->setTotalRating($level_data->getTotalRating() + $rating);
            $level_data->setRatedNum($level_data->getRatedNum() + 1);
            $entityManager->flush();
        }
            
        return new JsonResponse(array('success' => true));
    }
    /**
     * @Route("/{id}/next", methods={"GET"}, requirements={"id": "[1-9]\d*"})
     */
    public function next(Request $request, int $id): Response
    {
        $nextid = $id + 1;
        $level_data = $this->getDoctrine()->getManager()->getRepository(Level::class)->find($nextid);
        $user = $this->getUserFromRequest($request);
        if($this->permissionCheck($user, $level_data)) return $this->redirect("/nazo/{$nextid}");
        else return $this->redirect("/"); 
    }

}

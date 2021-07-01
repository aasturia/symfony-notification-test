<?php

namespace corite\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notification", name="notification")
 */
class TestController extends AbstractController
{
    /**
     * @Route("/test", name="test", methods={"POST"})
     */
    public function create(Request $request)
    {
        //decode request data:
        $data = json_decode($request->getContent(), false);
        $projects = $data->projects;

        //return response to client for test purposes
        return new Response(json_encode($projects));
    }

}

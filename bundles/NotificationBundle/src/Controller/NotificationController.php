<?php

namespace corite\NotificationBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * @Route("/notification", name="notification")
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/send", name="send", methods={"POST"})
     */
    public function create(Request $request, Transport $transport, Rules $rules)
    {
        // create a log channel
        $logger = new Logger('notification-service');
        $logger->pushHandler(new StreamHandler('../var/log/notifications.log', Logger::DEBUG));

        // add records to the log
        $logger->info('request received from IP: ' . json_encode($request->getClientIp()));
//        $logger->warning('Foo');
//        $logger->error('Bar');
//        $logger->notice('Adding a new user');

        //decode request data:
        $data = json_decode($request->getContent(), false);
        $projects = $data->projects;

        //find filtered projects
        $filteredProjects = array_filter($projects, function ($project) use ($rules) {
            return $rules->isConditionsTrue($project);
        });

        //send notifications through all transports
        $transport->sendNotification($filteredProjects, $logger);

        //find filtered projects names for test purposes
        $projectsNames = array_reduce($filteredProjects, function ($acc, $project) {
            array_push($acc, $project->name);
            return $acc;
        }, []);

        //return response to client for test purposes
        return new Response(json_encode($projectsNames));

    }

}

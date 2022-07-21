<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Callsign;
use App\Entity\Log;
use App\Form\CallsignSearch;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CallSignInfoController extends AbstractController
{
    /**
     * @Route("/call/{callsign}", name="call_search", defaults={"callsign"=""}, requirements={"callsign"=".+"})
     */
    public function callsignSearch($callsign)
    {
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $callsignRepository = $this->getDoctrine()->getRepository(Callsign::class);

        $callsignCheck = $callsignRepository->findBy(array('callsign' => $callsign));
        if (empty($callsignCheck) && !empty($callsign)) {
            return $this->redirectToRoute(
                'call_search',
                array( 'callsign' => '' )
            );
          }

        $lastLogsByCallsign = $logRepository->findLastLogsByCallsign($callsign);

        return $this->render(
            'callsign.html.twig',
            array(
                'callsign' => $callsign,
                'reports' => sizeof($logRepository->findLastLogsByCallsign($callsign, 9999)),
                'wwls' => '',
                'loghistory' => $lastLogsByCallsign,
                'callSearch' => $callsignSearchForm->createView()
            )
        );
    }
}

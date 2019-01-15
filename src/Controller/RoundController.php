<?php

namespace App\Controller;

use App\Entity\Round;
use App\Entity\Log;
use App\Form\CallsignSearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RoundController extends AbstractController
{
    /**
     * @Route("/rounds/{year}", name="rounds", defaults={"year"=""})
     */
    public function index($year)
    {
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $roundRepository = $this->getDoctrine()->getRepository(Round::class);

        $allRoundYears = $roundRepository->findAllRoundYears();
        $lastYear = substr($logRepository->findLastDate()[1],0,4);
        $validYear = '';

        foreach ($allRoundYears as $row) {
            if ($row['y'] == $year) {
                $validYear = $year;
                continue;
            }
        }
        dump($allRoundYears);
        if (empty($year) || empty($validYear)) {
            return $this->redirectToRoute(
                'rounds',
                array( 'year' => $lastYear )
            );
        }

        return $this->render('rounds/index.html.twig', [
            'round_years' => $allRoundYears,
            'round_count' => $roundRepository->getAllWithLogCount($validYear),
            'controller_name' => 'RoundController',
            'callSearch' => $callsignSearchForm->createView(),
        ]);
    }

    /**
     * @Route("/round/{date}", name="round", defaults={"date"=""})
     */
    public function round()
    {

    }
}

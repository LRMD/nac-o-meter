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
    public function round($date)
    {
        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $lastDate = $logRepository->findLastDate()[1];

        $roundCheck = $roundRepository->findBy(
            array('date' => new \DateTime($date) )
        );

        $allRoundYears = $roundRepository->findAllRoundYears();

        if (empty($roundCheck) && !empty($date)) {
            return $this->redirectToRoute(
                'round',
                array( 'date' => $lastDate )
            );
          }

        return $this->render('rounds/round.html.twig', [
            'round_years' => $allRoundYears,
            'round_date' => $date,
            'controller_name' => 'RoundController',
            'callSearch' => $callsignSearchForm->createView(),
        ]);

    }
}

<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Round;
use App\Entity\QsoRecord;
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
        $roundsThisYear = $roundRepository->getAllWithLogCount($validYear);

        foreach ($roundsThisYear as $k => $r) {
            $roundsThisYear[$k]['complete'] = $this->roundCompleteness($r[0]->getDate());
        }

        return $this->render('rounds/index.html.twig', [
            'round_years' => $allRoundYears,
            'year' => $year,
            'rounds_this_year' => $roundsThisYear,
            'controller_name' => 'RoundController',
            'callSearch' => $callsignSearchForm->createView(),
        ]);
    }

    private function roundCompleteness($date)
    {
        $qsoRepository = $this->getDoctrine()->getRepository(QsoRecord::class);
        $column = 'callsign';

        $roundLogsReceived = array_column(
            $qsoRepository->getDistinctParticipants($date),
            $column
        );
        $roundAllQSOs = array_column(
            $qsoRepository->getDistinctCorrespondents($date),
            $column
        );
        $allCalls = array_merge($roundLogsReceived, $roundAllQSOs);
        $received = sizeof($roundLogsReceived);
        $total = sizeof(array_unique($allCalls));
        if ($total == 0) {
            return $total;
        }
        $percent = $received / $total;
        return $percent;
    }

    private function validateRound($date)
    {
        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $roundCheck = $roundRepository->findBy(
            array('date' => new \DateTime($date) )
        );
        if (empty($roundCheck)) {
            return $this->redirectToRoute(
                'round',
                array( 'date' => $logRepository->findLastDate()[1] )
            );
        }
        return $roundCheck;
    }

    /**
     * @Route("/round/{date}/{callsign}", name="round_details", defaults={"date"=""})
     */
    public function getRoundLogsByCall($date,$callsign)
    {
        $dateCheck = $this->validateRound($date);
        if ($dateCheck instanceof \Symfony\Component\HttpFoundation\Response) {
            return $dateCheck;
        }

        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $qsoRecordRepository = $this->getDoctrine()->getRepository(QsoRecord::class);

        $roundName = $dateCheck[0]->getName();
        $allRoundYears = $roundRepository->findAllRoundYears();
        $roundLog = $qsoRecordRepository->getRoundLogByCallsign($date,$callsign);

        if (empty($roundLog) && !empty($callsign)) {
            return $this->redirectToRoute( 'round', array( 'date' => $date ) );
        }

        return $this->render('rounds/log.html.twig', [
            'round_years' => $allRoundYears,
            'round_name' > $roundName,
            'round_callsign' => $callsign,
            'round_log' => $roundLog,
            'round_date' => $date,
            'callSearch' => $callsignSearchForm->createView(),
        ]);

    }

    /**
     * @Route("/round/{date}", name="round", defaults={"date"=""})
     */
    public function getRoundLogs($date)
    {
        $roundCheck = $this->validateRound($date);
        if ($roundCheck instanceof \Symfony\Component\HttpFoundation\Response) {
            return $roundCheck;
        }

        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $callsignSearchForm = $this->createForm(CallsignSearch::class);

        $roundName = $roundCheck[0]->getName();
        $roundYears = $roundRepository->findAllRoundYears();
        $roundParticipants = $logRepository->findCallsignsByRoundDate($date);

        $roundCompleteness = $this->roundCompleteness($date);

        return $this->render('rounds/round.html.twig', [
            'round_years' => $roundYears,
            'round_name' => $roundName,
            'round_participants' => $roundParticipants,
            'round_date' => $date,
            'round_complete' => $roundCompleteness,
            'callSearch' => $callsignSearchForm->createView(),
        ]);

    }
}

<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Round;
use App\Entity\QsoRecord;
use App\Entity\Message;
use App\Form\CallsignSearch;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\ResultParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubmissionController extends AbstractController
{
    /**
     * @Route("/submit/", name="submit", defaults={"year"="","band"=""})
     */

    public function index(Request $request, $year, $band)
    {
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $qsoRepository = $this->getDoctrine()->getRepository(QsoRecord::class);
        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $msgRepository = $this->getDoctrine()->getRepository(Message::class);

        $lastDate = $logRepository->findLastDate()[1];
        $lastRounds = $roundRepository->findLastRoundDates($lastDate);
        $lastMsgDate = $msgRepository->getLastEntity()->getDate();
        $lastMonthStats = $logRepository->findLastMonthStats($lastDate);

        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $results = new ResultParser($this->getParameter('kernel.project_dir') . '/public_html/');
        $years = $results->getAllYears();

        $topFiveScores = [];

        foreach ($lastRounds as $lastRound) {
          $dateStr = $lastRound->getDate()->format('Y-m-d');
          $logsNotReceived[$dateStr] = $qsoRepository->getLogsNotReceived($dateStr);
          $topFiveScores[$dateStr] = $qsoRepository->getTopClaimedScores(
            $lastRound->getRoundId(),
            5,
            $this->getParameter('kernel.default_locale') == $request->getLocale()
          );
        }

        return $this->render('submit/index.html.twig', [
            'years' => $years,
            'year' => $year,
            'band' => $band,
            'table' => $results->getCSVRecords($year, $band),
            'controller_name' => 'ResultsController',
            'topFiveScores' => $topFiveScores,
            'lastRounds' => $lastRounds,
            'lastMonthStats' => $lastMonthStats,
            'lastDate' => $lastMsgDate->format('Y-m-d H:i'),
            'callSearch' => $callsignSearchForm->createView(),
        ]);
    }
}

<?php
// src/Controller/HomePageController.php
namespace App\Controller;

use App\Entity\Log;
use App\Entity\Round;
use App\Entity\Message;
use App\Entity\Callsign;
use App\Entity\QsoRecord;
use App\Form\CallsignSearch;
use Doctrine\ORM\EntityRepository;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, ChartBuilderInterface $chartBuilder)
    {
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $qsoRepository = $this->getDoctrine()->getRepository(QsoRecord::class);
        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $msgRepository = $this->getDoctrine()->getRepository(Message::class);

        $lastCallsigns = $logRepository->findLastCallsigns(5);
        $lastMsgDate = $msgRepository->getLastEntity()->getDate();
        $lastDate = $logRepository->findLastDate()[1];
        $lastMonthStats = $logRepository->findLastMonthStats($lastDate);
        $lastRounds = $roundRepository->findLastRoundDates($lastDate);
        $upcomingRounds = $roundRepository->findNextRoundDates();

        foreach ($lastMonthStats as $statItem) {
          $statLabels[] = $statItem['bandFreq'];
          $statData[] = $statItem['count'];
        }

        $lastMonthStatsChart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $lastMonthStatsChart->setData([
          'labels' => $statLabels,
          'datasets' => [
            [
              'label' => 'Month stats',
              'backgroundColor' => [
                  'rgb(255, 99, 132)',
                  'rgb(54, 162, 235)',
                  'rgb(255, 205, 86)',
                  'rgb(34, 139, 34)',
                  'rgb(148, 0, 211)'
              ],
              'data' => $statData,
            ],
          ],
        ]);

        $logsNotReceived = [];
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

        $callsignSearchForm = $this->createForm(CallsignSearch::class);

        return $this->render('home.html.twig', array(
          'lastMonthStats' => $lastMonthStats,
          'topFiveScores' => $topFiveScores,
          'lastDate' => $lastMsgDate->format('Y-m-d H:i'),
          'lastRounds' => $lastRounds,
          'lastMonthStatsChart' => $lastMonthStatsChart,
          'upcomingRounds' => $upcomingRounds,
          'lastCallsigns' => $lastCallsigns,
          'logsNotReceived' => $logsNotReceived,
          'callSearch' => $callsignSearchForm->createView(),
          'currentYear' => $lastMsgDate->format('Y')
        ));
    }

    /**
     * @Route("/call_search_handle", name="call_search_handle")
     */
    public function handleCallSearch(Request $request)
    {
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $callsignSearchForm->handleRequest($request);
        if ($callsignSearchForm->isSubmitted() && $callsignSearchForm->isValid()) {
            $data = $callsignSearchForm->getData();
            return $this->redirectToRoute(
              'call_search',
              array(
                'callsign' => $data['callsign']
              )
            );
        }
    }
}

<?php
// src/Controller/HomePageController.php
namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: 'home')]
    public function index(Request $request, ChartBuilderInterface $chartBuilder)
    {
        $logRepository = $this->doctrine->getRepository(Log::class);
        $qsoRepository = $this->doctrine->getRepository(QsoRecord::class);
        $roundRepository = $this->doctrine->getRepository(Round::class);
        $msgRepository = $this->doctrine->getRepository(Message::class);

        $lastCallsigns = $logRepository->findLastCallsigns(5);
        $lastMsgDate = $msgRepository->getLastEntity()->getDate();
        $lastDate = $logRepository->findLastDate()[1];
        $lastMonthStats = $logRepository->findLastMonthStats($lastDate);
        $lastMonthModeStats = $logRepository->findLastMonthModeStats($lastDate);

        $lastRounds = $roundRepository->findLastRoundDates($lastDate);
        $upcomingRounds = $roundRepository->findNextRoundDates();
        
        $locale = $this->getParameter('kernel.default_locale') == $request->getLocale();

        foreach ($lastMonthModeStats as $modeStatItem) {
          $modeStatLabels[] = $modeStatItem['mode'];
          $modeStatData[] = $modeStatItem['count'];
        }

        foreach ($modeStatLabels as $k => $v) {
          if ($v == 'RTTY') $modeStatLabels[$k] = 'FT8';
        }

        $lastMonthModeStatsChart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $lastMonthModeStatsChart->setData([
          'labels' => $modeStatLabels,
          'datasets' => [
            [
              'label' => 'Mode stats',
              'backgroundColor' => [
                  'rgb(255, 99, 132)',
                  'rgb(54, 162, 235)',
                  'rgb(255, 205, 86)',
                  'rgb(34, 139, 34)',
                  'rgb(148, 0, 211)'
              ],
              'data' => $modeStatData,
            ],
          ],
        ]);

        foreach ($lastMonthStats as $logStatItem) {
          $logStatLabels[] = $logStatItem['bandFreq'];
          $logStatData[] = $logStatItem['count'];
        }

        $lastMonthStatsChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $lastMonthStatsChart->setOptions([
          'maintainAspectRatio' => false
        ]);
        $lastMonthStatsChart->setData([
          'labels' => $logStatLabels,
          'datasets' => [
            [
              'label' => 'Logs received last month',
              'backgroundColor' => [
                  'rgb(255, 99, 132)',
                  'rgb(54, 162, 235)',
                  'rgb(255, 205, 86)',
                  'rgb(34, 139, 34)',
                  'rgb(148, 0, 211)'
              ],
              'data' => $logStatData,
            ],
          ],
        ]);

        // --- QSOs per year chart (LY vs others) ---
        $qsoPerYear = $qsoRepository->getQsoCountPerYearByPrefix();
        $qsoYearLabels = array_column($qsoPerYear, 'year');
        $qsoLyData     = array_column($qsoPerYear, 'ly');
        $qsoOtherData  = array_column($qsoPerYear, 'other');

        $qsoPerYearChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $qsoPerYearChart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ]);
        $qsoPerYearChart->setData([
            'labels' => $qsoYearLabels,
            'datasets' => [
                [
                    'label'           => 'LY',
                    'backgroundColor' => 'rgba(0, 87, 184, 0.8)',
                    'data'            => $qsoLyData,
                ],
                [
                    'label'           => 'DX',
                    'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                    'data'            => $qsoOtherData,
                ],
            ],
        ]);
        // --- end QSOs per year chart ---

        $logsNotReceived = [];
        $topFiveScores = [];

        foreach ($lastRounds as $lastRound) {
          $dateStr = $lastRound->getDate()->format('Y-m-d');
          $logsNotReceived[$dateStr] = $qsoRepository->getLogsNotReceived($dateStr);
          $topFiveScores[$dateStr] = $qsoRepository->getTopClaimedScores(
            $lastRound->getRoundId(),
            5,
            $locale
          );
          $topFiveScoresFM[$dateStr] = $qsoRepository->getTopClaimedScores(
            $lastRound->getRoundId(),
            5,
            $locale,
            'fm'
          );
          $topFiveScoresFT8[$dateStr] = $qsoRepository->getTopClaimedScores(
            $lastRound->getRoundId(),
            5,
            $locale,
            'ft8'
          );
        }

        $callsignSearchForm = $this->createForm(CallsignSearch::class);

        return $this->render('home.html.twig', array(
          'lastMonthStats' => $lastMonthStats,
          'topFiveScores' => $topFiveScores,
          'topFiveScoresFM' => $topFiveScoresFM,
          'topFiveScoresFT8' => $topFiveScoresFT8,
          'lastDate' => $lastMsgDate->format('Y-m-d H:i'),
          'lastRounds' => $lastRounds,
          'lastMonthStatsChart' => $lastMonthStatsChart,
          'lastMonthModeStatsChart' => $lastMonthModeStatsChart,
          'qsoPerYearChart' => $qsoPerYearChart,
          'upcomingRounds' => $upcomingRounds,
          'lastCallsigns' => $lastCallsigns,
          'logsNotReceived' => $logsNotReceived,
          'callSearch' => $callsignSearchForm->createView(),
          'currentYear' => $lastMsgDate->format('Y')
        ));
    }

    #[Route('/call_search_handle', name: 'call_search_handle', methods: ['GET', 'POST'])]
    public function handleCallSearch(Request $request)
    {
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $callsignSearchForm->handleRequest($request);

        // Handle both GET and POST methods
        $callsign = $request->get('callsign');
        if ($request->isMethod('GET') && $callsign) {
            return $this->redirectToRoute('call_search', ['callsign' => $callsign]);
        }

        if ($callsignSearchForm->isSubmitted() && $callsignSearchForm->isValid()) {
            $data = $callsignSearchForm->getData();
            return $this->redirectToRoute(
                'call_search',
                ['callsign' => $data['callsign']]
            );
        }
        
        // If form is not valid or not submitted, redirect back to home with a flash message
        $this->addFlash('error', 'Please enter a valid callsign');
        return $this->redirectToRoute('home');
    }
}

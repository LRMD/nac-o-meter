<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Callsign;
use App\Entity\Log;
use App\Form\CallsignSearch;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CallSignInfoController extends AbstractController
{
    /**
     * @Route("/call/{callsign}/{year}", name="call_search", defaults={"callsign"="","year"=""}, requirements={"callsign"=".+"})
     */
    public function callsignSearch($callsign, $year, ChartBuilderInterface $chartBuilder)
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

        $lastLogsByCallsign = $logRepository->findLastLogsByCallsign($callsign, 9999);

        $aggregatedUserLogs = array();
        foreach ($lastLogsByCallsign as $k => $v) {
            $year = $lastLogsByCallsign[$k]['date']->format("Y");
            if (isset($aggregatedUserLogs[$year])) {
                $aggregatedUserLogs[$year] = $aggregatedUserLogs[$year] + $v['count'];
            }
            else {
                $aggregatedUserLogs[$year] = $v['count'];
            }
        }

        $aggregatedUserModes = array();
        foreach ($lastLogsByCallsign as $k => $v) {
            $band = $lastLogsByCallsign[$k]['band'];
            if (isset($aggregatedUserModes[$mode])) {
                $aggregatedUserModes[$band] = $aggregatedUserModes[$band] + $v['count'];
            }
            else {
                $aggregatedUserModes[$band] = $v['count'];
            }
        }

        $userActivityChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $userActivityChart->setOptions([
          'maintainAspectRatio' => false
        ]);
        $userActivityChart->setData([
          'datasets' => [
            [
              'label' => 'QSO / year',
              'backgroundColor' => [
                  'rgb(255, 99, 132)',
                  'rgb(54, 162, 235)',
                  'rgb(255, 205, 86)',
                  'rgb(34, 139, 34)',
                  'rgb(148, 0, 211)'
              ],
              'data' => $aggregatedUserLogs
            ],
          ],
        ]);

        return $this->render(
            'callsign.html.twig',
            array(
                'callsign' => $callsign,
                'reports' => sizeof($lastLogsByCallsign),
                'wwls' => '',
                'loghistory' => array_slice($lastLogsByCallsign,0,20),
                'userActivityChart' => $userActivityChart,
                'callSearch' => $callsignSearchForm->createView()
            )
        );
    }
}

<?php

namespace App\Controller;

use App\Form\CallsignSearch;
use App\Utils\ResultParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopController extends AbstractController
{
    /**
     * @Route("/top/{year}/{band}", name="top_scores", defaults={"_locale"="lt"})
     * @Route("/{_locale}/top/{year}/{band}", name="top_scores_localized", requirements={"_locale"="en|lt|pl|uk"})
     */
    public function index(string $year, string $band): Response
    {
        $resultParser = new ResultParser($this->getParameter('kernel.project_dir') . '/public_html/');

        $records = $resultParser->getCSVRecords($year, $band);
        $scores = [];
        
        if ($records) {
            foreach ($records as $record) {
                $callsign = $record[array_keys($record)[0]];
                $score = $resultParser->getBestNineScores($callsign, $year, $band);
                if ($score !== null && preg_match('/^LY/', $callsign) && sizeof($scores) < 10) {
                    $scores[] = [
                        'callsign' => $callsign,
                        'score' => $score,
                        'mult' => 0
                    ];
                }
            }
            
            // Sort by score descending
            usort($scores, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            if (isset($scores[0])) $scores[0]['mult'] = 10;
            if (isset($scores[1])) $scores[1]['mult'] = 8;
            if (isset($scores[2])) $scores[2]['mult'] = 6;
            if (isset($scores[3])) $scores[3]['mult'] = 5;
            if (isset($scores[4])) $scores[4]['mult'] = 4;
            if (isset($scores[5])) $scores[5]['mult'] = 3;
            if (isset($scores[6])) $scores[6]['mult'] = 2;
            if (isset($scores[7])) $scores[7]['mult'] = 1;
        }

        $callsignSearchForm = $this->createForm(CallsignSearch::class);

        return $this->render('top/index.html.twig', [
            'scores' => $scores,
            'year' => $year,
            'band' => $band,
            'callSearch' => $callsignSearchForm->createView()
        ]);
    }
} 
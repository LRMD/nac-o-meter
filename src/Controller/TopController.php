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
     * @Route("/top/{year}", name="top_scores_all")
     * @Route("/top/{year}/{band}", name="top_scores")
     */
    public function index(string $year, ?string $band = null): Response
    {
        $resultParser = new ResultParser($this->getParameter('kernel.project_dir') . '/public_html/');

        $scores = $resultParser->getTopScoresWithMults($year, $band);
        
        $callsignSearchForm = $this->createForm(CallsignSearch::class);

        return $this->render('top/index.html.twig', [
            'scores' => $scores,
            'year' => $year,
            'band' => $band,
            'callSearch' => $callsignSearchForm->createView()
        ]);
    }
} 
<?php

namespace App\Controller;

use App\Form\CallsignSearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\ResultParser;

class ResultsController extends AbstractController
{
    /**
     * @Route("/results/{year}/{band}", name="results", defaults={"year"="","band"=""})
     */

    public function index($year, $band)
    {
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $results = new ResultParser($this->getParameter('kernel.project_dir') . '/public_html/');
        $years = $results->getAllYears();

        return $this->render('results/index.html.twig', [
            'years' => $years,
            'year' => $year,
            'band' => $band,
            'table' => $results->getCSVRecords($year, $band),
            'controller_name' => 'ResultsController',
            'callSearch' => $callsignSearchForm->createView(),
        ]);
    }
}

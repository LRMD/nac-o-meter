<?php

namespace App\Controller;

use App\Form\CallsignSearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RulesController extends AbstractController
{
    /**
     * @Route("/{_locale}/rules", name="rules", requirements={"_locale"="en|lt|uk"})
     */
    public function index(Request $request): Response
    {
        $callsignSearchForm = $this->createForm(CallsignSearch::class);
        $locale = $request->getLocale();

        return $this->render(sprintf('rules/%s.html.twig', $locale ?? 'lt'), [
            'callSearch' => $callsignSearchForm->createView()
        ]);
    }
} 
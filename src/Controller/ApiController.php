<?php

namespace App\Controller;

use App\Repository\CallsignRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/callsigns", name="api_callsigns", methods={"GET"})
     */
    public function getCallsigns(CallsignRepository $callsignRepository): JsonResponse
    {
        $callsigns = $callsignRepository->getAllCallsigns();

        return $this->json($callsigns);
    }
}

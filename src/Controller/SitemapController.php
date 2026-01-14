<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Round;
use App\Entity\Callsign;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap", name="sitemap", defaults={"_format"="xml"})
     */
    public function index(): Response
    {
        $urls = [];
        $hostname = 'https://lyac.qrz.lt';

        // Static pages
        $urls[] = ['loc' => $hostname . '/', 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => $hostname . '/rounds', 'priority' => '0.8', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => $hostname . '/results', 'priority' => '0.8', 'changefreq' => 'monthly'];
        $urls[] = ['loc' => $hostname . '/lt/rules', 'priority' => '0.5', 'changefreq' => 'yearly'];
        $urls[] = ['loc' => $hostname . '/en/rules', 'priority' => '0.5', 'changefreq' => 'yearly'];
        $urls[] = ['loc' => $hostname . '/submit', 'priority' => '0.6', 'changefreq' => 'monthly'];

        // Round years
        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $allRoundYears = $roundRepository->findAllRoundYears();
        foreach ($allRoundYears as $yearRow) {
            $urls[] = [
                'loc' => $hostname . '/rounds/' . $yearRow['y'],
                'priority' => '0.7',
                'changefreq' => 'monthly'
            ];
        }

        // Individual rounds
        $allRounds = $roundRepository->findAll();
        foreach ($allRounds as $round) {
            $dateStr = $round->getDate()->format('Y-m-d');
            $urls[] = [
                'loc' => $hostname . '/round/' . $dateStr,
                'priority' => '0.6',
                'changefreq' => 'monthly'
            ];
        }

        // Callsign pages (only those with logs)
        $logRepository = $this->getDoctrine()->getRepository(Log::class);
        $callsignRepository = $this->getDoctrine()->getRepository(Callsign::class);

        $callsignsWithLogs = $callsignRepository->createQueryBuilder('c')
            ->select('DISTINCT c.callsign')
            ->innerJoin('App\Entity\Log', 'l', 'WITH', 'l.callsignid = c.callsignid')
            ->orderBy('c.callsign', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        foreach ($callsignsWithLogs as $callsign) {
            $urls[] = [
                'loc' => $hostname . '/call/' . $callsign,
                'priority' => '0.5',
                'changefreq' => 'monthly'
            ];
        }

        // Generate XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($urls as $urlData) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $urlData['loc']);
            $url->addChild('changefreq', $urlData['changefreq']);
            $url->addChild('priority', $urlData['priority']);
        }

        $response = new Response($xml->asXML());
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
}

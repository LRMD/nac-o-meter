<?php

namespace App\Controller;

use App\Form\LogSubmitType;
use App\Form\CallsignSearch;
use App\Service\MailjetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubmitController extends AbstractController
{
    private $translator;
    private $mailjetService;

    public function __construct(MailjetService $mailjetService, TranslatorInterface $translator)
    {
        $this->mailjetService = $mailjetService;
        $this->translator = $translator;
    }

    /**
     * @Route("/submit", name="submit")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(LogSubmitType::class);
        $form->handleRequest($request);

        $callsignSearchForm = $this->createForm(CallsignSearch::class);

        $logContent = '';
        $filename = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Count valid QSO records
            $validQsos = 0;
            if (!empty($data['qsos'])) {
                foreach ($data['qsos'] as $qso) {
                    if (!empty($qso['date']) && !empty($qso['time']) && !empty($qso['call'])) {
                        $validQsos++;
                    }
                }
            }

            // Generate REG1TEST file content
            $logContent = "[REG1TEST;1]\r\n";
            $logContent .= "TName=" . $data['TName'] . "\r\n";
            $logContent .= "TDate=" . $data['TDate']->format('Ymd') . ";" . 
                          $data['TDate']->format('Ymd') . "\r\n";
            $logContent .= "PCall=" . strtoupper($data['PCall']) . "\r\n";
            $logContent .= "PWWLo=" . strtoupper($data['PWWLo']) . "\r\n";
            $logContent .= "PBand=" . $data['PBand'] . "\r\n";
            $logContent .= "PSect=SINGLE\r\n";
            
            if (!empty($data['RCall'])) {
                $logContent .= "RCall=" . strtoupper($data['RCall']) . "\r\n";
            }
            if (!empty($data['PClub'])) {
                $logContent .= "PClub=" . $data['PClub'] . "\r\n";
            }
            if (!empty($data['RAdr1'])) {
                $logContent .= "RAdr1=" . $data['RAdr1'] . "\r\n";
            }
            if (!empty($data['RAdr2'])) {
                $logContent .= "RAdr2=" . $data['RAdr2'] . "\r\n";
            }
            if (!empty($data['RPoCo'])) {
                $logContent .= "RPoCo=" . $data['RPoCo'] . "\r\n";
            }
            if (!empty($data['RCity'])) {
                $logContent .= "RCity=" . $data['RCity'] . "\r\n";
            }
            if (!empty($data['RCoun'])) {
                $logContent .= "RCoun=" . $data['RCoun'] . "\r\n";
            }
            if (!empty($data['RPhon'])) {
                $logContent .= "RPhon=" . $data['RPhon'] . "\r\n";
            }
            if (!empty($data['RHBBS'])) {
                $logContent .= "RHBBS=" . $data['RHBBS'] . "\r\n";
            }
            if (!empty($data['MOpe1'])) {
                $logContent .= "MOpe1=" . $data['MOpe1'] . "\r\n";
            }
            if (!empty($data['MOpe2'])) {
                $logContent .= "MOpe2=" . $data['MOpe2'] . "\r\n";
            }
            if (!empty($data['STXEq'])) {
                $logContent .= "STXEq=" . $data['STXEq'] . "\r\n";
            }
            if (!empty($data['SPowe'])) {
                $logContent .= "SPowe=" . $data['SPowe'] . "\r\n";
            }
            if (!empty($data['SRXEq'])) {
                $logContent .= "SRXEq=" . $data['SRXEq'] . "\r\n";
            }
            if (!empty($data['SAnte'])) {
                $logContent .= "SAnte=" . $data['SAnte'] . "\r\n";
            }
            if (!empty($data['SAntH'])) {
                $logContent .= "SAntH=" . $data['SAntH'] . "\r\n";
            }

            $logContent .= "[Remarks]\r\n";
            $logContent .= $data['Remarks'] ?? '' . "\r\n";
            $logContent .= "\r\n[QSORecords;" . $validQsos . "]\r\n";

            // Add QSO records
            if (!empty($data['qsos'])) {
                foreach ($data['qsos'] as $qso) {
                    if (!empty($qso['date']) && !empty($qso['time']) && !empty($qso['call'])) {
                        $logContent .= sprintf("%s;%s;%s;%s;%s;;%s;;;%s;7;;;;\r\n",
                            $qso['date']->format('ymd'),
                            str_replace(':', '', $qso['time']->format('Hi')),
                            $qso['call'],
                            $qso['mode'] === 'CW' ? '2' : 
                                ($qso['mode'] === 'SSB' ? '1' : 
                                ($qso['mode'] === 'FM' ? '6' : '7')),
                            $qso['sent'],
                            $qso['rcvd'],
                            $qso['wwl']
                        );
                    }
                }
            }

            // Generate filename
            $filename = sprintf('%s_%s_%s.edi',
                strtoupper($data['PCall']),
                $data['TDate']->format('Ymd'),
                str_replace(' ', '', $data['PBand'])
            );

            // Send via Mailjet
            $success = $this->mailjetService->sendLog(
                $data['PCall'],
                $data['TDate'],
                $logContent,
                $filename
            );

            if ($success) {
                $this->addFlash('success', $this->translator->trans('submit.success', [
                    'filename' => $filename
                ]));
            } else {
                $this->addFlash('error', $this->translator->trans('submit.error.send'));
            }
        }

        return $this->render('submit/index.html.twig', [
            'form' => $form->createView(),
            'logContent' => $logContent,
            'filename' => $filename,
            'callSearch' => $callsignSearchForm->createView()
        ]);
    }
} 
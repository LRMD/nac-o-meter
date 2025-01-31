<?php

namespace App\Utils;

use App\Kernel;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\CharsetConverter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResultParser
{
    private $result_dir;

    public function __construct($root_dir)
    {
        $this->result_dir = $root_dir . '/../results/';
    }

    private function getFilePath($year, $band)
    {
        $filename = "${year}_${band}.csv";
        $files = $this->getFilesByPattern($filename);
        if (!sizeof($files)) {
            return false;
        }
        $iterator = $files->getIterator();
        $iterator->rewind();

        return $iterator->current()->getRealPath();
    }

    private function getCSVReader($filepath) {
        return Reader::createFromPath($filepath, 'r')
                    ->setHeaderOffset(0)
                    ->setDelimiter(';')
        ;
    }

    public function getCSVRecords($year, $band)
    {
        $fp = $this->getFilePath($year, $band);
        if ($fp) {
            return $this->getCSVReader(
                $fp
            )->getRecords();
        }
    }

    public function getMonthResultByCall($call,$year,$month,$band)
    {
        $filepath = $this->getFilePath($year,$band);
        $reader = $this->getCSVReader($filepath);

        foreach ($reader as $record) {
                if ($record[array_keys($record)[0]] == $call) {
                    return $record[$month];
                }
        }
        return null;
    }

    public function getBestNineScores($call, $year, $band)
    {
        $filepath = $this->getFilePath($year, $band);
        if (!$filepath) {
            return null;
        }

        $reader = $this->getCSVReader($filepath);
        $scores = [];

        foreach ($reader as $record) {
            if ($record[array_keys($record)[0]] == $call) {
                // Get all month scores except the first column (callsign) and last column (total)
                $monthScores = array_slice($record, 1, 12);
                // Convert scores to integers and filter out empty values
                $monthScores = array_map('intval', array_filter($monthScores, 'strlen'));
                // Sort scores in descending order
                rsort($monthScores);
                // Take the best 9 scores (or all if less than 9)
                $bestNine = array_slice($monthScores, 0, 9);
                // Calculate sum
                return array_sum($bestNine);
            }
        }
        
        return null;
    }

    private function sortRounds($a,$b)
    {
        /* Check if round is microwave (has G in the name) */
        $ag = preg_match('/G/',$a);
        $bg = preg_match('/G/',$b);

        /* if both are G, check the first part until G */
        if ($ag && $bg) {
            return (
                (int) preg_split('/G/',$a)[0] >
                (int) preg_split('/G/',$b)[0]
            );
        }
        /* VUSHF always less than Gigahertz */
        elseif ($ag || $bg) {
            return $ag;
        }
        else return ($a > $b);
    }

    public function getAllYears()
    {
        $years = Array();
        $files = $this->getFilesByPattern('*.csv');
        foreach ($files as $f) {
            $name = $f->getFileName();
            list($year, $round, $_) = preg_split('/[_\.]/',$name);
            $years[$year][] = $round;

        }
        foreach ($years as $k => $_) {
            usort($years[$k],array($this,'sortRounds'));
        }
        ksort($years);
        return array_reverse($years,1);
    }

    private function getFilesByPattern($pattern)
    {
        $finder = new Finder();
        return $finder->files()->in($this->result_dir)->name($pattern);
    }

    public function getMultiplierForPosition(int $position): int 
    {
        if ($position === 1) return 10;
        if ($position === 2) return 8;
        if ($position === 3) return 6;
        if ($position === 4) return 5;
        if ($position === 5) return 4;
        if ($position === 6) return 3;
        if ($position === 7) return 2;
        if ($position === 8) return 1;
        return 0;
    }

    public function getTopScoresWithMults(?string $year = null, ?string $band = null): array
    {
        $bands = $band ? [$band] : ['144', '432', '1296', '2G4', '5G7', '10G'];
        $scores = [];
        $microwaveScores = [];
        $hasEmptyLastMonth = true;  // Start with true and set to false if any value found
        
        foreach ($bands as $currentBand) {
            $bandScores = [];
            $records = $this->getCSVRecords($year, $currentBand);
            
            if ($records) {
                foreach ($records as $record) {
                    $callsign = $record[array_keys($record)[0]];
                    $score = $this->getBestNineScores($callsign, $year, $currentBand);
                    
                    // Check if last month (12) has any value
                    $values = array_values($record);
                    if (isset($values[12]) && !empty(trim($values[12]))) {
                        $hasEmptyLastMonth = false;  // Found a value, so last month is not empty
                    }
                    
                    if ($score !== null && preg_match('/^LY/', $callsign)) {
                        if (in_array($currentBand, ['2G4', '5G7', '10G'])) {
                            // For microwave bands, accumulate scores per callsign
                            if (!isset($microwaveScores[$callsign])) {
                                $microwaveScores[$callsign] = 0;
                            }
                            $microwaveScores[$callsign] += $score;
                        } else {
                            $bandScores[] = [
                                'callsign' => $callsign,
                                'score' => $score,
                                'mult' => 0
                            ];
                        }
                    }
                }
                
                if (!in_array($currentBand, ['2G4', '5G7', '10G'])) {
                    // Sort and process regular bands
                    usort($bandScores, function($a, $b) {
                        return $b['score'] - $a['score'];
                    });
                    
                    // Take only top 10 scores
                    $bandScores = array_slice($bandScores, 0, 10);
                    
                    // Assign multipliers based on position
                    foreach ($bandScores as $index => $score) {
                        $bandScores[$index]['mult'] = $this->getMultiplierForPosition($index + 1);
                    }
                    
                    if (!empty($bandScores)) {
                        $scores[$currentBand] = $bandScores;
                    }
                }
            }
        }
        
        // Process microwave scores if any exist
        if (!empty($microwaveScores)) {
            $combinedScores = [];
            foreach ($microwaveScores as $callsign => $score) {
                $combinedScores[] = [
                    'callsign' => $callsign,
                    'score' => $score,
                    'mult' => 0
                ];
            }
            
            // Sort microwave scores
            usort($combinedScores, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            // Take only top 10 scores
            $combinedScores = array_slice($combinedScores, 0, 10);
            
            // Assign multipliers based on position
            foreach ($combinedScores as $index => $score) {
                $combinedScores[$index]['mult'] = $this->getMultiplierForPosition($index + 1);
            }
            
            if (!empty($combinedScores)) {
                $scores['Microwave'] = $combinedScores;
            }
        }
        
        return [
            'scores' => $scores,
            'hasEmptyLastMonth' => $hasEmptyLastMonth
        ];
    }
}

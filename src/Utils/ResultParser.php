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
}

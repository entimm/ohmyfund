<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

class Table2Array
{
    static function convert($html)
    {
        if (is_null($html) || ! is_string($html)) {
            return [];
        }

        $crawler = new Crawler();
        $crawler->addHtmlContent($html);

        //todo.. multile tables
        $table = $crawler->filterXPath('//table[1]');

        //parse headers
        $headers = $table->evaluate('.//tr/th/text()')->each(function (Crawler $node, $i) {
            return $node->text();
        });

        $data = $table->evaluate('.//tr')->each(function (Crawler $node, $i) use ($headers) {
            $row = $node->evaluate('.//td')->each(function (Crawler $node, $i) use ($headers) {
                return $node->text();
            });

            return Self::addHeader($row, $headers);
        });

        return array_values(array_filter($data));
    }

    static function addHeader(array $array, array $headers = [])
    {
        $output = [];

        foreach ($array as $key => $val) {
            $output[$headers[$key]] = $val;
        }

        return $output;
    }
}

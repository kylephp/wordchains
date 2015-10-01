<?php

require_once __DIR__ . "/../../vendor/autoload.php";

class GraphTest extends PHPUnit_Framework_TestCase
{
    public function testPlant2Chock()
    {
        $graph = new WordChains\Graph();
        $chains = $graph->getShortestPaths('plant', 'chock');
        $expected = array('plant', 'plank', 'clank', 'clack', 'clock', 'chock');

        $this->assertContains($expected, $chains);
    }

    public function testShock2Alone()
    {
        $graph = new WordChains\Graph();
        $chains = $graph->getShortestPaths('shock', 'alone');
        $expected = array('shock', 'stock', 'stork', 'store', 'stone', 'atone', 'alone');

        $this->assertContains($expected, $chains);
    }

    public function testCrazy2Glaze()
    {
        $graph = new WordChains\Graph();
        $chains = $graph->getShortestPaths('crazy', 'glaze');
        $expected = array('crazy', 'craze', 'graze', 'glaze');

        $this->assertContains($expected, $chains);
    }

    public function testClimb2Happy()
    {
        $graph = new WordChains\Graph();
        $chains = $graph->getShortestPaths('climb', 'happy');
        $expected = array('climb', 'clime', 'slime', 'slimy', 'slily', 'saily', 'haily', 'haply', 'happy');

        $this->assertContains($expected, $chains);
    }

    public function testRiver2Blade()
    {
        $graph = new WordChains\Graph();
        $chains = $graph->getShortestPaths('river', 'blade');
        $expected = array('river', 'raver', 'waver', 'wavey', 'waney', 'wandy', 'bandy', 'bendy', 'beady', 'blady', 'blade');

        $this->assertContains($expected, $chains);
    }
}

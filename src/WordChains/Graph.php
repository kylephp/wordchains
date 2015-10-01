<?php

namespace WordChains;

use Everyman\Neo4j\Client       as Database;
use Everyman\Neo4j\Cypher\Query as Query;

class Graph
{
    /**
     * Neo4j Client Object
     *
     * @var  Neo4j\Client
     */
    protected $db;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Get the shortest paths from A to B
     *
     * @param  string $a
     * @param  string $b
     * @return array
     */
    public function getShortestPaths($a, $b)
    {
        $q = new Query(
            $this->db,
            <<<EOHD
START a=node:words(word='$a'), b=node:words(word='$b')
MATCH p=allshortestPaths((a)-[:IS_ADJACENT_TO*]->(b))
RETURN p;
EOHD
        );
        $resultSet = $q->getResultSet();
        $shortestPaths = array();
        foreach ($resultSet as $row) {
            $path = array();
            foreach ($row['p']->getNodes() as $node) {
                $path[] = $node->getProperty('word');
            }
            $shortestPaths[] = $path;
        }
        return array_unique($shortestPaths, SORT_REGULAR);
    }
}

<?php

namespace Kyle\WordChains;

use Everyman\Neo4j\Client       as Database;
use Everyman\Neo4j\Index\NodeFulltextIndex       as NodeFulltextIndex;
use Everyman\Neo4j\Cypher\Query as Query;

class Graph
{
    protected $db;
    /**
     * Neo4j Index Object
     *
     * @var  Neo4j\Index
     */
    protected $index;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->db = new Database;
        $this->index = new NodeFulltextIndex($this->db, 'words');
        // $this->index->save();
        // $this->setWords(array());
        // $this->import();
        // $this->processAdjacentWords();
    }

    /**
     * Method to import words from db
     *
     * @return  mixed
     */
    public function import()
    {
        $this->db->startBatch();

        $words = file('words.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!empty($words)) {
            foreach ($words as $word) {
                $this->addWord($word);
            }
        }

        $this->db->commitBatch();
    }

    /**
     * Add single word
     *
     * @param string $word
     */
    public function addWord($word)
    {
        // Don’t add if already exists
        $wordNode = $this->index->findOne('word', $word);
        if ($wordNode) {
            return;
        }
        // Add to database
        $node = $this->db->makeNode();
        $node->setProperty('word', $word)->save();
        // Add to index
        $this->index->add($node, 'word', $node->getProperty('word'));
    }

    /**
     * Retrieve all words from the dictionary, then process & store adjacent words
     */
    public function processAdjacentWords()
    {
        $words = $this->getWords();
        foreach ($words as $word) {
            $this->setAdjacentWords($word, $this->getAdjacentWords($word, $words));
        }
    }

    /**
     * Get all words
     *
     * @return array
     */
    public function getWords()
    {
        $q = new Query(
            $this->db,
            <<<EOHD
START n=node(*)
WHERE has(n.word)
RETURN n.word as word;
EOHD
        );
        $words = array();
        $resultSet = $q->getResultSet();
        foreach ($resultSet as $word) {
            $words[] = $word['n'];
        }
        return $words;
    }

    /**
     * Store adjacent words for a given word
     *
     * @param string $word
     * @param array  $adjacentWords
     */
    public function setAdjacentWords($word, array $adjacentWords)
    {
        $this->db->startBatch();
        $wordNode = $this->index->findOne('word', $word);
        if ($wordNode) {
            foreach ($adjacentWords as $adjacentWord) {
                $adjacentWordNode = $this->index->findOne('word', $adjacentWord);
                if ($adjacentWordNode) {
                    $wordNode->relateTo($adjacentWordNode, 'IS_ADJACENT_TO')->save();
                }
            }
        }
        $this->db->commitBatch();
    }

    /**
     * Get adjacent words for a given word, from an array of words
     *
     * @param  string $a
     * @param  array  $dictionary
     * @return array
     */
    public function getAdjacentWords($a, $dictionary)
    {
        $adjacentWords = array();
        foreach ($dictionary as $b) {
            if ($a === $b || !$this->areTheSameLength($a, $b) || !$this->areOneLetterApart($a, $b)) {
                continue;
            }

            $adjacentWords[] = $b;
        }
        return $adjacentWords;
    }

    /**
     * Are two strings the same length?
     *
     * @param  string $a
     * @param  string $b
     * @return bool
     */
    public function areTheSameLength($a, $b)
    {
        return (mb_strlen($a) === mb_strlen($b));
    }

    /**
     * Are two strings one letter apart?
     *
     * @param  string $a
     * @param  string $b
     * @return bool
     */
    public function areOneLetterApart($a, $b)
    {
        $a = mb_strtolower($a);
        $b = mb_strtolower($b);

        // Levenshtein with a cost of 1 for replace, but 2 for insert/delete will give a result of 1 for one letter difference
        return levenshtein($a, $b, 2, 1, 2) === 1;
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
MATCH p=shortestPath((a)-[:IS_ADJACENT_TO*]->(b))
RETURN p;
EOHD
        );
        $resultSet = $q->getResultSet();
        $shortestPaths = array();
        foreach ($resultSet as $row) {
            $thisPath = array();
            foreach ($row['p']->getNodes() as $node) {
                $thisPath[] = $node->getProperty('word');
            }
            $shortestPaths[] = $thisPath;
        }

        return $shortestPaths;
    }

    /**
     * Method to get shortest word chain
     *
     * @param   string  $a  Word a
     * @param   string  $b  Word b
     *
     * @return  mixed
     */
    public function getShortestPath($a, $b)
    {
        if (!empty($this->graph)) {
            // Mark all nodes as unvisited
            foreach ($this->graph as $k => $v) {
                $this->visited[$k] = false;
            }

            // Create an empty queue
            $q = new \SplQueue();

            // enqueue the $a vertex and mark as visited
            $q->enqueue($a);
            $this->visited[$a] = true;

            // this is used to track the path back from each node
            $path = array();
            $path[$a] = new \SplDoublyLinkedList();
            $path[$a]->setIteratorMode(
                \SplDoublyLinkedList::IT_MODE_FIFO|\SplDoublyLinkedList::IT_MODE_KEEP
            );

            while (!$q->isEmpty() && !$q->bottom()!= $b) {
                $t = $q->dequeue();

                if (!empty($this->graph[$t])) {
                    foreach ($this->graph[$t] as $vertex) {
                        if (!$this->visited[$vertex]) {
                            $q->enqueue($vertex);
                            $this->visited[$vertex] = true;

                            // Add vertex to current path
                            $path[$vertex] = clone $path[$t];
                            $path[$vertex]->push($vertex);
                        }
                    }
                }
            }

            if (isset($path[$b])) {
                return $path[$b];
            }
        }

        return false;
    }

    /**
     * Remove and replace words in the dictionary
     *
     * @param array $words
     */
    public function setWords(array $words)
    {
        // Empty db
        $q = new Query(
            $this->db,
            <<<EOHD
START n=node(*)
MATCH (n)-[r]-()
WHERE has(n.word)
DELETE n, r;
EOHD
        );
        // Execute
        $resultSet = $q->getResultSet();
        $this->db->startBatch();
        foreach ($words as $word) {
            $this->addWord($word);
        }
        $this->db->commitBatch();
    }
}
$graph = new Graph();
$r = $graph->getShortestPaths('star', 'stop');
var_dump($r);
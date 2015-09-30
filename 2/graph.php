<?php

namespace Kyle\WordChain;

class Graph
{
    /**
     * Array of word chains relation
     *
     * @var  array
     */
    protected $graph;
    /**
     * Array to track the node visited or not
     *
     * @var  array
     */
    protected $visited;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->import();
    }

    /**
     * Method to import words from db
     *
     * @return  mixed
     */
    public function import()
    {
        $words = file('test.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!empty($words)) {
            foreach ($words as $word) {
                $this->graph[$word] = $this->getAdjacentWords($word, $words);
            }
        }
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
}
$graph = new Graph();
$r = $graph->getShortestPath('cat', 'dog');
if (!empty($r)) {
    foreach ($r as $v) {
        echo $v;
        echo "<br>";
    }

}

<?php

namespace WordChains;

use Everyman\Neo4j\Client       as Database;
use Everyman\Neo4j\Index\NodeFulltextIndex       as NodeFulltextIndex;
use Everyman\Neo4j\Cypher\Query as Query;

class Importer
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
        $this->index->save();
    }

    /**
     * Method to empty database
     *
     * @return  mixed
     */
    public function reset()
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

        return $q->getResultSet();
    }

    /**
     * Method to import words from db
     *
     * @return  mixed
     */
    public function import()
    {
        $this->db->startBatch();

        $files = array(
            'xml/gcide_a.xml',
            'xml/gcide_b.xml',
            'xml/gcide_c.xml',
            'xml/gcide_d.xml',
            'xml/gcide_e.xml',
            'xml/gcide_f.xml',
            'xml/gcide_g.xml',
            'xml/gcide_h.xml',
            'xml/gcide_i.xml',
            'xml/gcide_j.xml',
            'xml/gcide_k.xml',
            'xml/gcide_l.xml',
            'xml/gcide_m.xml',
            'xml/gcide_n.xml',
            'xml/gcide_o.xml',
            'xml/gcide_p.xml',
            'xml/gcide_q.xml',
            'xml/gcide_r.xml',
            'xml/gcide_s.xml',
            'xml/gcide_t.xml',
            'xml/gcide_u.xml',
            'xml/gcide_v.xml',
            'xml/gcide_w.xml',
            'xml/gcide_x.xml',
            'xml/gcide_y.xml',
            'xml/gcide_z.xml'
        );
        $words = array();

        foreach ($files as $file) {
            $content = @file_get_contents(__DIR__ . '/' .$file);

            if (!empty($content)) {
                if (preg_match_all("/<hw>(.*?)<\/hw>/", $content, $matches)) {
                    $tags = $matches[1];

                    if (!empty($tags)) {
                        foreach ($tags as $tag) {
                            $word = preg_replace("/[-]/", " ", $tag);
                            $word = preg_replace("/[^a-zA-Z0-9\s]/", "", $word);
                            $word = strtolower(trim($word));

                            if (!empty($word)) {
                                $words[] = $word;
                            }
                        }
                    }
                }
            }
        }

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
}

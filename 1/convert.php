<?php
/*    $connection = mysql_connect('localhost', 'root', 'root') or die('Could not connect to MySQL database. ' . mysql_error());
    $db = mysql_select_db('words',$connection);*/

    $xml = array('xml/gcide_a.xml', 'xml/gcide_b.xml', 'xml/gcide_c.xml', 'xml/gcide_d.xml', 'xml/gcide_e.xml','xml/gcide_f.xml','xml/gcide_g.xml', 'xml/gcide_h.xml', 'xml/gcide_i.xml', 'xml/gcide_j.xml', 'xml/gcide_k.xml', 'xml/gcide_l.xml', 'xml/gcide_m.xml', 'xml/gcide_n.xml', 'xml/gcide_o.xml', 'xml/gcide_p.xml', 'xml/gcide_q.xml', 'xml/gcide_r.xml', 'xml/gcide_s.xml', 'xml/gcide_t.xml', 'xml/gcide_u.xml', 'xml/gcide_v.xml', 'xml/gcide_w.xml', 'xml/gcide_x.xml', 'xml/gcide_y.xml', 'xml/gcide_z.xml');
    $numberoffiles = count($xml);
    $words = array();

    for ($i = 0; $i <= $numberoffiles-1; $i++) {
        $xmlfile = $xml[$i];

        // original file contents
        $original_file = @file_get_contents($xmlfile);

        // if file_get_contents fails to open the link do nothing
        if(!$original_file) {}
        else {
            // find words in original file contents

            preg_match_all("/<hw>(.*?)<\/hw>/", $original_file, $matches);

            $result = array_unique($matches[1]);

            $numberofwords = count($result);

            // traverse words array
            for ($j = 0; $j <= $numberofwords-1; $j++) {
                $word = preg_replace("/[-]/", " ", $result[$j]);
                $word = preg_replace("/[^a-zA-Z0-9\s]/", "", $word);
                $word = strtolower($word);
                if ($word != "") {

                   /* $uniquesql = "SELECT word FROM ind_words WHERE word='$word'";
                    $uniqueresult = mysql_query($uniquesql) or die(mysql_error());
                    $uniquenum = mysql_num_rows($uniqueresult);

                    // prevent duplicates
                    if ($uniquenum == 0) {
                        $insertsql = "INSERT INTO ind_words (word) VALUES ('$word')";
                        $insertresult = mysql_query($insertsql) or die(mysql_error());
                    }*/
                    $words[] = trim($word);
                }
            }
        }
    }

    if ($words)
    {
       $text = implode(PHP_EOL, $words);
       file_put_contents('words.txt', $text);
    }
    echo 'Done!';
?>

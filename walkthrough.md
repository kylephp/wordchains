###About word chains puzzle:
Write a program that solves a word-chain puzzle. The challenge is to build a chain of words, starting with one particular word and ending with another. Successive entries in the chain must all be real words from the dictionary, and each can differ from the previous word by just one letter. For example, you can get from “cat” to “dog” using the following chain.

**cat - cot - cog - dog**

The objective of this kata is to write a program that accepts start and end words and, using words from the dictionary, builds a word chain between them. If possible, return the shortest word chain that solves each puzzle. For example, you can turn “lead” into “gold” in four steps (lead - load - goad - gold), and “ruby” into “code” in six steps (ruby - rubs - robs - rods - rode - code).
###Walkthrough:

To solve this puzzle, we simply have to find the "adjacent words" of the given start word until the end word found.

####1. What are the "adjacent words"?

Word A is adjacent to word B only if:
	* They have the same length
	* They are one letter apart

For example: `cat` is adjacent to `cot` since they are both 3-letter words and just one letter apart (`a` vs `o`)

####2. Data structure:

First, we use the dictionary from here: http://www.ibiblio.org/webster/
They are in XML format, we have to parse and store them in a graph which is usually used to solve the Least Number of Hops and Shortest-Path problems.

In this case, it's a unweighted graph in which a node is considered as a word:
<img src="http://dab1nmslvvntp.cloudfront.net/wp-content/uploads/2013/07/dg-graphs01.png" alt="">

```
<?php
$graph = array(
  'A' => array('B', 'F'),
  'B' => array('A', 'D', 'E'),
  'C' => array('F'),
  'D' => array('B', 'E'),
  'E' => array('B', 'D', 'F'),
  'F' => array('A', 'E', 'C'),
);
```

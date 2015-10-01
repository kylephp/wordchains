#Requirements:
* [Neo4j server](http://neo4j.com/download/)
* [Composer](https://getcomposer.org/doc/00-intro.md#globally)

#Instructions:
1. Start neo4j server:
  * Extract neo4j-server.zip
  * Run: `./neo4j-server/bin/neo4j start`
2. Run: `composer install`
3. To import words to graph database, run:`./bin/import` (you can skip this step if you download neo4j server on this repo because it's fully imported)
4. To use cli application, run:`./bin/getchain wordA wordB`
5. Run test suite: `phpunit tests`

#Repository to solve word chains kata:

##Part I: Dictionary database:
Create an English dictionary database with any database tool of your choice. Take a list of all words from this site: http://www.ibiblio.org/webster/
They are in XML format so you would need to create a class that parses and dumps the XML data into a database.

##Part II: Word Chain Puzzle:
Write a program that solves a word-chain puzzle. The challenge is to build a chain of words, starting with one particular word and ending with another. Successive entries in the chain must all be real words from the dictionary, and each can differ from the previous word by just one letter. For example, you can get from “cat” to “dog” using the following chain.

cat - cot - cog - dog

The objective of this kata is to write a program that accepts start and end words and, using words from the dictionary, builds a word chain between them. If possible, return the shortest word chain that solves each puzzle. For example, you can turn “lead” into “gold” in four steps (lead - load - goad - gold), and “ruby” into “code” in six steps (ruby - rubs - robs - rods - rode - code).

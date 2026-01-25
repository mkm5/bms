<?php declare(strict_types=1);

namespace App\Doctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class Similarity extends FunctionNode
{
    private Node $text1;
    private Node $text2;

    public function parse(Parser $parser) : void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->text1 = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->text2  = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker) : string
    {
        return sprintf('similarity(%s, %s)',
            $this->text1->dispatch($sqlWalker),
            $this->text2->dispatch($sqlWalker),
        );
    }
}

<?php declare(strict_types=1);

namespace App\Doctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class WebsearchToTsquery extends FunctionNode
{
    private ?Node $config = null;
    private Node $expr1;

    public function parse(Parser $parser) : void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->expr1 = $parser->StringPrimary();

        if ($parser->getLexer()->isNextToken(TokenType::T_COMMA)) {
            $parser->match(TokenType::T_COMMA);
            $this->config = $this->expr1;
            $this->expr1  = $parser->StringPrimary();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker) : string
    {
        if (null === $this->config) {
            return sprintf(
                'websearch_to_tsquery(%s)',
                $this->expr1->dispatch($sqlWalker)
            );
        }

        return sprintf(
            'websearch_to_tsquery(%s, %s)',
            $this->config->dispatch($sqlWalker),
            $this->expr1->dispatch($sqlWalker)
        );
    }
}

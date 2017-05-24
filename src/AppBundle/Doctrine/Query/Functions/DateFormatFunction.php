<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Query\Functions;

use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class DateFormatFunction extends FunctionNode
{
    /**
     * @var ArithmeticExpression
     */
    private $date;

    /**
     * @var ArithmeticExpression
     */
    private $format;

    /**
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticExpression();

        $parser->match(Lexer::T_COMMA);

        $this->format = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param SqlWalker $walker
     *
     * @return string
     */
    public function getSql(SqlWalker $walker): string
    {
        return sprintf('DATE_FORMAT(%s, %s)', $this->date->dispatch($walker), $this->format->dispatch($walker));
    }
}

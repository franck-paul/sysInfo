<?php

/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo\Helper;

use Dotclear\App;
use Dotclear\Exception\AppException;
use Dotclear\Exception\BadRequestException;
use Dotclear\Exception\BlogException;
use Dotclear\Exception\ConfigException;
use Dotclear\Exception\ConflictException;
use Dotclear\Exception\ContextException;
use Dotclear\Exception\DatabaseException;
use Dotclear\Exception\InternalServerException;
use Dotclear\Exception\NotFoundException;
use Dotclear\Exception\PreconditionException;
use Dotclear\Exception\ProcessException;
use Dotclear\Exception\SessionException;
use Dotclear\Exception\TemplateException;
use Dotclear\Exception\UnauthorizedException;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use ReflectionClass;

class Exceptions
{
    /**
     * Return list of known exceptions
     */
    public static function render(): string
    {
        // Liste des exceptions connues

        /**
         * @var array<class-string, array{0: int, 1: string}>
         */
        $list = [
            AppException::class            => [503, 'Site temporarily unavailable'],
            BadRequestException::class     => [400, 'Bad Request'],
            BlogException::class           => [570, 'Blog handling error'],
            ConfigException::class         => [551, 'Application configuration error'],
            ConflictException::class       => [409, 'Conflict'],
            ContextException::class        => [553, 'Application context error'],
            DatabaseException::class       => [560, 'Database connection error'],
            InternalServerException::class => [500, 'Internal Server Error'],
            NotFoundException::class       => [404, 'Not found'],
            PreconditionException::class   => [412, 'Precondition Failed'],
            ProcessException::class        => [552, 'Application process error'],
            SessionException::class        => [561, 'Session handling error'],
            TemplateException::class       => [571, 'Template handling error'],
            UnauthorizedException::class   => [401, 'Unauthorized'],
        ];

        App::lexical()->lexicalKeySort($list, App::lexical()::ADMIN_LOCALE);

        $exceptions = function () use ($list) {
            foreach ($list as $name => $info) {
                // @phpstan-ignore argument.type
                $short = (new ReflectionClass($name))->getShortName();
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($short),
                        (new Td())
                            ->items([
                                (new Text('code', (string) $name)),
                            ]),
                        (new Td())
                            ->items([
                                (new Text('code', (string) $info[0])),
                            ]),
                        (new Td())
                            ->items([
                                (new Text('code', (string) $info[1])),
                            ]),
                    ]);
            }
        };

        return (new Table('exceptions'))
            ->class('sysinfo')
            ->caption(new Caption(__('Registered Exceptions') . ' (' . sprintf('%d', count($list)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Name')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Value')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Code')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Label')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $exceptions(),
                ]))
        ->render();
    }
}

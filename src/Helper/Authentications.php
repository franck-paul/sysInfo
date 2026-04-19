<?php

/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo\Helper;

use Dotclear\App;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\sysInfo\My;

class Authentications
{
    /**
     * Return list of authentications methods
     */
    public static function render(): string
    {
        $rows = [];

        $image = fn (bool $disabled): Img => $disabled ?
            (new Img('images/check-off.svg'))
                ->alt(__('Disabled'))
                ->class(['mark', 'mark-check-off']) :
            (new Img('images/check-on.svg'))
                ->alt(__('Enabled'))
                ->class(['mark', 'mark-check-on']);

        // Login/Password
        $rows[] = (new Tr())
            ->cols([
                (new Td())
                    ->class('nowrap')
                    ->text(__('Login/Password')),
                (new Td())
                    ->class(['nowrap', 'txt-center'])
                    ->items([
                        $image(false),
                    ]),
                (new Td())
                    ->class('maximal')
                    ->text('Dotclear'),
            ]);

        // 2FA
        $rows[] = (new Tr())
            ->cols([
                (new Td())
                    ->class('nowrap')
                    ->text(__('2 factors authentication')),
                (new Td())
                    ->class(['nowrap', 'txt-center'])
                    ->items([
                        $image(App::backend()->auth()->otp() === false),
                    ]),
                (new Td())
                    ->class('maximal')
                    ->text('Dotclear'),
            ]);

        // oAuth2

        // Set oAuth2 redirect URL (which also load services)
        App::backend()->auth()->oauth2(App::config()->adminUrl() . My::manageUrl());

        if (App::backend()->auth()->oauth2() !== false && App::backend()->auth()->oauth2()->services()->getProviders() !== []) {
            // oAuth2 enabled, list providers
            foreach (App::backend()->auth()->oauth2()->services()->getProviders() as $oauth2_service) {
                $oauth2_service_id = is_string($oauth2_service_id = $oauth2_service::getId()) ? $oauth2_service_id : '';
                if ($oauth2_service_id !== '') {
                    $disabled = App::backend()->auth()->oauth2()->services()->hasDisabledProvider($oauth2_service_id) || !App::backend()->auth()->oauth2()->store()->hasConsumer($oauth2_service_id);
                    $icon     = is_string($icon = $oauth2_service::getIcon()) ? $icon : '';
                    $name     = is_string($name = $oauth2_service::getName()) ? $name : '';

                    $rows[] = (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text(__('3rd party applications connections')),
                            (new Td())
                                ->class(['nowrap', 'txt-center'])
                                ->items([
                                    $image($disabled),
                                ]),
                            (new Td())
                                ->class('maximal')
                                ->separator(' ')
                                ->items([
                                    (new Img($icon))
                                        ->class('icon-mini'),
                                    (new Text(null, $name)),
                                ]),
                        ]);
                }
            }
        } else {
            // oAuth2 Disabled
            $rows[] = (new Tr())
                ->cols([
                    (new Td())
                        ->class('nowrap')
                        ->text(__('3rd party applications connections')),
                    (new Td())
                        ->class(['nowrap', 'txt-center'])
                        ->items([
                            $image(true),
                        ]),
                    (new Td())
                        ->class('maximal')
                        ->text(''),
                ]);
        }

        // Passkeys
        $rows[] = (new Tr())
            ->cols([
                (new Td())
                    ->class('nowrap')
                    ->text(__('Passkey sign in')),
                (new Td())
                    ->class(['nowrap', 'txt-center'])
                    ->items([
                        $image(App::backend()->auth()->webauthn() === false),
                    ]),
                (new Td())
                    ->class('maximal')
                    ->text('Dotclear'),
            ]);

        return (new Table('authentications'))
            ->class('sysinfo')
            ->caption(new Caption(__('Authentication methods')))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Type')),
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Enabled')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('Provider')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows($rows))
        ->render();
    }
}

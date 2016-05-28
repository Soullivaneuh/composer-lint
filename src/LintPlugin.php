<?php

namespace SLLH\ComposerLint;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LintPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Linter
     */
    private $linter;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->linter = new Linter();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::COMMAND => array(
                array('command'),
            ),
        );
    }

    /**
     * @param CommandEvent $event
     *
     * @return bool true if no violation, false otherwise.
     */
    public function command(CommandEvent $event)
    {
        if ('validate' !== $event->getCommandName()) {
            return true;
        }

        $json = new JsonFile($event->getInput()->getArgument('file'));
        $manifest = $json->read();

        $errors = $this->linter->validate($manifest);

        foreach ($errors as $error) {
            $this->io->writeError(sprintf('<error>%s</error>', $error));
        }

        return empty($errors);
    }
}

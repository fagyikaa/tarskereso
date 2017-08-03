<?php

namespace Core\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class AssetsCommand extends ContainerAwareCommand {

    /**
     * Set name, add description and an argument to the command.
     */
    protected function configure() {
        $this
                ->setName('tarsker:assets')
                ->setDescription('Firstly, assets install runs, if it\'s executed, then assetic dump runs, and finally bazinga js-translation dump runs.')
                ->addArgument(
                        'cache', InputArgument::OPTIONAL, 'If you would like to run cache clear before assets install and assetic dump, then add \'dev\' or \'prod\' to this parameter based on the environment. If you would not like to run cache clear, then do not add this parameter.'
                )
        ;
    }

    /**
     * If the argument is 'dev' then runs cache clear first. Then installs assets and dumps it. Finally
     * dumps bazingajs translations. 
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $cache = $input->getArgument('cache');
        if (!is_null($cache)) {
            $this->cacheClearCommand($output, $input->getArgument('cache'));
        }
        $this->assetsInstallCommand($output);
        $this->asseticDumpCommand($output);
        $this->bazinaJsTranslationDumpCommand($output);
    }

    /**
     * Runs aassets isntall symfony command.
     * 
     * @param OutputInterface $output
     * @return int
     */
    private function assetsInstallCommand(OutputInterface $output) {
        $command = $this->getApplication()->find('assets:install');

        $arguments = array(
            'command' => 'assets:install'
        );

        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }

    /**
     * Runs assetic dump symfony command.
     * 
     * @param OutputInterface $output
     * @return int
     */
    private function asseticDumpCommand(OutputInterface $output) {
        $command = $this->getApplication()->find('assetic:dump');

        $arguments = array(
            'command' => 'assetic:dump'
        );

        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }

    /**
     * If the argumant isn't 'dev' or 'prod' then throws exception. Clears the cache
     * via symfony cache clear command.
     * 
     * @param OutputInterface $output
     * @param string $environment
     * @return int
     * @throws \InvalidArgumentException
     */
    private function cacheClearCommand(OutputInterface $output, $environment) {
        if ('dev' !== $environment && 'prod' !== $environment) {
            throw new \InvalidArgumentException();
        }
        
        $command = $this->getApplication()->find('cache:clear');
        
        $arguments = array(
            'command' => 'cache:clear',
            '--env' => $environment
        );

        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }
    
    /**
     * Runs bazingajs's translation dump command.
     * 
     * @param OutputInterface $output
     * @return int
     */
    private function bazinaJsTranslationDumpCommand(OutputInterface $output) {
        $command = $this->getApplication()->find('bazinga:js-translation:dump');

        $arguments = array(
            'command' => 'bazinga:js-translation:dump'
        );

        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }

}
